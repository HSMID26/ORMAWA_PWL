<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivityApprovalWorkflowService
{
    public function create(array $data, User $user): Activity
    {
        return DB::transaction(function () use ($data, $user) {
            $status = $data['status'];
            $activity = Activity::create([
                'user_id' => $user->id,
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'],
                'tanggal_pelaksanaan' => $data['tanggal_pelaksanaan'],
                'status' => $status,
                'published_at' => $status === 'published' ? now() : null,
                'reviewed_by' => $status === 'published' ? $user->id : null,
                'reviewed_at' => $status === 'published' ? now() : null,
            ]);

            ActivityLogService::log(
                'create',
                'activities',
                'Membuat agenda baru: ' . $activity->judul . ' (Status: ' . $activity->status . ')',
                $activity,
                ['status' => $activity->status]
            );

            if ($status === 'review') {
                NotificationService::sendToOrganizationReviewers(
                    $activity->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Agenda '{$activity->judul}' untuk direview.",
                    '/organization/agenda',
                    ['content_id' => $activity->id, 'content_type' => 'activity', 'author_id' => $user->id],
                    $user->id
                );
                NotificationService::send(
                    $user,
                    'content_submitted',
                    'Agenda Diajukan',
                    "Agenda '{$activity->judul}' berhasil dikirim untuk direview.",
                    '/organization/agenda',
                    ['content_id' => $activity->id, 'content_type' => 'activity']
                );
            }

            return $activity;
        });
    }

    public function update(Activity $activity, array $data, User $user): Activity
    {
        return DB::transaction(function () use ($activity, $data, $user) {
            $oldStatus = $activity->status;
            $activity->update($data);
            $action = match ($activity->status) {
                'published' => 'publish',
                'rejected' => 'reject',
                'revision_requested' => 'request_revision',
                'review' => 'submit_review',
                default => 'update',
            };
            $description = 'Memperbarui agenda: ' . $activity->judul . ' (Status: ' . $activity->status . ')';

            if ($action === 'submit_review' && $oldStatus !== 'review') {
                NotificationService::sendToOrganizationReviewers(
                    $activity->organization_id,
                    'content_review',
                    'Konten Menunggu Review',
                    "{$user->name} mengirim Agenda '{$activity->judul}' untuk direview.",
                    '/organization/agenda',
                    ['content_id' => $activity->id, 'content_type' => 'activity', 'author_id' => $user->id],
                    $user->id
                );
                NotificationService::send(
                    $user,
                    'content_submitted',
                    'Agenda Diajukan',
                    "Agenda '{$activity->judul}' berhasil dikirim untuk direview.",
                    '/organization/agenda',
                    ['content_id' => $activity->id, 'content_type' => 'activity']
                );
            } elseif ($action === 'publish' && $oldStatus !== 'published' && $activity->user_id !== $user->id && $activity->user) {
                NotificationService::send(
                    $activity->user,
                    'content_published',
                    'Agenda Dipublikasikan',
                    "Agenda '{$activity->judul}' berhasil dipublikasikan.",
                    '/organization/agenda',
                    ['content_id' => $activity->id, 'content_type' => 'activity']
                );
            } elseif (in_array($action, ['reject', 'request_revision'], true) && $oldStatus !== $activity->status && $activity->user_id !== $user->id && $activity->user) {
                NotificationService::send(
                    $activity->user,
                    $action === 'reject' ? 'content_rejected' : 'content_revision_requested',
                    $action === 'reject' ? 'Agenda Ditolak' : 'Agenda Perlu Revisi',
                    "Agenda '{$activity->judul}' memerlukan perbaikan. " . ($activity->review_note ?? ''),
                    '/organization/agenda',
                    ['content_id' => $activity->id, 'content_type' => 'activity']
                );
            }

            ActivityLogService::log($action, 'activities', $description, $activity, [
                'status' => $activity->status,
                'review_note' => $activity->review_note,
            ]);

            return $activity;
        });
    }
}
