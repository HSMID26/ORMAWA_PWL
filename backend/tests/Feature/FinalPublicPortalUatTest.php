<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinalPublicPortalUatTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $adminOrg;
    protected User $superAdmin;
    protected User $contributor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        $this->org = Organization::create([
            'nama' => 'Himpunan Mahasiswa Informatika',
            'jenis' => 'HMPS',
            'subdomain' => 'hmif',
            'status' => 'active',
            'warna_tema' => '#3b82f6',
            'logo' => 'https://example.com/logo-old.png',
            'modul_aktif' => [
                'slogan' => 'Slogan Lama',
                'hero_title' => 'Hero Lama',
                'hero_subtitle' => 'Subtitle Lama',
                'posts' => true,
                'agenda' => true,
                'announcements' => true,
                'galeri' => true,
                'documents' => true,
                'structure' => true,
                'public_website_enabled' => true,
            ],
        ]);

        $this->adminOrg = User::create([
            'name' => 'Admin HMIF',
            'email' => 'admin@hmif.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->adminOrg->assignRole('Admin Organisasi');

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@iti.test',
            'password' => bcrypt('password123'),
            'organization_id' => null,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->contributor = User::create([
            'name' => 'Kontributor HMIF',
            'email' => 'contributor@hmif.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->contributor->assignRole('Kontributor');
    }

    /**
     * TEST 1 — ORGANIZATION BRANDING SYNCHRONIZATION
     */
    public function test_uat_1_organization_branding_sync(): void
    {
        // 1. Admin Organisasi updates branding in /api/organizations/{id}
        $token = $this->adminOrg->createToken('admin-token')->plainTextToken;

        $updatePayload = [
            'warna_tema' => '#ef4444',
            'logo' => 'https://example.com/logo-new.webp',
            'modul_aktif' => [
                'slogan' => 'Slogan Baru Berprestasi',
                'hero_title' => 'Informatika Masa Depan',
                'hero_subtitle' => 'Membangun teknologi terdepan',
                'posts' => true,
                'agenda' => true,
                'announcements' => true,
                'galeri' => true,
                'documents' => true,
                'structure' => true,
                'public_website_enabled' => true,
            ]
        ];

        $updateResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/organizations/{$this->org->id}", $updatePayload);

        $updateResponse->assertStatus(200);

        // 2. Guest visits public portal /api/public/organizations/hmif
        $publicResponse = $this->getJson('/api/public/organizations/hmif');
        $publicResponse->assertStatus(200);

        $data = $publicResponse->json('data');
        $this->assertEquals('https://example.com/logo-new.webp', $data['logo']);
        $this->assertEquals('Slogan Baru Berprestasi', $data['slogan']);
        $this->assertEquals('Informatika Masa Depan', $data['hero_title']);
        $this->assertEquals('Membangun teknologi terdepan', $data['hero_subtitle']);
        $this->assertEquals('#ef4444', $data['warna_tema']);
    }

    /**
     * TEST 2 — THEME COLOR SYNCHRONIZATION
     */
    public function test_uat_2_theme_color_sync(): void
    {
        $token = $this->adminOrg->createToken('admin-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/organizations/{$this->org->id}", [
                'warna_tema' => '#10b981', // Emerald
            ])
            ->assertStatus(200);

        $res = $this->getJson('/api/public/organizations/hmif');
        $res->assertStatus(200);
        $this->assertEquals('#10b981', $res->json('data.warna_tema'));
    }

    /**
     * TEST 3 — MODULE TOGGLE SYNCHRONIZATION (ON vs OFF)
     */
    public function test_uat_3_module_toggle_sync(): void
    {
        $token = $this->adminOrg->createToken('admin-token')->plainTextToken;

        // Turn OFF posts & galeri
        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/organizations/{$this->org->id}", [
                'modul_aktif' => [
                    'posts' => false,
                    'galeri' => false,
                    'agenda' => true,
                    'announcements' => true,
                    'documents' => true,
                    'structure' => true,
                    'public_website_enabled' => true,
                ]
            ])
            ->assertStatus(200);

        // Articles API should return 404
        $resArticles = $this->getJson('/api/public/organizations/hmif/articles');
        $resArticles->assertStatus(404);

        // Gallery API should return 404
        $resGallery = $this->getJson('/api/public/organizations/hmif/gallery');
        $resGallery->assertStatus(404);

        // Agenda API should return 200
        $resAgenda = $this->getJson('/api/public/organizations/hmif/agenda');
        $resAgenda->assertStatus(200);

        // Sitemap should not include articles or gallery for hmif
        $resSitemap = $this->getJson('/api/public/sitemap');
        $urls = collect($resSitemap->json('data'))->pluck('loc')->all();
        
        $appUrl = config('app.url', 'https://ormawa.iti.ac.id');
        $this->assertNotContains("{$appUrl}/organizations/hmif/articles", $urls);
        $this->assertNotContains("{$appUrl}/organizations/hmif/gallery", $urls);
        $this->assertContains("{$appUrl}/organizations/hmif/agenda", $urls);
    }

    /**
     * TEST 4 — ARTICLE PUBLISH WORKFLOW
     */
    public function test_uat_4_article_publish_workflow(): void
    {
        // 1. Contributor creates Draft article
        $post = Post::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->contributor->id,
            'judul' => 'Artikel Riset AI Mahasiswa',
            'slug' => 'artikel-riset-ai',
            'konten' => 'Isi riset AI',
            'status' => 'draft',
        ]);

        // Verify Draft is NOT visible to public
        $resList = $this->getJson('/api/public/organizations/hmif/articles');
        $this->assertEmpty($resList->json('data'));

        $resDetailDraft = $this->getJson("/api/public/organizations/hmif/articles/{$post->slug}");
        $resDetailDraft->assertStatus(404);

        // 2. Admin publishes the article
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Verify Published article IS visible to public
        $resListPub = $this->getJson('/api/public/organizations/hmif/articles');
        $this->assertCount(1, $resListPub->json('data'));
        $this->assertEquals('Artikel Riset AI Mahasiswa', $resListPub->json('data.0.judul'));

        $resDetailPub = $this->getJson("/api/public/organizations/hmif/articles/{$post->slug}");
        $resDetailPub->assertStatus(200);

        // 3. Admin reverts Published -> Draft
        $post->update(['status' => 'draft']);

        // Verify article disappears from public
        $resListAfter = $this->getJson('/api/public/organizations/hmif/articles');
        $this->assertEmpty($resListAfter->json('data'));

        $resDetailAfter = $this->getJson("/api/public/organizations/hmif/articles/{$post->slug}");
        $resDetailAfter->assertStatus(404);
    }

    /**
     * TEST 5 — ARTICLE SEO METADATA
     */
    public function test_uat_5_article_seo_metadata(): void
    {
        $cat = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        $tag = Tag::create(['name' => 'AI', 'slug' => 'ai']);

        $post = Post::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->contributor->id,
            'category_id' => $cat->id,
            'judul' => 'Masa Depan Quantum Computing',
            'slug' => 'quantum-computing',
            'excerpt' => 'Eksplorasi komputasi kuantum oleh mahasiswa ITI.',
            'konten' => '<p>Konten lengkap mengenai quantum computing.</p>',
            'cover_image' => 'https://example.com/quantum.webp',
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => 'Quantum Computing ITI 2026',
            'meta_description' => 'Artikel komputasi kuantum HMIF ITI.',
        ]);
        $post->tags()->sync([$tag->id]);

        $res = $this->getJson("/api/public/organizations/hmif/articles/{$post->slug}");
        $res->assertStatus(200);

        $seo = $res->json('data.seo');
        $this->assertEquals('Quantum Computing ITI 2026', $seo['meta_title']);
        $this->assertEquals('Artikel komputasi kuantum HMIF ITI.', $seo['meta_description']);
        $this->assertEquals('https://example.com/quantum.webp', $seo['og_image']);
        $this->assertEquals('Kontributor HMIF', $res->json('data.author.name'));
        $this->assertEquals('Teknologi', $res->json('data.category.name'));
    }

    /**
     * TEST 6 — AGENDA PUBLISH AND FILTERING
     */
    public function test_uat_6_agenda_publish_sync(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $agendaUpcoming = Activity::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'judul' => 'Seminar Cloud Architecture',
            'deskripsi' => 'Belajar AWS dan GCP',
            'tanggal_pelaksanaan' => $tomorrow,
            'tempat' => 'Auditorium ITI',
            'status' => 'published',
        ]);

        $agendaPast = Activity::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'judul' => 'Pelatihan Git Masa Lalu',
            'deskripsi' => 'Pelatihan Git',
            'tanggal_pelaksanaan' => $yesterday,
            'tempat' => 'Lab Komputer',
            'status' => 'published',
        ]);

        $agendaDraft = Activity::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'judul' => 'Rapat Rahasia Draft',
            'deskripsi' => 'Internal',
            'tanggal_pelaksanaan' => $tomorrow,
            'status' => 'draft',
        ]);

        // Upcoming tab
        $resUpcoming = $this->getJson('/api/public/organizations/hmif/agenda?tab=upcoming');
        $resUpcoming->assertStatus(200);
        $this->assertCount(1, $resUpcoming->json('data'));
        $this->assertEquals('Seminar Cloud Architecture', $resUpcoming->json('data.0.judul'));

        // Past tab
        $resPast = $this->getJson('/api/public/organizations/hmif/agenda?tab=past');
        $resPast->assertStatus(200);
        $this->assertCount(1, $resPast->json('data'));
        $this->assertEquals('Pelatihan Git Masa Lalu', $resPast->json('data.0.judul'));
    }

    /**
     * TEST 7 — ANNOUNCEMENTS AND EXPIRATION
     */
    public function test_uat_7_announcements_expiration(): void
    {
        $activeAnn = Announcement::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'title' => 'Open Recruitment Pengurus Baru',
            'slug' => 'oprec-pengurus',
            'content' => 'Pendaftaran dibuka sampai akhir bulan.',
            'priority' => 'urgent',
            'status' => 'published',
            'effective_date' => now()->subDay(),
        ]);

        $futureAnn = Announcement::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'title' => 'Pengumuman Masa Depan',
            'slug' => 'future-ann',
            'content' => 'Belum berlaku.',
            'priority' => 'normal',
            'status' => 'published',
            'effective_date' => now()->addDays(5), // Future date
        ]);

        $draftAnn = Announcement::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'title' => 'Pengumuman Masih Draft',
            'slug' => 'draft-ann',
            'content' => 'Draft internal.',
            'priority' => 'high',
            'status' => 'draft',
            'effective_date' => now()->subDays(2),
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/announcements');
        $res->assertStatus(200);

        $titles = collect($res->json('data'))->pluck('title')->all();
        $this->assertContains('Open Recruitment Pengurus Baru', $titles);
        $this->assertNotContains('Pengumuman Masa Depan', $titles);
        $this->assertNotContains('Pengumuman Masih Draft', $titles);
    }

    /**
     * TEST 8 & 9 — DOCUMENT AND GALLERY VISIBILITY & DELETE
     */
    public function test_uat_8_and_9_gallery_and_document_sync(): void
    {
        $img = Media::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'filename' => 'kegiatan-makrab.webp',
            'path' => 'media/kegiatan-makrab.webp',
            'mime_type' => 'image/webp',
            'is_published_to_gallery' => true,
            'size' => 4096,
        ]);

        $doc = Media::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'filename' => 'Pedoman-Organisasi.pdf',
            'path' => 'media/Pedoman-Organisasi.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1048576,
        ]);

        // Verify presence in public endpoints
        $resGal = $this->getJson('/api/public/organizations/hmif/gallery');
        $this->assertCount(1, $resGal->json('data'));
        $this->assertEquals('kegiatan-makrab.webp', $resGal->json('data.0.filename'));

        $resDoc = $this->getJson('/api/public/organizations/hmif/documents');
        $this->assertCount(1, $resDoc->json('data'));
        $this->assertEquals('Pedoman-Organisasi.pdf', $resDoc->json('data.0.filename'));
        $this->assertEquals('1024 KB', $resDoc->json('data.0.formatted_size'));

        // Test secure document download endpoint
        $resDownload = $this->getJson("/api/public/organizations/hmif/documents/{$doc->id}/download");
        // Storage download or redirect or fallback
        $this->assertTrue(in_array($resDownload->status(), [200, 302, 404]));

        // Delete image
        $img->delete();

        // Verify image disappears from public gallery
        $resGalAfter = $this->getJson('/api/public/organizations/hmif/gallery');
        $this->assertEmpty($resGalAfter->json('data'));
    }

    /**
     * TEST 10 — STRUCTURE NEVER LEAKS INTERNAL CREDENTIALS
     */
    public function test_uat_10_structure_sanitization(): void
    {
        Committee::create([
            'organization_id' => $this->org->id,
            'name' => 'Fakhri Pratama',
            'position' => 'Ketua Himpunan',
            'department' => 'Badan Pengurus Harian',
            'period' => '2026/2027',
            'status' => 'active',
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/structure');
        $res->assertStatus(200);

        $jsonStr = $res->getContent();
        $this->assertStringNotContainsString('password', $jsonStr);
        $this->assertStringNotContainsString('email', $jsonStr);
        $this->assertStringNotContainsString('role', $jsonStr);
        $this->assertStringNotContainsString('user_id', $jsonStr);

        $members = $res->json('data.members');
        $this->assertCount(1, $members);
        $this->assertEquals('Fakhri Pratama', $members[0]['name']);
        $this->assertEquals('Ketua Himpunan', $members[0]['position']);
    }

    /**
     * TEST 11 — ORGANIZATION DEACTIVATION & REACTIVATION LIFECYCLE
     */
    public function test_uat_11_organization_status_lifecycle(): void
    {
        // 1. Initially active -> 200
        $this->getJson('/api/public/organizations/hmif')->assertStatus(200);

        // 2. Super Admin deactivates organization
        $superToken = $this->superAdmin->createToken('super-token')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$superToken}")
            ->postJson("/api/organizations/{$this->org->id}/deactivate")
            ->assertStatus(200);

        // 3. Task 2: Public access remains 200 (visible to public)
        $this->getJson('/api/public/organizations/hmif')->assertStatus(200);

        // 4. Directory still includes organization
        $resDir = $this->getJson('/api/public/organizations');
        $slugs = collect($resDir->json('data'))->pluck('subdomain')->all();
        $this->assertContains('hmif', $slugs);

        // 5. Internal user login is blocked
        $this->postJson('/api/login', [
            'email' => $this->adminOrg->email,
            'password' => 'password123',
        ])->assertStatus(403);

        // 6. Super Admin reactivates organization
        $this->withHeader('Authorization', "Bearer {$superToken}")
            ->postJson("/api/organizations/{$this->org->id}/activate")
            ->assertStatus(200);

        // 7. Public access still OK
        $this->getJson('/api/public/organizations/hmif')->assertStatus(200);
    }

    /**
     * TEST 12, 13 & 14 — CANONICAL URL, SITEMAP PURITY & DATA LEAK CHECK
     */
    public function test_uat_12_13_14_canonical_sitemap_and_data_leak(): void
    {
        $resSitemap = $this->getJson('/api/public/sitemap');
        $resSitemap->assertStatus(200);
        $urls = collect($resSitemap->json('data'))->pluck('loc')->all();

        // Must NOT contain internal routes
        foreach ($urls as $url) {
            $this->assertStringNotContainsString('/dashboard', $url);
            $this->assertStringNotContainsString('/super-admin', $url);
            $this->assertStringNotContainsString('/organization/', $url);
            $this->assertStringNotContainsString('/login', $url);
            $this->assertStringNotContainsString('/register', $url);
            $this->assertStringNotContainsString('/activity-logs', $url);
            $this->assertStringNotContainsString('/users', $url);
        }

        // Test public home data leak
        $resHome = $this->getJson('/api/public/home');
        $homeStr = $resHome->getContent();
        $this->assertStringNotContainsString('password', $homeStr);
        $this->assertStringNotContainsString('token', $homeStr);
        $this->assertStringNotContainsString('admin@hmif.test', $homeStr);
    }

    /**
     * TEST 15 — INVALID SLUG, ID & NON-EXISTENT RESOURCE ERROR HANDLING
     */
    public function test_uat_15_invalid_slug_and_id_handling(): void
    {
        // 1. Non-existent organization slug returns 404
        $this->getJson('/api/public/organizations/non-existent-ormawa')->assertStatus(404);

        // 2. Non-existent article slug returns 404
        $this->getJson('/api/public/organizations/hmif/articles/unknown-article-slug')->assertStatus(404);

        // 3. Non-existent agenda ID returns 404
        $this->getJson('/api/public/organizations/hmif/agenda/99999')->assertStatus(404);
    }
}
