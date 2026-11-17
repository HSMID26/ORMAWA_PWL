<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public const DEFAULT_TAGS = [
        'Mahasiswa', 'Prestasi', 'Kegiatan', 'Akademik', 'Workshop', 'Seminar', 'Teknologi', 'Kepanitiaan', 'Kompetisi'
    ];

    public static function ensureDefaultTags(): void
    {
        foreach (self::DEFAULT_TAGS as $tagName) {
            Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => \Illuminate\Support\Str::slug($tagName)]
            );
        }
    }

    // Ambil semua tag global
    public function index()
    {
        if (Tag::count() === 0) {
            self::ensureDefaultTags();
        }

        $tags = Tag::orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'data'   => $tags
        ]);
    }

    // Simpan Tag Baru
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $tag = Tag::firstOrCreate([
            'name' => trim($request->name),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $tag
        ], 201);
    }
}
