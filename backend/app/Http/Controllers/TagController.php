<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // Ambil semua tag global
    public function index()
    {
        $tags = Tag::latest()->get();
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
