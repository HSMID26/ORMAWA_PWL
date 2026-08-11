<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Tampilkan semua kegiatan (Otomatis terfilter sesuai organisasi user yang login!)
     */
    public function index()
    {
        $activities = Activity::with('user')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $activities
        ]);
    }

    /**
     * Tambah kegiatan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul'              => 'required|string|max:255',
            'deskripsi'          => 'required|string',
            'tanggal_pelaksanaan'=> 'required|date',
            'status'             => 'nullable|in:draft,published',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user(); // Atau auth()->user()

        $activity = Activity::create([
            'user_id'            => $user->id,
            'judul'              => $request->judul,
            'deskripsi'          => $request->deskripsi,
            'tanggal_pelaksanaan'=> $request->tanggal_pelaksanaan,
            'status'             => $request->status ?? 'published',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil ditambahkan!',
            'data'    => $activity
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        return response()->json([
            'status' => 'success',
            'data'   => $activity->load(['user:id,name', 'organization:id,nama'])
        ]);
    }

    /**
     * Update kegiatan
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'judul'                => 'required|string|max:255',
            'deskripsi'            => 'required|string',
            'tanggal_pelaksanaan'  => 'required|date',
            'status'               => 'required|in:draft,published',
        ]);

        $activity->update($request->only(['judul', 'deskripsi', 'tanggal_pelaksanaan', 'status']));

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil diperbarui!',
            'data'    => $activity
        ]);
    }

    /**
     * Hapus kegiatan
     */
    public function destroy(string $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil dihapus!'
        ]);
    }
}