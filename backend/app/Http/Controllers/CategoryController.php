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
            Category::firstOrCreate(
                ['organization_id' => $organizationId, 'slug' => $cat['slug']],
                ['name' => $cat['name']]
            );
        }
    }

    // Ambil semua daftar kategori milik organisasi yang sedang login
    public function index()
    {
        $user = auth()->user();
        if ($user && $user->organization_id) {
            if (Category::where('organization_id', $user->organization_id)->count() === 0) {
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
