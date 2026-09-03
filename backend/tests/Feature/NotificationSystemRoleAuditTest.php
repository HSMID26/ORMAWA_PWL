<?php

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\Post;
use App\Models\User;
use App\Services\NotificationService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    \App\Http\Controllers\Api\RoleController::ensureDefaultPermissions();
});

test('NOTIFICATION SYSTEM: Reviewers (Admin Org & Editor) receive notification when Contributor submits post for review', function () {
    $timestamp = microtime(true);
    $org = Organization::create([
        'nama' => 'HMIF Test ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'hmif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $admin = User::create([
        'name' => 'Admin Org',
        'email' => 'admin_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $admin->assignRole('Admin Organisasi');

    $editor = User::create([
        'name' => 'Editor Org',
        'email' => 'editor_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $editor->assignRole('Editor');

    $contributor = User::create([
        'name' => 'Contributor Org',
        'email' => 'contrib_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $contributor->assignRole('Kontributor');

    Sanctum::actingAs($contributor);

    // Contributor creates a post for review
    $response = $this->postJson('/api/posts', [
        'judul' => 'Berita Mahasiswa ITI',
        'konten' => 'Isi berita mahasiswa yang membutuhkan review redaksi.',
        'status' => 'review',
    ]);

    $response->assertStatus(201);
    $postId = $response->json('data.id');

    // 1. Admin should have received a notification
    $adminNotif = Notification::where('user_id', $admin->id)->first();
    expect($adminNotif)->not->toBeNull();
    expect($adminNotif->type)->toBe('content_review');
    expect($adminNotif->title)->toBe('Konten Menunggu Review');

    // 2. Editor should have received a notification
    $editorNotif = Notification::where('user_id', $editor->id)->first();
    expect($editorNotif)->not->toBeNull();
    expect($editorNotif->type)->toBe('content_review');
    expect($editorNotif->title)->toBe('Konten Menunggu Review');

    // 3. Contributor should have received a submission confirmation
    $contributorNotif = Notification::where('user_id', $contributor->id)->first();
    expect($contributorNotif)->not->toBeNull();
    expect($contributorNotif->type)->toBe('content_submitted');
    expect($contributorNotif->title)->toBe('Artikel Diajukan');
});

test('NOTIFICATION SYSTEM: Contributor receives publication and rejection notifications on review decision', function () {
    $timestamp = microtime(true);
    $org = Organization::create([
        'nama' => 'HMIF Test ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'hmif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $admin = User::create([
        'name' => 'Admin Org',
        'email' => 'admin_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $admin->assignRole('Admin Organisasi');

    $contributor = User::create([
        'name' => 'Contributor Org',
        'email' => 'contrib_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $contributor->assignRole('Kontributor');

    $post = Post::create([
        'organization_id' => $org->id,
        'user_id' => $contributor->id,
        'judul' => 'Proposal Lomba Robotik',
        'slug' => 'proposal-lomba-robotik-' . Str::random(5),
        'konten' => 'Isi draft proposal',
        'status' => 'review',
    ]);

    // Admin approves & publishes the post
    Sanctum::actingAs($admin);
    $this->putJson("/api/posts/{$post->id}", [
        'status' => 'published',
    ])->assertStatus(200);

    // Contributor receives publication notification
    $pubNotif = Notification::where('user_id', $contributor->id)
        ->where('type', 'content_published')
        ->first();
    expect($pubNotif)->not->toBeNull();
    expect($pubNotif->title)->toBe('Artikel Dipublikasikan');

    // Now test rejection notification
    $post2 = Post::create([
        'organization_id' => $org->id,
        'user_id' => $contributor->id,
        'judul' => 'Tulisan Belum Lengkap',
        'slug' => 'tulisan-belum-lengkap-' . Str::random(5),
        'konten' => 'Draft tidak lengkap',
        'status' => 'review',
    ]);

    $this->putJson("/api/posts/{$post2->id}", [
        'status' => 'rejected',
    ])->assertStatus(200);

    $rejNotif = Notification::where('user_id', $contributor->id)
        ->where('type', 'content_rejected')
        ->first();
    expect($rejNotif)->not->toBeNull();
    expect($rejNotif->title)->toBe('Artikel Perlu Revisi');
});

test('NOTIFICATION SYSTEM: Agenda & Announcement review notifications reach Reviewers and Author', function () {
    $timestamp = microtime(true);
    $org = Organization::create([
        'nama' => 'HMIF Test ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'hmif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $editor = User::create([
        'name' => 'Editor Org',
        'email' => 'editor_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $editor->assignRole('Editor');

    $admin = User::create([
        'name' => 'Admin Org',
        'email' => 'admin_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $admin->assignRole('Admin Organisasi');

    Sanctum::actingAs($editor);

    // Editor creates an agenda with status review
    $this->postJson('/api/activities', [
        'judul' => 'Seminar Nasional AI 2026',
        'deskripsi' => 'Deskripsi seminar',
        'tanggal_pelaksanaan' => '2026-10-15',
        'status' => 'review',
    ])->assertStatus(201);

    // Admin receives reviewer notification
    $adminNotif = Notification::where('user_id', $admin->id)->where('type', 'content_review')->first();
    expect($adminNotif)->not->toBeNull();

    // Editor receives confirmation notification
    $editorNotif = Notification::where('user_id', $editor->id)->where('type', 'content_submitted')->first();
    expect($editorNotif)->not->toBeNull();
});

test('NOTIFICATION SYSTEM: Strict Tenant and User Isolation for Notifications', function () {
    $timestamp = microtime(true);
    $orgA = Organization::create([
        'nama' => 'Org A ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'orga' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);
    $orgB = Organization::create([
        'nama' => 'Org B ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'orgb' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $orgA->id,
        'status' => 'active',
    ]);
    $userA->assignRole('Editor');

    $userB = User::create([
        'name' => 'User B',
        'email' => 'userb_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $orgB->id,
        'status' => 'active',
    ]);
    $userB->assignRole('Editor');

    NotificationService::send($userA, 'test_type', 'Notif A', 'Pesan A');
    NotificationService::send($userB, 'test_type', 'Notif B', 'Pesan B');

    // User A can only see Notif A
    Sanctum::actingAs($userA);
    $response = $this->getJson('/api/notifications');
    $response->assertStatus(200);
    $items = $response->json('data');
    expect(count($items))->toBe(1);
    expect($items[0]['title'])->toBe('Notif A');

    // User A cannot mark User B's notification as read
    $notifB = Notification::where('user_id', $userB->id)->first();
    $this->postJson("/api/notifications/{$notifB->id}/read")->assertStatus(404);

    // User A marks own notification as read
    $notifA = Notification::where('user_id', $userA->id)->first();
    $this->postJson("/api/notifications/{$notifA->id}/read")->assertStatus(200);

    // Unread count becomes 0
    $unreadResp = $this->getJson('/api/notifications/unread-count');
    expect($unreadResp->json('unread_count'))->toBe(0);
});

test('NOTIFICATION SYSTEM: markAllAsRead updates all unread items', function () {
    $timestamp = microtime(true);
    $org = Organization::create([
        'nama' => 'Org Test ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'orgtest' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);
    $user = User::create([
        'name' => 'User Test',
        'email' => 'user_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $user->assignRole('Kontributor');

    NotificationService::send($user, 'type_1', 'Judul 1', 'Pesan 1');
    NotificationService::send($user, 'type_2', 'Judul 2', 'Pesan 2');
    NotificationService::send($user, 'type_3', 'Judul 3', 'Pesan 3');

    Sanctum::actingAs($user);

    $unreadBefore = $this->getJson('/api/notifications/unread-count');
    expect($unreadBefore->json('unread_count'))->toBe(3);

    $this->postJson('/api/notifications/read-all')->assertStatus(200);

    $unreadAfter = $this->getJson('/api/notifications/unread-count');
    expect($unreadAfter->json('unread_count'))->toBe(0);
});
