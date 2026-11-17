<?php

use App\Http\Controllers\Api\RoleController;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');

    // Ensure all permissions and canonical role definitions are in place
    RoleController::ensureDefaultPermissions();

    $timestamp = microtime(true);

    // Organization A
    $this->orgA = Organization::create([
        'nama' => 'HMPS Informatika ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'hmif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    // Organization B
    $this->orgB = Organization::create([
        'nama' => 'BEM ITI ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'bem' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    // Editor A (Org A)
    $this->editorA = User::create([
        'name' => 'Editor HMIF',
        'email' => 'editor_hmif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $this->editorA->assignRole('Editor');

    // Editor B (Org B)
    $this->editorB = User::create([
        'name' => 'Editor BEM',
        'email' => 'editor_bem_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgB->id,
        'status' => 'active',
    ]);
    $this->editorB->assignRole('Editor');

    // Admin A (Org A)
    $this->adminA = User::create([
        'name' => 'Admin HMIF',
        'email' => 'admin_hmif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $this->adminA->assignRole('Admin Organisasi');
});

test('editor has canonical permissions assigned correctly', function () {
    $editorPermissions = $this->editorA->getAllPermissions()->pluck('name')->toArray();

    // Must have content CRUD
    expect($editorPermissions)->toContain('posts.view', 'posts.create', 'posts.update', 'posts.delete');
    expect($editorPermissions)->toContain('agenda.view', 'agenda.create', 'agenda.update', 'agenda.delete');
    expect($editorPermissions)->toContain('announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete');
    expect($editorPermissions)->toContain('gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete');
    expect($editorPermissions)->toContain('documents.view', 'documents.create', 'documents.update', 'documents.delete');
    expect($editorPermissions)->toContain('structure.view');

    // Must NOT have publish or administrative permissions
    expect($editorPermissions)->not->toContain('posts.publish');
    expect($editorPermissions)->not->toContain('agenda.publish');
    expect($editorPermissions)->not->toContain('announcements.publish');
    expect($editorPermissions)->not->toContain('structure.manage');
    expect($editorPermissions)->not->toContain('users.view', 'users.manage');
    expect($editorPermissions)->not->toContain('organizations.view', 'organizations.manage');
    expect($editorPermissions)->not->toContain('periods.manage');
    expect($editorPermissions)->not->toContain('activity_logs.view');
});

test('editor can create and edit posts in their organization, but direct publish is downgraded to review', function () {
    $category = Category::create([
        'organization_id' => $this->orgA->id,
        'name' => 'Liputan Kegiatan',
        'slug' => 'liputan-kegiatan-' . Str::random(5),
    ]);

    // Create post attempting 'published' status
    $res = $this->actingAs($this->editorA, 'sanctum')->postJson('/api/posts', [
        'judul' => 'Liputan Workshop AI 2026',
        'konten' => '<p>Workshop AI telah selesai dilaksanakan.</p>',
        'status' => 'published', // Editor cannot publish directly!
        'category_id' => $category->id,
    ]);

    $res->assertStatus(201);
    $postId = $res->json('data.id');
    $post = Post::find($postId);

    expect($post->status)->toBe('review'); // Downgraded to review
    expect($post->organization_id)->toBe($this->orgA->id);
    expect($post->user_id)->toBe($this->editorA->id);

    // Editor updates post
    $updateRes = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/posts/{$postId}", [
        'judul' => 'Liputan Workshop AI 2026 (Revisi)',
    ]);
    $updateRes->assertStatus(200);
    expect($post->fresh()->judul)->toBe('Liputan Workshop AI 2026 (Revisi)');

    // Editor deletes post
    $delRes = $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/posts/{$postId}");
    $delRes->assertStatus(200);
    expect(Post::find($postId))->toBeNull();
});

test('editor can edit posts created by contributors in the same organization, while contributor cannot edit posts of other users', function () {
    $contributor = User::create([
        'name' => 'Kontributor HMIF',
        'email' => 'kontributor_hmif_' . microtime(true) . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $contributor->assignRole('Kontributor');

    $otherUser = User::create([
        'name' => 'Other Kontributor',
        'email' => 'other_kontributor_' . microtime(true) . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $otherUser->assignRole('Kontributor');

    // Post created by contributor
    $post = Post::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $contributor->id,
        'judul' => 'Artikel dari Kontributor',
        'slug' => 'artikel-kontributor-' . Str::random(5),
        'konten' => 'Draft tulisan awal.',
        'status' => 'review',
    ]);

    // 1. Editor CAN edit the contributor's post
    $editorEdit = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/posts/{$post->id}", [
        'judul' => 'Artikel dari Kontributor (Diedit oleh Editor)',
    ]);
    $editorEdit->assertStatus(200);
    expect($post->fresh()->judul)->toBe('Artikel dari Kontributor (Diedit oleh Editor)');

    // 2. Another Contributor CANNOT edit this post (403)
    $otherContribEdit = $this->actingAs($otherUser, 'sanctum')->putJson("/api/posts/{$post->id}", [
        'judul' => 'Dibajak oleh Kontributor Lain',
    ]);
    $otherContribEdit->assertStatus(403);
});

test('editor can manage agenda activities within their organization', function () {
    $res = $this->actingAs($this->editorA, 'sanctum')->postJson('/api/activities', [
        'judul' => 'Rapat Redaksi Bulanan',
        'deskripsi' => 'Membahas konten warta kampus.',
        'tanggal_pelaksanaan' => '2026-10-15',
        'status' => 'draft',
    ]);

    $res->assertStatus(201);
    $agendaId = $res->json('data.id');

    $updateRes = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/activities/{$agendaId}", [
        'judul' => 'Rapat Redaksi Bulanan (Updated)',
        'deskripsi' => 'Deskripsi update.',
        'tanggal_pelaksanaan' => '2026-10-16',
        'status' => 'draft',
    ]);
    $updateRes->assertStatus(200);

    $delRes = $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/activities/{$agendaId}");
    $delRes->assertStatus(200);
    expect(Activity::find($agendaId))->toBeNull();
});

test('editor can manage announcements within their organization', function () {
    $res = $this->actingAs($this->editorA, 'sanctum')->postJson('/api/announcements', [
        'title' => 'Pengumuman Draf Internal',
        'content' => 'Pengumuman khusus internal.',
        'status' => 'draft',
    ]);

    $res->assertStatus(201);
    $announcementId = $res->json('data.id');

    $updateRes = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/announcements/{$announcementId}", [
        'title' => 'Pengumuman Draf Internal (Edited)',
    ]);
    $updateRes->assertStatus(200);

    $delRes = $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/announcements/{$announcementId}");
    $delRes->assertStatus(200);
    expect(Announcement::find($announcementId))->toBeNull();
});

test('editor can upload, view, edit, and delete gallery images', function () {
    $imageFile = UploadedFile::fake()->image('kegiatan.jpg', 800, 600);

    $res = $this->actingAs($this->editorA, 'sanctum')->postJson('/api/upload-image', [
        'image' => $imageFile,
        'title' => 'Foto Dokumentasi Pelantikan',
        'category' => 'Pelantikan',
    ]);

    $res->assertStatus(200);
    $mediaId = $res->json('id');
    expect($mediaId)->not->toBeNull();

    // View list
    $listRes = $this->actingAs($this->editorA, 'sanctum')->getJson('/api/media');
    $listRes->assertStatus(200);

    // Update metadata
    $updateRes = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/media/{$mediaId}", [
        'title' => 'Foto Dokumentasi Pelantikan (Updated)',
    ]);
    $updateRes->assertStatus(200);

    // Delete
    $delRes = $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/media/{$mediaId}");
    $delRes->assertStatus(200);
    expect(Media::find($mediaId))->toBeNull();
});

test('editor can upload, view, and delete documents', function () {
    $docFile = UploadedFile::fake()->create('sop-liputan.pdf', 500, 'application/pdf');

    $res = $this->actingAs($this->editorA, 'sanctum')->postJson('/api/documents', [
        'file' => $docFile,
        'name' => 'SOP Liputan Berita',
        'category' => 'SOP',
    ]);

    $res->assertStatus(201);
    $docId = $res->json('data.id');

    // List docs
    $listRes = $this->actingAs($this->editorA, 'sanctum')->getJson('/api/documents');
    $listRes->assertStatus(200);

    // Delete doc
    $delRes = $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/documents/{$docId}");
    $delRes->assertStatus(200);
    expect(Media::find($docId))->toBeNull();
});

test('editor can view structure but cannot create, update, or delete committee members', function () {
    $period = OrganizationPeriod::create([
        'organization_id' => $this->orgA->id,
        'period_name' => '2026/2027',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ]);

    $committee = Committee::create([
        'organization_id' => $this->orgA->id,
        'organization_period_id' => $period->id,
        'name' => 'Budi Santoso',
        'position' => 'Ketua Umum',
        'status' => 'active',
    ]);

    // View: ALLOWED
    $indexRes = $this->actingAs($this->editorA, 'sanctum')->getJson('/api/committees');
    $indexRes->assertStatus(200);

    $showRes = $this->actingAs($this->editorA, 'sanctum')->getJson("/api/committees/{$committee->id}");
    $showRes->assertStatus(200);

    // Create: FORBIDDEN (403)
    $createRes = $this->actingAs($this->editorA, 'sanctum')->postJson('/api/committees', [
        'name' => 'Sekretaris Baru',
        'position' => 'Sekretaris',
        'organization_period_id' => $period->id,
    ]);
    $createRes->assertStatus(403);

    // Update: FORBIDDEN (403)
    $updateRes = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/committees/{$committee->id}", [
        'name' => 'Budi Santoso Edited',
        'position' => 'Ketua Umum',
    ]);
    $updateRes->assertStatus(403);

    // Delete: FORBIDDEN (403)
    $delRes = $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/committees/{$committee->id}");
    $delRes->assertStatus(403);
});

test('editor is strictly forbidden from administrative endpoints', function () {
    // 1. Users management
    $usersRes = $this->actingAs($this->editorA, 'sanctum')->getJson('/api/users');
    $usersRes->assertStatus(403);

    // 2. Organization settings
    $settingsRes = $this->actingAs($this->editorA, 'sanctum')->putJson("/api/organizations/{$this->orgA->id}", [
        'nama' => 'Hacked Organization Name',
    ]);
    $settingsRes->assertStatus(403);

    // 3. Period management
    $periodRes = $this->actingAs($this->editorA, 'sanctum')->postJson("/api/organizations/{$this->orgA->id}/periods", [
        'period_name' => '2027/2028',
        'start_date' => '2027-01-01',
        'end_date' => '2027-12-31',
    ]);
    $periodRes->assertStatus(403);

    // 4. Activity logs
    $logsRes = $this->actingAs($this->editorA, 'sanctum')->getJson('/api/activity-logs');
    $logsRes->assertStatus(403);
});

test('tenant isolation: editor A cannot view, update, or delete any resource of organization B', function () {
    // Create Org B resources
    $postB = Post::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->editorB->id,
        'judul' => 'Artikel Rahasia Org B',
        'slug' => 'artikel-rahasia-b-' . Str::random(5),
        'konten' => 'Konten Org B',
        'status' => 'draft',
    ]);

    $agendaB = Activity::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->editorB->id,
        'judul' => 'Agenda Internal Org B',
        'deskripsi' => 'Deskripsi Org B',
        'tanggal_pelaksanaan' => '2026-11-01',
        'status' => 'draft',
    ]);

    $announcementB = Announcement::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->editorB->id,
        'title' => 'Pengumuman Org B',
        'slug' => 'pengumuman-org-b-' . Str::random(5),
        'content' => 'Isi Org B',
        'status' => 'draft',
    ]);

    $mediaB = Media::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->editorB->id,
        'name' => 'Foto Rahasia Org B',
        'filename' => 'foto-b.webp',
        'path' => 'gallery-images/foto-b.webp',
        'mime_type' => 'image/webp',
    ]);

    // 1. Post Cross-Tenant Isolation
    $this->actingAs($this->editorA, 'sanctum')->getJson("/api/posts/{$postB->id}")->assertStatus(403);
    $this->actingAs($this->editorA, 'sanctum')->putJson("/api/posts/{$postB->id}", ['judul' => 'Hacked'])->assertStatus(403);
    $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/posts/{$postB->id}")->assertStatus(403);

    // 2. Agenda Cross-Tenant Isolation
    $this->actingAs($this->editorA, 'sanctum')->putJson("/api/activities/{$agendaB->id}", ['judul' => 'Hacked'])->assertStatus(403);
    $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/activities/{$agendaB->id}")->assertStatus(403);

    // 3. Announcement Cross-Tenant Isolation
    $this->actingAs($this->editorA, 'sanctum')->getJson("/api/announcements/{$announcementB->id}")->assertStatus(403);
    $this->actingAs($this->editorA, 'sanctum')->putJson("/api/announcements/{$announcementB->id}", ['title' => 'Hacked'])->assertStatus(403);
    $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/announcements/{$announcementB->id}")->assertStatus(403);

    // 4. Media Cross-Tenant Isolation
    $this->actingAs($this->editorA, 'sanctum')->putJson("/api/media/{$mediaB->id}", ['title' => 'Hacked'])->assertStatus(403);
    $this->actingAs($this->editorA, 'sanctum')->deleteJson("/api/media/{$mediaB->id}")->assertStatus(403);
});
