<?php

use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Pastikan roles exists
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);

    $this->superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin_test_'.time().'@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->superAdmin->assignRole('Super Admin');

    $this->organization = Organization::create([
        'nama' => 'Test Org ' . time(),
        'jenis' => 'UKM',
        'subdomain' => 'testorg'.time(),
        'status' => 'active'
    ]);

    $this->adminOrg = User::create([
        'name' => 'Admin Org',
        'email' => 'adminorg_test_'.time().'@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->organization->id
    ]);
    $this->adminOrg->assignRole('Admin Organisasi');
});

test('super admin can set initial period', function () {
    $response = $this->actingAs($this->superAdmin)->postJson("/api/organizations/{$this->organization->id}/periods", [
        'period_name' => 'Kepengurusan 2026',
        'start_date' => Carbon::now()->toDateString(),
        'end_date' => Carbon::now()->addYear()->toDateString(),
        'notes' => 'Initial setup'
    ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('organization_periods', [
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'status' => 'active' // By SuperAdmin is directly active
    ]);
});

test('admin org can request period renewal', function () {
    // Setup existing active period
    OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => Carbon::now()->subMonths(10)->toDateString(),
        'end_date' => Carbon::now()->addMonths(2)->toDateString(),
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->adminOrg)->postJson("/api/organizations/{$this->organization->id}/periods", [
        'period_name' => 'Kepengurusan 2027',
        'start_date' => Carbon::now()->addMonths(2)->toDateString(),
        'end_date' => Carbon::now()->addMonths(14)->toDateString(),
        'notes' => 'Renewal request'
    ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('organization_periods', [
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2027',
        'status' => 'pending' // Admin org request is pending
    ]);
});

test('super admin can approve renewal', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2027',
        'start_date' => Carbon::now()->addMonths(2)->toDateString(),
        'end_date' => Carbon::now()->addMonths(14)->toDateString(),
        'status' => 'pending'
    ]);

    $response = $this->actingAs($this->superAdmin)->postJson("/api/organization-periods/{$period->id}/approve");

    $response->assertStatus(200);
    
    $this->assertDatabaseHas('organization_periods', [
        'id' => $period->id,
        'status' => 'active'
    ]);
});

test('super admin can reject renewal', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2027',
        'start_date' => Carbon::now()->addMonths(2)->toDateString(),
        'end_date' => Carbon::now()->addMonths(14)->toDateString(),
        'status' => 'pending'
    ]);

    $response = $this->actingAs($this->superAdmin)->postJson("/api/organization-periods/{$period->id}/reject", [
        'reason' => 'Tanggal tidak sesuai'
    ]);

    $response->assertStatus(200);
    
    $this->assertDatabaseHas('organization_periods', [
        'id' => $period->id,
        'status' => 'rejected',
        'rejection_reason' => 'Tanggal tidak sesuai'
    ]);
});

test('artisan command expires periods', function () {
    $period1 = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2025',
        'start_date' => Carbon::now()->subMonths(14)->toDateString(),
        'end_date' => Carbon::now()->subDays(1)->toDateString(), // Expired yesterday
        'status' => 'active'
    ]);

    $period2 = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => Carbon::now()->subMonths(10)->toDateString(),
        'end_date' => Carbon::now()->addDays(1)->toDateString(), // Expires tomorrow
        'status' => 'active'
    ]);

    $this->artisan('organizations:expire-periods')->assertSuccessful();

    $this->assertDatabaseHas('organization_periods', [
        'id' => $period1->id,
        'status' => 'expired'
    ]);

    $this->assertDatabaseHas('organization_periods', [
        'id' => $period2->id,
        'status' => 'active'
    ]);
});
