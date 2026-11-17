<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogService;

class OrganizationWebController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Organisasi & Modul
     */
    public function settings()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $organization = $user->organization;

        // Jika Super Admin tidak punya organisasi terikat, ambil organisasi pertama atau izinkan pilih
        if (!$organization && $user->hasRole('Super Admin')) {
            $organization = Organization::first();
        }

        if (!$organization) {
            return redirect()->route('dashboard')->with('error', 'Organisasi tidak ditemukan.');
        }

        $defaultModules = [
            'posts'         => true,
            'activities'    => true,
            'committees'    => true,
            'media'         => true,
            'announcements' => true,
        ];

        $activeModules = array_merge($defaultModules, $organization->modul_aktif ?? []);

        $defaultLabels = [
            'posts'         => 'Artikel / Konten',
            'activities'    => 'Agenda Kegiatan',
            'committees'    => 'Kelola Pengurus',
            'media'         => 'Galeri Media',
            'announcements' => 'Pengumuman',
        ];

        $menuLabels = array_merge($defaultLabels, $organization->label_menu ?? []);
        $socialMedia = $organization->media_sosial ?? [];

        return view('organization.settings', compact('organization', 'activeModules', 'menuLabels', 'socialMedia'));
    }

    /**
     * Simpan / Update Pengaturan Organisasi, Kontak, Media Sosial, Logo, Warna Tema, & Modul
     */
    public function updateSettings(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organization = $user->organization;

        if (!$organization && $user->hasRole('Super Admin')) {
            $organization = Organization::findOrFail($request->input('organization_id', Organization::first()?->id));
        }

        if (!$organization) {
            return redirect()->back()->with('error', 'Organisasi tidak ditemukan.');
        }

        $request->validate([
            'nama'            => 'required|string|max:255',
            'jenis'           => 'required|in:HMPS,UKM,BEM,Senat,Lainnya',
            'warna_tema'      => 'required|string|max:10',
            'email'           => 'nullable|email|max:255',
            'telepon'         => 'nullable|string|max:50',
            'alamat'          => 'nullable|string|max:500',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'modul_aktif'     => 'nullable|array',
            'label_menu'      => 'nullable|array',
            'media_sosial'    => 'nullable|array',
            'ga_tracking_id'  => 'nullable|string|max:50',
        ]);

        $updateData = [
            'nama'           => $request->nama,
            'jenis'          => $request->jenis,
            'warna_tema'     => $request->warna_tema,
            'email'          => $request->filled('email') ? trim($request->email) : null,
            'telepon'        => $request->filled('telepon') ? trim($request->telepon) : null,
            'alamat'         => $request->filled('alamat') ? trim($request->alamat) : null,
            'ga_tracking_id' => $request->filled('ga_tracking_id') ? trim($request->ga_tracking_id) : null,
        ];

        // Process Logo Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo && Storage::disk('public')->exists($organization->logo)) {
                Storage::disk('public')->delete($organization->logo);
            }

            $path = $request->file('logo')->store('organization-logos', 'public');
            $updateData['logo'] = $path;
        }

        // Process Modul Aktif (Switches)
        $modules = [
            'posts'         => $request->has('modul_aktif.posts'),
            'activities'    => $request->has('modul_aktif.activities'),
            'committees'    => $request->has('modul_aktif.committees'),
            'media'         => $request->has('modul_aktif.media'),
            'announcements' => $request->has('modul_aktif.announcements'),
        ];
        $updateData['modul_aktif'] = $modules;

        // Process Custom Menu Labels
        if ($request->has('label_menu')) {
            $labels = [
                'posts'         => trim($request->input('label_menu.posts', 'Artikel / Konten')) ?: 'Artikel / Konten',
                'activities'    => trim($request->input('label_menu.activities', 'Agenda Kegiatan')) ?: 'Agenda Kegiatan',
                'committees'    => trim($request->input('label_menu.committees', 'Kelola Pengurus')) ?: 'Kelola Pengurus',
                'media'         => trim($request->input('label_menu.media', 'Galeri Media')) ?: 'Galeri Media',
                'announcements' => trim($request->input('label_menu.announcements', 'Pengumuman')) ?: 'Pengumuman',
            ];
            $updateData['label_menu'] = $labels;
        }

        // Process Media Sosial Links / Handles
        if ($request->has('media_sosial')) {
            $socials = [
                'instagram' => trim($request->input('media_sosial.instagram', '')) ?: null,
                'tiktok'    => trim($request->input('media_sosial.tiktok', '')) ?: null,
                'youtube'   => trim($request->input('media_sosial.youtube', '')) ?: null,
                'linkedin'  => trim($request->input('media_sosial.linkedin', '')) ?: null,
                'twitter_x' => trim($request->input('media_sosial.twitter_x', '')) ?: null,
                'website'   => trim($request->input('media_sosial.website', '')) ?: null,
            ];
            $updateData['media_sosial'] = array_filter($socials);
        }

        $organization->update($updateData);

        ActivityLogService::log('update', 'organizations', 'Memperbarui pengaturan organisasi, kontak & media sosial: ' . $organization->nama, $organization);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pengaturan organisasi, kontak, dan media sosial berhasil disimpan!',
                'data'    => $organization
            ]);
        }

        return redirect()->route('organization.settings')->with('success', 'Pengaturan organisasi, kontak, dan media sosial berhasil disimpan!');
    }
}
