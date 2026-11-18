<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;
use App\Services\ActivityApprovalWorkflowService;

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
    public function store(Request $request, ActivityApprovalWorkflowService $workflow)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('agenda.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk membuat agenda kegiatan.');
        }

        $validated = $request->validate([
            'judul'              => 'required|string|max:255',
            'deskripsi'          => 'required|string',
            'tanggal_pelaksanaan'=> 'required|date',
            'status'             => 'nullable|in:draft,review,published,rejected',
        ]);
        
        $status = $validated['status'] ?? 'draft';

        $canPublish = $user->hasRole('Super Admin') || $user->can('agenda.publish');
        if (in_array($status, ['published', 'rejected']) && !$canPublish) {
            $status = 'draft';
        }
        $validated['status'] = $status;
        $activity = $workflow->create($validated, $user);

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

        if (!$user->hasRole('Super Admin') && (int)$activity->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat melihat agenda organisasi lain.');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $activity->load(['user:id,name', 'organization:id,nama'])
        ]);
    }

    /**
     * Update kegiatan
     */
    public function update(Request $request, string $id, ActivityApprovalWorkflowService $workflow)
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
            'status'               => 'required|in:draft,review,published,rejected,revision_requested',
            'review_note'          => 'required_if:status,rejected,revision_requested|nullable|string|max:2000',
        ]);

        $data = $request->only(['judul', 'deskripsi', 'tanggal_pelaksanaan', 'status']);
        
        if ($request->has('status')) {
            $newStatus = $request->status;

            $canPublish = $user->hasRole('Super Admin') || $user->can('agenda.publish');
            abort_if($newStatus === 'revision_requested' && !$canPublish, 403, 'Anda tidak memiliki izin untuk meminta revisi agenda.');
            if (in_array($newStatus, ['published', 'rejected', 'revision_requested']) && !$canPublish) {
                $newStatus = $activity->status;
            }
            $data['status'] = $newStatus;

            if ($newStatus === 'published' && $activity->status !== 'published') {
                $data['published_at'] = now();
            }
            if ($newStatus !== 'published' && $activity->status === 'published') {
                $data['published_at'] = null;
            }
            if (in_array($newStatus, ['published', 'rejected', 'revision_requested'], true) && $canPublish) {
                $data['reviewed_by'] = $user->id;
                $data['reviewed_at'] = now();
                $data['review_note'] = $request->input('review_note');
                if ($newStatus !== 'published') {
                    $data['published_at'] = null;
                }
            } elseif ($newStatus === 'review' && $activity->status !== 'review') {
                $data['review_note'] = null;
            }
        }

        $activity = $workflow->update($activity, $data, $user);

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