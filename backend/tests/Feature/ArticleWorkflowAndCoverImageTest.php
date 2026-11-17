<?php

use App\Models\Category;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Storage::fake('public');

    // Ensure permissions exist
    Permission::firstOrCreate(['name' => 'posts.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'posts.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'posts.update', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'posts.delete', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'posts.publish', 'guard_name' => 'web']);

    $adminRole = Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
    $adminRole->syncPermissions(['posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish']);

    $contribRole = Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
    $contribRole->syncPermissions(['posts.view', 'posts.create', 'posts.update', 'posts.delete']);

    $timestamp = microtime(true);

    $this->org1 = Organization::create([
        'nama' => 'HMPS Informatika ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'hmif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->org2 = Organization::create([
        'nama' => 'BEM ITI ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'bem' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->adminOrg1 = User::create([
        'name' => 'Admin HMIF',
        'email' => 'admin_hmif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);
    $this->adminOrg1->assignRole('Admin Organisasi');

    $this->adminOrg2 = User::create([
        'name' => 'Admin BEM',
        'email' => 'admin_bem_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org2->id,
        'status' => 'active',
    ]);
    $this->adminOrg2->assignRole('Admin Organisasi');

    $this->contributor1 = User::create([
        'name' => 'Kontributor HMIF',
        'email' => 'contrib_hmif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);
    $this->contributor1->assignRole('Kontributor');
});

test('contributor can submit article for review with base64 cover image and category/tags without 500 error', function () {
    // 1x1 transparent PNG as base64 Data URL
    $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    $category = Category::create([
        'organization_id' => $this->org1->id,
        'name' => 'Akademik',
        'slug' => 'akademik-' . Str::random(5),
    ]);

    $tag = Tag::create([
        'organization_id' => $this->org1->id,
        'name' => 'Workshop',
        'slug' => 'workshop-' . Str::random(5),
    ]);

    $response = $this->actingAs($this->contributor1, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Podcast ITI Perkuat Kreativitas Mahasiswa',
            'konten' => '<p>Podcast ITI terus mengembangkan perannya sebagai wadah kreativitas.</p>',
            'excerpt' => 'Podcast ITI menjadi wadah mahasiswa untuk berbagi ide.',
            'cover_image' => $base64Image,
            'status' => 'review',
            'category_id' => $category->id,
            'tags' => [$tag->id],
            'meta_title' => 'Podcast ITI Perkuat Kreativitas',
            'meta_description' => 'Podcast ITI adalah program resmi.',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'message' => 'Artikel berhasil dibuat!',
            'data' => [
                'judul' => 'Podcast ITI Perkuat Kreativitas Mahasiswa',
                'status' => 'review',
                'category_id' => $category->id,
            ]
        ]);

    $post = Post::where('judul', 'Podcast ITI Perkuat Kreativitas Mahasiswa')->first();
    expect($post)->not->toBeNull();
    expect($post->status)->toBe('review');
    expect($post->organization_id)->toBe($this->org1->id);
    expect($post->user_id)->toBe($this->contributor1->id);
    expect($post->cover_image)->toStartWith('/storage/posts/covers/');
    expect($post->tags)->toHaveCount(1);

    // Verify file exists on disk
    $storagePath = str_replace('/storage/', '', $post->cover_image);
    expect(Storage::disk('public')->exists($storagePath))->toBeTrue();
});

test('admin can create and directly publish article with draft or published status', function () {
    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Warta Resmi Dies Natalis',
            'konten' => '<p>Perayaan Dies Natalis ITI ke-42.</p>',
            'status' => 'published',
        ]);

    $response->assertStatus(201);
    $post = Post::where('judul', 'Warta Resmi Dies Natalis')->first();
    expect($post->status)->toBe('published');
    expect($post->published_at)->not->toBeNull();
});

test('contributor attempting to directly publish is automatically set to review status', function () {
    $response = $this->actingAs($this->contributor1, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Artikel Upaya Langsung Publish',
            'konten' => '<p>Konten kontributor yang ingin langsung terbit.</p>',
            'status' => 'published',
        ]);

    $response->assertStatus(201);
    $post = Post::where('judul', 'Artikel Upaya Langsung Publish')->first();
    expect($post->status)->toBe('review');
});

test('admin can approve review article and publish it', function () {
    $post = Post::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->contributor1->id,
        'judul' => 'Artikel Menunggu Persetujuan',
        'slug' => 'artikel-menunggu-persetujuan-' . Str::random(5),
        'konten' => '<p>Konten artikel review.</p>',
        'status' => 'review',
    ]);

    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [
            'status' => 'published',
        ]);

    $response->assertStatus(200);
    $freshPost = $post->fresh();
    expect($freshPost->status)->toBe('published');
    expect($freshPost->published_at)->not->toBeNull();
});

test('updating article with new cover image replaces old file and preserves clean path', function () {
    $oldBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    $createResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Artikel Gambar Awal',
            'konten' => '<p>Konten awal.</p>',
            'cover_image' => $oldBase64,
            'status' => 'draft',
        ]);

    $postId = $createResponse->json('data.id');
    $oldPost = Post::find($postId);
    $oldPath = str_replace('/storage/', '', $oldPost->cover_image);
    expect(Storage::disk('public')->exists($oldPath))->toBeTrue();

    // Now update with new image
    $newBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-new-image-content');
    $updateResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->putJson("/api/posts/{$postId}", [
            'cover_image' => $newBase64,
        ]);

    $updateResponse->assertStatus(200);
    $freshPost = Post::find($postId);
    $newPath = str_replace('/storage/', '', $freshPost->cover_image);

    expect($newPath)->not->toBe($oldPath);
    expect(Storage::disk('public')->exists($newPath))->toBeTrue();
    expect(Storage::disk('public')->exists($oldPath))->toBeFalse();
});

test('tenant isolation: org2 user cannot view, edit, or delete org1 posts', function () {
    $postOrg1 = Post::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->adminOrg1->id,
        'judul' => 'Artikel Rahasia HMIF',
        'slug' => 'artikel-rahasia-hmif-' . Str::random(5),
        'konten' => '<p>Konten HMIF.</p>',
        'status' => 'draft',
    ]);

    // Org 2 tries to view
    $showRes = $this->actingAs($this->adminOrg2, 'sanctum')->getJson("/api/posts/{$postOrg1->id}");
    $showRes->assertStatus(403);

    // Org 2 tries to edit
    $updateRes = $this->actingAs($this->adminOrg2, 'sanctum')->putJson("/api/posts/{$postOrg1->id}", [
        'judul' => 'Hacked by BEM',
    ]);
    $updateRes->assertStatus(403);

    // Org 2 tries to delete
    $deleteRes = $this->actingAs($this->adminOrg2, 'sanctum')->deleteJson("/api/posts/{$postOrg1->id}");
    $deleteRes->assertStatus(403);
});

test('user without posts.create permission cannot create post', function () {
    $unprivilegedUser = User::create([
        'name' => 'Viewer Only',
        'email' => 'viewer_' . microtime(true) . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($unprivilegedUser, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Artikel Tidak Berizin',
            'konten' => '<p>Konten.</p>',
            'status' => 'draft',
        ]);

    $response->assertStatus(403);
});
