<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Post;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class MediaController extends Controller
{
    // 1. Ambil Daftar Media Library / Galeri Foto Organisasi
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('gallery.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat galeri foto / media library.');
        }

        $query = Media::with(['user:id,name', 'organization:id,nama,subdomain'])->latest();

        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        } elseif ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Scope filter: Public Gallery vs Media Library
        if ($request->boolean('published_only') || $request->input('scope') === 'gallery' || $request->input('published_to_gallery') === '1' || $request->input('published_to_gallery') === 'true' || $request->input('gallery_status') === 'published') {
            $query->where('is_published_to_gallery', true);
        } elseif ($request->input('gallery_status') === 'unpublished' || $request->input('published_to_gallery') === '0' || $request->input('published_to_gallery') === 'false') {
            $query->where('is_published_to_gallery', false);
        } elseif ($request->has('is_published_to_gallery')) {
            $query->where('is_published_to_gallery', filter_var($request->is_published_to_gallery, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'image' || $type === 'images') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'video' || $type === 'videos') {
                $query->where('mime_type', 'like', 'video/%');
            } elseif ($type === 'document' || $type === 'documents') {
                $query->where('mime_type', 'not like', 'image/%')->where('mime_type', 'not like', 'video/%');
            }
        } else {
            // Default: image type
            $query->where('mime_type', 'like', 'image/%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $searchTerm = $request->search ?: $request->q;
        if (!empty($searchTerm)) {
            $s = trim($searchTerm);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('filename', 'like', "%{$s}%")
                  ->orWhere('caption', 'like', "%{$s}%")
                  ->orWhere('alt_text', 'like', "%{$s}%");
            });
        }

        $mediaList = $query->get()->map(function ($item) {
            return [
                'id'                      => $item->id,
                'organization_id'         => $item->organization_id,
                'user_id'                 => $item->user_id,
                'name'                    => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'title'                   => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'judul'                   => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'caption'                 => $item->caption,
                'deskripsi'               => $item->caption,
                'taken_at'                => $item->taken_at?->toDateString(),
                'alt_text'                => $item->alt_text,
                'category'                => $item->category ?? 'Dokumentasi',
                'kategori'                => $item->category ?? 'Dokumentasi',
                'visibility'              => $item->visibility ?? 'public',
                'is_published_to_gallery' => (bool)$item->is_published_to_gallery,
                'filename'                => $item->filename,
                'url'                     => $item->url,
                'image_url'               => $item->url,
                'size'                    => $item->size,
                'formatted_size'          => $item->formatted_size,
                'mime_type'               => $item->mime_type,
                'is_image'                => $item->is_image,
                'is_video'                => $item->is_video,
                'uploader'                => $item->user->name ?? 'System',
                'used_in_articles'        => $item->used_in_articles,
                'used_count'              => count($item->used_in_articles),
                'organization'            => $item->organization ? [
                    'id'        => $item->organization->id,
                    'nama'      => $item->organization->nama,
                    'subdomain' => $item->organization->subdomain,
                ] : null,
                'created_at'              => $item->created_at?->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $mediaList
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 2. Upload Media Baru (Single / Multi-file, Gambar WebP atau Video)
    public function upload(Request $request)
    {
        $user = $request->user() ?? Auth::user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('gallery.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengunggah berkas ke media library / galeri.');
        }

        // Support both single 'image'/'file' or multiple 'files'/'images'
        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        } elseif ($request->hasFile('images')) {
            $files = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        if (empty($files)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak ada file yang dipilih untuk diunggah.',
            ], 422);
        }

        // Validate that all files are valid image/media files
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;
            $mime = $file->getMimeType() ?: '';
            if (!str_starts_with($mime, 'image/') && !str_starts_with($mime, 'video/')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'File yang diunggah harus berupa foto atau gambar.',
                    'errors'  => [
                        'image' => ['File yang diunggah harus berupa foto atau gambar (JPG, PNG, WebP, AVIF).']
                    ]
                ], 422);
            }
        }

        $uploadedMedia = [];
        $userId = $user?->id;
        $orgId = $user?->organization_id;
        if ($user?->hasRole('Super Admin') && $request->filled('organization_id')) {
            $orgId = $request->organization_id;
        }

        $publishToGallery = $request->boolean('publish_to_gallery', false) || $request->boolean('is_published_to_gallery', false);

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $mimeType = $file->getMimeType() ?: '';
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $origName);

            if (str_starts_with($mimeType, 'image/')) {
                // Image Processing & WebP Conversion
                try {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->decode($file->get());

                    // Resize bila resolusi terlalu besar (max width 1920px untuk galeri HD)
                    if ($image->width() > 1920) {
                        $image->scale(1920);
                    }

                    $encoded = $image->encode(new WebpEncoder(80, true));
                    $filename = $cleanName . '_' . time() . '_' . uniqid() . '.webp';
                    $path = 'gallery-images/' . $filename;

                    Storage::disk('public')->put($path, (string) $encoded);
                    $finalMime = 'image/webp';
                    $finalSize = Storage::disk('public')->size($path);
                } catch (\Throwable $e) {
                    $filename = $cleanName . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('gallery-images', $filename, 'public');
                    $finalMime = $mimeType;
                    $finalSize = $file->getSize();
                }
            } else {
                // Video or Document Processing
                $filename = $cleanName . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('media-gallery', $filename, 'public');
                $finalMime = $mimeType;
                $finalSize = $file->getSize();
            }

            $name = $request->title ?: ($request->name ?: $origName);

            $media = Media::create([
                'organization_id'         => $orgId,
                'user_id'                 => $userId,
                'name'                    => $name,
                'caption'                 => $request->caption,
                'taken_at'                => $request->taken_at,
                'alt_text'                => $request->alt_text ?: $name,
                'category'                => $request->category ?? 'Dokumentasi',
                'visibility'              => $request->visibility ?? 'public',
                'is_published_to_gallery' => $publishToGallery,
                'filename'                => $filename,
                'path'                    => $path,
                'mime_type'               => $finalMime,
                'size'                    => $finalSize,
            ]);

            $uploadedMedia[] = [
                'id'                      => $media->id,
                'filename'                => $media->filename,
                'url'                     => $media->url,
                'formatted_size'          => $media->formatted_size,
                'mime_type'               => $media->mime_type,
                'is_image'                => $media->is_image,
                'is_video'                => $media->is_video,
                'is_published_to_gallery' => $media->is_published_to_gallery,
                'name'                    => $media->name,
                'title'                   => $media->name,
                'caption'                 => $media->caption,
                'taken_at'                => $media->taken_at?->toDateString(),
                'alt_text'                => $media->alt_text,
                'category'                => $media->category,
            ];
        }

        $count = count($uploadedMedia);
        if ($count > 0) {
            $dest = $publishToGallery ? 'Galeri Publik' : 'Media Library Internal';
            ActivityLogService::log('create', 'media', "Mengunggah {$count} berkas media baru ke {$dest}");
        }

        // Return single response if single upload for TipTap & existing forms compatibility
        if (count($uploadedMedia) === 1) {
            $single = $uploadedMedia[0];
            return response()->json([
                'status'                  => 'success',
                'id'                      => $single['id'],
                'type'                    => $single['is_image'] ? 'image' : 'video',
                'title'                   => $single['name'],
                'name'                    => $single['name'],
                'judul'                   => $single['name'],
                'caption'                 => $single['caption'],
                'deskripsi'               => $single['caption'],
                'taken_at'                => $single['taken_at'],
                'alt_text'                => $single['alt_text'],
                'url'                     => $single['url'],
                'image_url'               => $single['url'],
                'formatted_size'          => $single['formatted_size'],
                'mime_type'               => $single['mime_type'],
                'is_image'                => $single['is_image'],
                'is_video'                => $single['is_video'],
                'is_published_to_gallery' => $single['is_published_to_gallery'],
                'data'                    => $single,
                'message'                 => 'Berkas berhasil diunggah!'
            ], 200, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil mengunggah {$count} berkas media!",
            'data'    => $uploadedMedia
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 3. Update Metadata Media (Foto Galeri / Dokumen / Status Publikasi)
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $media = Media::withoutGlobalScopes()->findOrFail($id);
        $isImage = str_starts_with($media->mime_type ?? '', 'image/');
        $requiredPerm = $isImage ? 'gallery.update' : 'documents.update';

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can($requiredPerm))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengubah ' . ($isImage ? 'galeri foto / media library.' : 'dokumen.'));
        }

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && (int)$media->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah media milik organisasi lain.');
        }

        $request->validate([
            'title'                   => 'nullable|string|max:255',
            'name'                    => 'nullable|string|max:255',
            'caption'                 => 'nullable|string|max:1000',
            'taken_at'                => 'nullable|date',
            'alt_text'                => 'nullable|string|max:255',
            'category'                => 'nullable|string|max:100',
            'visibility'              => 'nullable|in:public,internal',
            'is_published_to_gallery' => 'nullable|boolean',
        ]);

        $updateData = [];
        if ($request->has('title') || $request->has('name')) {
            $updateData['name'] = $request->title ?: $request->name;
        }
        if ($request->has('caption')) {
            $updateData['caption'] = $request->caption;
        }
        if ($request->has('taken_at')) {
            $updateData['taken_at'] = $request->taken_at;
        }
        if ($request->has('alt_text')) {
            $updateData['alt_text'] = $request->alt_text;
        }
        if ($request->has('category')) {
            $updateData['category'] = $request->category;
        }
        if ($request->has('visibility')) {
            $updateData['visibility'] = $request->visibility;
        }
        if ($request->has('is_published_to_gallery')) {
            $updateData['is_published_to_gallery'] = $request->boolean('is_published_to_gallery');
        }

        $media->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Media berhasil diperbarui.',
            'data'    => $media,
        ]);
    }

    // 4. Toggle / Set Status Publikasi Galeri Publik
    public function toggleGalleryPublish(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('gallery.update') && !$user->can('gallery.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mempublikasikan media ke Galeri.');
        }

        $media = Media::withoutGlobalScopes()->findOrFail($id);

        if (!$user->hasRole('Super Admin') && (int)$media->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah status media milik organisasi lain.');
        }

        if ($request->has('is_published_to_gallery')) {
            $media->is_published_to_gallery = $request->boolean('is_published_to_gallery');
        } else {
            $media->is_published_to_gallery = !$media->is_published_to_gallery;
        }

        $media->save();

        $actionText = $media->is_published_to_gallery ? 'dipublikasikan ke Galeri Publik' : 'ditarik dari Galeri Publik (tetap aman di Media Library)';
        ActivityLogService::log('update', 'media', "Media '{$media->name}' {$actionText}");

        return response()->json([
            'status'  => 'success',
            'message' => "Media berhasil {$actionText}!",
            'data'    => $media,
        ]);
    }

    // 5. Hapus Media (Safe Deletion with Reference Checking)
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $media = Media::withoutGlobalScopes()->findOrFail($id);
        $isImage = str_starts_with($media->mime_type ?? '', 'image/');
        $requiredPerm = $isImage ? 'gallery.delete' : 'documents.delete';

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can($requiredPerm))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk menghapus ' . ($isImage ? 'galeri foto / media library.' : 'dokumen.'));
        }

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && (int)$media->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat menghapus media milik organisasi lain.');
        }

        // DELETE SAFETY: Cek apakah media sedang digunakan oleh artikel organisasi
        $usedInArticles = $media->used_in_articles;
        if (!empty($usedInArticles)) {
            $count = count($usedInArticles);
            $titles = implode(', ', array_slice(array_column($usedInArticles, 'judul'), 0, 3));
            if ($count > 3) {
                $titles .= ' dan ' . ($count - 3) . ' lainnya';
            }
            return response()->json([
                'status'  => 'error',
                'message' => "Media tidak dapat dihapus permanen karena sedang digunakan sebagai referensi oleh {$count} artikel ({$titles}).",
                'used_in' => $usedInArticles,
            ], 422);
        }

        $fileName = $media->filename;

        // Hapus file fisik di storage jika ada
        if ($media->path && Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        // Hapus record di database
        $media->delete();

        ActivityLogService::log('delete', 'media', "Menghapus berkas media: {$fileName}");

        return response()->json([
            'status'  => 'success',
            'message' => "Berkas media '{$fileName}' berhasil dihapus!"
        ]);
    }

    // 6. Ambil Daftar Dokumen Organisasi (Documents Only)
    public function listDocuments(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('documents.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat dokumen.');
        }

        $query = Media::with(['user:id,name', 'organization:id,nama,subdomain'])
            ->where('mime_type', 'not like', 'image/%');

        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('filename', 'like', "%{$s}%");
            });
        }

        $documents = $query->latest()->get()->map(function ($item) {
            $orgSubdomain = $item->organization?->subdomain ?? 'org';
            return [
                'id'           => $item->id,
                'name'         => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'filename'     => $item->filename,
                'url'          => $item->url,
                'download_url' => $item->url,
                'size'         => $item->size,
                'mime_type'    => $item->mime_type,
                'category'     => $item->category ?? 'Dokumen Resmi',
                'visibility'   => $item->visibility ?? 'public',
                'uploader'     => $item->user->name ?? 'System',
                'organization' => $item->organization ? [
                    'id'        => $item->organization->id,
                    'nama'      => $item->organization->nama,
                    'subdomain' => $item->organization->subdomain,
                ] : null,
                'created_at'   => $item->created_at?->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $documents
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 7. Upload Dokumen Baru (Non-Image, max 20MB)
    public function uploadDocument(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('documents.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengunggah dokumen.');
        }

        $request->validate([
            'file'       => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar',
            'name'       => 'required|string|max:255',
            'category'   => 'nullable|string|max:100',
            'visibility' => 'nullable|in:public,internal',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
        $path = $file->storeAs('documents', $storedName, 'public');

        $orgId = $user->organization_id;
        if ($user->hasRole('Super Admin') && $request->filled('organization_id')) {
            $orgId = $request->organization_id;
        }

        $media = Media::create([
            'organization_id' => $orgId,
            'user_id'         => $user->id,
            'name'            => $request->name,
            'filename'        => $filename,
            'path'            => $path,
            'mime_type'       => $file->getMimeType(),
            'size'            => $file->getSize(),
            'category'        => $request->category ?? 'Dokumen Resmi',
            'visibility'      => $request->visibility ?? 'public',
        ]);

        ActivityLogService::log('create', 'document', "Mengunggah dokumen baru: {$request->name}");

        return response()->json([
            'status'  => 'success',
            'message' => 'Dokumen berhasil diunggah!',
            'data'    => [
                'id'           => $media->id,
                'name'         => $media->name,
                'filename'     => $media->filename,
                'url'          => $media->url,
                'download_url' => $media->url,
                'size'         => $media->size,
                'mime_type'    => $media->mime_type,
                'category'     => $media->category,
                'visibility'   => $media->visibility,
            ]
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }
}
