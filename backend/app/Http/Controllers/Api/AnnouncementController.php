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
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses ke modul pengumuman.');
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
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses untuk membuat pengumuman.');
        }

        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'effective_date'   => 'nullable|date',
            'priority'         => 'nullable|in:low,normal,high,urgent',
            'status'           => 'nullable|in:draft,review,published,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $status = $request->status ?? 'draft';

        if (in_array($status, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
            $status = 'draft';
        }

        $announcement = Announcement::create([
            'user_id'          => $user->id,
            'title'            => $request->title,
            'slug'             => Str::slug($request->title) . '-' . Str::random(5),
            'content'          => $request->content,
            'effective_date'   => $request->effective_date,
            'priority'         => $request->priority ?? 'normal',
            'status'           => $status,
            'published_at'     => $status === 'published' ? now() : null,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
        ]);

        ActivityLogService::log('create', 'announcements', 'Membuat pengumuman baru: ' . $announcement->title . ' (Status: ' . $announcement->status . ')', $announcement);

        if ($status === 'review') {
            \App\Services\NotificationService::sendToOrganizationAdmins(
                $announcement->organization_id,
                'content_review',
                'Konten Menunggu Review',
                "{$user->name} mengirim Pengumuman untuk direview.",
                '/organization/announcements',
                ['content_id' => $announcement->id, 'content_type' => 'announcement', 'author_id' => $user->id]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman berhasil ditambahkan!',
            'data'    => $announcement
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses ke modul pengumuman.');
        }

        $announcement = Announcement::with('user:id,name')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $announcement
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses untuk mengedit pengumuman.');
        }

        $announcement = Announcement::findOrFail($id);

        $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'content'          => 'sometimes|required|string',
            'effective_date'   => 'nullable|date',
            'priority'         => 'nullable|in:low,normal,high,urgent',
            'status'           => 'sometimes|required|in:draft,review,published,rejected',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $data = $request->only([
            'title', 'content', 'effective_date', 'priority', 'status', 'meta_title', 'meta_description'
        ]);

        if ($request->has('title') && $request->title !== $announcement->title) {
            $data['slug'] = Str::slug($request->title) . '-' . Str::random(5);
        }

        if ($request->has('status')) {
            $newStatus = $request->status;

            if (in_array($newStatus, ['published', 'rejected']) && !$user->hasRole(['Super Admin', 'Admin Organisasi'])) {
                $newStatus = $announcement->status;
            }
            $data['status'] = $newStatus;

            if ($newStatus === 'published' && $announcement->status !== 'published') {
                $data['published_at'] = now();
            }
        }

        $announcement->update($data);

        $action = 'update';
        $desc = 'Memperbarui pengumuman: ' . $announcement->title;
        if (isset($data['status'])) {
            $action = $data['status'] === 'published' ? 'publish' : ($data['status'] === 'rejected' ? 'reject' : ($data['status'] === 'review' ? 'submit_review' : 'update'));
            $desc .= ' (Status: ' . $data['status'] . ')';

            if ($action === 'submit_review') {
                \App\Services\NotificationService::sendToOrganizationAdmins(
                    $announcement->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Pengumuman untuk direview.",
                    '/organization/announcements',
                    ['content_id' => $announcement->id, 'content_type' => 'announcement', 'author_id' => $user->id]
                );
            } elseif ($action === 'publish' && $announcement->user_id !== $user->id) {
                if ($announcement->user) {
                    \App\Services\NotificationService::send(
                        $announcement->user,
                        'content_published',
                        'Konten Dipublikasikan',
                        "Konten '{$announcement->title}' berhasil dipublikasikan.",
                        '/organization/announcements',
                        ['content_id' => $announcement->id, 'content_type' => 'announcement']
                    );
                }
            } elseif ($action === 'reject' && $announcement->user_id !== $user->id) {
                if ($announcement->user) {
                    \App\Services\NotificationService::send(
                        $announcement->user,
                        'content_rejected',
                        'Konten Ditolak',
                        "Konten '{$announcement->title}' ditolak.",
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
        if (!$user || !$user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor'])) {
            abort(403, 'Unauthorized. Kontributor tidak memiliki akses untuk menghapus pengumuman.');
        }

        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        ActivityLogService::log('delete', 'announcements', 'Menghapus pengumuman: ' . $announcement->title, clone $announcement);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman berhasil dihapus!'
        ]);
    }
}
