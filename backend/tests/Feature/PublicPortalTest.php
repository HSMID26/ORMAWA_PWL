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

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected Organization $inactiveOrg;
    protected User $authorA;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'announcements.create', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'announcements.publish', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'announcements.view', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(['announcements.create', 'announcements.publish', 'announcements.view']);

        $this->orgA = Organization::create([
            'nama' => 'UKM Robotik ITI',
            'jenis' => 'UKM',
            'subdomain' => 'robotik',
            'status' => 'active',
            'warna_tema' => '#1d4ed8',
            'modul_aktif' => [
                'slogan' => 'Innovate and Automate',
                'deskripsi' => 'Unit kegiatan mahasiswa robotika dan otomasi.',
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
            'nama' => 'Himpunan Informatika',
            'jenis' => 'HMPS',
            'subdomain' => 'hmif',
            'status' => 'active',
            'warna_tema' => '#059669',
            'modul_aktif' => [
                'posts' => true,
                'agenda' => true,
                'announcements' => true,
                'galeri' => true,
                'documents' => true,
                'structure' => true,
                'public_website_enabled' => true,
            ],
        ]);

        $this->inactiveOrg = Organization::create([
            'nama' => 'UKM Nonaktif',
            'jenis' => 'UKM',
            'subdomain' => 'nonaktif',
            'status' => 'inactive',
            'warna_tema' => '#6b7280',
        ]);

        $this->authorA = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@robotik.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->authorA->assignRole('Admin Organisasi');
    }

    public function test_guest_can_access_portal_home_summary(): void
    {
        // Create 1 published post and 1 draft post
        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Robotik Juara 1 Nasional',
            'slug' => 'robotik-juara-1-nasional',
            'konten' => 'Isi berita kejuaraan nasional robotika',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Draft Artikel Rahasia',
            'slug' => 'draft-artikel-rahasia',
            'konten' => 'Draft body',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/public/home');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'organizations',
                'featured_articles',
                'upcoming_agenda',
                'active_announcements',
                'stats' => ['total_organizations', 'total_articles', 'total_agenda']
            ]
        ]);

        $articles = $response->json('data.featured_articles');
        $this->assertCount(1, $articles);
        $this->assertEquals('Robotik Juara 1 Nasional', $articles[0]['judul']);
    }

    public function test_guest_can_list_organizations_in_public_directory(): void
    {
        $response = $this->getJson('/api/public/organizations');

        $response->assertStatus(200);
        $data = $response->json('data');
        $slugs = collect($data)->pluck('subdomain')->all();

        $this->assertContains('robotik', $slugs);
        $this->assertContains('hmif', $slugs);
        $this->assertContains('nonaktif', $slugs);
    }

    public function test_guest_can_view_organization_profile_by_slug(): void
    {
        $response = $this->getJson('/api/public/organizations/robotik');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'nama' => 'UKM Robotik ITI',
                'subdomain' => 'robotik',
                'slogan' => 'Innovate and Automate',
            ]
        ]);
    }

    public function test_guest_gets_404_for_non_existent_organization(): void
    {
        $response404 = $this->getJson('/api/public/organizations/tidak-ada');
        $response404->assertStatus(404);
    }

    public function test_guest_can_view_published_articles_only(): void
    {
        $cat = Category::create(['name' => 'Prestasi', 'slug' => 'prestasi']);
        $tag = Tag::create(['name' => 'Robotik', 'slug' => 'robotik']);

        $pubPost = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'category_id' => $cat->id,
            'judul' => 'Kemenangan Gemilang di Kontes Robot',
            'slug' => 'kemenangan-gemilang',
            'konten' => 'Berita lengkap kemenangan kontes robot',
            'excerpt' => 'Ringkasan berita kemenangan',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $pubPost->tags()->sync([$tag->id]);

        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Review Artikel Belum Terbit',
            'slug' => 'review-artikel',
            'konten' => 'Review body',
            'status' => 'review',
        ]);

        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Artikel Ditolak',
            'slug' => 'artikel-ditolak',
            'konten' => 'Rejected body',
            'status' => 'rejected',
        ]);

        $response = $this->getJson('/api/public/organizations/robotik/articles');

        $response->assertStatus(200);
        $articles = $response->json('data');
        $this->assertCount(1, $articles);
        $this->assertEquals('Kemenangan Gemilang di Kontes Robot', $articles[0]['judul']);
        $this->assertEquals('Prestasi', $articles[0]['category']['name']);
        $this->assertEquals('Budi Santoso', $articles[0]['author']['name']);
    }

    public function test_guest_can_view_published_article_detail_and_gets_404_for_draft(): void
    {
        $pub = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Artikel Publik Detail',
            'slug' => 'artikel-publik-detail',
            'konten' => '<p>Konten artikel lengkap HTML</p>',
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => 'SEO Title Artikel',
            'meta_description' => 'SEO Description Artikel',
        ]);

        $draft = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Draft Tersembunyi',
            'slug' => 'draft-tersembunyi',
            'konten' => 'Draft',
            'status' => 'draft',
        ]);

        // Success for published
        $resSuccess = $this->getJson("/api/public/organizations/robotik/articles/{$pub->slug}");
        $resSuccess->assertStatus(200);
        $resSuccess->assertJson([
            'status' => 'success',
            'data' => [
                'judul' => 'Artikel Publik Detail',
                'konten' => '<p>Konten artikel lengkap HTML</p>',
                'seo' => [
                    'meta_title' => 'SEO Title Artikel',
                ]
            ]
        ]);

        // 404 for draft
        $resDraft = $this->getJson("/api/public/organizations/robotik/articles/{$draft->slug}");
        $resDraft->assertStatus(404);
    }

    public function test_tenant_boundary_guest_cannot_access_org_b_article_from_org_a_url(): void
    {
        $postB = Post::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Artikel HMIF',
            'slug' => 'artikel-hmif',
            'konten' => 'Konten HMIF',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Requesting HMIF article via Robotik URL should 404
        $res = $this->getJson("/api/public/organizations/robotik/articles/{$postB->slug}");
        $res->assertStatus(404);
    }

    public function test_guest_can_view_published_agenda_and_draft_is_hidden(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        Activity::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Workshop Robotika Terbuka',
            'deskripsi' => 'Pelatihan membuat robot line follower',
            'tanggal_pelaksanaan' => $tomorrow,
            'status' => 'published',
        ]);

        Activity::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Rapat Internal Pengurus',
            'deskripsi' => 'Rapat rahasia',
            'tanggal_pelaksanaan' => $tomorrow,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/public/organizations/robotik/agenda?tab=upcoming');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Workshop Robotika Terbuka', $data[0]['judul']);
    }

    public function test_guest_can_view_active_announcements_only(): void
    {
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Pendaftaran Anggota Baru Dibuka',
            'slug' => 'open-recruitment',
            'content' => 'Silakan mendaftar melalui form',
            'priority' => 'urgent',
            'status' => 'published',
            'effective_date' => now()->subDay(),
        ]);

        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Pengumuman Draft',
            'slug' => 'draft-announcement',
            'content' => 'Belum siap',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/public/organizations/robotik/announcements');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Pendaftaran Anggota Baru Dibuka', $data[0]['title']);
    }

    public function test_guest_can_view_gallery_and_documents(): void
    {
        Media::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'filename' => 'foto-juara.webp',
            'path' => 'media/foto-juara.webp',
            'mime_type' => 'image/webp',
            'is_published_to_gallery' => true,
            'size' => 2048,
        ]);

        Media::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'filename' => 'AD-ART-Robotik.pdf',
            'path' => 'media/AD-ART-Robotik.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10240,
        ]);

        $resGallery = $this->getJson('/api/public/organizations/robotik/gallery');
        $resGallery->assertStatus(200);
        $this->assertCount(1, $resGallery->json('data'));
        $this->assertEquals('foto-juara.webp', $resGallery->json('data.0.filename'));

        $resDocs = $this->getJson('/api/public/organizations/robotik/documents');
        $resDocs->assertStatus(200);
        $this->assertCount(1, $resDocs->json('data'));
        $this->assertEquals('AD-ART-Robotik.pdf', $resDocs->json('data.0.filename'));
    }

    public function test_guest_can_view_public_structure(): void
    {
        Committee::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Ahmad Fikri',
            'position' => 'Ketua Umum',
            'department' => 'Badan Pengurus Harian',
            'period' => '2026/2027',
            'status' => 'active',
        ]);

        Committee::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Mantan Pengurus',
            'position' => 'Anggota',
            'department' => 'Divisi Mekanik',
            'status' => 'inactive',
        ]);

        $response = $this->getJson('/api/public/organizations/robotik/structure');

        $response->assertStatus(200);
        $members = $response->json('data.members');
        $this->assertCount(1, $members);
        $this->assertEquals('Ahmad Fikri', $members[0]['name']);
    }

    public function test_disabled_module_returns_404(): void
    {
        $this->orgA->update([
            'modul_aktif' => [
                'posts' => false,
                'agenda' => true,
            ]
        ]);

        $response = $this->getJson('/api/public/organizations/robotik/articles');
        $response->assertStatus(404);
    }

    public function test_public_endpoints_do_not_leak_passwords_or_tokens_or_private_emails(): void
    {
        $response = $this->getJson('/api/public/organizations/robotik/articles');

        $content = $response->getContent();
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('budi@robotik.test', $content);
        $this->assertStringNotContainsString('remember_token', $content);
    }

    public function test_sitemap_returns_valid_url_structures(): void
    {
        $response = $this->getJson('/api/public/sitemap');

        $response->assertStatus(200);
        $urls = $response->json('data');
        $this->assertNotEmpty($urls);
        $this->assertArrayHasKey('loc', $urls[0]);
    }

    public function test_guest_can_access_global_articles_hub(): void
    {
        // 1. Published post for Org A
        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Inovasi Robot Line Follower',
            'slug' => 'inovasi-robot-line-follower',
            'konten' => 'Berita tentang inovasi robot line follower ITI.',
            'excerpt' => 'Ringkasan robot line follower.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // 2. Draft post for Org A (Must NOT appear)
        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Draft Rahasia Robotik',
            'slug' => 'draft-rahasia-robotik',
            'konten' => 'Draft tidak boleh tampil publik.',
            'status' => 'draft',
        ]);

        // 3. Published post for Inactive Org (Task 2: Published content remains accessible)
        Post::create([
            'organization_id' => $this->inactiveOrg->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Berita Organisasi Nonaktif',
            'slug' => 'berita-organisasi-nonaktif',
            'konten' => 'Berita dari org nonaktif.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/public/articles');

        $response->assertStatus(200);
        $articles = $response->json('data');
        $this->assertCount(2, $articles);
        $this->assertEquals('Inovasi Robot Line Follower', $articles[1]['judul']);
        $this->assertEquals('robotik', $articles[1]['organization']['subdomain']);
        $this->assertArrayHasKey('meta', $response->json());
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_global_articles_filters_by_search_and_organization(): void
    {
        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Workshop AI dan Robotika',
            'slug' => 'workshop-ai-dan-robotika',
            'konten' => 'Belajar machine learning di lab robotika.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Post::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Seminar Web Development HMIF',
            'slug' => 'seminar-web-development-hmif',
            'konten' => 'Seminar teknologi web masa kini.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Filter by organization
        $resOrg = $this->getJson('/api/public/articles?organization=hmif');
        $resOrg->assertStatus(200);
        $this->assertCount(1, $resOrg->json('data'));
        $this->assertEquals('Seminar Web Development HMIF', $resOrg->json('data.0.judul'));

        // Filter by search
        $resSearch = $this->getJson('/api/public/articles?search=Robotika');
        $resSearch->assertStatus(200);
        $this->assertCount(1, $resSearch->json('data'));
        $this->assertEquals('Workshop AI dan Robotika', $resSearch->json('data.0.judul'));
    }

    public function test_global_categories_returns_distinct_categories(): void
    {
        $cat = Category::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Teknologi',
            'slug' => 'teknologi',
        ]);

        Post::create([
            'organization_id' => $this->orgA->id,
            'category_id' => $cat->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Teknologi Terbaru Robotika',
            'slug' => 'teknologi-terbaru-robotika',
            'konten' => 'Konten teknologi robotika.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/public/categories');
        $response->assertStatus(200);
        $categories = $response->json('data');
        $this->assertNotEmpty($categories);
        $this->assertEquals('Teknologi', $categories[0]['name']);
    }

    public function test_guest_can_access_global_agenda_hub_with_tabs(): void
    {
        // 1. Upcoming event for Org A
        Activity::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Kompetisi Robotika Mahasiswa 2026',
            'deskripsi' => 'Ajang tahunan inovasi robot.',
            'tanggal_pelaksanaan' => now()->addDays(5)->toDateString(),
            'tempat' => 'Auditorium ITI',
            'status' => 'published',
        ]);

        // 2. Past event for Org B
        Activity::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Bakti Sosial Informatika',
            'deskripsi' => 'Pengabdian masyarakat.',
            'tanggal_pelaksanaan' => now()->subDays(10)->toDateString(),
            'tempat' => 'Desa Binaan',
            'status' => 'published',
        ]);

        // 3. Draft event (Must NOT appear)
        Activity::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Draft Rapat Internal',
            'deskripsi' => 'Internal.',
            'tanggal_pelaksanaan' => now()->addDays(2)->toDateString(),
            'status' => 'draft',
        ]);

        // Query upcoming
        $resUpcoming = $this->getJson('/api/public/agenda?tab=upcoming');
        $resUpcoming->assertStatus(200);
        $this->assertCount(1, $resUpcoming->json('data'));
        $this->assertEquals('Kompetisi Robotika Mahasiswa 2026', $resUpcoming->json('data.0.judul'));

        // Query past
        $resPast = $this->getJson('/api/public/agenda?tab=past');
        $resPast->assertStatus(200);
        $this->assertCount(1, $resPast->json('data'));
        $this->assertEquals('Bakti Sosial Informatika', $resPast->json('data.0.judul'));
    }

    public function test_guest_can_access_global_announcements_hub(): void
    {
        // 1. Urgent announcement
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Pendaftaran Anggota Baru Robotik',
            'slug' => 'pendaftaran-anggota-baru-robotik',
            'content' => 'Pendaftaran dibuka sampai akhir pekan.',
            'priority' => 'urgent',
            'status' => 'published',
            'effective_date' => now()->subDay(),
            'published_at' => now()->subDay(),
        ]);

        // 2. Normal announcement
        Announcement::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->authorA->id,
            'title' => 'Pengumuman Kuliah Tamu HMIF',
            'slug' => 'pengumuman-kuliah-tamu-hmif',
            'content' => 'Kuliah tamu seputar cloud computing.',
            'priority' => 'normal',
            'status' => 'published',
            'effective_date' => now()->subHours(2),
            'published_at' => now()->subHours(2),
        ]);

        $response = $this->getJson('/api/public/announcements');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
        // Urgent must come first
        $this->assertEquals('urgent', $data[0]['priority']);
        $this->assertEquals('Pendaftaran Anggota Baru Robotik', $data[0]['title']);

        // Filter by priority
        $resFilter = $this->getJson('/api/public/announcements?priority=urgent');
        $resFilter->assertStatus(200);
        $this->assertCount(1, $resFilter->json('data'));
        $this->assertEquals('Pendaftaran Anggota Baru Robotik', $resFilter->json('data.0.title'));
    }

    public function test_announcement_end_to_end_lifecycle_and_visibility_rules(): void
    {
        // 1. Admin creates published announcement via admin API
        $this->actingAs($this->authorA, 'sanctum');
        $createRes = $this->postJson('/api/announcements', [
            'title' => 'Perekrutan Anggota Baru 2026',
            'content' => 'Perekrutan dibuka untuk seluruh mahasiswa angkatan baru.',
            'priority' => 'high',
            'effective_date' => now()->toDateString(),
            'status' => 'published',
        ]);
        $createRes->assertStatus(201);
        $announcementId = $createRes->json('data.id');

        // Reset authentication
        $this->app['auth']->forgetGuards();

        // 2. Published announcement appears in organization public endpoint
        $orgRes = $this->getJson('/api/public/organizations/robotik/announcements');
        $orgRes->assertStatus(200);
        $this->assertTrue(collect($orgRes->json('data'))->contains('title', 'Perekrutan Anggota Baru 2026'));

        // 3. Published announcement appears in global public endpoint
        $globalRes = $this->getJson('/api/public/announcements');
        $globalRes->assertStatus(200);
        $this->assertTrue(collect($globalRes->json('data'))->contains('title', 'Perekrutan Anggota Baru 2026'));

        // 3b. Announcement with null effective_date is immediately public
        $this->actingAs($this->authorA, 'sanctum');
        $nullDateRes = $this->postJson('/api/announcements', [
            'title' => 'Pengumuman Langsung Tayang',
            'content' => 'Pengumuman tanpa tanggal efektif harus langsung tayang.',
            'priority' => 'normal',
            'effective_date' => null,
            'status' => 'published',
        ]);
        $nullDateRes->assertStatus(201);
        $this->app['auth']->forgetGuards();

        $orgNullRes = $this->getJson('/api/public/organizations/robotik/announcements');
        $this->assertTrue(collect($orgNullRes->json('data'))->contains('title', 'Pengumuman Langsung Tayang'));

        // 4. Draft, Review, Rejected announcements are hidden from public
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Draft Announcement',
            'slug' => 'draft-announcement',
            'content' => 'Draft content.',
            'status' => 'draft',
        ]);
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Review Announcement',
            'slug' => 'review-announcement',
            'content' => 'Review content.',
            'status' => 'review',
        ]);
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Rejected Announcement',
            'slug' => 'rejected-announcement',
            'content' => 'Rejected content.',
            'status' => 'rejected',
        ]);

        $checkRes = $this->getJson('/api/public/organizations/robotik/announcements');
        $this->assertFalse(collect($checkRes->json('data'))->contains('title', 'Draft Announcement'));
        $this->assertFalse(collect($checkRes->json('data'))->contains('title', 'Review Announcement'));
        $this->assertFalse(collect($checkRes->json('data'))->contains('title', 'Rejected Announcement'));

        // 5. Future announcement (effective_date in future) is hidden from public today
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Future Announcement',
            'slug' => 'future-announcement',
            'content' => 'Future content.',
            'effective_date' => now()->addDays(3)->toDateString(),
            'status' => 'published',
        ]);
        $futureCheck = $this->getJson('/api/public/organizations/robotik/announcements');
        $this->assertFalse(collect($futureCheck->json('data'))->contains('title', 'Future Announcement'));

        // 5b. Expiration date rules
        // 5b-1. Announcement with future expires_at is visible today
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Active Unexpired Announcement',
            'slug' => 'active-unexpired-announcement',
            'content' => 'Content valid until next week.',
            'effective_date' => now()->toDateString(),
            'expires_at' => now()->addDays(7)->toDateString(),
            'status' => 'published',
        ]);
        $unexpiredOrgCheck = $this->getJson('/api/public/organizations/robotik/announcements');
        $this->assertTrue(collect($unexpiredOrgCheck->json('data'))->contains('title', 'Active Unexpired Announcement'));

        // 5b-2. Announcement with past expires_at is hidden (expired)
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'title' => 'Expired Announcement',
            'slug' => 'expired-announcement',
            'content' => 'Expired content.',
            'effective_date' => now()->subDays(5)->toDateString(),
            'expires_at' => now()->subDays(1)->toDateString(),
            'status' => 'published',
        ]);
        $expiredOrgCheck = $this->getJson('/api/public/organizations/robotik/announcements');
        $this->assertFalse(collect($expiredOrgCheck->json('data'))->contains('title', 'Expired Announcement'));
        $expiredGlobalCheck = $this->getJson('/api/public/announcements');
        $this->assertFalse(collect($expiredGlobalCheck->json('data'))->contains('title', 'Expired Announcement'));

        // 5b-3. Validation: expires_at cannot be before effective_date
        $this->actingAs($this->authorA);
        $invalidDateRes = $this->postJson('/api/announcements', [
            'title' => 'Invalid Expiry Announcement',
            'content' => 'Content with invalid dates.',
            'effective_date' => now()->addDays(5)->toDateString(),
            'expires_at' => now()->addDays(2)->toDateString(),
            'status' => 'draft',
        ]);
        $invalidDateRes->assertStatus(422);
        $invalidDateRes->assertJsonValidationErrors(['expires_at']);
        $this->app['auth']->forgetGuards();

        // 6. Inactive organization's announcement remains visible globally per Task 2 requirements
        Announcement::create([
            'organization_id' => $this->inactiveOrg->id,
            'user_id' => $this->authorA->id,
            'title' => 'Inactive Org Announcement',
            'slug' => 'inactive-org-announcement',
            'content' => 'Content from inactive org.',
            'effective_date' => now()->toDateString(),
            'status' => 'published',
        ]);
        $inactiveGlobalCheck = $this->getJson('/api/public/announcements');
        $this->assertTrue(collect($inactiveGlobalCheck->json('data'))->contains('title', 'Inactive Org Announcement'));

        // 7. Disabled announcements module hides announcements from public org endpoint (404) & global hub
        $this->orgA->update(['modul_aktif' => ['announcements' => false, 'posts' => true]]);
        $disabledOrgCheck = $this->getJson('/api/public/organizations/robotik/announcements');
        $disabledOrgCheck->assertStatus(404);

        $disabledGlobalCheck = $this->getJson('/api/public/announcements');
        $this->assertFalse(collect($disabledGlobalCheck->json('data'))->contains('title', 'Perekrutan Anggota Baru 2026'));
    }

    public function test_cross_navigation_and_canonical_hub_endpoints(): void
    {
        $postA = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Inovasi Robotika Mahasiswa 2026',
            'slug' => 'inovasi-robotika-mahasiswa-2026',
            'konten' => 'Berita seputar riset robotika.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Article canonical organization detail
        $artRes = $this->getJson('/api/public/organizations/robotik/articles/' . $postA->slug);
        $artRes->assertStatus(200);
        $this->assertEquals($postA->judul, $artRes->json('data.judul'));
        $this->assertEquals('robotik', $artRes->json('data.organization.subdomain'));

        // 2. Agenda canonical organization detail
        $agenda = Activity::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'judul' => 'Rapat Kerja Robotik 2026',
            'deskripsi' => 'Rapat kerja awal periode.',
            'tanggal_pelaksanaan' => now()->addDays(2)->toDateString(),
            'tempat' => 'Lab Robotik',
            'status' => 'published',
        ]);
        $agendaRes = $this->getJson("/api/public/organizations/robotik/agenda/{$agenda->id}");
        $agendaRes->assertStatus(200);
        $this->assertEquals('Rapat Kerja Robotik 2026', $agendaRes->json('data.judul'));
        $this->assertEquals('robotik', $agendaRes->json('data.organization.subdomain'));

        // 3. Inactive org content returns 404
        $inactiveOrgRes = $this->getJson('/api/public/organizations/inaktif');
        $inactiveOrgRes->assertStatus(404);
        $inactiveArtRes = $this->getJson('/api/public/organizations/inaktif/articles');
        $inactiveArtRes->assertStatus(404);

        // 4. Global search respects query across orgs and articles
        $searchArt = $this->getJson('/api/public/articles?search=Robotika');
        $searchArt->assertStatus(200);
        $this->assertTrue(collect($searchArt->json('data'))->contains('judul', 'Inovasi Robotika Mahasiswa 2026'));
    }

    public function test_article_detail_includes_dynamic_related_articles_excluding_self(): void
    {
        $catTekno = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        $catPrestasi = Category::create(['name' => 'Prestasi', 'slug' => 'prestasi']);

        $mainPost = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'category_id' => $catTekno->id,
            'judul' => 'Artikel Utama Robotika',
            'slug' => 'artikel-utama-robotika',
            'konten' => 'Konten lengkap artikel utama.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $rel1 = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'category_id' => $catTekno->id,
            'judul' => 'Terkait 1 Kategori Sama',
            'slug' => 'terkait-1-kategori-sama',
            'konten' => 'Konten terkait 1',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $rel2 = Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->authorA->id,
            'category_id' => $catPrestasi->id,
            'judul' => 'Terkait 2 Kategori Beda',
            'slug' => 'terkait-2-kategori-beda',
            'konten' => 'Konten terkait 2',
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        $res = $this->getJson("/api/public/organizations/robotik/articles/{$mainPost->slug}");
        $res->assertStatus(200);

        $data = $res->json('data');
        $this->assertEquals('Artikel Utama Robotika', $data['judul']);
        $this->assertArrayHasKey('related_articles', $data);

        $related = $data['related_articles'];
        $this->assertCount(2, $related);

        $relatedSlugs = collect($related)->pluck('slug')->all();
        $this->assertNotContains('artikel-utama-robotika', $relatedSlugs);
        $this->assertContains('terkait-1-kategori-sama', $relatedSlugs);
        $this->assertContains('terkait-2-kategori-beda', $relatedSlugs);
    }
}
