<?php

use App\Models\Committee;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

    $timestamp = microtime(true);

    $this->org1 = Organization::create([
        'nama' => 'HIMATIF ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'himatif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->org2 = Organization::create([
        'nama' => 'BEM ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'bem' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->superAdmin = User::create([
        'name' => 'Super Admin PKA',
        'email' => 'superadmin_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'status' => 'active',
    ]);
    $this->superAdmin->assignRole('Super Admin');

    $this->adminOrg1 = User::create([
        'name' => 'Admin HIMATIF',
        'email' => 'admin_himatif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);
    $this->adminOrg1->assignRole('Admin Organisasi');

    $this->adminOrg2 = User::create([
        'name' => 'Admin BEM',
        'email' => 'admin_bem_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org2->id,
        'status' => 'active',
    ]);
    $this->adminOrg2->assignRole('Admin Organisasi');

    $this->editor1 = User::create([
        'name' => 'Editor HIMATIF',
        'email' => 'editor_himatif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);
    $this->editor1->assignRole('Editor');

    $this->contributor1 = User::create([
        'name' => 'Kontributor HIMATIF',
        'email' => 'kontrib_himatif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);
    $this->contributor1->assignRole('Kontributor');

    $this->inactiveUser1 = User::create([
        'name' => 'Inactive User HIMATIF',
        'email' => 'inactive_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'inactive',
    ]);
    $this->inactiveUser1->assignRole('Editor');

    $this->userNoOrg = User::create([
        'name' => 'User Without Org',
        'email' => 'noorg_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => null,
        'status' => 'active',
    ]);
});

// TEST 10: Guest gets 401
test('guest cannot access committee CRUD or linkable users', function () {
    $this->postJson('/api/committees', [
        'name' => 'Test Pengurus',
        'position' => 'Ketua',
        'period' => '2026/2027',
    ])->assertStatus(401);

    $this->getJson('/api/organization-users/linkable')->assertStatus(401);
});

// TEST 9: Editor & Contributor cannot CRUD committee (403)
test('editor and contributor cannot create committee', function () {
    // Editor -> 403
    $this->actingAs($this->editor1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Pengurus Editor',
            'position' => 'Divisi Media',
            'period' => '2026/2027',
        ])->assertStatus(403);

    // Contributor -> 403
    $this->actingAs($this->contributor1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Pengurus Contributor',
            'position' => 'Anggota',
            'period' => '2026/2027',
        ])->assertStatus(403);
});

// TEST 1, 2, 3, 4, 5: Linkable users filtering
test('linkable users endpoint returns only active non-superadmin users of authenticated organization', function () {
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->getJson('/api/organization-users/linkable');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'name', 'email', 'role', 'organization_id', 'status', 'is_already_linked']
            ]
        ]);

    $userIds = collect($response->json('data'))->pluck('id')->all();

    // TEST 1: Org 1 users appear
    expect($userIds)->toContain($this->adminOrg1->id);
    expect($userIds)->toContain($this->editor1->id);
    expect($userIds)->toContain($this->contributor1->id);

    // TEST 2: Org 2 users DO NOT appear
    expect($userIds)->not->toContain($this->adminOrg2->id);

    // TEST 3: Super Admin DOES NOT appear
    expect($userIds)->not->toContain($this->superAdmin->id);

    // TEST 4: User without org DOES NOT appear
    expect($userIds)->not->toContain($this->userNoOrg->id);

    // TEST 5: Inactive user DOES NOT appear
    expect($userIds)->not->toContain($this->inactiveUser1->id);
});

// TEST 6: Admin Org cannot use user from another org (422)
test('admin cannot create committee using user from another organization', function () {
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Ketua Ilegal',
            'position' => 'Ketua',
            'period' => '2026',
            'user_id' => $this->adminOrg2->id, // User from Org 2
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Akun yang dipilih tidak dapat ditautkan ke organisasi ini.');
});

// TEST 7: Admin Org cannot use Super Admin as user_id (422)
test('admin cannot create committee linking to super admin', function () {
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Ketua Super',
            'position' => 'Ketua',
            'period' => '2026',
            'user_id' => $this->superAdmin->id, // Super Admin
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Akun Super Admin tidak dapat ditautkan sebagai pengurus organisasi.');
});

// TEST 8: Admin Org cannot use fake organization_id to bypass tenant isolation
test('admin cannot bypass tenant isolation using spoofed organization_id', function () {
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Spoofed Org Committee',
            'position' => 'Ketua',
            'period' => '2026',
            'organization_id' => $this->org2->id, // Attempt to create on org 2
        ]);

    $response->assertStatus(201);
    $committeeId = $response->json('data.id');

    // Must be assigned to org 1 (authenticated user's org), NOT org 2
    $this->assertDatabaseHas('committees', [
        'id' => $committeeId,
        'organization_id' => $this->org1->id,
    ]);
});

// TEST 11: Duplicate linking prevention within the same period
test('duplicate linking prevention: cannot link same user twice in the same period', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->org1->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ]);

    // Create 1st committee linked to editor1
    $c1 = Committee::create([
        'organization_id' => $this->org1->id,
        'organization_period_id' => $period->id,
        'name' => 'Ketua Divisi Media',
        'position' => 'Kadiv',
        'period' => 'Kepengurusan 2026',
        'user_id' => $this->editor1->id,
        'status' => 'active',
    ]);

    // Attempt to create 2nd committee in same period with same editor1 -> 422
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Sekretaris Media',
            'position' => 'Sekretaris',
            'organization_period_id' => $period->id,
            'user_id' => $this->editor1->id,
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Akun ini sudah ditautkan ke pengurus lain pada periode tersebut.');
});

// TEST 12: Edit committee can keep its currently linked user
test('edit committee can maintain its currently linked user without duplicate error', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->org1->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ]);

    $c1 = Committee::create([
        'organization_id' => $this->org1->id,
        'organization_period_id' => $period->id,
        'name' => 'Ketua Divisi Media',
        'position' => 'Kadiv',
        'period' => 'Kepengurusan 2026',
        'user_id' => $this->editor1->id,
        'status' => 'active',
    ]);

    // Update same committee with same user_id -> 200 OK
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson("/api/committees/{$c1->id}", [
            'name' => 'Ketua Divisi Media (Updated)',
            'organization_period_id' => $period->id,
            'user_id' => $this->editor1->id,
        ]);

    $response->assertStatus(200);
});

// TEST 13: Unlink account does not delete user from database
test('unlinking account only removes committee user_id and preserves user record', function () {
    $c = Committee::create([
        'organization_id' => $this->org1->id,
        'name' => 'Pengurus Demo',
        'position' => 'Staf',
        'period' => '2026',
        'user_id' => $this->editor1->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson("/api/committees/{$c->id}", [
            'user_id' => null, // Unlink
        ]);

    $response->assertStatus(200);
    $c->refresh();
    expect($c->user_id)->toBeNull();

    // User must still exist in users table
    $this->assertDatabaseHas('users', [
        'id' => $this->editor1->id,
        'email' => $this->editor1->email,
    ]);
});

// TEST 14 & 15: Linking account does NOT change user's role or organization_id
test('linking account to committee does not alter user role, permissions, or organization_id', function () {
    $initialRole = $this->editor1->roles->first()->name;
    $initialOrgId = $this->editor1->organization_id;

    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/committees', [
            'name' => 'Ketua Umum',
            'position' => 'Ketua Umum',
            'period' => '2026',
            'user_id' => $this->editor1->id,
        ]);

    $response->assertStatus(201);

    // Refresh user from database
    $this->editor1->refresh();

    // TEST 14: Role remains 'Editor'
    expect($this->editor1->roles->first()->name)->toBe($initialRole);

    // TEST 15: Organization ID remains the same
    expect($this->editor1->organization_id)->toBe($initialOrgId);
});

// Tenant Isolation between Org 1 and Org 2
test('tenant isolation: admin org1 cannot view or modify committee of org2', function () {
    $cOrg2 = Committee::create([
        'organization_id' => $this->org2->id,
        'name' => 'BEM President',
        'position' => 'Presiden BEM',
        'period' => '2026',
        'status' => 'active',
    ]);

    $listResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->getJson('/api/committees');

    $listResponse->assertStatus(200);
    $ids = collect($listResponse->json('data'))->pluck('id')->all();
    expect($ids)->not->toContain($cOrg2->id);

    $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson("/api/committees/{$cOrg2->id}", [
            'name' => 'Hacked Name',
        ])->assertStatus(404);

    $this->actingAs($this->adminOrg1, 'sanctum')
        ->deleteJson("/api/committees/{$cOrg2->id}")
        ->assertStatus(404);
});

// Super Admin can view committees across organizations
test('super admin can view and manage committees across organizations', function () {
    $cOrg1 = Committee::create([
        'organization_id' => $this->org1->id,
        'name' => 'Ketua HIMATIF',
        'position' => 'Ketua',
        'period' => '2026',
        'status' => 'active',
    ]);

    $cOrg2 = Committee::create([
        'organization_id' => $this->org2->id,
        'name' => 'Ketua BEM',
        'position' => 'Ketua',
        'period' => '2026',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->superAdmin, 'sanctum')
        ->getJson('/api/committees');

    $response->assertStatus(200);
    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toContain($cOrg1->id);
    expect($ids)->toContain($cOrg2->id);
});
