<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Ambil semua daftar kategori milik organisasi yang sedang login
    public function index()
    {
        // Trait BelongsToOrganization otomatis memfilter berdasarkan organization_id
        $categories = Category::latest()->get();
        return response()->json([
            'status' => 'success',
            'data'   => $categories
        ]);
    }

    // Simpan Kategori Baru
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $category = Category::firstOrCreate([
            'organization_id' => auth()->user()?->organization_id,
            'name'            => trim($request->name),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $category
        ], 201);
    }
}
