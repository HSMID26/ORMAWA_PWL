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

test('super admin can update period', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
        'notes' => 'Old note'
    ]);

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Kepengurusan 2026 Revisi',
        'start_date' => '2026-01-15',
        'end_date' => '2026-12-31',
        'notes' => 'Updated note'
    ]);

    $response->assertStatus(200)
             ->assertJson([
                 'status' => 'success',
                 'message' => 'Periode berhasil diperbarui.',
                 'data' => [
                     'id' => $period->id,
                     'period_name' => 'Kepengurusan 2026 Revisi',
                     'start_date' => '2026-01-15T00:00:00.000000Z',
                     'end_date' => '2026-12-31T00:00:00.000000Z',
                     'notes' => 'Updated note'
                 ]
             ]);

    $this->assertDatabaseHas('organization_periods', [
        'id' => $period->id,
        'period_name' => 'Kepengurusan 2026 Revisi',
        'notes' => 'Updated note'
    ]);
});

test('admin org cannot update period', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->adminOrg)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Hacked Period',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31'
    ]);

    $response->assertStatus(403);
});

test('editor cannot update period', function () {
    Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
    $editor = User::create([
        'name' => 'Editor User',
        'email' => 'editor_'.time().'@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->organization->id
    ]);
    $editor->assignRole('Editor');

    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($editor)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Edited by Editor',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31'
    ]);

    $response->assertStatus(403);
});

test('kontributor cannot update period', function () {
    Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
    $kontributor = User::create([
        'name' => 'Kontributor User',
        'email' => 'kontributor_'.time().'@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->organization->id
    ]);
    $kontributor->assignRole('Kontributor');

    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($kontributor)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Edited by Kontributor',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31'
    ]);

    $response->assertStatus(403);
});

test('guest cannot update period', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Edited by Guest',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31'
    ]);

    $response->assertStatus(401);
});

test('invalid date start_date greater than end_date is rejected', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-12-31',
        'end_date' => '2026-01-01'
    ]);

    $response->assertStatus(422);
});

test('overlapping active period is rejected', function () {
    // Existing active period 1
    OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    // Period 2 being updated
    $period2 = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2027',
        'start_date' => '2027-01-01',
        'end_date' => '2027-12-31',
        'status' => 'active'
    ]);

    // Attempt to update period 2 so it overlaps with period 1
    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period2->id}", [
        'period_name' => 'Kepengurusan 2027',
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31'
    ]);

    $response->assertStatus(422)
             ->assertJson([
                 'message' => 'Tanggal periode bertabrakan dengan periode lain pada organisasi ini.'
             ]);
});

test('valid non-overlapping update succeeds', function () {
    OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $period2 = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2027',
        'start_date' => '2027-01-01',
        'end_date' => '2027-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period2->id}", [
        'period_name' => 'Kepengurusan 2027 Extended',
        'start_date' => '2027-01-01',
        'end_date' => '2028-01-31'
    ]);

    $response->assertStatus(200);
});

test('activity log records period_update with full metadata', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Kepengurusan 2026 Revisi',
        'start_date' => '2026-01-10',
        'end_date' => '2026-12-25'
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'period_update',
        'module' => 'organization_periods',
        'subject_id' => $period->id,
        'description' => 'Super Admin memperbarui periode organisasi'
    ]);
});

test('update active period does not break organization status', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => Carbon::now()->subMonths(2)->toDateString(),
        'end_date' => Carbon::now()->addMonths(10)->toDateString(),
        'status' => 'active'
    ]);

    $this->assertTrue($this->organization->fresh()->isActive());

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Kepengurusan 2026 Renamed',
        'start_date' => Carbon::now()->subMonths(1)->toDateString(),
        'end_date' => Carbon::now()->addMonths(11)->toDateString()
    ]);

    $response->assertStatus(200);

    $freshOrg = $this->organization->fresh();
    $this->assertEquals('active', $freshOrg->status);
    $this->assertTrue($freshOrg->isActive());
});

test('notification is sent to org admin when active period is updated', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Kepengurusan 2026 Update',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31'
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $this->adminOrg->id,
        'type' => 'period_updated',
        'title' => 'Periode Organisasi Diperbarui'
    ]);
});

test('status cannot be manipulated via update request payload', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->organization->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active'
    ]);

    $response = $this->actingAs($this->superAdmin)->putJson("/api/organization-periods/{$period->id}", [
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'expired' // Injected arbitrary status
    ]);

    $response->assertStatus(200);

    $this->assertEquals('active', $period->fresh()->status);
});
