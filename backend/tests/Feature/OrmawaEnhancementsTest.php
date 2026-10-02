<?php

use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\User;
use App\Models\Post;
use App\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Ensure roles exist
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'Admin Organisasi']);
    Role::firstOrCreate(['name' => 'Editor']);
    Role::firstOrCreate(['name' => 'Kontributor']);
});

test('Task 1: Super admin can activate and deactivate periods with single active period rule', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $org = Organization::create([
        'nama' => 'Himpunan Teknik Informatika',
        'subdomain' => 'hmif',
        'jenis' => 'HMPS',
        'status' => 'active',
    ]);

    $period1 = OrganizationPeriod::create([
        'organization_id' => $org->id,
        'period_name' => 'Periode 2024/2025',
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'status' => 'active',
    ]);

    $period2 = OrganizationPeriod::create([
        'organization_id' => $org->id,
        'period_name' => 'Periode 2025/2026',
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'status' => 'pending',
    ]);

    // Activating period 2 should make period 1 expired
    $response = $this->actingAs($superAdmin, 'sanctum')
        ->postJson("/api/organization-periods/{$period2->id}/activate");

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $period2->id,
                'status' => 'active',
            ]
        ]);

    expect($period1->fresh()->status)->toBe('expired');
    expect($period2->fresh()->status)->toBe('active');

    // Deactivating period 2 sets it to expired
    $deactivateRes = $this->actingAs($superAdmin, 'sanctum')
        ->postJson("/api/organization-periods/{$period2->id}/deactivate");

    $deactivateRes->assertOk();
    expect($period2->fresh()->status)->toBe('expired');
});

test('Task 2: Inactive organization remains visible in public portal & directory', function () {
    $author = User::factory()->create();

    $org = Organization::create([
        'nama' => 'Himpunan Nonaktif',
        'subdomain' => 'hm-nonaktif',
        'jenis' => 'HMPS',
        'status' => 'inactive',
    ]);

    $post = Post::create([
        'organization_id' => $org->id,
        'user_id' => $author->id,
        'judul' => 'Berita Organisasi Nonaktif',
        'slug' => 'berita-organisasi-nonaktif',
        'konten' => '<p>Konten artikel tetap bisa dibaca publik.</p>',
        'excerpt' => 'Ringkasan artikel',
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Public Home includes inactive organization
    $homeRes = $this->getJson('/api/public/home');
    $homeRes->assertOk();
    $orgs = collect($homeRes->json('data.organizations'));
    expect($orgs->contains('subdomain', 'hm-nonaktif'))->toBeTrue();

    // Public Directory includes inactive organization
    $dirRes = $this->getJson('/api/public/organizations?search=hm-nonaktif');
    $dirRes->assertOk();
    $dirOrgs = collect($dirRes->json('data'));
    expect($dirOrgs->contains('subdomain', 'hm-nonaktif'))->toBeTrue();

    // Public Organization profile resolves 200 OK
    $profileRes = $this->getJson('/api/public/organizations/hm-nonaktif');
    $profileRes->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'subdomain' => 'hm-nonaktif',
            ]
        ]);

    // Published article from inactive organization resolves 200 OK
    $artRes = $this->getJson('/api/public/organizations/hm-nonaktif/articles/berita-organisasi-nonaktif');
    $artRes->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'slug' => 'berita-organisasi-nonaktif',
            ]
        ]);
});

test('Task 2: Inactive organization internal user login and dashboard access are blocked (403)', function () {
    $org = Organization::create([
        'nama' => 'Himpunan Inaktif Internal',
        'subdomain' => 'hm-inaktif-internal',
        'jenis' => 'UKM',
        'status' => 'inactive',
    ]);

    $user = User::factory()->create([
        'email' => 'admin@inaktif.test',
        'password' => bcrypt('password123'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $user->assignRole('Admin Organisasi');

    // Login must fail with 403
    $loginRes = $this->postJson('/api/login', [
        'email' => 'admin@inaktif.test',
        'password' => 'password123',
    ]);
    $loginRes->assertStatus(403)
        ->assertJson([
            'message' => 'Organisasi Anda sedang dinonaktifkan oleh Super Admin. Akses dashboard dan manajemen internal tidak tersedia.',
        ]);

    // Internal protected route via token must also be blocked by EnsureActiveOrganization middleware
    $tokenRes = $this->actingAs($user, 'sanctum')
        ->getJson('/api/organization/dashboard');

    $tokenRes->assertStatus(403);
});

test('Task 4: Super admin can customize platform settings & public homepage receives them', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $payload = [
        'campusName' => 'Institut Teknologi Indonesia Kampuk Merdeka',
        'mainDomain' => 'iti.ac.id',
        'defaultStorageLimit' => 50,
        'autoApproveNewOrg' => false,
        'maintenanceMode' => false,
        'heroBadge' => 'KAMPUS INOVASI & TEKNOLOGI',
        'heroTitle' => 'Selamat Datang di Portal Ormawa ITI',
        'heroSubtitle' => 'Wadah resmi kegiatan dan kreativitas mahasiswa.',
        'heroCtaText' => 'Lihat Ormawa',
        'heroCtaLink' => '/organizations',
        'heroSecondaryCtaText' => 'Kabar Kampus',
        'heroSecondaryCtaLink' => '/berita',
        'introBadge' => 'Profil Singkat',
        'introTitle' => 'Tentang Kemahasiswaan ITI',
        'introDescription' => 'Menghubungkan seluruh civitas akademika.',
        'showStatsSection' => true,
        'showIntroSection' => true,
        'showQuickLinks' => true,
        'showLatestArticles' => true,
        'showUpcomingAgenda' => true,
        'showAnnouncements' => true,
    ];

    $res = $this->actingAs($superAdmin, 'sanctum')
        ->putJson('/api/platform-settings', $payload);

    $res->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'heroTitle' => 'Selamat Datang di Portal Ormawa ITI',
                'heroBadge' => 'KAMPUS INOVASI & TEKNOLOGI',
            ]
        ]);

    // Public home endpoint returns the customized platform_settings
    $homeRes = $this->getJson('/api/public/home');
    $homeRes->assertOk();
    expect($homeRes->json('data.platform_settings.heroTitle'))->toBe('Selamat Datang di Portal Ormawa ITI');
    expect($homeRes->json('data.platform_settings.heroBadge'))->toBe('KAMPUS INOVASI & TEKNOLOGI');
});

test('Task 5: Organization public website master switch and module toggles return 404 when disabled', function () {
    $org = Organization::create([
        'nama' => 'Himpunan Modul Test',
        'subdomain' => 'hm-modul-test',
        'jenis' => 'HMPS',
        'status' => 'active',
        'modul_aktif' => [
            'public_website_enabled' => true,
            'posts' => false, // Articles disabled
            'agenda' => true,
        ]
    ]);

    // Articles endpoint should return 404 because posts module is disabled
    $articlesRes = $this->getJson('/api/public/organizations/hm-modul-test/articles');
    $articlesRes->assertStatus(404);

    // Agenda endpoint is enabled so it returns 200 OK
    $agendaRes = $this->getJson('/api/public/organizations/hm-modul-test/agenda');
    $agendaRes->assertOk();

    // Now disable public website completely
    $org->update([
        'modul_aktif' => [
            'public_website_enabled' => false,
        ]
    ]);

    // Main detail returns status unavailable
    $detailRes = $this->getJson('/api/public/organizations/hm-modul-test');
    $detailRes->assertOk()
        ->assertJson([
            'status' => 'unavailable',
        ]);

    // Sub-routes return 404
    $agendaDisabledRes = $this->getJson('/api/public/organizations/hm-modul-test/agenda');
    $agendaDisabledRes->assertStatus(404);
});
