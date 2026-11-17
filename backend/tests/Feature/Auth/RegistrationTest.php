<?php

use App\Models\Organization;
use App\Models\OrganizationRegistration;
use App\Models\User;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Pastikan roles ada
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
});

test('A. Guest dapat mengirim organization registration (HTTP 201)', function () {
    $response = postJson('/api/register/organization', [
        'organization_name' => 'UKM Catur',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'catur-iti',
        'admin_first_name' => 'Andi',
        'admin_last_name' => 'Saputra',
        'admin_email' => 'andi@catur.com',
        'admin_password' => 'password123',
        'admin_password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.status', 'pending');
    
    // B. Organization dan User BELUM dibuat sebelum approval
    expect(Organization::where('subdomain', 'catur-iti')->exists())->toBeFalse();
    expect(User::where('email', 'andi@catur.com')->exists())->toBeFalse();
    
    // F. Password tidak muncul pada response API
    expect(isset($response->json('data')['admin_password']))->toBeFalse();
});

test('C. Super Admin dapat approve registration', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $registration = OrganizationRegistration::create([
        'organization_name' => 'HMPS Informatika',
        'organization_type' => 'HMPS',
        'organization_subdomain' => 'hmps-if',
        'admin_first_name' => 'Budi',
        'admin_last_name' => 'Santoso',
        'admin_email' => 'budi@if.com',
        'admin_password' => bcrypt('password123'),
        'status' => 'pending',
    ]);

    $response = actingAs($superAdmin)->postJson("/api/organization-registrations/{$registration->id}/approve");

    $response->assertStatus(200);

    expect(Organization::where('subdomain', 'hmps-if')->exists())->toBeTrue();
    expect(User::where('email', 'budi@if.com')->exists())->toBeTrue();

    $newUser = User::where('email', 'budi@if.com')->first();
    expect($newUser->hasRole('Admin Organisasi'))->toBeTrue();

    $registration->refresh();
    expect($registration->status)->toBe('approved');
});

test('D. Super Admin dapat reject registration', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $registration = OrganizationRegistration::create([
        'organization_name' => 'UKM Fiktif',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'fiktif',
        'admin_first_name' => 'Fiktif',
        'admin_last_name' => 'Fiktif',
        'admin_email' => 'fiktif@ukm.com',
        'admin_password' => bcrypt('password123'),
        'status' => 'pending',
    ]);

    $response = actingAs($superAdmin)->postJson("/api/organization-registrations/{$registration->id}/reject", [
        'reason' => 'Tidak valid'
    ]);

    $response->assertStatus(200);

    expect(Organization::where('subdomain', 'fiktif')->exists())->toBeFalse();
    expect(User::where('email', 'fiktif@ukm.com')->exists())->toBeFalse();

    $registration->refresh();
    expect($registration->status)->toBe('rejected');
    expect($registration->rejection_reason)->toBe('Tidak valid');
});

test('E. Duplicate email/subdomain ditolak (Validasi Registrasi)', function () {
    OrganizationRegistration::create([
        'organization_name' => 'UKM Lama',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'lama-iti',
        'admin_first_name' => 'Lama',
        'admin_last_name' => 'User',
        'admin_email' => 'lama@iti.com',
        'admin_password' => bcrypt('password123'),
        'status' => 'pending',
    ]);

    $response = postJson('/api/register/organization', [
        'organization_name' => 'UKM Baru',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'lama-iti', // Duplicate Subdomain
        'admin_first_name' => 'Baru',
        'admin_last_name' => 'User',
        'admin_email' => 'lama@iti.com', // Duplicate Email
        'admin_password' => 'password123',
        'admin_password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['organization_subdomain', 'admin_email']);
});

test('Guest tidak dapat approve/reject registration', function () {
    $registration = OrganizationRegistration::create([
        'organization_name' => 'Guest Test',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'guest-test',
        'admin_first_name' => 'Guest',
        'admin_last_name' => 'Test',
        'admin_email' => 'guest@test.com',
        'admin_password' => bcrypt('password123'),
        'status' => 'pending',
    ]);

    postJson("/api/organization-registrations/{$registration->id}/approve")->assertStatus(401);
    postJson("/api/organization-registrations/{$registration->id}/reject", ['reason' => 'test'])->assertStatus(401);
});

test('Admin Organisasi tidak dapat approve/reject registration', function () {
    $adminOrg = User::factory()->create();
    $adminOrg->assignRole('Admin Organisasi');

    $registration = OrganizationRegistration::create([
        'organization_name' => 'Admin Org Test',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'adminorg-test',
        'admin_first_name' => 'Admin Org',
        'admin_last_name' => 'Test',
        'admin_email' => 'adminorg@test.com',
        'admin_password' => bcrypt('password123'),
        'status' => 'pending',
    ]);


    actingAs($adminOrg)->postJson("/api/organization-registrations/{$registration->id}/approve")->assertStatus(403);
    actingAs($adminOrg)->postJson("/api/organization-registrations/{$registration->id}/reject", ['reason' => 'test'])->assertStatus(403);
});

test('Kontributor tidak dapat approve/reject registration', function () {
    $kontributor = User::factory()->create();
    $kontributor->assignRole('Kontributor');

    $registration = OrganizationRegistration::create([
        'organization_name' => 'Kontributor Test',
        'organization_type' => 'UKM',
        'organization_subdomain' => 'kontributor-test',
        'admin_first_name' => 'Kontributor',
        'admin_last_name' => 'Test',
        'admin_email' => 'kontributor@test.com',
        'admin_password' => bcrypt('password123'),
        'status' => 'pending',
    ]);

    actingAs($kontributor)->postJson("/api/organization-registrations/{$registration->id}/approve")->assertStatus(403);
    actingAs($kontributor)->postJson("/api/organization-registrations/{$registration->id}/reject", ['reason' => 'test'])->assertStatus(403);
});
