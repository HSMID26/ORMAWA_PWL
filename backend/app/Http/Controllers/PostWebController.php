<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostWebController extends Controller
{
    // 1. Halaman Daftar Artikel (Daftar Post Ormawa)
    public function index()
    {
        // Trait BelongsToOrganization otomatis menyaring post sesuai organisasi user
        $posts = Post::with(['user', 'organization'])->latest()->get();

        return view('posts.index', compact('posts'));
    }

    // 2. Halaman Form Buat Artikel Baru (Editor TipTap)
    public function create()
    {
        return view('posts.create');
    }

    // 3. Halaman Detail Artikel
    public function show(Post $post)
    {
        $post->load(['user', 'organization']);
        return view('posts.show', compact('post'));
    }

    // 4. Halaman Form Edit Artikel
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    // 5. Proses Update Artikel (via Form / Fetch Web)
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'judul'  => 'required|string|max:255',
            'konten' => 'required|string',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->judul !== $post->judul) {
            $post->slug = Str::slug($request->judul) . '-' . Str::random(5);
        }

        $post->update([
            'judul'  => $request->judul,
            'konten' => $request->konten,
            'status' => $request->status,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Artikel berhasil diperbarui!',
                'data'    => $post
            ]);
        }

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil diperbarui!');
    }

    // 6. Proses Hapus Artikel
    public function destroy(Request $request, Post $post)
    {
        $post->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Artikel berhasil dihapus!'
            ]);
        }

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil dihapus!');
    }
}
