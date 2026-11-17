<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaWebController extends Controller
{
    /**
     * Tampilkan Galeri & Manajemen Media Library
     */
    public function index(Request $request)
    {
        $search = trim($request->input('q', ''));
        $type   = $request->input('type', 'all');
        $sort   = $request->input('sort', 'latest');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orgId = $user->organization_id ?? Organization::first()?->id ?? 1;

        // Base Query (Trait BelongsToOrganization otomatis mengisolasi per Ormawa)
        $query = Media::with(['user:id,name', 'organization:id,nama']);

        // 1. Search Filter
        if (!empty($search)) {
            $query->where('filename', 'like', "%{$search}%");
        }

        // 2. Type Filter
        if ($type === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($type === 'video') {
            $query->where('mime_type', 'like', 'video/%');
        }

        // 3. Sorting
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'size_desc':
                $query->orderByDesc('size');
                break;
            case 'size_asc':
                $query->orderBy('size');
                break;
            case 'name_asc':
                $query->orderBy('filename');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $mediaList = $query->paginate(24)->withQueryString();

        // 4. Kalkulasi Statistik Penyimpanan Ormawa
        $statsQuery = Media::query();
        $totalFiles = (clone $statsQuery)->count();
        $totalBytes = (clone $statsQuery)->sum('size');
        $imagesCount = (clone $statsQuery)->where('mime_type', 'like', 'image/%')->count();
        $videosCount = (clone $statsQuery)->where('mime_type', 'like', 'video/%')->count();

        $totalStorageFormatted = $this->formatBytes($totalBytes);

        return view('Media.index', compact(
            'mediaList',
            'search',
            'type',
            'sort',
            'totalFiles',
            'totalBytes',
            'totalStorageFormatted',
            'imagesCount',
            'videosCount'
        ));
    }

    /**
     * API JSON untuk Modal Media Picker
     */
    public function picker(Request $request)
    {
        $search = trim($request->input('q', ''));
        $type   = $request->input('type', 'image');

        $query = Media::with('user:id,name')->latest();

        if (!empty($search)) {
            $query->where('filename', 'like', "%{$search}%");
        }

        if ($type === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($type === 'video') {
            $query->where('mime_type', 'like', 'video/%');
        }

        $items = $query->take(40)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $items
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), 1) . ' ' . $units[$power];
    }
}
