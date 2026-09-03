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
    // 1. Ambil Semua Daftar Media milik Ormawa yang sedang login
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses ke galeri media.');
        }

        // Trait BelongsToOrganization otomatis menyaring berdasarkan organization_id jika user memiliki tenant
        $query = Media::with(['user:id,name', 'organization:id,nama'])->latest();

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'video') {
                $query->where('mime_type', 'like', 'video/%');
            }
        }

        if ($request->filled('q')) {
            $query->where('filename', 'like', '%' . trim($request->q) . '%');
        }

        $mediaList = $query->get()->map(function ($item) {
            return [
                'id'             => $item->id,
                'filename'       => $item->filename,
                'url'            => $item->url,
                'size'           => $item->size,
                'formatted_size' => $item->formatted_size,
                'mime_type'      => $item->mime_type,
                'is_image'       => $item->is_image,
                'is_video'       => $item->is_video,
                'uploader'       => $item->user->name ?? 'System',
                'organization'   => $item->organization ? [
                    'id'   => $item->organization->id,
                    'nama' => $item->organization->nama,
                ] : null,
                'created_at'     => $item->created_at?->format('d M Y, H:i'),
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

        $uploadedMedia = [];
        $user = $request->user() ?? Auth::user();
        $userId = $user?->id;
        $orgId = $user?->organization_id ?? $request->input('organization_id') ?? \App\Models\Organization::first()?->id ?? 1;

        foreach ($files as $file) {
            if (!$file->isValid()) continue;

            $mimeType = $file->getMimeType() ?: '';
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $origName);

            if (str_starts_with($mimeType, 'image/')) {
                // Image Processing & WebP Conversion
                try {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->decode($file->get());

                    // Resize bila resolusi terlalu raksasa
                    if ($image->width() > 1920) {
                        $image->scale(1920);
                    }

                    $encoded = $image->encode(new WebpEncoder(80, true));
                    $filename = $cleanName . '_' . time() . '_' . uniqid() . '.webp';
                    $path = 'media-gallery/' . $filename;

                    Storage::disk('public')->put($path, (string) $encoded);
                    $finalMime = 'image/webp';
                    $finalSize = Storage::disk('public')->size($path);
                } catch (\Throwable $e) {
                    // Fallback to direct store if GD fails
                    $filename = $cleanName . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('media-gallery', $filename, 'public');
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

            $media = Media::create([
                'organization_id' => $orgId,
                'user_id'         => $userId,
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
            ];
        }

        // Log Aktivitas
        $count = count($uploadedMedia);
        if ($count > 0) {
            ActivityLogService::log('create', 'media', "Mengunggah {$count} berkas media baru ke Galeri Media");
        }

        // Return single response if single upload for TipTap backward compatibility
        if (count($uploadedMedia) === 1) {
            return response()->json([
                'status'  => 'success',
                'id'      => $uploadedMedia[0]['id'],
                'type'    => $uploadedMedia[0]['is_image'] ? 'image' : 'video',
                'url'     => $uploadedMedia[0]['url'],
                'data'    => $uploadedMedia[0],
                'message' => 'Berkas berhasil diunggah!'
            ], 200, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil mengunggah {$count} berkas media!",
            'data'    => $uploadedMedia
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 3. Hapus Media (File Fisik + Record Database)
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses untuk menghapus media.');
        }

        $media = Media::findOrFail($id);
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
}