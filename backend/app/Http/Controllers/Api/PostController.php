<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;

class PostController extends Controller
{
    // 1. Ambil Semua Artikel (Read List)
    public function index()
    {
        $posts = Post::with('user:id,name')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $posts
        ], 200);
    }

    // 2. Buat Artikel Baru (Create - Hasil dari TipTap Editor)
    public function store(Request $request)
    {
        $request->validate([
            'judul'            => 'required|string|max:255',
            'konten'           => 'required|string', 
            'excerpt'          => 'nullable|string',
            'cover_image'      => 'nullable|string',
            'status'           => 'required|in:draft,review,published,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $status = $request->status;

        if (in_array($status, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
            if ($user->hasRole('Kontributor') && $status === 'published') {
                $status = 'review';
            }
        }

        $post = Post::create([
            'judul'            => $request->judul,
            'slug'             => Str::slug($request->judul) . '-' . Str::random(5),
            'konten'           => $request->konten,
            'excerpt'          => $request->excerpt,
            'cover_image'      => $request->cover_image,
            'status'           => $status,
            'published_at'     => $status === 'published' ? now() : null,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'user_id'          => $user->id,
            // organization_id terisi otomatis via Trait BelongsToOrganization!
        ]);

        ActivityLogService::log('create', 'posts', 'Membuat berita baru: ' . $post->judul . ' (Status: ' . $post->status . ')', $post);

        if ($status === 'review') {
            \App\Services\NotificationService::sendToOrganizationAdmins(
                $post->organization_id,
                'content_review',
                'Konten Menunggu Review',
                "{$user->name} mengirim Berita untuk direview.",
                '/organization/posts',
                ['content_id' => $post->id, 'content_type' => 'post', 'author_id' => $user->id]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dibuat!',
            'data'    => $post
        ], 201);
    }

    // 3. Ambil Detail 1 Artikel (Read Single)
    public function show(string $id)
    {
        $post = Post::with('user:id,name')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $post
        ], 200);
    }

    // 4. Update Artikel (Update)
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'judul'            => 'sometimes|required|string|max:255',
            'konten'           => 'sometimes|required|string',
            'excerpt'          => 'nullable|string',
            'cover_image'      => 'nullable|string',
            'status'           => 'sometimes|required|in:draft,review,published,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $data = $request->only(['judul', 'konten', 'excerpt', 'cover_image', 'status', 'meta_title', 'meta_description']);

        // Update slug jika judul berubah
        if ($request->has('judul') && $request->judul !== $post->judul) {
            $data['slug'] = Str::slug($request->judul) . '-' . Str::random(5);
        }

        if ($request->has('status')) {
            /** @var \App\Models\User $user */
            $user = $request->user();
            $newStatus = $request->status;

            if (in_array($newStatus, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
                 if ($user->hasRole('Kontributor') && $newStatus === 'published') {
                    $newStatus = 'review';
                 }
            }
            $data['status'] = $newStatus;

            if ($newStatus === 'published' && $post->status !== 'published') {
                $data['published_at'] = now();
            }
        }

        $post->update($data);

        $action = 'update';
        $desc = 'Memperbarui berita: ' . $post->judul;
        if (isset($data['status'])) {
            $action = $data['status'] === 'published' ? 'publish' : ($data['status'] === 'rejected' ? 'reject' : ($data['status'] === 'review' ? 'submit_review' : 'update'));
            $desc .= ' (Status: ' . $data['status'] . ')';

            /** @var \App\Models\User $user */
            $user = $request->user();

            if ($action === 'submit_review') {
                \App\Services\NotificationService::sendToOrganizationAdmins(
                    $post->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Berita untuk direview.",
                    '/organization/posts',
                    ['content_id' => $post->id, 'content_type' => 'post', 'author_id' => $user->id]
                );
            } elseif ($action === 'publish' && $post->user_id !== $user->id) {
                if ($post->user) {
                    \App\Services\NotificationService::send(
                        $post->user,
                        'content_published',
                        'Konten Dipublikasikan',
                        "Konten '{$post->judul}' berhasil dipublikasikan.",
                        '/organization/posts',
                        ['content_id' => $post->id, 'content_type' => 'post']
                    );
                }
            } elseif ($action === 'reject' && $post->user_id !== $user->id) {
                if ($post->user) {
                    \App\Services\NotificationService::send(
                        $post->user,
                        'content_rejected',
                        'Konten Ditolak',
                        "Konten '{$post->judul}' ditolak.",
                        '/organization/posts',
                        ['content_id' => $post->id, 'content_type' => 'post']
                    );
                }
            }
        }
        ActivityLogService::log($action, 'posts', $desc, $post);

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil diperbarui!',
            'data'    => $post
        ], 200);
    }

    // 5. Hapus Artikel (Delete)
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        ActivityLogService::log('delete', 'posts', 'Menghapus berita: ' . $post->judul, clone $post);

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dihapus!'
        ], 200);
    }
}