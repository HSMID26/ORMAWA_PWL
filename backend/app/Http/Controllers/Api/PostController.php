<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class PostController extends Controller
{
    // 1. Ambil Semua Artikel (Read List dengan Category & Tags)
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('posts.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat artikel/berita.');
        }

        $query = Post::with(['user:id,name', 'category', 'tags']);

        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        $posts = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $posts
        ], 200);
    }

    // 2. Buat Artikel Baru (Create - Hasil dari TipTap Editor)
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('posts.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk membuat artikel/berita.');
        }

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

        $status = $request->status;

        // Check if user has permission to publish directly
        $canPublish = $user->hasRole('Super Admin') || $user->can('posts.publish');
        if (in_array($status, ['published', 'rejected']) && !$canPublish) {
            if ($status === 'published') {
                $status = 'review';
            } else {
                $status = 'draft';
            }
        }

        $coverImagePath = $this->processCoverImage($request->cover_image ?? $request->file('cover_image'));

        $post = Post::create([
            'judul'            => trim($request->judul),
            'slug'             => Str::slug($request->judul) . '-' . Str::random(5),
            'konten'           => $request->konten,
            'excerpt'          => $request->excerpt,
            'cover_image'      => $coverImagePath,
            'status'           => $status,
            'published_at'     => $status === 'published' ? now() : null,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'category_id'      => $request->category_id,
            'user_id'          => $user->id,
            // organization_id terisi otomatis via Trait BelongsToOrganization!
        ]);

        if ($request->has('tags') && is_array($request->tags)) {
            $post->tags()->sync($request->tags);
        }

        ActivityLogService::log('create', 'posts', 'Membuat berita baru: ' . $post->judul . ' (Status: ' . $post->status . ')', $post);

        if ($status === 'review') {
            NotificationService::sendToOrganizationReviewers(
                $post->organization_id,
                'content_review',
                'Konten Menunggu Review',
                "{$user->name} mengirim Berita '{$post->judul}' untuk direview.",
                '/organization/posts',
                ['content_id' => $post->id, 'content_type' => 'post', 'author_id' => $user->id],
                $user->id
            );

            NotificationService::send(
                $user,
                'content_submitted',
                'Artikel Diajukan',
                "Artikel '{$post->judul}' berhasil dikirim untuk direview.",
                '/organization/posts',
                ['content_id' => $post->id, 'content_type' => 'post']
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dibuat!',
            'data'    => $post->load(['user:id,name', 'category', 'tags'])
        ], 201);
    }

    // 3. Ambil Detail 1 Artikel (Read Single dengan Category & Tags)
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('posts.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat artikel/berita.');
        }

        $post = Post::withoutGlobalScopes()->with(['user:id,name', 'category', 'tags'])->findOrFail($id);

        if (!$user->hasRole('Super Admin') && (int)$post->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat melihat artikel organisasi lain.');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $post
        ], 200);
    }

    // 4. Update Artikel (Update dengan Sync Kategori & Tags)
    public function update(Request $request, string $id)
    {
        $post = Post::withoutGlobalScopes()->findOrFail($id);
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('posts.update'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengubah artikel/berita.');
        }

        // Contributor can only update their own posts
        if ($user->hasRole('Kontributor') && $post->user_id !== $user->id) {
            abort(403, 'Unauthorized. Kontributor hanya dapat mengedit artikel miliknya sendiri.');
        }

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && $post->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah artikel organisasi lain.');
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

        $data = $request->only(['judul', 'konten', 'excerpt', 'status', 'meta_title', 'meta_description', 'category_id']);

        if ($request->has('cover_image') || $request->hasFile('cover_image')) {
            $data['cover_image'] = $this->processCoverImage($request->cover_image ?? $request->file('cover_image'), $post->cover_image);
        }

        if ($request->has('judul') && $request->judul !== $post->judul) {
            $data['slug'] = Str::slug($request->judul) . '-' . Str::random(5);
        }

        if ($request->has('status')) {
            $newStatus = $request->status;

            $canPublish = $user->hasRole('Super Admin') || $user->can('posts.publish');
            if (in_array($newStatus, ['published', 'rejected']) && !$canPublish) {
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

        $oldStatus = $post->status;
        $post->update($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags ?? []);
        }

        $action = 'update';
        $desc = 'Memperbarui berita: ' . $post->judul;
        if (isset($data['status'])) {
            $action = $data['status'] === 'published' ? 'publish' : ($data['status'] === 'rejected' ? 'reject' : ($data['status'] === 'review' ? 'submit_review' : 'update'));
            $desc .= ' (Status: ' . $data['status'] . ')';

            if ($action === 'submit_review' && $oldStatus !== 'review') {
                NotificationService::sendToOrganizationReviewers(
                    $post->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Berita '{$post->judul}' untuk direview.",
                    '/organization/posts',
                    ['content_id' => $post->id, 'content_type' => 'post', 'author_id' => $user->id],
                    $user->id
                );

                NotificationService::send(
                    $user,
                    'content_submitted',
                    'Artikel Diajukan',
                    "Artikel '{$post->judul}' berhasil dikirim untuk direview.",
                    '/organization/posts',
                    ['content_id' => $post->id, 'content_type' => 'post']
                );
            } elseif ($action === 'publish' && $oldStatus !== 'published' && $post->user_id !== $user->id) {
                if ($post->user) {
                    NotificationService::send(
                        $post->user,
                        'content_published',
                        'Artikel Dipublikasikan',
                        "Artikel '{$post->judul}' telah disetujui dan terbit di portal publik.",
                        '/organization/posts',
                        ['content_id' => $post->id, 'content_type' => 'post']
                    );
                }
            } elseif ($action === 'reject' && $oldStatus !== 'rejected' && $post->user_id !== $user->id) {
                if ($post->user) {
                    NotificationService::send(
                        $post->user,
                        'content_rejected',
                        'Artikel Perlu Revisi',
                        "Artikel '{$post->judul}' ditolak / memerlukan perbaikan.",
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

    // 5. Hapus Artikel
    public function destroy(Request $request, string $id)
    {
        $post = Post::withoutGlobalScopes()->findOrFail($id);
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('posts.delete'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk menghapus artikel/berita.');
        }

        // Contributor can only delete their own posts
        if ($user->hasRole('Kontributor') && $post->user_id !== $user->id) {
            abort(403, 'Unauthorized. Kontributor hanya dapat menghapus artikel miliknya sendiri.');
        }

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && $post->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat menghapus artikel organisasi lain.');
        }

        if ($post->cover_image && str_starts_with($post->cover_image, '/storage/posts/covers/')) {
            $oldPath = str_replace('/storage/', '', $post->cover_image);
            Storage::disk('public')->delete($oldPath);
        }

        $post->delete();

        ActivityLogService::log('delete', 'posts', 'Menghapus berita: ' . $post->judul, clone $post);

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dihapus!'
        ], 200);
    }

    /**
     * Process and store cover image from base64, uploaded file, or existing path.
     */
    private function processCoverImage($coverImageInput, ?string $oldCoverImage = null): ?string
    {
        if (empty($coverImageInput)) {
            if ($oldCoverImage && str_starts_with($oldCoverImage, '/storage/posts/covers/')) {
                $oldPath = str_replace('/storage/', '', $oldCoverImage);
                Storage::disk('public')->delete($oldPath);
            }
            return null;
        }

        // Uploaded File object
        if ($coverImageInput instanceof \Illuminate\Http\UploadedFile) {
            $path = $coverImageInput->store('posts/covers', 'public');
            if ($oldCoverImage && str_starts_with($oldCoverImage, '/storage/posts/covers/')) {
                $oldPath = str_replace('/storage/', '', $oldCoverImage);
                Storage::disk('public')->delete($oldPath);
            }
            return '/storage/' . $path;
        }

        // Base64 Data URL (e.g. data:image/png;base64,iVBORw0KGgo...)
        if (is_string($coverImageInput) && preg_match('/^data:image\/(\w+);base64,/', $coverImageInput, $matches)) {
            $imageType = strtolower($matches[1]);
            if ($imageType === 'jpeg') {
                $imageType = 'jpg';
            }

            $base64Data = substr($coverImageInput, strpos($coverImageInput, ',') + 1);
            $decoded = base64_decode($base64Data);

            if ($decoded !== false) {
                $fileName = Str::random(40) . '.' . $imageType;
                $path = 'posts/covers/' . $fileName;
                Storage::disk('public')->put($path, $decoded);

                if ($oldCoverImage && str_starts_with($oldCoverImage, '/storage/posts/covers/')) {
                    $oldPath = str_replace('/storage/', '', $oldCoverImage);
                    Storage::disk('public')->delete($oldPath);
                }

                return '/storage/' . $path;
            }
        }

        // Existing relative or absolute path
        if (is_string($coverImageInput)) {
            return $coverImageInput;
        }

        return null;
    }
}