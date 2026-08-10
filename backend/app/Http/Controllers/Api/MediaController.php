<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072', // Max 3MB
        ]);

        if ($request->hasFile('image')) {
            // Simpan gambar ke storage/app/public/editor-images
            $path = $request->file('image')->store('editor-images', 'public');
            
            // Dapatkan Full URL menggunakan helper url()
            $url = url(Storage::url($path));

            return response()->json([
                'status' => 'success',
                'url'    => $url
            ], 200, [], JSON_UNESCAPED_SLASHES); 
        }

        return response()->json(['message' => 'Upload gagal'], 400);
    }
}