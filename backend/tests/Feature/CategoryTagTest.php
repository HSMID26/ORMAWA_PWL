<?php

use App\Models\Category;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

    $timestamp = microtime(true);

    $this->org1 = Organization::create([
        'nama' => 'HIMATIF ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'himatif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->org2 = Organization::create([
        'nama' => 'BEM ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'bem' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $this->adminOrg1 = User::create([
        'name' => 'Admin HIMATIF',
        'email' => 'admin_himatif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
    ]);
    $this->adminOrg1->assignRole('Admin Organisasi');

    $this->adminOrg2 = User::create([
        'name' => 'Admin BEM',
        'email' => 'admin_bem_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org2->id,
    ]);
    $this->adminOrg2->assignRole('Admin Organisasi');

    $this->contributor1 = User::create([
        'name' => 'Kontributor HIMATIF',
        'email' => 'kontrib_himatif_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
    ]);
    $this->contributor1->assignRole('Kontributor');
});

test('category and tag creation and association with post', function () {
    $catResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/categories', [
            'name' => 'Akademik',
        ]);
    $catResponse->assertStatus(201);
    $categoryId = $catResponse->json('data.id');

    $tag1Response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/tags', [
            'name' => 'Workshop',
        ]);
    $tag2Response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/tags', [
            'name' => 'Laravel',
        ]);

    $tag1Id = $tag1Response->json('data.id');
    $tag2Id = $tag2Response->json('data.id');

    // Create Post with Category & Tags
    $postResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Workshop Backend 2026',
            'konten' => '<p>Belajar Laravel</p>',
            'excerpt' => 'Workshop backend seru',
            'status' => 'published',
            'category_id' => $categoryId,
            'tags' => [$tag1Id, $tag2Id],
        ]);

    $postResponse->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'judul' => 'Workshop Backend 2026',
                'category_id' => $categoryId,
            ]
        ]);

    $postId = $postResponse->json('data.id');
    $post = Post::with(['category', 'tags'])->find($postId);
    expect($post->category->name)->toBe('Akademik');
    expect($post->tags)->toHaveCount(2);

    // Update Post tags (sync to only 1 tag)
    $updateResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->putJson("/api/posts/{$postId}", [
            'judul' => 'Workshop Backend 2026 (Updated)',
            'tags' => [$tag1Id],
        ]);

    $updateResponse->assertStatus(200);
    $post->refresh();
    expect($post->tags)->toHaveCount(1);
    expect($post->tags->first()->id)->toBe($tag1Id);
});

test('category tenant isolation: org 1 cannot see org 2 category', function () {
    Category::create([
        'organization_id' => $this->org2->id,
        'name' => 'BEM Specific Category',
        'slug' => 'bem-cat',
    ]);

    $listResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->getJson('/api/categories');

    $listResponse->assertStatus(200);
    $names = collect($listResponse->json('data'))->pluck('name')->all();
    expect($names)->not->toContain('BEM Specific Category');
});

test('post review workflow converts contributor published status to review with notification and activity log', function () {
    $postResponse = $this->actingAs($this->contributor1, 'sanctum')
        ->postJson('/api/posts', [
            'judul' => 'Artikel Dari Kontributor',
            'konten' => '<p>Konten berita penting</p>',
            'status' => 'published', // Kontributor chooses published
        ]);

    $postResponse->assertStatus(201);
    $post = Post::find($postResponse->json('data.id'));

    // Status must be automatically set to 'review' for contributor
    expect($post->status)->toBe('review');

    // Notification created for Admin Organisasi
    $this->assertDatabaseHas('notifications', [
        'user_id' => $this->adminOrg1->id,
        'type' => 'content_review',
    ]);

    // Activity log created
    $this->assertDatabaseHas('activity_logs', [
        'module' => 'posts',
        'action' => 'create',
    ]);

    // Admin Organisasi publishes post -> notification sent to author
    $publishResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [
            'status' => 'published',
        ]);

    $publishResponse->assertStatus(200);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $this->contributor1->id,
        'type' => 'content_published',
    ]);
});

test('public article detail and global categories endpoints expose category and tags accurately', function () {
    $category = Category::create([
        'organization_id' => $this->org1->id,
        'name' => 'Prestasi Mahasiswa',
        'slug' => 'prestasi-mahasiswa',
    ]);

    $tag = Tag::create([
        'name' => 'Juara 1',
        'slug' => 'juara-1',
    ]);

    $post = Post::create([
        'organization_id' => $this->org1->id,
        'user_id' => $this->adminOrg1->id,
        'category_id' => $category->id,
        'judul' => 'Tim Robotik Juara 1 Nasional',
        'slug' => 'tim-robotik-juara-1-nasional',
        'konten' => 'Isi artikel lengkap prestasi...',
        'excerpt' => 'Ringkasan prestasi...',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $post->tags()->sync([$tag->id]);

    // Test GET /api/public/categories
    $globalCatsRes = $this->getJson('/api/public/categories');
    $globalCatsRes->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Prestasi Mahasiswa',
            'slug' => 'prestasi-mahasiswa',
        ]);

    // Test GET /api/public/organizations/{subdomain}/articles/{slug}
    $detailRes = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/articles/{$post->slug}");
    $detailRes->assertStatus(200);
    $data = $detailRes->json('data');

    expect($data['category'])->not->toBeNull();
    expect($data['category']['name'])->toBe('Prestasi Mahasiswa');
    expect($data['tags'])->toHaveCount(1);
    expect($data['tags'][0]['name'])->toBe('Juara 1');
});

test('categories and tags endpoints auto-provision baseline options if empty', function () {
    $timestamp = microtime(true);
    $freshOrg = Organization::create([
        'nama' => 'UKM Musik ' . $timestamp,
        'jenis' => 'UKM',
        'subdomain' => 'musik' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    $freshAdmin = User::create([
        'name' => 'Admin Musik',
        'email' => 'admin_musik_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $freshOrg->id,
    ]);
    $freshAdmin->assignRole('Admin Organisasi');

    // Query categories as fresh organization
    $catRes = $this->actingAs($freshAdmin, 'sanctum')->getJson('/api/categories');
    $catRes->assertStatus(200);
    $catNames = collect($catRes->json('data'))->pluck('name')->all();

    expect($catNames)->toContain('Akademik');
    expect($catNames)->toContain('Kegiatan');
    expect($catNames)->toContain('Prestasi');
    expect($catNames)->toContain('Informasi');

    // Query tags
    $tagRes = $this->actingAs($freshAdmin, 'sanctum')->getJson('/api/tags');
    $tagRes->assertStatus(200);
    $tagNames = collect($tagRes->json('data'))->pluck('name')->all();

    expect($tagNames)->toContain('Mahasiswa');
    expect($tagNames)->toContain('Prestasi');
});

test('multiple organizations can query categories simultaneously without slug collision or 500 error', function () {
    $timestamp = microtime(true);
    $orgA = Organization::create([
        'nama' => 'Org A ' . $timestamp,
        'jenis' => 'UKM',
        'subdomain' => 'orga' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);
    $userA = User::create([
        'name' => 'Admin Org A',
        'email' => 'admin_a_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $orgA->id,
    ]);
    $userA->assignRole('Admin Organisasi');

    $orgB = Organization::create([
        'nama' => 'Org B ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'orgb' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);
    $userB = User::create([
        'name' => 'Admin Org B',
        'email' => 'admin_b_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $orgB->id,
    ]);
    $userB->assignRole('Admin Organisasi');

    // Both query /api/categories -> both get 200 with baseline categories
    $resA = $this->actingAs($userA, 'sanctum')->getJson('/api/categories');
    $resA->assertStatus(200);
    expect($resA->json('data'))->not->toBeEmpty();

    $resB = $this->actingAs($userB, 'sanctum')->getJson('/api/categories');
    $resB->assertStatus(200);
    expect($resB->json('data'))->not->toBeEmpty();

    // Quick add custom category for Org A
    $createCatA = $this->actingAs($userA, 'sanctum')->postJson('/api/categories', [
        'name' => 'Khusus Org A',
    ]);
    $createCatA->assertStatus(201);

    // Quick add same category name for Org B -> works cleanly per org
    $createCatB = $this->actingAs($userB, 'sanctum')->postJson('/api/categories', [
        'name' => 'Khusus Org A',
    ]);
    $createCatB->assertStatus(201);
});



