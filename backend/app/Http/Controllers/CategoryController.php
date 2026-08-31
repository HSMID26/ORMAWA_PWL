<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public const DEFAULT_CATEGORIES = [
        ['name' => 'Akademik', 'slug' => 'akademik'],
        ['name' => 'Kegiatan', 'slug' => 'kegiatan'],
        ['name' => 'Prestasi', 'slug' => 'prestasi'],
        ['name' => 'Informasi', 'slug' => 'informasi'],
        ['name' => 'Opini', 'slug' => 'opini'],
    ];

    /**
     * Memastikan kategori baseline default tersedia untuk suatu organisasi
     */
    public static function ensureDefaultCategories(int $organizationId): void
    {
        foreach (self::DEFAULT_CATEGORIES as $cat) {
            $exists = Category::withoutGlobalScopes()
                ->where('organization_id', $organizationId)
                ->where(function ($q) use ($cat) {
                    $q->where('slug', $cat['slug'])
                      ->orWhere('name', $cat['name']);
                })
                ->exists();

            if (!$exists) {
                Category::create([
                    'organization_id' => $organizationId,
                    'name'            => $cat['name'],
                    'slug'            => $cat['slug'],
                ]);
            }
        }
    }

    // Ambil semua daftar kategori milik organisasi yang sedang login
    public function index()
    {
        $user = auth()->user();
        if ($user && $user->organization_id) {
            $count = Category::withoutGlobalScopes()
                ->where('organization_id', $user->organization_id)
                ->count();

            if ($count === 0) {
                self::ensureDefaultCategories($user->organization_id);
            }
        }

        // Trait BelongsToOrganization otomatis memfilter berdasarkan organization_id
        $categories = Category::orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'data'   => $categories
        ]);
    }

    // Simpan Kategori Baru
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $orgId = auth()->user()?->organization_id;

        $name = trim($request->name);
        $existing = Category::withoutGlobalScopes()
            ->where('organization_id', $orgId)
            ->where('name', $name)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'success',
                'data'   => $existing
            ], 200);
        }

        $category = Category::create([
            'organization_id' => $orgId,
            'name'            => $name,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $category
        ], 201);
    }
}
