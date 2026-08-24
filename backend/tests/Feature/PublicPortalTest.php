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

        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

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

    public function test_guest_can_list_active_organizations_and_inactive_are_hidden(): void
    {
        $response = $this->getJson('/api/public/organizations');

        $response->assertStatus(200);
        $data = $response->json('data');
        $slugs = collect($data)->pluck('subdomain')->all();

        $this->assertContains('robotik', $slugs);
        $this->assertContains('hmif', $slugs);
        $this->assertNotContains('nonaktif', $slugs);
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

    public function test_guest_gets_404_for_non_existent_or_inactive_organization(): void
    {
        $response404 = $this->getJson('/api/public/organizations/tidak-ada');
        $response404->assertStatus(404);

        $responseInactive = $this->getJson('/api/public/organizations/nonaktif');
        $responseInactive->assertStatus(404);
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
}
