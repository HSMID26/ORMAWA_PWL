<?php

use App\Http\Controllers\Api\RoleController;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Storage::fake('public');
    RoleController::ensureDefaultPermissions();

    $timestamp = microtime(true);

    // Organization A
    $this->orgA = Organization::create([
        'nama' => 'HIMATIF ITI ' . $timestamp,
        'jenis' => 'HMPS',
        'subdomain' => 'himatif' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    // Organization B
    $this->orgB = Organization::create([
        'nama' => 'BEM ITI ' . $timestamp,
        'jenis' => 'BEM',
        'subdomain' => 'bem' . str_replace('.', '', (string)$timestamp),
        'status' => 'active',
    ]);

    // Super Admin
    $this->superAdmin = User::create([
        'name' => 'Super Admin Global',
        'email' => 'superadmin_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => null,
        'status' => 'active',
    ]);
    $this->superAdmin->assignRole('Super Admin');

    // Users Org A
    $this->adminA = User::create([
        'name' => 'Admin Org A',
        'email' => 'admin_a_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $this->adminA->assignRole('Admin Organisasi');

    $this->editorA = User::create([
        'name' => 'Editor Org A',
        'email' => 'editor_a_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $this->editorA->assignRole('Editor');

    $this->contributorA = User::create([
        'name' => 'Contributor Org A',
        'email' => 'contrib_a_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgA->id,
        'status' => 'active',
    ]);
    $this->contributorA->assignRole('Kontributor');

    // User Org B
    $this->adminB = User::create([
        'name' => 'Admin Org B',
        'email' => 'admin_b_' . $timestamp . '@example.com',
        'password' => bcrypt('password'),
        'organization_id' => $this->orgB->id,
        'status' => 'active',
    ]);
    $this->adminB->assignRole('Admin Organisasi');
});

test('1 & 2. Upload image to Media Library defaults to is_published_to_gallery = false', function () {
    Sanctum::actingAs($this->adminA);

    $fakeImage = UploadedFile::fake()->image('internal_doc.jpg', 600, 400);

    $res = $this->postJson('/api/upload-image', [
        'image' => $fakeImage,
        'title' => 'Internal Documentation 2026',
        'category' => 'Dokumentasi',
        'publish_to_gallery' => 0,
    ]);

    $res->assertStatus(200);
    $mediaId = $res->json('id');
    expect($mediaId)->not->toBeNull();

    $media = Media::find($mediaId);
    expect($media)->not->toBeNull();
    expect($media->is_published_to_gallery)->toBeFalse();

    // Verify it appears in Media Library
    $libRes = $this->getJson('/api/media');
    $libRes->assertStatus(200);
    expect(count($libRes->json('data')))->toBe(1);

    // Verify it does NOT appear in Public Gallery list
    $galleryRes = $this->getJson('/api/media?published_only=1');
    $galleryRes->assertStatus(200);
    expect(count($galleryRes->json('data')))->toBe(0);
});

test('3 & 4. Toggle Publish/Unpublish to Public Gallery works seamlessly', function () {
    Sanctum::actingAs($this->adminA);

    $media = Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Foto Kegiatan',
        'filename' => 'kegiatan.webp',
        'path' => 'gallery-images/kegiatan.webp',
        'mime_type' => 'image/webp',
        'size' => 20000,
        'is_published_to_gallery' => false,
    ]);

    // 3. Publish to gallery
    $pubRes = $this->patchJson("/api/media/{$media->id}/toggle-gallery", [
        'is_published_to_gallery' => true,
    ]);
    $pubRes->assertStatus(200);
    $media->refresh();
    expect($media->is_published_to_gallery)->toBeTrue();

    // 4. Unpublish from gallery
    $unpubRes = $this->patchJson("/api/media/{$media->id}/toggle-gallery", [
        'is_published_to_gallery' => false,
    ]);
    $unpubRes->assertStatus(200);
    $media->refresh();
    expect($media->is_published_to_gallery)->toBeFalse();
    // Asset still exists in Media Library!
    expect(Media::find($media->id))->not->toBeNull();
});

test('5. Public endpoint ONLY returns published gallery media (is_published_to_gallery = true)', function () {
    // Media 1: Internal only
    Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Internal Draft Photo',
        'filename' => 'internal.webp',
        'path' => 'gallery-images/internal.webp',
        'mime_type' => 'image/webp',
        'size' => 15000,
        'is_published_to_gallery' => false,
    ]);

    // Media 2: Published to gallery
    $publishedMedia = Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Public Exhibition Photo',
        'filename' => 'public_exhibition.webp',
        'path' => 'gallery-images/public_exhibition.webp',
        'mime_type' => 'image/webp',
        'size' => 25000,
        'is_published_to_gallery' => true,
        'visibility' => 'public',
    ]);

    $res = $this->getJson("/api/public/organizations/{$this->orgA->subdomain}/gallery");
    $res->assertStatus(200);
    $data = $res->json('data');
    expect(count($data))->toBe(1);
    expect($data[0]['id'])->toBe($publishedMedia->id);
    expect($data[0]['title'])->toBe('Public Exhibition Photo');
});

test('6 & 15. Article can choose Media Library asset as Cover Image', function () {
    $media = Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Cover Prestasi',
        'filename' => 'prestasi.webp',
        'path' => 'gallery-images/prestasi.webp',
        'mime_type' => 'image/webp',
        'size' => 30000,
        'is_published_to_gallery' => false, // Media library internal
    ]);

    Sanctum::actingAs($this->editorA);

    $res = $this->postJson('/api/posts', [
        'judul' => 'Juara Hackathon Nasional 2026',
        'konten' => '<p>Mahasiswa berhasil meraih juara 1 dalam ajang Hackathon.</p>',
        'status' => 'draft',
        'cover_image' => $media->url,
    ]);

    $res->assertStatus(201);
    $postId = $res->json('data.id');
    $post = Post::find($postId);
    expect($post->cover_image)->toBe($media->url);
});

test('7 & 18. Cross-Tenant Protection: Article CANNOT choose media belonging to another organization', function () {
    $mediaB = Media::create([
        'organization_id' => $this->orgB->id,
        'user_id' => $this->adminB->id,
        'name' => 'BEM Private Asset',
        'filename' => 'bem_asset.webp',
        'path' => 'gallery-images/bem_asset.webp',
        'mime_type' => 'image/webp',
        'size' => 25000,
    ]);

    Sanctum::actingAs($this->editorA); // Org A user

    // Tries to set Org B media as cover
    $res = $this->postJson('/api/posts', [
        'judul' => 'Artikel Manipulasi Media',
        'konten' => '<p>Konten artikel tes.</p>',
        'status' => 'draft',
        'cover_image' => $mediaB->url,
    ]);

    $res->assertStatus(403);
});

test('8 & 9 & 10 & 11. Role Permission Matrix across Super Admin, Admin Org, Editor, and Contributor', function () {
    // Super Admin: sees all
    Sanctum::actingAs($this->superAdmin);
    $this->getJson('/api/media')->assertStatus(200);

    // Admin Org: sees own tenant
    Sanctum::actingAs($this->adminA);
    $this->getJson('/api/media')->assertStatus(200);

    // Editor: sees own tenant & can upload
    Sanctum::actingAs($this->editorA);
    $this->getJson('/api/media')->assertStatus(200);

    // Contributor default: no gallery.view -> 403
    Sanctum::actingAs($this->contributorA);
    $this->getJson('/api/media')->assertStatus(403);
});

test('12. Delete Safety: Deleting media referenced by an article is REJECTED (422)', function () {
    $media = Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Foto Acara Penting',
        'filename' => 'acara_penting.webp',
        'path' => 'gallery-images/acara_penting.webp',
        'mime_type' => 'image/webp',
        'size' => 40000,
    ]);

    // Create an article referencing this media
    Post::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->editorA->id,
        'judul' => 'Liputan Acara Dies Natalis',
        'slug' => 'liputan-acara-dies-natalis',
        'konten' => '<p>Dokumentasi dies natalis.</p>',
        'status' => 'published',
        'cover_image' => $media->url,
    ]);

    Sanctum::actingAs($this->adminA);

    // Attempt deletion
    $res = $this->deleteJson("/api/media/{$media->id}");
    $res->assertStatus(422);
    $res->assertJsonPath('status', 'error');
    expect($res->json('message'))->toContain('sedang digunakan sebagai referensi oleh 1 artikel');

    // Media record and storage remain intact
    expect(Media::find($media->id))->not->toBeNull();
});

test('13. Deleting unreferenced media deletes physical file and database safely', function () {
    Storage::disk('public')->put('gallery-images/unreferenced.webp', 'some-binary-data');
    $media = Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Foto Tidak Terpakai',
        'filename' => 'unreferenced.webp',
        'path' => 'gallery-images/unreferenced.webp',
        'mime_type' => 'image/webp',
        'size' => 15000,
    ]);

    Sanctum::actingAs($this->adminA);

    $res = $this->deleteJson("/api/media/{$media->id}");
    $res->assertStatus(200);

    expect(Media::find($media->id))->toBeNull();
    expect(Storage::disk('public')->exists('gallery-images/unreferenced.webp'))->toBeFalse();
});

test('14 & 17. Inline Rich Text Editor HTML figure reference saved without Base64', function () {
    $media = Media::create([
        'organization_id' => $this->orgA->id,
        'user_id' => $this->adminA->id,
        'name' => 'Dokumentasi Lab',
        'filename' => 'lab.webp',
        'path' => 'gallery-images/lab.webp',
        'mime_type' => 'image/webp',
        'size' => 35000,
    ]);

    Sanctum::actingAs($this->editorA);

    $inlineContent = '<h2>Fasilitas Baru</h2><figure class="my-6 text-center"><img src="' . $media->url . '" alt="Lab Komputer" class="rounded-xl" /><figcaption class="text-xs">Laboratorium Komputer 2026</figcaption></figure><p>Fasilitas lab telah diperbarui.</p>';

    $res = $this->postJson('/api/posts', [
        'judul' => 'Peresmian Laboratorium Komputer',
        'konten' => $inlineContent,
        'status' => 'draft',
    ]);

    $res->assertStatus(201);
    $postId = $res->json('data.id');
    $post = Post::find($postId);

    expect($post->konten)->toContain($media->url);
    expect($post->konten)->not->toContain('data:image/');
});

test('16. Upload from Media Picker persists to Media Library internal', function () {
    Sanctum::actingAs($this->adminA);

    $fakeImage = UploadedFile::fake()->image('picker_uploaded.png', 400, 400);

    $res = $this->postJson('/api/upload-image', [
        'image' => $fakeImage,
        'title' => 'Foto dari Media Picker',
        'publish_to_gallery' => 0,
    ]);

    $res->assertStatus(200);
    $media = Media::find($res->json('id'));
    expect($media)->not->toBeNull();
    expect($media->is_published_to_gallery)->toBeFalse();
});

test('19. Web session fallback routes do not throw unexpected errors', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});
