<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'judul'       => 'required|string|max:255',
            'konten'      => 'required|string', // String HTML dari TipTap
            'cover_image' => 'nullable|string',
            'status'      => 'required|in:draft,published',
        ]);

        $post = Post::create([
            'judul'       => $request->judul,
            'slug'        => Str::slug($request->judul) . '-' . Str::random(5), // Auto-generate slug unik
            'konten'      => $request->konten,
            'cover_image' => $request->cover_image,
            'status'      => $request->status,
            'user_id'     => Auth::id() ?? $request->user()?->id, // Otomatis id user yang login
            // organization_id terisi otomatis via Trait BelongsToOrganization!
        ]);

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
            'judul'       => 'sometimes|required|string|max:255',
            'konten'      => 'sometimes|required|string',
            'cover_image' => 'nullable|string',
            'status'      => 'sometimes|required|in:draft,published',
        ]);

        // Update slug jika judul berubah
        if ($request->has('judul') && $request->judul !== $post->judul) {
            $post->slug = Str::slug($request->judul) . '-' . Str::random(5);
        }

        $post->update($request->only(['judul', 'konten', 'cover_image', 'status']));

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

        return response()->json([
            'status'  => 'success',
            'message' => 'Artikel berhasil dihapus!'
        ], 200);
    }
}