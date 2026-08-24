<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\Post;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Media;
use App\Models\Committee;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $roles = ['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor'];
    foreach ($roles as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    $ts = microtime(true);

    // Organization A
    $this->orgA = Organization::create([
        'nama' => 'HIMATIF A ' . $ts,
        'subdomain' => 'himatif-a-' . str_replace('.', '', (string)$ts),
        'jenis' => 'HMPS',
        'status' => 'active'
    ]);
    $this->adminA = User::create([
        'name' => 'Admin A',
        'email' => 'admin_a_' . $ts . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active'
    ]);
    $this->adminA->assignRole('Admin Organisasi');

    // Organization B
    $this->orgB = Organization::create([
        'nama' => 'BEM B ' . $ts,
        'subdomain' => 'bem-b-' . str_replace('.', '', (string)$ts),
        'jenis' => 'BEM',
        'status' => 'active'
    ]);
    $this->adminB = User::create([
        'name' => 'Admin B',
        'email' => 'admin_b_' . $ts . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgB->id,
        'status' => 'active'
    ]);
    $this->adminB->assignRole('Admin Organisasi');

    // Contributor in Org A
    $this->contributorA = User::create([
        'name' => 'Contributor A',
        'email' => 'contrib_a_' . $ts . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active'
    ]);
    $this->contributorA->assignRole('Kontributor');
});

test('TENANT ATTACK: Admin A cannot read, update, or delete Posts of Org B', function () {
    $postB = Post::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->adminB->id,
        'judul' => 'Rahasia Org B',
        'slug' => 'rahasia-b',
        'konten' => 'Konten Rahasia Org B',
        'status' => 'published'
    ]);

    Sanctum::actingAs($this->adminA);

    // 1. Cannot GET single post of Org B
    $resGet = $this->getJson("/api/posts/{$postB->id}");
    expect($resGet->status())->toBeIn([403, 404]);

    // 2. Cannot UPDATE post of Org B
    $resPut = $this->putJson("/api/posts/{$postB->id}", [
        'judul' => 'Hacked by Admin A'
    ]);
    expect($resPut->status())->toBeIn([403, 404]);
    expect($postB->fresh()->judul)->toBe('Rahasia Org B');

    // 3. Cannot DELETE post of Org B
    $resDel = $this->deleteJson("/api/posts/{$postB->id}");
    expect($resDel->status())->toBeIn([403, 404]);
    expect(Post::withoutGlobalScopes()->where('id', $postB->id)->exists())->toBeTrue();
});

test('TENANT ATTACK: Admin A cannot read, update, or delete Activities of Org B', function () {
    $actB = Activity::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->adminB->id,
        'judul' => 'Agenda Rahasia B',
        'deskripsi' => 'Rapat Rahasia',
        'tanggal_pelaksanaan' => now()->addDays(3)->toDateString(),
        'status' => 'published'
    ]);

    Sanctum::actingAs($this->adminA);

    // 1. Cannot GET single activity of Org B
    $resGet = $this->getJson("/api/activities/{$actB->id}");
    expect($resGet->status())->toBeIn([403, 404]);

    // 2. Cannot UPDATE activity of Org B
    $resPut = $this->putJson("/api/activities/{$actB->id}", [
        'judul' => 'Hacked Agenda'
    ]);
    expect($resPut->status())->toBeIn([403, 404]);
    expect(Activity::withoutGlobalScopes()->find($actB->id)->judul)->toBe('Agenda Rahasia B');

    // 3. Cannot DELETE activity of Org B
    $resDel = $this->deleteJson("/api/activities/{$actB->id}");
    expect($resDel->status())->toBeIn([403, 404]);
    expect(Activity::withoutGlobalScopes()->where('id', $actB->id)->exists())->toBeTrue();
});

test('TENANT ATTACK: Admin A cannot read, update, or delete Announcements of Org B', function () {
    $annB = Announcement::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->adminB->id,
        'title' => 'Pengumuman Rahasia B',
        'slug' => 'pengumuman-rahasia-b',
        'content' => 'Maklumat Internal Org B',
        'priority' => 'urgent',
        'status' => 'published'
    ]);

    Sanctum::actingAs($this->adminA);

    // 1. Cannot GET single announcement of Org B
    $resGet = $this->getJson("/api/announcements/{$annB->id}");
    expect($resGet->status())->toBeIn([403, 404]);

    // 2. Cannot UPDATE announcement of Org B
    $resPut = $this->putJson("/api/announcements/{$annB->id}", [
        'title' => 'Hacked Maklumat'
    ]);
    expect($resPut->status())->toBeIn([403, 404]);
    expect(Announcement::withoutGlobalScopes()->find($annB->id)->title)->toBe('Pengumuman Rahasia B');

    // 3. Cannot DELETE announcement of Org B
    $resDel = $this->deleteJson("/api/announcements/{$annB->id}");
    expect($resDel->status())->toBeIn([403, 404]);
    expect(Announcement::withoutGlobalScopes()->where('id', $annB->id)->exists())->toBeTrue();
});

test('TENANT ATTACK: Admin A cannot delete Media of Org B', function () {
    $mediaB = Media::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->adminB->id,
        'filename' => 'dokumen_rahasia_b.webp',
        'path' => 'editor-images/dokumen_rahasia_b.webp',
        'mime_type' => 'image/webp',
        'size' => 1024
    ]);

    Sanctum::actingAs($this->adminA);

    $resDel = $this->deleteJson("/api/media/{$mediaB->id}");
    expect($resDel->status())->toBeIn([403, 404]);
    expect(Media::withoutGlobalScopes()->where('id', $mediaB->id)->exists())->toBeTrue();
});

test('TENANT ATTACK: Admin A sending organization_id of Org B to dashboard receives own Org A stats', function () {
    Sanctum::actingAs($this->adminA);

    $response = $this->getJson("/api/organization/dashboard?organization_id={$this->orgB->id}");
    $response->assertStatus(200);

    // Must return Org A data, ignoring the manipulated query parameter
    expect($response->json('data.organization.id'))->toBe($this->orgA->id);
    expect($response->json('data.organization.nama'))->toBe($this->orgA->nama);
});

test('EDITORIAL WORKFLOW: Contributor creating published post is forced to review status', function () {
    Sanctum::actingAs($this->contributorA);

    $response = $this->postJson('/api/posts', [
        'judul' => 'Artikel dari Kontributor',
        'konten' => 'Isi artikel kontributor',
        'status' => 'published' // Contributor trying to publish immediately
    ]);

    $response->assertStatus(201);
    expect($response->json('data.status'))->toBe('review');
    expect(Post::find($response->json('data.id'))->status)->toBe('review');
});
