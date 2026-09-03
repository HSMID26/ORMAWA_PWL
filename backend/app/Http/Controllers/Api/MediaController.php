<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class MediaController extends Controller
{
    // 1. Ambil Daftar Foto Galeri / Media Organisasi
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('gallery.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat galeri foto.');
        }

        $query = Media::with(['user:id,name', 'organization:id,nama,subdomain'])->latest();

        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'video') {
                $query->where('mime_type', 'like', 'video/%');
            }
        } else {
            // Default filter for gallery: ambil media bertipe gambar
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
            $orgSubdomain = $item->organization?->subdomain ?? 'org';
            return [
                'id'             => $item->id,
                'name'           => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'title'          => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'judul'          => $item->name ?? pathinfo($item->filename, PATHINFO_FILENAME),
                'caption'        => $item->caption,
                'deskripsi'      => $item->caption,
                'taken_at'       => $item->taken_at?->toDateString(),
                'alt_text'       => $item->alt_text,
                'category'       => $item->category ?? 'Dokumentasi',
                'kategori'       => $item->category ?? 'Dokumentasi',
                'visibility'     => $item->visibility ?? 'public',
                'filename'       => $item->filename,
                'url'            => $item->url,
                'image_url'      => $item->url,
                'size'           => $item->size,
                'formatted_size' => $item->formatted_size,
                'mime_type'      => $item->mime_type,
                'is_image'       => $item->is_image,
                'is_video'       => $item->is_video,
                'uploader'       => $item->user->name ?? 'System',
                'organization'   => $item->organization ? [
                    'id'        => $item->organization->id,
                    'nama'      => $item->organization->nama,
                    'subdomain' => $item->organization->subdomain,
                ] : null,
                'created_at'     => $item->created_at?->format('d M Y H:i'),
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
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengunggah foto galeri.');
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
                'organization_id' => $orgId,
                'user_id'         => $userId,
                'name'            => $name,
                'caption'         => $request->caption,
                'taken_at'        => $request->taken_at,
                'alt_text'        => $request->alt_text ?: $name,
                'category'        => $request->category ?? 'Dokumentasi',
                'visibility'      => $request->visibility ?? 'public',
                'filename'        => $filename,
                'path'            => $path,
                'mime_type'       => $finalMime,
                'size'            => $finalSize,
            ]);

            $uploadedMedia[] = [
                'id'             => $media->id,
                'filename'       => $media->filename,
                'url'            => $media->url,
                'formatted_size' => $media->formatted_size,
                'mime_type'      => $media->mime_type,
                'is_image'       => $media->is_image,
                'is_video'       => $media->is_video,
                'name'           => $media->name,
                'title'          => $media->name,
                'caption'        => $media->caption,
                'taken_at'       => $media->taken_at?->toDateString(),
                'alt_text'       => $media->alt_text,
                'category'       => $media->category,
            ];
        }

        $count = count($uploadedMedia);
        if ($count > 0) {
            ActivityLogService::log('create', 'media', "Mengunggah {$count} berkas media baru ke Galeri Media");
        }

        // Return single response if single upload for TipTap & existing forms compatibility
        if (count($uploadedMedia) === 1) {
            $single = $uploadedMedia[0];
            return response()->json([
                'status'         => 'success',
                'id'             => $single['id'],
                'type'           => $single['is_image'] ? 'image' : 'video',
                'title'          => $single['name'],
                'name'           => $single['name'],
                'judul'          => $single['name'],
                'caption'        => $single['caption'],
                'deskripsi'      => $single['caption'],
                'taken_at'       => $single['taken_at'],
                'alt_text'       => $single['alt_text'],
                'url'            => $single['url'],
                'image_url'      => $single['url'],
                'formatted_size' => $single['formatted_size'],
                'mime_type'      => $single['mime_type'],
                'is_image'       => $single['is_image'],
                'is_video'       => $single['is_video'],
                'data'           => $single,
                'message'        => 'Berkas berhasil diunggah!'
            ], 200, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil mengunggah {$count} berkas media!",
            'data'    => $uploadedMedia
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 3. Update Metadata Media (Foto Galeri / Dokumen)
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $media = Media::withoutGlobalScopes()->findOrFail($id);
        $isImage = str_starts_with($media->mime_type ?? '', 'image/');
        $requiredPerm = $isImage ? 'gallery.update' : 'documents.update';

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can($requiredPerm))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengubah ' . ($isImage ? 'galeri foto.' : 'dokumen.'));
        }

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && (int)$media->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah media milik organisasi lain.');
        }

        $request->validate([
            'title'      => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',
            'caption'    => 'nullable|string|max:1000',
            'taken_at'   => 'nullable|date',
            'alt_text'   => 'nullable|string|max:255',
            'category'   => 'nullable|string|max:100',
            'visibility' => 'nullable|in:public,internal',
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

        $media->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Media berhasil diperbarui.',
            'data'    => $media,
        ]);
    }

    // 4. Hapus Media (File Fisik + Record Database)
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $media = Media::withoutGlobalScopes()->findOrFail($id);
        $isImage = str_starts_with($media->mime_type ?? '', 'image/');
        $requiredPerm = $isImage ? 'gallery.delete' : 'documents.delete';

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can($requiredPerm))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk menghapus ' . ($isImage ? 'galeri foto.' : 'dokumen.'));
        }

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && (int)$media->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat menghapus media milik organisasi lain.');
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

    // 5. Ambil Daftar Dokumen Organisasi (Documents Only)
    public function listDocuments(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('documents.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat dokumen.');
        }

        // Strict query: Dokumen HANYA mengambil media bertipe dokumen (bukan gambar)
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
                'id'            => $item->id,
                'name'          => $item->name ?? $item->filename,
                'judul'         => $item->name ?? $item->filename,
                'filename'      => $item->filename,
                'category'      => $item->category ?? 'Other',
                'kategori'      => $item->category ?? 'Other',
                'visibility'    => $item->visibility ?? 'public',
                'mime_type'     => $item->mime_type ?? 'application/octet-stream',
                'file_type'     => strtoupper(pathinfo($item->filename, PATHINFO_EXTENSION) ?: 'FILE'),
                'size'          => $item->size,
                'file_size'     => $item->size,
                'formatted_size'=> $item->formatted_size,
                'url'           => $item->url,
                'file_url'      => $item->url,
                'download_url'  => url("/api/public/organizations/{$orgSubdomain}/documents/{$item->id}/download"),
                'uploader_name' => $item->user->name ?? 'System',
                'created_at'    => $item->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $documents
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 6. Upload Dokumen Baru (Documents Only)
    public function uploadDocument(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('documents.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengunggah dokumen.');
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'file'       => 'required|file|max:25600|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt,csv',
            'category'   => 'nullable|string|in:SK,Proposal,LPJ,SOP,Template,Other',
            'visibility' => 'nullable|in:public,internal',
        ], [
            'name.required' => 'Nama dokumen wajib diisi.',
            'file.required' => 'Berkas file dokumen wajib dipilih.',
            'file.mimes'    => 'File yang diunggah harus berupa dokumen (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, RAR, TXT, CSV).',
            'file.max'      => 'Ukuran file maksimal adalah 25MB.',
        ]);

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        // Tolak secara eksplisit jika file bertipe gambar
        if (str_starts_with($mimeType, 'image/')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'File yang diunggah harus berupa dokumen.',
            ], 422);
        }
        $size = $file->getSize();

        // Simpan ke storage public disk dalam direktori documents
        $path = $file->store('documents', 'public');

        $orgId = $user->organization_id;
        if ($user->hasRole('Super Admin') && $request->filled('organization_id')) {
            $orgId = $request->organization_id;
        }

        $media = Media::create([
            'organization_id' => $orgId,
            'user_id'         => $user->id,
            'name'            => $request->name,
            'filename'        => $originalFilename,
            'path'            => $path,
            'mime_type'       => $mimeType,
            'category'        => $request->category ?? 'Other',
            'visibility'      => $request->visibility ?? 'public',
            'size'            => $size,
        ]);

        $media->load(['user:id,name', 'organization:id,nama,subdomain']);
        $orgSubdomain = $media->organization?->subdomain ?? 'org';

        return response()->json([
            'status'  => 'success',
            'message' => 'Dokumen berhasil diunggah',
            'data'    => [
                'id'            => $media->id,
                'name'          => $media->name,
                'judul'         => $media->name,
                'filename'      => $media->filename,
                'category'      => $media->category,
                'kategori'      => $media->category,
                'visibility'    => $media->visibility,
                'mime_type'     => $media->mime_type,
                'file_type'     => strtoupper(pathinfo($media->filename, PATHINFO_EXTENSION) ?: 'FILE'),
                'size'          => $media->size,
                'file_size'     => $media->size,
                'formatted_size'=> $media->formatted_size,
                'url'           => $media->url,
                'file_url'      => $media->url,
                'download_url'  => url("/api/public/organizations/{$orgSubdomain}/documents/{$media->id}/download"),
                'uploader_name' => $media->user->name ?? 'System',
                'created_at'    => $media->created_at?->toIso8601String(),
            ]
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }
}
