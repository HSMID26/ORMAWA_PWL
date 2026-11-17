<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogService;

class OrganizationController extends Controller
{
    /**
     * Tampilkan semua daftar organisasi
     */
    public function index()
    {
        $organizations = Organization::with('periods')
            ->withCount(['users', 'committees', 'posts', 'activities', 'announcements', 'media'])
            ->latest()
            ->get();

        $organizations = $organizations->map(function ($org) {
            $org->current_period = $org->currentPeriod();
            $adminUser = $org->users()->whereHas('roles', function ($q) {
                $q->where('name', 'Admin Organisasi');
            })->first();
            $org->admin_user = $adminUser ? [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'status' => $adminUser->status,
            ] : null;
            return $org;
        });

        return response()->json([
            'status' => 'success',
            'data' => $organizations
        ]);
    }

    /**
     * Tambah organisasi baru (Khusus Super Admin PKA)
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat membuat organisasi baru.');
        }

        $request->validate([
            'nama'        => 'required|string|max:255',
            'jenis'       => 'required|in:HMPS,UKM,BEM',
            'subdomain'   => 'required|string|unique:organizations,subdomain',
            'warna_tema'  => 'nullable|string',
            'modul_aktif' => 'nullable|array',
        ]);

        $organization = Organization::create([
            'nama'        => $request->nama,
            'jenis'       => $request->jenis,
            'subdomain'   => strtolower($request->subdomain),
            'warna_tema'  => $request->warna_tema ?? '#1d4ed8',
            'modul_aktif' => $request->modul_aktif ?? ['galeri' => true, 'proker' => true],
        ]);

        ActivityLogService::log('create', 'organizations', 'Membuat organisasi baru: ' . $organization->nama, $organization);

        return response()->json([
            'status'  => 'success',
            'message' => 'Organisasi berhasil ditambahkan!',
            'data'    => $organization
        ], 201);
    }

    /**
     * Detail satu organisasi
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasRole('Super Admin') && (int)$user->organization_id !== (int)$id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengakses data organisasi lain.');
        }

        $organization = Organization::withCount(['users', 'committees', 'posts', 'activities', 'announcements', 'media'])
            ->findOrFail($id);

        $currentPeriod = $organization->currentPeriod();
        $adminUser = $organization->users()->whereHas('roles', function ($q) {
            $q->where('name', 'Admin Organisasi');
        })->first();

        $editorsCount = $organization->users()->whereHas('roles', function ($q) {
            $q->where('name', 'Editor');
        })->count();

        $contributorsCount = $organization->users()->whereHas('roles', function ($q) {
            $q->where('name', 'Kontributor');
        })->count();

        $publishedPosts = $organization->posts()->where('status', 'published')->count();
        $reviewPosts = $organization->posts()->where('status', 'review')->count();

        $recentActivities = \App\Models\ActivityLog::where('organization_id', $organization->id)
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        $organization->current_period = $currentPeriod;
        $organization->admin_user = $adminUser ? [
            'id' => $adminUser->id,
            'name' => $adminUser->name,
            'email' => $adminUser->email,
            'status' => $adminUser->status,
        ] : null;
        $organization->editors_count = $editorsCount;
        $organization->contributors_count = $contributorsCount;
        $organization->published_posts_count = $publishedPosts;
        $organization->review_posts_count = $reviewPosts;
        $organization->recent_activities = $recentActivities;

        return response()->json([
            'status' => 'success',
            'data'   => $organization
        ]);
    }

    /**
     * Update data organisasi
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('organizations.manage'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengubah pengaturan organisasi.');
        }

        if (!$user->hasRole('Super Admin') && (int)$user->organization_id !== (int)$id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah pengaturan organisasi lain.');
        }

        $organization = Organization::findOrFail($id);

        $request->validate([
            'nama'        => 'sometimes|required|string|max:255',
            'jenis'       => 'sometimes|required|in:HMPS,UKM,BEM',
            'subdomain'   => 'sometimes|required|string|unique:organizations,subdomain,' . $id,
            'warna_tema'  => 'nullable|string',
            'logo'        => 'nullable|string',
            'modul_aktif' => 'nullable|array',
        ]);

        $updateData = [
            'jenis'       => $request->jenis ?? $organization->jenis,
            'warna_tema'  => $request->warna_tema ?? $organization->warna_tema,
            'modul_aktif' => $request->modul_aktif ?? $organization->modul_aktif,
        ];

        if ($request->has('logo')) {
            $updateData['logo'] = $request->logo;
        }
        if ($request->has('hero_image')) {
            $updateData['hero_image'] = $request->hero_image;
        }
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }
        if ($request->has('telepon')) {
            $updateData['telepon'] = $request->telepon;
        }
        if ($request->has('alamat')) {
            $updateData['alamat'] = $request->alamat;
        }
        if ($request->has('media_sosial')) {
            $updateData['media_sosial'] = $request->media_sosial;
        }
        if ($request->has('label_menu')) {
            $updateData['label_menu'] = $request->label_menu;
        }
        if ($request->has('ga_tracking_id')) {
            $updateData['ga_tracking_id'] = $request->ga_tracking_id;
        }

        // Super Admin can change nama & subdomain
        if ($user->hasRole('Super Admin')) {
            if ($request->has('nama')) $updateData['nama'] = $request->nama;
            if ($request->has('subdomain')) $updateData['subdomain'] = strtolower($request->subdomain);
        }

        $organization->update($updateData);

        ActivityLogService::log('update', 'organizations', 'Memperbarui data organisasi: ' . $organization->nama, $organization);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data organisasi berhasil diperbarui!',
            'data'    => $organization
        ]);
    }

    /**
     * Upload logo organisasi
     */
    public function uploadLogo(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('organizations.manage'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengunggah logo organisasi.');
        }

        if (!$user->hasRole('Super Admin') && (int)$user->organization_id !== (int)$id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah pengaturan organisasi lain.');
        }

        $organization = Organization::findOrFail($id);

        $request->validate([
            'logo' => 'required|file|mimes:jpeg,jpg,png,webp,avif,svg|max:5120',
        ], [
            'logo.required' => 'Berkas logo wajib dipilih.',
            'logo.mimes' => 'Format logo harus berupa JPEG, JPG, PNG, WEBP, AVIF, atau SVG.',
            'logo.max' => 'Ukuran berkas logo maksimal 5MB.',
        ]);

        $oldLogo = $organization->logo;
        $path = $request->file('logo')->store('organizations/logos', 'public');
        $logoUrl = '/storage/' . $path;

        $organization->update(['logo' => $logoUrl]);

        if ($oldLogo && str_starts_with($oldLogo, '/storage/organizations/logos/')) {
            $oldPath = str_replace('/storage/', '', $oldLogo);
            Storage::disk('public')->delete($oldPath);
        }

        ActivityLogService::log('update', 'organizations', 'Mengunggah logo baru organisasi: ' . $organization->nama, $organization);

        return response()->json([
            'status' => 'success',
            'message' => 'Logo organisasi berhasil diperbarui!',
            'logo' => $logoUrl,
            'data' => $organization
        ]);
    }

    /**
     * Upload foto hero website organisasi
     */
    public function uploadHero(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('organizations.manage'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengunggah foto hero website organisasi.');
        }

        if (!$user->hasRole('Super Admin') && (int)$user->organization_id !== (int)$id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah pengaturan organisasi lain.');
        }

        $organization = Organization::findOrFail($id);

        $request->validate([
            'hero_image' => 'required|file|mimes:jpeg,jpg,png,webp,avif|max:10240',
        ], [
            'hero_image.required' => 'Berkas foto hero wajib dipilih.',
            'hero_image.mimes' => 'Format foto hero harus berupa JPG, JPEG, PNG, WEBP, atau AVIF.',
            'hero_image.max' => 'Ukuran berkas foto hero maksimal 10MB.',
        ]);

        $oldHero = $organization->hero_image;
        $path = $request->file('hero_image')->store('organizations/heroes', 'public');
        $heroUrl = '/storage/' . $path;

        $organization->update(['hero_image' => $heroUrl]);

        if ($oldHero && str_starts_with($oldHero, '/storage/organizations/heroes/')) {
            $oldPath = str_replace('/storage/', '', $oldHero);
            Storage::disk('public')->delete($oldPath);
        }

        ActivityLogService::log('update', 'organizations', 'Mengunggah foto hero baru organisasi: ' . $organization->nama, $organization);

        return response()->json([
            'status' => 'success',
            'message' => 'Foto hero website berhasil diperbarui!',
            'hero_image' => $heroUrl,
            'data' => $organization
        ]);
    }

    /**
     * Hapus foto hero website organisasi (kembali ke fallback theme color)
     */
    public function removeHero(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('organizations.manage'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk menghapus foto hero organisasi.');
        }

        if (!$user->hasRole('Super Admin') && (int)$user->organization_id !== (int)$id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah pengaturan organisasi lain.');
        }

        $organization = Organization::findOrFail($id);

        $oldHero = $organization->hero_image;
        if ($oldHero && str_starts_with($oldHero, '/storage/organizations/heroes/')) {
            $oldPath = str_replace('/storage/', '', $oldHero);
            Storage::disk('public')->delete($oldPath);
        }

        $organization->update(['hero_image' => null]);

        ActivityLogService::log('update', 'organizations', 'Menghapus foto hero organisasi: ' . $organization->nama, $organization);

        return response()->json([
            'status' => 'success',
            'message' => 'Foto hero berhasil dihapus.',
            'hero_image' => null,
            'data' => $organization
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat menghapus organisasi.');
        }

        $organization = Organization::findOrFail($id);
        $organization->delete();

        ActivityLogService::log('delete', 'organizations', 'Menghapus organisasi: ' . $organization->nama, clone $organization);

        return response()->json([
            'status'  => 'success',
            'message' => 'Organisasi berhasil dihapus!'
        ]);
    }

    /**
     * Activate Organization
     */
    public function activate($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat mengaktifkan organisasi.');
        }

        $organization = Organization::findOrFail($id);

        if ($organization->periods()->count() > 0 && !$organization->currentPeriod()) {
            return response()->json([
                'message' => 'Organisasi belum memiliki periode aktif. Aktivasi ditolak.'
            ], 422);
        }

        $organization->update(['status' => 'active']);

        ActivityLogService::log('activate', 'organizations', 'Mengaktifkan kembali organisasi: ' . $organization->nama, clone $organization);

        return response()->json([
            'status' => 'success',
            'message' => 'Organisasi berhasil diaktifkan.',
            'data' => $organization
        ]);
    }

    /**
     * Deactivate Organization
     */
    public function deactivate($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat menonaktifkan organisasi.');
        }

        $organization = Organization::findOrFail($id);
        
        $organization->update(['status' => 'inactive']);

        ActivityLogService::log('deactivate', 'organizations', 'Menonaktifkan organisasi: ' . $organization->nama, clone $organization);

        return response()->json([
            'status' => 'success',
            'message' => 'Organisasi berhasil dinonaktifkan.',
            'data' => $organization
        ]);
    }
}