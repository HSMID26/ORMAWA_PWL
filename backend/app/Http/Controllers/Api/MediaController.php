<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072', // Max 3MB
        ]);

        if ($request->file('image')) {
            // Simpan gambar ke storage/app/public/editor-images
            $path = $request->file('image')->store('editor-images', 'public');
            
            // Kembalikan URL publik
            return response()->json([
                'status' => 'success',
                'url'    => asset('storage/' . $path)
            ], 200);
        }

        return response()->json(['message' => 'Upload gagal'], 400);
    }
}