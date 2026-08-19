<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

class ActivityController extends Controller
{
    /**
     * Tampilkan semua kegiatan
     */
    public function index()
    {
        $activities = Activity::latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $activities
        ]);
    }

    /**
     * Tambah kegiatan baru (API)
     */
    public function store(Request $request)
    {
        $title       = $request->title ?? $request->judul;
        $description = $request->description ?? $request->deskripsi;
        $startTime   = $request->start_time ?? $request->tanggal_pelaksanaan;

        if (!$title || !$startTime) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Judul dan Waktu Mulai wajib diisi!'
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        $status = $request->status ?? 'upcoming';

        if (in_array($status, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
            if ($user->hasRole('Kontributor') && $status === 'published') {
                $status = 'review';
            }
        }

        $activity = Activity::create([
            'user_id'             => $user->id,
            'title'               => $title,
            'judul'               => $title,
            'description'         => $description,
            'deskripsi'           => $description,
            'location'            => $request->location ?? $request->lokasi,
            'start_time'          => $startTime,
            'tanggal_pelaksanaan' => $startTime,
            'end_time'            => $request->end_time,
            'status'              => $status,
            'published_at'        => $status === 'published' ? now() : null,
        ]);

        ActivityLogService::log('create', 'activities', 'Membuat agenda baru: ' . $activity->title . ' (Status: ' . $activity->status . ')', $activity);

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
     * Update kegiatan (API)
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $title       = $request->title ?? $request->judul ?? $activity->title;
        $description = $request->description ?? $request->deskripsi ?? $activity->description;
        $startTime   = $request->start_time ?? $request->tanggal_pelaksanaan ?? $activity->start_time;

        $activity->update([
            'title'               => $title,
            'judul'               => $title,
            'description'         => $description,
            'deskripsi'           => $description,
            'location'            => $request->location ?? $request->lokasi ?? $activity->location,
            'start_time'          => $startTime,
            'tanggal_pelaksanaan' => $startTime,
            'end_time'            => $request->end_time ?? $activity->end_time,
            'status'              => $request->status ?? $activity->status,
        ]);

        ActivityLogService::log('update', 'activities', 'Memperbarui agenda: ' . $activity->title, $activity);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil diperbarui!',
            'data'    => $activity
        ]);
    }

    /**
     * Hapus kegiatan (API)
     */
    public function destroy(string $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        ActivityLogService::log('delete', 'activities', 'Menghapus agenda: ' . $activity->title, clone $activity);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil dihapus!'
        ]);
    }
}