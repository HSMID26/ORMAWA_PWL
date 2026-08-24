<?php

namespace App\Http\Controllers;

use App\Models\Media;

class MediaWebController extends Controller
{
    public function index()
    {
        // Trait BelongsToOrganization otomatis memfilter media per Ormawa
        $mediaList = Media::with('user')->latest()->get();

        return view('Media.index', compact('mediaList'));
    }
}
