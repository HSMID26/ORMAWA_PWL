<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Ambil semua daftar kategori untuk dropdown/pilihan
    public function index()
    {
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

        $category = Category::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $category
        ], 201);
    }
}