<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Post;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Media;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationMicrositeTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);

        $this->org = Organization::create([
            'nama' => 'HMPS Teknik Informatika ITI',
            'jenis' => 'HMPS',
            'subdomain' => 'hmps-ti',
            'status' => 'active',
            'warna_tema' => '#00346F',
            'modul_aktif' => [
                'slogan' => 'Code, Create, Collaborate',
                'deskripsi' => 'Himpunan Mahasiswa Program Studi Teknik Informatika Institut Teknologi Indonesia.',
                'deskripsi_lengkap' => 'Profil lengkap HMPS Teknik Informatika ITI dengan visi dan misi terstruktur.',
                'hero_title' => 'HMPS Teknik Informatika',
                'hero_subtitle' => 'Pusat Inovasi, Riset, dan Kreativitas Mahasiswa Informatika ITI',
                'email_publik' => 'hmpsti@iti.ac.id',
                'instagram' => 'hmps_ti_iti',
                'website_eksternal' => 'https://hmpsti.iti.ac.id',
                'seo_title' => 'HMPS Teknik Informatika ITI — Website Resmi',
                'seo_description' => 'Portal resmi HMPS Teknik Informatika Institut Teknologi Indonesia.',
                'posts' => true,
                'agenda' => true,
                'announcements' => true,
                'galeri' => true,
                'documents' => true,
                'structure' => true,
                'public_website_enabled' => true,
            ],
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin HMPS TI',
            'email' => 'admin@hmpsti.test',
            'password' => bcrypt('secret123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->adminUser->assignRole('Admin Organisasi');
    }

    public function test_public_organization_endpoint_returns_all_branding_and_settings_fields(): void
    {
        $response = $this->getJson('/api/public/organizations/hmps-ti');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'nama' => 'HMPS Teknik Informatika ITI',
                'jenis' => 'HMPS',
                'subdomain' => 'hmps-ti',
                'warna_tema' => '#00346F',
                'slogan' => 'Code, Create, Collaborate',
                'deskripsi' => 'Himpunan Mahasiswa Program Studi Teknik Informatika Institut Teknologi Indonesia.',
                'deskripsi_lengkap' => 'Profil lengkap HMPS Teknik Informatika ITI dengan visi dan misi terstruktur.',
                'hero_title' => 'HMPS Teknik Informatika',
                'hero_subtitle' => 'Pusat Inovasi, Riset, dan Kreativitas Mahasiswa Informatika ITI',
                'email_publik' => 'hmpsti@iti.ac.id',
                'instagram' => 'hmps_ti_iti',
                'website_eksternal' => 'https://hmpsti.iti.ac.id',
                'seo_title' => 'HMPS Teknik Informatika ITI — Website Resmi',
                'seo_description' => 'Portal resmi HMPS Teknik Informatika Institut Teknologi Indonesia.',
                'modules' => [
                    'posts' => true,
                    'agenda' => true,
                    'announcements' => true,
                    'galeri' => true,
                    'documents' => true,
                    'structure' => true,
                    'public_website_enabled' => true,
                ],
            ]
        ]);
    }

    public function test_inactive_organization_remains_visible_on_public_portal(): void
    {
        $this->org->update(['status' => 'inactive']);

        $response = $this->getJson('/api/public/organizations/hmps-ti');
        $response->assertStatus(200);
    }

    public function test_disabled_public_website_returns_unavailable_status(): void
    {
        $modul = $this->org->modul_aktif;
        $modul['public_website_enabled'] = false;
        $this->org->update(['modul_aktif' => $modul]);

        $response = $this->getJson('/api/public/organizations/hmps-ti');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'unavailable',
            'data' => [
                'public_website_enabled' => false,
            ]
        ]);

        // Subpages should return 404 when public_website_enabled is false
        $subpageResponse = $this->getJson('/api/public/organizations/hmps-ti/articles');
        $subpageResponse->assertStatus(404);
    }

    public function test_disabled_module_returns_404_on_public_subpage(): void
    {
        $modul = $this->org->modul_aktif;
        $modul['posts'] = false;
        $this->org->update(['modul_aktif' => $modul]);

        $response = $this->getJson('/api/public/organizations/hmps-ti/articles');
        $response->assertStatus(404);
    }

    public function test_public_organization_endpoint_does_not_leak_internal_credentials(): void
    {
        $response = $this->getJson('/api/public/organizations/hmps-ti');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayNotHasKey('password', $json['data']);
        $this->assertArrayNotHasKey('remember_token', $json['data']);
        $this->assertArrayNotHasKey('api_token', $json['data']);
        $this->assertArrayNotHasKey('users', $json['data']);
    }
}
