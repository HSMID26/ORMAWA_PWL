<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FooterConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminOrgA;
    protected User $adminOrgB;
    protected User $editorOrgA;
    protected User $contributorOrgA;
    protected Organization $orgA;
    protected Organization $orgB;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'platform_settings.manage', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'organizations.manage', 'guard_name' => 'web']);

        $roleAdminOrg = Role::findByName('Admin Organisasi', 'web');
        $roleAdminOrg->givePermissionTo('organizations.manage');

        $this->orgA = Organization::create([
            'nama' => 'Himpunan Mahasiswa Informatika',
            'jenis' => 'HMPS',
            'subdomain' => 'hmif',
            'status' => 'active',
            'email' => 'hmif@iti.ac.id',
            'telepon' => '081234567891',
            'alamat' => 'Sekretariat HMIF Gedung PKM Lt. 2',
            'media_sosial' => [
                'instagram' => 'https://instagram.com/hmif_iti',
                'facebook' => 'https://facebook.com/hmif.iti',
                'youtube' => 'https://youtube.com/@hmif_iti',
                'tiktok' => 'https://tiktok.com/@hmif_iti',
                'website' => 'https://hmif.iti.ac.id',
                'whatsapp' => '081234567891',
            ],
            'modul_aktif' => [
                'slogan' => 'Informatika Mengabdi untuk Bangsa',
                'deskripsi' => 'Himpunan Mahasiswa Informatika ITI.',
                'copyright' => 'HMIF ITI. Hak Cipta Dilindungi.',
                'footer_description' => 'Himpunan resmi mahasiswa informatika ITI.',
                'posts' => true,
                'agenda' => true,
                'announcements' => true,
                'galeri' => true,
                'documents' => true,
                'structure' => true,
                'public_website_enabled' => true,
            ],
        ]);

        $this->orgB = Organization::create([
            'nama' => 'UKM Robotik ITI',
            'jenis' => 'UKM',
            'subdomain' => 'robotik',
            'status' => 'active',
            'email' => 'robotik@iti.ac.id',
            'telepon' => '081234567892',
            'alamat' => 'Sekretariat Robotik Gedung PKM Lt. 1',
            'media_sosial' => [
                'instagram' => 'https://instagram.com/robotik_iti',
            ],
            'modul_aktif' => [
                'slogan' => 'Innovate and Automate',
                'posts' => true,
            ],
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@iti.test',
            'password' => bcrypt('password123'),
            'organization_id' => null,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->adminOrgA = User::create([
            'name' => 'Admin HMIF',
            'email' => 'admin@hmif.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->adminOrgA->assignRole('Admin Organisasi');

        $this->adminOrgB = User::create([
            'name' => 'Admin Robotik',
            'email' => 'admin@robotik.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgB->id,
            'status' => 'active',
        ]);
        $this->adminOrgB->assignRole('Admin Organisasi');

        $this->editorOrgA = User::create([
            'name' => 'Editor HMIF',
            'email' => 'editor@hmif.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->editorOrgA->assignRole('Editor');

        $this->contributorOrgA = User::create([
            'name' => 'Kontributor HMIF',
            'email' => 'contributor@hmif.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->contributorOrgA->assignRole('Kontributor');
    }

    public function test_guest_can_fetch_public_platform_settings_with_footer(): void
    {
        $res = $this->getJson('/api/public/platform-settings');
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'status',
            'data' => [
                'campusName',
                'footerDescription',
                'footerAddress',
                'footerEmail',
                'footerPhone',
                'footerWhatsapp',
                'footerInstagram',
                'footerFacebook',
                'footerYoutube',
                'footerTiktok',
                'footerWebsite',
                'footerCopyright',
            ],
        ]);
    }

    public function test_super_admin_can_update_platform_footer_settings(): void
    {
        $token = $this->superAdmin->createToken('super-token')->plainTextToken;

        $payload = [
            'campusName' => 'Institut Teknologi Indonesia',
            'mainDomain' => 'iti.ac.id',
            'defaultStorageLimit' => 25,
            'autoApproveNewOrg' => true,
            'maintenanceMode' => false,
            'footerDescription' => 'Pusat tata kelola dan kegiatan ormawa kampus ITI.',
            'footerAddress' => 'Gedung Rektorat ITI, Jl. Raya Puspiptek Serpong',
            'footerEmail' => 'kemahasiswaan@iti.ac.id',
            'footerPhone' => '021-7561000',
            'footerWhatsapp' => '081122334455',
            'footerInstagram' => 'https://instagram.com/kampus_iti',
            'footerFacebook' => 'https://facebook.com/kampusiti',
            'footerYoutube' => 'https://youtube.com/@kampusiti',
            'footerTiktok' => 'https://tiktok.com/@kampus_iti',
            'footerWebsite' => 'https://iti.ac.id',
            'footerCopyright' => 'Hak Cipta Terpelihara Institut Teknologi Indonesia.',
        ];

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/platform-settings', $payload);

        $res->assertStatus(200);
        $res->assertJsonPath('data.footerDescription', 'Pusat tata kelola dan kegiatan ormawa kampus ITI.');
        $res->assertJsonPath('data.footerEmail', 'kemahasiswaan@iti.ac.id');
        $res->assertJsonPath('data.footerCopyright', 'Hak Cipta Terpelihara Institut Teknologi Indonesia.');

        // Verify public home endpoint returns updated settings
        $homeRes = $this->getJson('/api/public/home');
        $homeRes->assertStatus(200);
        $homeRes->assertJsonPath('data.platform_settings.footerDescription', 'Pusat tata kelola dan kegiatan ormawa kampus ITI.');
        $homeRes->assertJsonPath('data.platform_settings.footerEmail', 'kemahasiswaan@iti.ac.id');
    }

    public function test_org_admin_cannot_update_platform_settings(): void
    {
        $token = $this->adminOrgA->createToken('org-token')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/platform-settings', [
                'campusName' => 'Institut Teknologi Indonesia',
                'mainDomain' => 'iti.ac.id',
                'defaultStorageLimit' => 20,
                'autoApproveNewOrg' => false,
                'maintenanceMode' => false,
            ]);

        $res->assertStatus(403);
    }

    public function test_organization_public_endpoint_returns_org_specific_footer_data(): void
    {
        $res = $this->getJson('/api/public/organizations/hmif');
        $res->assertStatus(200);
        $res->assertJsonPath('data.nama', 'Himpunan Mahasiswa Informatika');
        $res->assertJsonPath('data.email', 'hmif@iti.ac.id');
        $res->assertJsonPath('data.telepon', '081234567891');
        $res->assertJsonPath('data.alamat', 'Sekretariat HMIF Gedung PKM Lt. 2');
        $res->assertJsonPath('data.instagram', 'https://instagram.com/hmif_iti');
        $res->assertJsonPath('data.facebook', 'https://facebook.com/hmif.iti');
        $res->assertJsonPath('data.youtube', 'https://youtube.com/@hmif_iti');
        $res->assertJsonPath('data.tiktok', 'https://tiktok.com/@hmif_iti');
        $res->assertJsonPath('data.copyright', 'HMIF ITI. Hak Cipta Dilindungi.');
    }

    public function test_org_admin_can_update_own_organization_footer(): void
    {
        $token = $this->adminOrgA->createToken('org-token')->plainTextToken;

        $payload = [
            'email' => 'humas.hmif@iti.ac.id',
            'telepon' => '089988776655',
            'alamat' => 'Gedung Laboratorium Komputer Lt. 3 Kampus ITI',
            'media_sosial' => [
                'instagram' => 'https://instagram.com/hmif.official',
                'facebook' => 'https://facebook.com/hmif.official',
                'youtube' => 'https://youtube.com/@hmif_official',
                'tiktok' => 'https://tiktok.com/@hmif_official',
                'website' => 'https://hmif-iti.id',
                'whatsapp' => '089988776655',
            ],
            'modul_aktif' => [
                'slogan' => 'Slogan Baru Informatika',
                'deskripsi' => 'Deskripsi Baru Singkat',
                'copyright' => 'Hak Cipta 2026 HMIF ITI.',
                'footer_description' => 'Wadah resmi kegiatan mahasiswa teknik informatika.',
                'posts' => true,
                'agenda' => true,
                'public_website_enabled' => true,
            ],
        ];

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/organizations/{$this->orgA->id}", $payload);

        $res->assertStatus(200);

        // Verify public endpoint reflects changes for HMIF
        $pubRes = $this->getJson('/api/public/organizations/hmif');
        $pubRes->assertStatus(200);
        $pubRes->assertJsonPath('data.email', 'humas.hmif@iti.ac.id');
        $pubRes->assertJsonPath('data.telepon', '089988776655');
        $pubRes->assertJsonPath('data.alamat', 'Gedung Laboratorium Komputer Lt. 3 Kampus ITI');
        $pubRes->assertJsonPath('data.instagram', 'https://instagram.com/hmif.official');
        $pubRes->assertJsonPath('data.facebook', 'https://facebook.com/hmif.official');
        $pubRes->assertJsonPath('data.youtube', 'https://youtube.com/@hmif_official');
        $pubRes->assertJsonPath('data.copyright', 'Hak Cipta 2026 HMIF ITI.');

        // Verify Robotik is untouched
        $robotikRes = $this->getJson('/api/public/organizations/robotik');
        $robotikRes->assertStatus(200);
        $robotikRes->assertJsonPath('data.email', 'robotik@iti.ac.id');
        $robotikRes->assertJsonPath('data.instagram', 'https://instagram.com/robotik_iti');
        $this->assertNotEquals('humas.hmif@iti.ac.id', $robotikRes->json('data.email'));

        // Verify Platform Settings are untouched
        $platformRes = $this->getJson('/api/public/platform-settings');
        $platformRes->assertStatus(200);
        $this->assertNotEquals('humas.hmif@iti.ac.id', $platformRes->json('data.footerEmail'));
    }

    public function test_org_admin_cannot_update_other_organization(): void
    {
        $token = $this->adminOrgA->createToken('org-token')->plainTextToken;

        // Admin Org A tries to edit Org B (Robotik)
        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/organizations/{$this->orgB->id}", [
                'email' => 'hacked@hmif.test',
            ]);

        $res->assertStatus(403);
    }

    public function test_editor_and_contributor_cannot_update_organization_settings(): void
    {
        $editorToken = $this->editorOrgA->createToken('editor-token')->plainTextToken;
        $contributorToken = $this->contributorOrgA->createToken('contrib-token')->plainTextToken;

        $resEditor = $this->withHeader('Authorization', "Bearer {$editorToken}")
            ->putJson("/api/organizations/{$this->orgA->id}", [
                'email' => 'editor.attempt@hmif.test',
            ]);
        $resEditor->assertStatus(403);

        $resContrib = $this->withHeader('Authorization', "Bearer {$contributorToken}")
            ->putJson("/api/organizations/{$this->orgA->id}", [
                'email' => 'contrib.attempt@hmif.test',
            ]);
        $resContrib->assertStatus(403);
    }
}
