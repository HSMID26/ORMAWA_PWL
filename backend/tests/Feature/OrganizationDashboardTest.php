<?php

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Committee;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\Post;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

    $timestamp = microtime(true);

    $this->org1 = Organization::create([
        'nama' => 'HIMATIF ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'himatif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->adminOrg1 = User::create([
        'name' => 'Admin HIMATIF',
        'email' => 'admin_himatif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);
    $this->adminOrg1->assignRole('Admin Organisasi');

    $this->period = OrganizationPeriod::create([
        'organization_id' => $this->org1->id,
        'period_name' => 'Kepengurusan 2026',
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->addMonths(10)->toDateString(),
        'status' => 'active',
    ]);
});

test('organization admin can view own dashboard summary with real aggregate stats', function () {
    // Create Posts
    Post::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->adminOrg1->id,
        'judul' => 'Berita Organisasi 1',
        'slug' => 'berita-1',
        'konten' => '<p>Konten</p>',
        'status' => 'published',
    ]);

    Post::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->adminOrg1->id,
        'judul' => 'Berita Draft',
        'slug' => 'berita-draft',
        'konten' => '<p>Draft</p>',
        'status' => 'draft',
    ]);

    // Create Activity
    Activity::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->adminOrg1->id,
        'judul' => 'Workshop Tech',
        'deskripsi' => 'Belajar Coding',
        'tanggal_pelaksanaan' => now()->addDays(5)->toDateString(),
        'status' => 'published',
    ]);

    // Create Announcement
    Announcement::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->adminOrg1->id,
        'title' => 'Pengumuman Penting',
        'slug' => 'pengumuman-penting',
        'content' => 'Rapat pengurus',
        'priority' => 'urgent',
        'status' => 'published',
    ]);

    // Create Committee
    Committee::create([
        'organization_id' => $this->org1->id,
        'organization_period_id' => $this->period->id,
        'name' => 'Ketua Umum',
        'position' => 'Ketua',
        'period' => 'Kepengurusan 2026',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->getJson('/api/organization/dashboard');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'organization' => [
                    'id' => $this->org1->id,
                    'nama' => $this->org1->nama,
                ],
                'posts' => [
                    'total' => 2,
                    'published' => 1,
                    'draft' => 1,
                ],
                'agenda' => [
                    'total' => 1,
                    'upcoming' => 1,
                ],
                'announcements' => [
                    'total' => 1,
                    'urgent' => 1,
                ],
                'members' => [
                    'total_users' => 1,
                    'total_pengurus' => 1,
                ]
            ]
        ]);
});

test('guest cannot access organization dashboard', function () {
    $this->getJson('/api/organization/dashboard')->assertStatus(401);
});
