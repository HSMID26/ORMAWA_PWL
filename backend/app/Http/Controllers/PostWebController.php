<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PageView;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostWebController extends Controller
{
    // 1. Halaman Daftar Artikel (Daftar Post Ormawa)
    public function index()
    {
        // Trait BelongsToOrganization otomatis menyaring post sesuai organisasi user
        $posts = Post::with(['user', 'organization', 'category'])->latest()->get();

        return view('posts.index', compact('posts'));
    }

    // 2. Halaman Form Buat Artikel Baru (Editor TipTap Modern)
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('posts.create', compact('categories', 'tags'));
    }

    // 3. Halaman Detail Artikel (Termasuk Pencatatan PageView)
    public function show(Request $request, Post $post)
    {
        $post->load(['user', 'organization', 'category', 'tags']);

        // Catat PageView internal traffic
        try {
            PageView::create([
                'organization_id' => $post->organization_id,
                'url_path'        => '/' . ltrim($request->path(), '/'),
                'ip_address'      => $request->ip(),
                'user_agent'      => substr($request->userAgent() ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            // Ignore tracking exceptions if any
        }

        return view('posts.show', compact('post'));
    }

    // 4. Halaman Form Edit Artikel
    public function edit(Post $post)
    {
        $post->load(['tags', 'category', 'user']);
        $categories = Category::all();
        $tags = Tag::all();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    // 5. Proses Update Artikel (via Form / Fetch Web)
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'konten'      => 'required|string',
            'status'      => 'required|in:draft,published',
            'category_id' => 'nullable|exists:categories,id',
            'tags'        => 'nullable|array',
            'cover_image' => 'nullable|string',
            'excerpt'     => 'nullable|string',
        ]);

        if ($request->judul !== $post->judul) {
            $post->slug = Str::slug($request->judul) . '-' . Str::random(5);
        }

        $post->update([
            'judul'        => $request->judul,
            'konten'       => $request->konten,
            'status'       => $request->status,
            'category_id'  => $request->category_id,
            'cover_image'  => $request->cover_image,
            'excerpt'      => $request->excerpt,
            'published_at' => $request->status === 'published' && !$post->published_at ? now() : $post->published_at,
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        ActivityLogService::log('update', 'posts', 'Memperbarui artikel: ' . $post->judul, $post);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Artikel berhasil diperbarui!',
                'data'    => $post->load(['category', 'tags'])
            ]);
        }

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil diperbarui!');
    }

    // 6. Proses Hapus Artikel
    public function destroy(Request $request, Post $post)
    {
        $judul = $post->judul;
        $postCopy = clone $post;
        $post->delete();

        ActivityLogService::log('delete', 'posts', 'Menghapus artikel: ' . $judul, $postCopy);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Artikel berhasil dihapus!'
            ]);
        }

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil dihapus!');
    }
}
