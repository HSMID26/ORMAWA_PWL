<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('announcements.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat pengumuman.');
        }

        $announcements = Announcement::with(['user:id,name', 'organization:id,nama'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $announcements
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('announcements.create'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk membuat pengumuman.');
        }

        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'effective_date'   => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:effective_date',
            'priority'         => 'nullable|in:low,normal,high,urgent',
            'status'           => 'nullable|in:draft,review,published,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ], [
            'expires_at.after_or_equal' => 'Tanggal berlaku hingga harus setelah atau sama dengan tanggal efektif.',
        ]);

        $status = $request->status ?? 'draft';

        $canPublish = $user->hasRole('Super Admin') || $user->can('announcements.publish');
        if (in_array($status, ['published', 'rejected']) && !$canPublish) {
            $status = 'draft';
        }

        $announcement = Announcement::create([
            'organization_id'  => $user->organization_id,
            'user_id'          => $user->id,
            'title'            => $request->title,
            'slug'             => Str::slug($request->title) . '-' . Str::random(5),
            'content'          => $request->content,
            'effective_date'   => $request->effective_date,
            'expires_at'       => $request->expires_at,
            'priority'         => $request->priority ?? 'normal',
            'status'           => $status,
            'published_at'     => $status === 'published' ? now() : null,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
        ]);

        ActivityLogService::log('create', 'announcements', 'Membuat pengumuman baru: ' . $announcement->title . ' (Status: ' . $announcement->status . ')', $announcement);

        if ($status === 'review') {
            \App\Services\NotificationService::sendToOrganizationReviewers(
                $announcement->organization_id,
                'content_review',
                'Konten Menunggu Review',
                "{$user->name} mengirim Pengumuman '{$announcement->title}' untuk direview.",
                '/organization/announcements',
                ['content_id' => $announcement->id, 'content_type' => 'announcement', 'author_id' => $user->id],
                $user->id
            );

            \App\Services\NotificationService::send(
                $user,
                'content_submitted',
                'Pengumuman Diajukan',
                "Pengumuman '{$announcement->title}' berhasil dikirim untuk direview.",
                '/organization/announcements',
                ['content_id' => $announcement->id, 'content_type' => 'announcement']
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman berhasil ditambahkan!',
            'data'    => $announcement
        ], 201);
    }

    /**
     * Detail pengumuman
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('announcements.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat pengumuman.');
        }

        $announcement = Announcement::withoutGlobalScopes()->findOrFail($id);

        if (!$user->hasRole('Super Admin') && $announcement->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat melihat pengumuman organisasi lain.');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $announcement->load(['user:id,name', 'organization:id,nama'])
        ]);
    }

    /**
     * Update pengumuman
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('announcements.update'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengubah pengumuman.');
        }

        $announcement = Announcement::withoutGlobalScopes()->findOrFail($id);

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && $announcement->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat mengubah pengumuman organisasi lain.');
        }

        $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'content'          => 'sometimes|required|string',
            'priority'         => 'sometimes|required|in:low,normal,high,urgent',
            'status'           => 'sometimes|required|in:draft,review,published,archived,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'expires_at'       => 'nullable|date',
        ]);

        $data = $request->only(['title', 'content', 'priority', 'status', 'meta_title', 'meta_description', 'expires_at']);

        if ($request->has('status')) {
            $newStatus = $request->status;

            $canPublish = $user->hasRole('Super Admin') || $user->can('announcements.publish');

            if (in_array($newStatus, ['published', 'rejected']) && !$canPublish) {
                $newStatus = $announcement->status;
            }
            $data['status'] = $newStatus;

            if ($newStatus === 'published' && $announcement->status !== 'published') {
                $data['published_at'] = now();
            }
        }

        $oldStatus = $announcement->status;
        $announcement->update($data);

        $action = 'update';
        $desc = 'Memperbarui pengumuman: ' . $announcement->title;
        if (isset($data['status'])) {
            $action = $data['status'] === 'published' ? 'publish' : ($data['status'] === 'rejected' ? 'reject' : ($data['status'] === 'review' ? 'submit_review' : 'update'));
            $desc .= ' (Status: ' . $data['status'] . ')';

            if ($action === 'submit_review' && $oldStatus !== 'review') {
                \App\Services\NotificationService::sendToOrganizationReviewers(
                    $announcement->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Pengumuman '{$announcement->title}' untuk direview.",
                    '/organization/announcements',
                    ['content_id' => $announcement->id, 'content_type' => 'announcement', 'author_id' => $user->id],
                    $user->id
                );

                \App\Services\NotificationService::send(
                    $user,
                    'content_submitted',
                    'Pengumuman Diajukan',
                    "Pengumuman '{$announcement->title}' berhasil dikirim untuk direview.",
                    '/organization/announcements',
                    ['content_id' => $announcement->id, 'content_type' => 'announcement']
                );
            } elseif ($action === 'publish' && $oldStatus !== 'published' && $announcement->user_id !== $user->id) {
                if ($announcement->user) {
                    \App\Services\NotificationService::send(
                        $announcement->user,
                        'content_published',
                        'Pengumuman Dipublikasikan',
                        "Pengumuman '{$announcement->title}' berhasil dipublikasikan.",
                        '/organization/announcements',
                        ['content_id' => $announcement->id, 'content_type' => 'announcement']
                    );
                }
            } elseif ($action === 'reject' && $oldStatus !== 'rejected' && $announcement->user_id !== $user->id) {
                if ($announcement->user) {
                    \App\Services\NotificationService::send(
                        $announcement->user,
                        'content_rejected',
                        'Pengumuman Perlu Revisi',
                        "Pengumuman '{$announcement->title}' ditolak / memerlukan perbaikan.",
                        '/organization/announcements',
                        ['content_id' => $announcement->id, 'content_type' => 'announcement']
                    );
                }
            }
        }
        ActivityLogService::log($action, 'announcements', $desc, $announcement);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman berhasil diperbarui!',
            'data'    => $announcement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('announcements.delete'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk menghapus pengumuman.');
        }

        $announcement = Announcement::withoutGlobalScopes()->findOrFail($id);

        // Strict Tenant Isolation
        if (!$user->hasRole('Super Admin') && $announcement->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat menghapus pengumuman organisasi lain.');
        }

        $announcement->delete();

        ActivityLogService::log('delete', 'announcements', 'Menghapus pengumuman: ' . $announcement->title, clone $announcement);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman berhasil dihapus!'
        ]);
    }
}
