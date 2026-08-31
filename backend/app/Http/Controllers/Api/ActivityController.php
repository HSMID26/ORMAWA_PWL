<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

class ActivityController extends Controller
{
    /**
     * Tampilkan semua kegiatan (Otomatis terfilter sesuai organisasi user yang login!)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('agenda.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat agenda kegiatan.');
        }

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
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('agenda.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk membuat agenda kegiatan.');
        }

        $request->validate([
            'judul'              => 'required|string|max:255',
            'deskripsi'          => 'required|string',
            'tanggal_pelaksanaan'=> 'required|date',
            'status'             => 'nullable|in:draft,review,published,rejected',
        ]);
        
        $status = $request->status ?? 'draft';

        $canPublish = $user->hasRole('Super Admin') || $user->can('agenda.publish');
        if (in_array($status, ['published', 'rejected']) && !$canPublish) {
            $status = 'draft';
        }

        $activity = Activity::create([
            'user_id'            => $user->id,
            // organization_id terisi otomatis via Trait BelongsToOrganization!
            'judul'              => $request->judul,
            'deskripsi'          => $request->deskripsi,
            'tanggal_pelaksanaan'=> $request->tanggal_pelaksanaan,
            'status'             => $status,
            'published_at'       => $status === 'published' ? now() : null,
        ]);

        ActivityLogService::log('create', 'activities', 'Membuat agenda baru: ' . $activity->judul . ' (Status: ' . $activity->status . ')', $activity);

        if ($status === 'review') {
            \App\Services\NotificationService::sendToOrganizationAdmins(
                $activity->organization_id,
                'content_review',
                'Konten Menunggu Review',
                "{$user->name} mengirim Agenda untuk direview.",
                '/organization/activities',
                ['content_id' => $activity->id, 'content_type' => 'activity', 'author_id' => $user->id]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil ditambahkan!',
            'data'    => $activity
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Activity $activity)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('agenda.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat agenda kegiatan.');
        }

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
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('agenda.update'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengubah agenda kegiatan.');
        }

        $activity = Activity::withoutGlobalScopes()->findOrFail($id);

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && $activity->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah agenda organisasi lain.');
        }

        $request->validate([
            'judul'                => 'required|string|max:255',
            'deskripsi'            => 'required|string',
            'tanggal_pelaksanaan'  => 'required|date',
            'status'               => 'required|in:draft,review,published,rejected',
        ]);

        $data = $request->only(['judul', 'deskripsi', 'tanggal_pelaksanaan', 'status']);
        
        if ($request->has('status')) {
            $newStatus = $request->status;

            $canPublish = $user->hasRole('Super Admin') || $user->can('agenda.publish');
            if (in_array($newStatus, ['published', 'rejected']) && !$canPublish) {
                $newStatus = $activity->status;
            }
            $data['status'] = $newStatus;

            if ($newStatus === 'published' && $activity->status !== 'published') {
                $data['published_at'] = now();
            }
        }

        $activity->update($data);

        $action = 'update';
        $desc = 'Memperbarui agenda: ' . $activity->judul;
        if (isset($data['status'])) {
            $action = $data['status'] === 'published' ? 'publish' : ($data['status'] === 'rejected' ? 'reject' : ($data['status'] === 'review' ? 'submit_review' : 'update'));
            $desc .= ' (Status: ' . $data['status'] . ')';

            if ($action === 'submit_review') {
                \App\Services\NotificationService::sendToOrganizationAdmins(
                    $activity->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Agenda untuk direview.",
                    '/organization/activities',
                    ['content_id' => $activity->id, 'content_type' => 'activity', 'author_id' => $user->id]
                );
            } elseif ($action === 'publish' && $activity->user_id !== $user->id) {
                if ($activity->user) {
                    \App\Services\NotificationService::send(
                        $activity->user,
                        'content_published',
                        'Konten Dipublikasikan',
                        "Konten '{$activity->judul}' berhasil dipublikasikan.",
                        '/organization/activities',
                        ['content_id' => $activity->id, 'content_type' => 'activity']
                    );
                }
            } elseif ($action === 'reject' && $activity->user_id !== $user->id) {
                if ($activity->user) {
                    \App\Services\NotificationService::send(
                        $activity->user,
                        'content_rejected',
                        'Konten Ditolak',
                        "Konten '{$activity->judul}' ditolak.",
                        '/organization/activities',
                        ['content_id' => $activity->id, 'content_type' => 'activity']
                    );
                }
            }
        }
        ActivityLogService::log($action, 'activities', $desc, $activity);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil diperbarui!',
            'data'    => $activity
        ]);
    }

    /**
     * Hapus kegiatan
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('agenda.delete'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk menghapus agenda kegiatan.');
        }

        $activity = Activity::withoutGlobalScopes()->findOrFail($id);

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && $activity->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat menghapus agenda organisasi lain.');
        }

        $activity->delete();

        ActivityLogService::log('delete', 'activities', 'Menghapus agenda: ' . $activity->judul, clone $activity);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil dihapus!'
        ]);
    }
}