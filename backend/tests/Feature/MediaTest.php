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
        ->assertForbidden();

    $this->assertDatabaseHas('media', ['id' => $mediaId]);
});

test('admin can upload document and file is stored in storage and database', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('SK_Kepengurusan_2026.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name'       => 'SK Kepengurusan HIMATIF 2026',
            'file'       => $file,
            'category'   => 'SK',
            'visibility' => 'public',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'SK Kepengurusan HIMATIF 2026')
        ->assertJsonPath('data.category', 'SK')
        ->assertJsonPath('data.visibility', 'public')
        ->assertJsonPath('data.filename', 'SK_Kepengurusan_2026.pdf');

    $docId = $response->json('data.id');
    $media = Media::find($docId);
    expect($media)->not->toBeNull();
    expect($media->organization_id)->toBe($this->org1->id);
    expect($media->name)->toBe('SK Kepengurusan HIMATIF 2026');
    expect($media->category)->toBe('SK');
    expect($media->visibility)->toBe('public');

    Storage::disk('public')->assertExists($media->path);
});

test('admin can list and delete documents belonging to their organization', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('Proposal_Lomba.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $uploadRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name'       => 'Proposal Lomba Web ITI',
            'file'       => $file,
            'category'   => 'Proposal',
            'visibility' => 'public',
        ]);

    $docId = $uploadRes->json('data.id');
    $media = Media::find($docId);

    // List documents
    $listRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->getJson('/api/documents');

    $listRes->assertStatus(200);
    expect(collect($listRes->json('data'))->pluck('id')->all())->toContain($docId);

    // Org 2 cannot list Org 1's documents
    $listResOrg2 = $this->actingAs($this->adminOrg2, 'sanctum')
        ->getJson('/api/documents');
    expect(collect($listResOrg2->json('data'))->pluck('id')->all())->not->toContain($docId);

    // Delete document
    $delRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->deleteJson("/api/documents/{$docId}");
    $delRes->assertStatus(200);

    $this->assertDatabaseMissing('media', ['id' => $docId]);
    Storage::disk('public')->assertMissing($media->path);
});

test('public endpoint and secure download for documents with tenant isolation', function () {
    Storage::fake('public');

    $filePublic = UploadedFile::fake()->create('AD_ART_2026.pdf', 300, 'application/pdf');
    $fileInternal = UploadedFile::fake()->create('LPJ_Internal.xlsx', 400, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // 1. Upload public document for Org 1
    $uploadPublicRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name'       => 'AD/ART HIMATIF 2026',
            'file'       => $filePublic,
            'category'   => 'SOP',
            'visibility' => 'public',
        ]);
    $publicDocId = $uploadPublicRes->json('data.id');

    // 2. Upload internal document for Org 1
    $uploadInternalRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name'       => 'Laporan Keuangan Internal',
            'file'       => $fileInternal,
            'category'   => 'LPJ',
            'visibility' => 'internal',
        ]);
    $internalDocId = $uploadInternalRes->json('data.id');

    // 3. Guest accesses public documents endpoint for Org 1
    $publicListRes = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/documents");
    $publicListRes->assertStatus(200);
    $docTitles = collect($publicListRes->json('data'))->pluck('name')->all();
    expect($docTitles)->toContain('AD/ART HIMATIF 2026');
    expect($docTitles)->not->toContain('Laporan Keuangan Internal');

    // 4. Guest can download public document
    $downloadRes = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/documents/{$publicDocId}/download");
    $downloadRes->assertStatus(200);

    // 5. Guest cannot download internal document (403)
    $internalDownloadRes = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/documents/{$internalDocId}/download");
    $internalDownloadRes->assertStatus(403);

    // 6. Cross-tenant isolation: cannot download Org 1 document through Org 2 endpoint (404)
    $crossTenantRes = $this->getJson("/api/public/organizations/{$this->org2->subdomain}/documents/{$publicDocId}/download");
    $crossTenantRes->assertStatus(404);
});

