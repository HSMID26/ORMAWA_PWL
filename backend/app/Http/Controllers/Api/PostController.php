<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class PostController extends Controller
{
    // 1. Ambil Semua Artikel (Read List dengan Category & Tags)
    public function index()
    {
        $posts = Post::with(['user:id,name', 'category', 'tags'])->latest()->get();

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
            'category_id'      => 'nullable|exists:categories,id',
            'tags'             => 'nullable|array',
            'tags.*'           => 'exists:tags,id',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $status = $request->status;

        // Only Admin Organisasi and Super Admin can directly publish or reject
        if (in_array($status, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
            if ($status === 'published') {
                $status = 'review';
            } else {
                $status = 'draft';
            }
        }

        $post = Post::create([
            'judul'            => trim($request->judul),
            'slug'             => Str::slug($request->judul) . '-' . Str::random(5),
            'konten'           => $request->konten,
            'excerpt'          => $request->excerpt,
            'cover_image'      => $request->cover_image,
            'status'           => $status,
            'published_at'     => $status === 'published' ? now() : null,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'category_id'      => $request->category_id,
            'user_id'          => $user->id,
            // organization_id terisi otomatis via Trait BelongsToOrganization!
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        ActivityLogService::log('create', 'posts', 'Membuat berita baru: ' . $post->judul . ' (Status: ' . $post->status . ')', $post);

        if ($status === 'review') {
            NotificationService::sendToOrganizationAdmins(
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
            'data'    => $post->load(['user:id,name', 'category', 'tags'])
        ], 201);
    }

    // 3. Ambil Detail 1 Artikel (Read Single dengan Category & Tags)
    public function show(string $id)
    {
        $post = Post::with(['user:id,name', 'category', 'tags'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $post
        ], 200);
    }

    // 4. Update Artikel (Update dengan Sync Kategori & Tags)
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Contributor can only update their own posts
        if ($user->hasRole('Kontributor') && $post->user_id !== $user->id) {
            abort(403, 'Unauthorized. Kontributor hanya dapat mengedit artikel miliknya sendiri.');
        }

        $request->validate([
            'judul'            => 'sometimes|required|string|max:255',
            'konten'           => 'sometimes|required|string',
            'excerpt'          => 'nullable|string',
            'cover_image'      => 'nullable|string',
            'status'           => 'sometimes|required|in:draft,review,published,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'category_id'      => 'nullable|exists:categories,id',
            'tags'             => 'nullable|array',
            'tags.*'           => 'exists:tags,id',
        ]);

        $data = $request->only(['judul', 'konten', 'excerpt', 'cover_image', 'status', 'meta_title', 'meta_description', 'category_id']);

        if ($request->has('judul') && $request->judul !== $post->judul) {
            $data['slug'] = Str::slug($request->judul) . '-' . Str::random(5);
        }

        if ($request->has('status')) {
            $newStatus = $request->status;

            if (in_array($newStatus, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
                if ($newStatus === 'published') {
                    $newStatus = 'review';
                } else {
                    $newStatus = $post->status;
                }
            }
            $data['status'] = $newStatus;

            if ($newStatus === 'published' && $post->status !== 'published') {
                $data['published_at'] = now();
            }
        }

        $post->update($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        $action = 'update';
        $desc = 'Memperbarui berita: ' . $post->judul;
        if (isset($data['status'])) {
            $action = $data['status'] === 'published' ? 'publish' : ($data['status'] === 'rejected' ? 'reject' : ($data['status'] === 'review' ? 'submit_review' : 'update'));
            $desc .= ' (Status: ' . $data['status'] . ')';

            if ($action === 'submit_review') {
                NotificationService::sendToOrganizationAdmins(
                    $post->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Berita untuk direview.",
                    '/organization/posts',
                    ['content_id' => $post->id, 'content_type' => 'post', 'author_id' => $user->id]
                );
            } elseif ($action === 'publish' && $post->user_id !== $user->id) {
                if ($post->user) {
                    NotificationService::send(
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
                    NotificationService::send(
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
            'data'    => $post->load(['user:id,name', 'category', 'tags'])
        ], 200);
    }

    // 5. Hapus Artikel (Delete)
    public function destroy(Request $request, string $id)
    {
        $post = Post::findOrFail($id);
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Contributor can only delete their own posts
        if ($user->hasRole('Kontributor') && $post->user_id !== $user->id) {
            abort(403, 'Unauthorized. Kontributor hanya dapat menghapus artikel miliknya sendiri.');
        }

        $post->delete();

        ActivityLogService::log('delete', 'posts', 'Menghapus berita: ' . $post->judul, clone $post);

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dihapus!'
        ], 200);
    }
}