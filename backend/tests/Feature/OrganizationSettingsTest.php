<?php

use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // Create required roles & permissions
    $managePerm = Permission::firstOrCreate(['name' => 'organizations.manage', 'guard_name' => 'web']);
    
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $orgAdminRole = Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
    $editorRole = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);

    $orgAdminRole->givePermissionTo($managePerm);

    $this->orgA = Organization::create([
        'nama' => 'Himpunan Mahasiswa Informatika',
        'jenis' => 'HMPS',
        'subdomain' => 'hmif',
        'email' => 'hmif@iti.ac.id',
        'telepon' => '081234567890',
        'alamat' => 'Gedung PKM Lt. 2 ITI',
        'warna_tema' => '#1d4ed8',
        'status' => 'active',
        'media_sosial' => [
            'instagram' => 'hmif_iti',
            'youtube' => 'https://youtube.com/@hmif_iti',
            'website' => 'https://hmif.iti.ac.id'
        ],
        'label_menu' => [
            'posts' => 'Warta Informatika',
            'agenda' => 'Kalender Kegiatan'
        ],
        'modul_aktif' => [
            'posts' => true,
            'agenda' => true,
            'public_website_enabled' => true,
            'slogan' => 'Inovasi Tanpa Batas',
            'deskripsi' => 'Himpunan mahasiswa informatika kampus ITI.',
        ],
        'ga_tracking_id' => 'G-ABC1234567'
    ]);

    $this->orgB = Organization::create([
        'nama' => 'Unit Kegiatan Robotika',
        'jenis' => 'UKM',
        'subdomain' => 'robotika',
        'email' => 'robotika@iti.ac.id',
        'status' => 'active',
        'warna_tema' => '#b91c1c',
    ]);

    $this->userOrgA = User::factory()->create([
        'organization_id' => $this->orgA->id,
        'status' => 'active'
    ]);
    $this->userOrgA->assignRole($orgAdminRole);

    $this->userOrgB = User::factory()->create([
        'organization_id' => $this->orgB->id,
        'status' => 'active'
    ]);
    $this->userOrgB->assignRole($orgAdminRole);

    $this->editorUser = User::factory()->create([
        'organization_id' => $this->orgA->id,
        'status' => 'active'
    ]);
    $this->editorUser->assignRole($editorRole);

    $this->superAdmin = User::factory()->create([
        'status' => 'active'
    ]);
    $this->superAdmin->assignRole($superAdminRole);
});

test('organization admin can view its own organization settings', function () {
    $response = $this->actingAs($this->userOrgA)
        ->getJson("/api/organizations/{$this->orgA->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $this->orgA->id,
                'nama' => 'Himpunan Mahasiswa Informatika',
                'subdomain' => 'hmif',
                'email' => 'hmif@iti.ac.id',
                'telepon' => '081234567890',
                'alamat' => 'Gedung PKM Lt. 2 ITI',
                'warna_tema' => '#1d4ed8',
                'ga_tracking_id' => 'G-ABC1234567',
                'media_sosial' => [
                    'instagram' => 'hmif_iti',
                    'youtube' => 'https://youtube.com/@hmif_iti',
                    'website' => 'https://hmif.iti.ac.id'
                ],
                'label_menu' => [
                    'posts' => 'Warta Informatika',
                    'agenda' => 'Kalender Kegiatan'
                ],
            ]
        ]);
});

test('organization admin can update organization settings', function () {
    $payload = [
        'warna_tema' => '#047857',
        'email' => 'sekretariat.hmif@iti.ac.id',
        'telepon' => '089876543210',
        'alamat' => 'Gedung Rektorat Lt. 1 ITI',
        'media_sosial' => [
            'instagram' => 'hmif_iti_official',
            'tiktok' => '@hmifiti',
            'website' => 'https://hmifiti.com',
            'whatsapp' => 'https://wa.me/6289876543210'
        ],
        'label_menu' => [
            'posts' => 'Rilis Berita',
            'agenda' => 'Agenda Akbar',
            'galeri' => 'Dokumentasi Visual'
        ],
        'ga_tracking_id' => 'G-XYZ9876543',
        'modul_aktif' => [
            'posts' => true,
            'agenda' => true,
            'galeri' => false,
            'slogan' => 'Sinergi dan Berkarya',
            'deskripsi' => 'Portal resmi HMIF ITI terbaru.',
            'public_website_enabled' => true,
        ]
    ];

    $response = $this->actingAs($this->userOrgA)
        ->putJson("/api/organizations/{$this->orgA->id}", $payload);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Data organisasi berhasil diperbarui!'
        ]);

    $this->orgA->refresh();
    expect($this->orgA->warna_tema)->toBe('#047857');
    expect($this->orgA->email)->toBe('sekretariat.hmif@iti.ac.id');
    expect($this->orgA->telepon)->toBe('089876543210');
    expect($this->orgA->alamat)->toBe('Gedung Rektorat Lt. 1 ITI');
    expect($this->orgA->ga_tracking_id)->toBe('G-XYZ9876543');
    expect($this->orgA->media_sosial['instagram'])->toBe('hmif_iti_official');
    expect($this->orgA->label_menu['posts'])->toBe('Rilis Berita');
    expect($this->orgA->modul_aktif['slogan'])->toBe('Sinergi dan Berkarya');
});

test('organization admin can upload logo successfully', function () {
    $file = UploadedFile::fake()->image('logo.png', 400, 400);

    $response = $this->actingAs($this->userOrgA)
        ->postJson("/api/organizations/{$this->orgA->id}/upload-logo", [
            'logo' => $file
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Logo organisasi berhasil diperbarui!'
        ]);

    $this->orgA->refresh();
    expect($this->orgA->logo)->not->toBeNull();
    expect($this->orgA->logo)->toStartWith('/storage/organizations/logos/');

    $storedPath = str_replace('/storage/', '', $this->orgA->logo);
    Storage::disk('public')->assertExists($storedPath);
});

test('organization admin can upload hero image successfully', function () {
    $file = UploadedFile::fake()->image('hero_banner.jpg', 1920, 1080);

    $response = $this->actingAs($this->userOrgA)
        ->postJson("/api/organizations/{$this->orgA->id}/upload-hero", [
            'hero_image' => $file
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Foto hero website berhasil diperbarui!'
        ]);

    $this->orgA->refresh();
    expect($this->orgA->hero_image)->not->toBeNull();
    expect($this->orgA->hero_image)->toStartWith('/storage/organizations/heroes/');

    $storedPath = str_replace('/storage/', '', $this->orgA->hero_image);
    Storage::disk('public')->assertExists($storedPath);
});

test('organization admin can remove hero image and fallback smoothly', function () {
    $file = UploadedFile::fake()->image('hero_old.jpg', 1920, 1080);
    $path = $file->store('organizations/heroes', 'public');
    $this->orgA->update(['hero_image' => '/storage/' . $path]);

    Storage::disk('public')->assertExists($path);

    $response = $this->actingAs($this->userOrgA)
        ->deleteJson("/api/organizations/{$this->orgA->id}/hero");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'hero_image' => null
        ]);

    $this->orgA->refresh();
    expect($this->orgA->hero_image)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('tenant isolation: organization admin A cannot modify organization B settings', function () {
    $response = $this->actingAs($this->userOrgA)
        ->putJson("/api/organizations/{$this->orgB->id}", [
            'warna_tema' => '#000000'
        ]);

    $response->assertStatus(403);
});

test('tenant isolation: organization admin A cannot upload hero to organization B', function () {
    $file = UploadedFile::fake()->image('hero.jpg', 1200, 600);

    $response = $this->actingAs($this->userOrgA)
        ->postJson("/api/organizations/{$this->orgB->id}/upload-hero", [
            'hero_image' => $file
        ]);

    $response->assertStatus(403);
});

test('unauthorized user without manage permission cannot update settings', function () {
    $response = $this->actingAs($this->editorUser)
        ->putJson("/api/organizations/{$this->orgA->id}", [
            'warna_tema' => '#123456'
        ]);

    $response->assertStatus(403);
});

test('public endpoint exposes full organization configuration and hero image', function () {
    $this->orgA->update([
        'hero_image' => '/storage/organizations/heroes/test_hero.jpg'
    ]);

    $response = $this->getJson("/api/public/organizations/{$this->orgA->subdomain}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $this->orgA->id,
                'nama' => 'Himpunan Mahasiswa Informatika',
                'subdomain' => 'hmif',
                'hero_image' => '/storage/organizations/heroes/test_hero.jpg',
                'warna_tema' => '#1d4ed8',
                'email' => 'hmif@iti.ac.id',
                'telepon' => '081234567890',
                'alamat' => 'Gedung PKM Lt. 2 ITI',
                'ga_tracking_id' => 'G-ABC1234567',
                'label_menu' => [
                    'posts' => 'Warta Informatika',
                    'agenda' => 'Kalender Kegiatan'
                ],
                'modules' => [
                    'posts' => true,
                    'agenda' => true,
                    'public_website_enabled' => true,
                ]
            ]
        ]);
});

test('public endpoint returns unavailable state when public website is disabled', function () {
    $this->orgA->update([
        'modul_aktif' => [
            'public_website_enabled' => false
        ]
    ]);

    $response = $this->getJson("/api/public/organizations/{$this->orgA->subdomain}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'unavailable',
            'data' => [
                'nama' => 'Himpunan Mahasiswa Informatika',
                'public_website_enabled' => false
            ]
        ]);
});

test('public module route returns 404 when module is disabled', function () {
    $this->orgA->update([
        'modul_aktif' => [
            'galeri' => false,
            'public_website_enabled' => true
        ]
    ]);

    $response = $this->getJson("/api/public/organizations/{$this->orgA->subdomain}/gallery");

    $response->assertStatus(404);
});
