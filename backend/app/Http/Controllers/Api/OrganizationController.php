<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Tampilkan semua daftar organisasi
     */
    public function index()
    {
        $organizations = Organization::latest()->get();

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
        $organization = Organization::findOrFail($id);

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
        $organization = Organization::findOrFail($id);

        $request->validate([
            'nama'        => 'required|string|max:255',
            'jenis'       => 'required|in:HMPS,UKM,BEM',
            'subdomain'   => 'required|string|unique:organizations,subdomain,' . $id,
            'warna_tema'  => 'nullable|string',
            'modul_aktif' => 'nullable|array',
        ]);

        $organization->update([
            'nama'        => $request->nama,
            'jenis'       => $request->jenis,
            'subdomain'   => strtolower($request->subdomain),
            'warna_tema'  => $request->warna_tema ?? $organization->warna_tema,
            'modul_aktif' => $request->modul_aktif ?? $organization->modul_aktif,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data organisasi berhasil diperbarui!',
            'data'    => $organization
        ]);
    }

    /**
     * Hapus organisasi
     */
    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        $organization->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Organisasi berhasil dihapus!'
        ]);
    }
}