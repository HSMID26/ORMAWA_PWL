<?php

use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
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
        'modul_aktif' => [
            'berita' => true,
            'agenda' => true,
            'pengumuman' => true,
            'galeri' => true,
            'structure' => true,
            'documents' => true,
        ],
    ]);

    $this->org2 = Organization::create([
        'nama' => 'BEM ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'bem' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
        'modul_aktif' => [
            'berita' => true,
            'agenda' => true,
            'pengumuman' => true,
            'galeri' => true,
            'structure' => true,
            'documents' => true,
        ],
    ]);

    $this->period1 = OrganizationPeriod::create([
        'organization_id' => $this->org1->id,
        'period_name' => '2026/2027',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ]);

    $this->adminOrg1 = User::create([
        'name' => 'Admin HIMATIF',
        'email' => 'admin_himatif_gallery_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org1->id,
    ]);
    $this->adminOrg1->assignRole('Admin Organisasi');

    $this->adminOrg2 = User::create([
        'name' => 'Admin BEM',
        'email' => 'admin_bem_gallery_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->org2->id,
    ]);
    $this->adminOrg2->assignRole('Admin Organisasi');
});

// ── GALLERY TESTS ─────────────────────────────────────────────────────────────

test('1. gallery endpoint returns images only', function () {
    Storage::fake('public');

    $imageFile = UploadedFile::fake()->image('kegiatan.jpg', 800, 600);
    $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => $imageFile,
            'title' => 'Foto Kegiatan HIMATIF',
        ]);

    $res = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/media');
    $res->assertStatus(200);

    $items = $res->json('data');
    expect(count($items))->toBe(1);
    expect($items[0]['is_image'])->toBeTrue();
    expect($items[0]['mime_type'])->toContain('image/');
});

test('2, 3, 4. gallery endpoint excludes DOCX, PDF, and XLSX', function () {
    Storage::fake('public');

    // Create 1 DOCX, 1 PDF, 1 XLSX, 1 Image
    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Laporan.docx',
        'filename'        => 'Laporan.docx',
        'path'            => 'documents/laporan.docx',
        'mime_type'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'category'        => 'LPJ',
        'visibility'      => 'public',
        'size'            => 1024,
    ]);

    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'SK_Pengurus.pdf',
        'filename'        => 'SK_Pengurus.pdf',
        'path'            => 'documents/sk.pdf',
        'mime_type'       => 'application/pdf',
        'category'        => 'SK',
        'visibility'      => 'public',
        'size'            => 2048,
    ]);

    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Anggaran.xlsx',
        'filename'        => 'Anggaran.xlsx',
        'path'            => 'documents/anggaran.xlsx',
        'mime_type'       => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'category'        => 'Proposal',
        'visibility'      => 'public',
        'size'            => 3072,
    ]);

    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Dokumentasi Malam Keakraban',
        'filename'        => 'makrab.webp',
        'path'            => 'gallery-images/makrab.webp',
        'mime_type'       => 'image/webp',
        'category'        => 'Dokumentasi',
        'visibility'      => 'public',
        'size'            => 4096,
    ]);

    // Admin Gallery list (GET /api/media)
    $resAdmin = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/media');
    $resAdmin->assertStatus(200);
    $adminData = $resAdmin->json('data');
    expect(count($adminData))->toBe(1);
    expect($adminData[0]['title'])->toBe('Dokumentasi Malam Keakraban');

    // Public Gallery endpoint (GET /api/public/organizations/{slug}/gallery)
    $resPublic = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/gallery");
    $resPublic->assertStatus(200);
    $publicData = $resPublic->json('data');
    expect(count($publicData))->toBe(1);
    expect($publicData[0]['title'])->toBe('Dokumentasi Malam Keakraban');
});

test('5. admin cannot upload DOCX through gallery endpoint', function () {
    Storage::fake('public');

    $doc = UploadedFile::fake()->create('Proposal.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $res = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => $doc,
            'title' => 'Proposal Dokumen',
        ]);

    $res->assertStatus(422);
});

test('6 & 7. admin can upload valid image with metadata and it appears in gallery', function () {
    Storage::fake('public');

    $img = UploadedFile::fake()->image('pelantikan.jpg', 1200, 800);

    $res = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => $img,
            'title' => 'Pelantikan Pengurus 2026',
            'caption' => 'Acara serah terima jabatan.',
            'taken_at' => '2026-08-26',
            'alt_text' => 'Foto pelantikan',
            'category' => 'Pelantikan',
        ]);

    $res->assertStatus(200);
    $id = $res->json('id');

    $listRes = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/media');
    $listRes->assertStatus(200);
    $ids = collect($listRes->json('data'))->pluck('id')->all();
    expect($ids)->toContain($id);
});

test('8. deleted image disappears from gallery', function () {
    Storage::fake('public');

    $img = UploadedFile::fake()->image('to_delete.jpg', 400, 400);
    $uploadRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => $img,
            'title' => 'Temporary Photo',
        ]);
    $id = $uploadRes->json('id');

    $delRes = $this->actingAs($this->adminOrg1, 'sanctum')->deleteJson("/api/media/{$id}");
    $delRes->assertStatus(200);

    $listRes = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/media');
    $ids = collect($listRes->json('data'))->pluck('id')->all();
    expect($ids)->not->toContain($id);
});

test('9. cross-tenant gallery isolation', function () {
    Storage::fake('public');

    $img = UploadedFile::fake()->image('himatif_secret.jpg', 400, 400);
    $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/upload-image', [
            'image' => $img,
            'title' => 'HIMATIF Only',
        ]);

    $bemRes = $this->actingAs($this->adminOrg2, 'sanctum')->getJson('/api/media');
    expect(count($bemRes->json('data')))->toBe(0);

    $pubBemRes = $this->getJson("/api/public/organizations/{$this->org2->subdomain}/gallery");
    expect(count($pubBemRes->json('data')))->toBe(0);
});

// ── DOCUMENTS TESTS ───────────────────────────────────────────────────────────

test('10. document endpoint returns documents only', function () {
    Storage::fake('public');

    $doc = UploadedFile::fake()->create('SK_BPH.pdf', 300, 'application/pdf');
    $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name' => 'SK BPH 2026',
            'file' => $doc,
            'category' => 'SK',
        ]);

    $res = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/documents');
    $res->assertStatus(200);

    $items = $res->json('data');
    expect(count($items))->toBe(1);
    expect($items[0]['name'])->toBe('SK BPH 2026');
    expect($items[0]['mime_type'])->toBe('application/pdf');
});

test('11, 12, 13. documents endpoint excludes JPG, PNG, and WEBP', function () {
    Storage::fake('public');

    // Create 1 JPG, 1 PNG, 1 WEBP in media table
    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Foto_A.jpg',
        'filename'        => 'Foto_A.jpg',
        'path'            => 'gallery-images/foto_a.jpg',
        'mime_type'       => 'image/jpeg',
        'category'        => 'Dokumentasi',
        'visibility'      => 'public',
        'size'            => 1024,
    ]);

    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Foto_B.png',
        'filename'        => 'Foto_B.png',
        'path'            => 'gallery-images/foto_b.png',
        'mime_type'       => 'image/png',
        'category'        => 'Dokumentasi',
        'visibility'      => 'public',
        'size'            => 2048,
    ]);

    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Foto_C.webp',
        'filename'        => 'Foto_C.webp',
        'path'            => 'gallery-images/foto_c.webp',
        'mime_type'       => 'image/webp',
        'category'        => 'Dokumentasi',
        'visibility'      => 'public',
        'size'            => 3072,
    ]);

    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'SOP_Peminjaman.pdf',
        'filename'        => 'SOP_Peminjaman.pdf',
        'path'            => 'documents/sop.pdf',
        'mime_type'       => 'application/pdf',
        'category'        => 'SOP',
        'visibility'      => 'public',
        'size'            => 4096,
    ]);

    // Admin Documents list
    $resAdmin = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/documents');
    $resAdmin->assertStatus(200);
    $adminData = $resAdmin->json('data');
    expect(count($adminData))->toBe(1);
    expect($adminData[0]['name'])->toBe('SOP_Peminjaman.pdf');

    // Public Documents endpoint
    $resPublic = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/documents");
    $resPublic->assertStatus(200);
    $publicData = $resPublic->json('data');
    expect(count($publicData))->toBe(1);
    expect($publicData[0]['name'])->toBe('SOP_Peminjaman.pdf');
});

test('14. admin cannot upload image through document endpoint', function () {
    Storage::fake('public');

    $img = UploadedFile::fake()->image('gambar.jpg', 500, 500);

    $res = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name' => 'Dokumen Gambar Palsu',
            'file' => $img,
            'category' => 'Other',
        ]);

    $res->assertStatus(422);
});

test('15 & 16. admin can upload DOCX/PDF and it appears in Documents', function () {
    Storage::fake('public');

    $doc = UploadedFile::fake()->create('Template_Proposal.docx', 800, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $res = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name' => 'Template Proposal Resmi HIMATIF',
            'file' => $doc,
            'category' => 'Template',
        ]);

    $res->assertStatus(201);
    $id = $res->json('data.id');

    $listRes = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/documents');
    $listRes->assertStatus(200);
    $ids = collect($listRes->json('data'))->pluck('id')->all();
    expect($ids)->toContain($id);
});

test('17. deleted document disappears from documents list', function () {
    Storage::fake('public');

    $doc = UploadedFile::fake()->create('To_Delete.pdf', 300, 'application/pdf');
    $uploadRes = $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name' => 'Doc To Delete',
            'file' => $doc,
            'category' => 'Other',
        ]);
    $id = $uploadRes->json('data.id');

    $delRes = $this->actingAs($this->adminOrg1, 'sanctum')->deleteJson("/api/documents/{$id}");
    $delRes->assertStatus(200);

    $listRes = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/documents');
    $ids = collect($listRes->json('data'))->pluck('id')->all();
    expect($ids)->not->toContain($id);
});

test('18. cross-tenant document isolation', function () {
    Storage::fake('public');

    $doc = UploadedFile::fake()->create('HIMATIF_Only_LPJ.pdf', 500, 'application/pdf');
    $this->actingAs($this->adminOrg1, 'sanctum')
        ->postJson('/api/documents', [
            'name' => 'LPJ HIMATIF Internal',
            'file' => $doc,
            'category' => 'LPJ',
        ]);

    $bemRes = $this->actingAs($this->adminOrg2, 'sanctum')->getJson('/api/documents');
    expect(count($bemRes->json('data')))->toBe(0);

    $pubBemRes = $this->getJson("/api/public/organizations/{$this->org2->subdomain}/documents");
    expect(count($pubBemRes->json('data')))->toBe(0);
});

// ── CLASSIFICATION TESTS ──────────────────────────────────────────────────────

test('19. existing DOCX record does not appear in Gallery', function () {
    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Laporan_Tahunan_Existing.docx',
        'filename'        => 'Laporan_Tahunan_Existing.docx',
        'path'            => 'documents/laporan.docx',
        'mime_type'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'category'        => 'LPJ',
        'visibility'      => 'public',
        'size'            => 1024,
    ]);

    // Admin Gallery List
    $resGallery = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/media');
    $resGallery->assertStatus(200);
    expect(count($resGallery->json('data')))->toBe(0);

    // Public Gallery List
    $resPubGallery = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/gallery");
    $resPubGallery->assertStatus(200);
    expect(count($resPubGallery->json('data')))->toBe(0);

    // But it DOES appear in Documents
    $resDocs = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/documents');
    $resDocs->assertStatus(200);
    expect(count($resDocs->json('data')))->toBe(1);
    expect($resDocs->json('data.0.name'))->toBe('Laporan_Tahunan_Existing.docx');
});

test('20. existing image record does not appear in Documents', function () {
    Media::create([
        'organization_id' => $this->org1->id,
        'user_id'         => $this->adminOrg1->id,
        'name'            => 'Foto_Dokumentasi_Existing.jpg',
        'filename'        => 'Foto_Dokumentasi_Existing.jpg',
        'path'            => 'gallery-images/foto.jpg',
        'mime_type'       => 'image/jpeg',
        'category'        => 'Dokumentasi',
        'visibility'      => 'public',
        'size'            => 2048,
    ]);

    // Admin Documents List
    $resDocs = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/documents');
    $resDocs->assertStatus(200);
    expect(count($resDocs->json('data')))->toBe(0);

    // Public Documents List
    $resPubDocs = $this->getJson("/api/public/organizations/{$this->org1->subdomain}/documents");
    $resPubDocs->assertStatus(200);
    expect(count($resPubDocs->json('data')))->toBe(0);

    // But it DOES appear in Gallery
    $resGallery = $this->actingAs($this->adminOrg1, 'sanctum')->getJson('/api/media');
    $resGallery->assertStatus(200);
    expect(count($resGallery->json('data')))->toBe(1);
    expect($resGallery->json('data.0.title'))->toBe('Foto_Dokumentasi_Existing.jpg');
});