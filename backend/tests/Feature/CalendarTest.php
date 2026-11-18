<?php

use App\Http\Controllers\Api\RoleController;
use App\Models\Activity;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    RoleController::ensureDefaultPermissions();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $suffix = str_replace('.', '', (string) microtime(true));
    $this->org = Organization::create([
        'nama' => 'Organisasi Kalender ' . $suffix,
        'jenis' => 'HMPS',
        'subdomain' => 'calendar' . $suffix,
        'status' => 'active',
    ]);
    $this->otherOrg = Organization::create([
        'nama' => 'Organisasi Kalender Lain ' . $suffix,
        'jenis' => 'HMPS',
        'subdomain' => 'othercalendar' . $suffix,
        'status' => 'active',
    ]);
});

test('calendar is available to contributors and only shows published tenant activities', function () {
    $contributor = User::create([
        'name' => 'Kontributor',
        'email' => 'contributor-' . uniqid() . '@example.com',
        'password' => 'password',
        'organization_id' => $this->org->id,
        'status' => 'active',
    ]);
    $contributor->assignRole('Kontributor');

    Activity::create([
        'organization_id' => $this->org->id,
        'user_id' => $contributor->id,
        'judul' => 'Kegiatan Terbit',
        'deskripsi' => 'Dapat dilihat contributor.',
        'tanggal_pelaksanaan' => '2026-11-20',
        'status' => 'published',
    ]);
    Activity::create([
        'organization_id' => $this->org->id,
        'user_id' => $contributor->id,
        'judul' => 'Draf Internal',
        'deskripsi' => 'Tidak boleh muncul untuk contributor.',
        'tanggal_pelaksanaan' => '2026-11-21',
        'status' => 'draft',
    ]);
    Activity::create([
        'organization_id' => $this->otherOrg->id,
        'user_id' => $contributor->id,
        'judul' => 'Kegiatan Organisasi Lain',
        'deskripsi' => 'Tidak boleh lintas tenant.',
        'tanggal_pelaksanaan' => '2026-11-22',
        'status' => 'published',
    ]);

    $this->actingAs($contributor, 'sanctum')
        ->getJson('/api/calendar/events?start=2026-11-01&end=2026-12-01')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Kegiatan Terbit')
        ->assertJsonCount(1, 'data');
});

test('calendar validates date ranges', function () {
    $contributor = User::create([
        'name' => 'Kontributor',
        'email' => 'contributor-' . uniqid() . '@example.com',
        'password' => 'password',
        'organization_id' => $this->org->id,
        'status' => 'active',
    ]);
    $contributor->assignRole('Kontributor');

    $this->actingAs($contributor, 'sanctum')
        ->getJson('/api/calendar/events?start=2026-12-01&end=2026-11-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end');
});

test('super admin calendar includes activities across organizations', function () {
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super-admin-' . uniqid() . '@example.com',
        'password' => 'password',
        'status' => 'active',
    ]);
    $superAdmin->assignRole('Super Admin');

    Activity::create([
        'organization_id' => $this->org->id,
        'user_id' => $superAdmin->id,
        'judul' => 'Agenda Organisasi Pertama',
        'deskripsi' => 'Agenda internal.',
        'tanggal_pelaksanaan' => '2026-11-20',
        'status' => 'draft',
    ]);
    Activity::create([
        'organization_id' => $this->otherOrg->id,
        'user_id' => $superAdmin->id,
        'judul' => 'Agenda Organisasi Kedua',
        'deskripsi' => 'Agenda lintas organisasi.',
        'tanggal_pelaksanaan' => '2026-11-21',
        'status' => 'review',
    ]);

    $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/calendar/events?start=2026-11-01&end=2026-12-01')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.extendedProps.organization', $this->org->nama)
        ->assertJsonPath('data.1.extendedProps.organization', $this->otherOrg->nama);
});
