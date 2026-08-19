<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class MediaController extends Controller
{
    // 1. Ambil Semua Daftar Media milik Ormawa yang sedang login
    public function index()
    {
        // Trait BelongsToOrganization otomatis menyaring berdasarkan organization_id
        $mediaList = Media::with('user')->latest()->get()->map(function ($item) {
            return [
                'id'         => $item->id,
                'filename'   => $item->filename,
                'url'        => $item->url,
                'size'       => $item->size,
                'mime_type'  => $item->mime_type,
                'uploader'   => $item->user->name ?? 'System',
                'created_at' => $item->created_at->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $mediaList
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 2. Upload Gambar Baru (Dipakai TipTap & Media Library)
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|max:51200',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $mimeType = $file->getMimeType() ?: '';
            $isImage = str_starts_with($mimeType, 'image/');

            if (! $isImage) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya gambar yang dapat dikompresi otomatis saat ini.',
                ], 422);
            }
            
            $manager = new ImageManager(new Driver());
            $image = $manager->decode($file->get());

            // Resize gambar besar agar upload lebih ringan tanpa merusak rasio.
            if ($image->width() > 1080) {
                $image->scale(1080);
            }

            $encoded = $image->encode(new WebpEncoder(75, true));

            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.webp';
            $path = 'editor-images/' . $filename;

            Storage::disk('public')->put($path, (string) $encoded);

            $media = Media::create([
                'user_id'   => Auth::id(),
                'filename'  => $filename,
                'path'      => $path,
                'mime_type' => 'image/webp',
                'size'      => Storage::disk('public')->size($path),
            ]);

            return response()->json([
                'status' => 'success',
                'id'     => $media->id,
                'type'   => 'image',
                'url'    => $media->url,
            ], 200, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Upload gagal',
        ], 400);
    }

    // 3. Hapus Media (File Fisik + Record Database)
    public function destroy(string $id)
    {
        $media = Media::findOrFail($id);

        // Hapus file fisik di storage
        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        // Hapus record di database
        $media->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'File media berhasil dihapus'
        ]);
    }
}