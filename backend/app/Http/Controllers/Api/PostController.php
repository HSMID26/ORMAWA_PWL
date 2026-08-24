<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\PageView; 
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    // 1. Ambil Semua Artikel (Eager Load Relasi Category & Tags)
    public function index()
    {
        $posts = Post::with(['user:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $posts
        ], 200);
    }

    // 2. Buat Artikel Baru (Termasuk Category & Tags)
    public function store(Request $request)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'konten'      => 'required|string',
            'cover_image' => 'nullable|string',
            'status'      => 'required|in:draft,published',
            'category_id' => 'nullable|exists:categories,id', // Validasi Kategori
            'tags'        => 'nullable|array',               // Array of Tag IDs, ex: [1, 2]
            'tags.*'      => 'exists:tags,id',
        ]);

        $post = Post::create([
            'judul'       => $request->judul,
            'slug'        => Str::slug($request->judul) . '-' . Str::random(5),
            'konten'      => $request->konten,
            'cover_image' => $request->cover_image,
            'status'      => $request->status,
            'category_id' => $request->category_id,
            'user_id'     => Auth::id() ?? $request->user()?->id,
        ]);

        // Attach Tags ke Pivot Table post_tag
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dibuat!',
            'data'    => $post->load(['category', 'tags'])
        ], 201);
    }

    // 3. Ambil Detail 1 Artikel (Include Category & Tags)
    public function show(string $id)
    {
        $post = Post::with(['user:id,name', 'category', 'tags'])->findOrFail($id);

        // Catat kunjungan pengunjung ke database PageView
        PageView::create([
            'organization_id' => $post->organization_id ?? null,
            'url_path'        => request()->path(),
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $post
        ], 200);
    }

    // 4. Update Artikel (Termasuk Sync Kategori & Tags)
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'judul'       => 'sometimes|required|string|max:255',
            'konten'      => 'sometimes|required|string',
            'cover_image' => 'nullable|string',
            'status'      => 'sometimes|required|in:draft,published',
            'category_id' => 'nullable|exists:categories,id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,id',
        ]);

        if ($request->has('judul') && $request->judul !== $post->judul) {
            $post->slug = Str::slug($request->judul) . '-' . Str::random(5);
        }

        $post->update($request->only(['judul', 'konten', 'cover_image', 'status', 'category_id']));

        // Update Tag Sync jika dikirimkan di Request
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil diperbarui!',
            'data'    => $post->load(['category', 'tags'])
        ], 200);
    }

    // 5. Hapus Artikel
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dihapus!'
        ], 200);
    }
}