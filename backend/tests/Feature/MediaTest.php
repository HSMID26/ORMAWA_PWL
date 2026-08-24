<?php

use App\Models\Media;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);

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
});

test('media upload compresses and stores webp image', function () {
    Storage::fake('public');

    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => UploadedFile::fake()->image('banner.jpg', 1200, 600),
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'id',
            'type',
            'url',
        ]);

    $mediaId = $response->json('id');
    $media = Media::find($mediaId);
    expect($media)->not->toBeNull();
    expect($media->organization_id)->toBe($this->org1->id);
    expect($media->mime_type)->toBe('image/webp');
    expect($media->path)->toEndWith('.webp');

    Storage::disk('public')->assertExists($media->path);
});

test('media delete cleans up database record and physical storage file', function () {
    Storage::fake('public');

    $uploadResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => UploadedFile::fake()->image('event.png', 800, 600),
        ]);

    $mediaId = $uploadResponse->json('id');
    $media = Media::find($mediaId);
    $path = $media->path;

    Storage::disk('public')->assertExists($path);

    // Delete media
    $deleteResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->deleteJson("/api/media/{$mediaId}");

    $deleteResponse->assertStatus(200);
    $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    Storage::disk('public')->assertMissing($path);
});

test('tenant isolation: user from org 2 cannot delete media of org 1', function () {
    Storage::fake('public');

    $uploadResponse = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => UploadedFile::fake()->image('logo.png', 500, 500),
        ]);

    $mediaId = $uploadResponse->json('id');

    // Admin Org 2 tries to delete
    $this->actingAs($this->adminOrg2, 'sanctum')
        ->deleteJson("/api/media/{$mediaId}")
        ->assertStatus(404);

    $this->assertDatabaseHas('media', ['id' => $mediaId]);
});
