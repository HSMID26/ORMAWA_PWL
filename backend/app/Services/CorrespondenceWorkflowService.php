<?php

namespace App\Services;

use App\Models\Correspondence;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CorrespondenceWorkflowService
{
    public function create(array $data, User $user, ?UploadedFile $file): Correspondence
    {
        $path = $file?->store('correspondence', 'local');
        if ($file && !$path) {
            throw new RuntimeException('Lampiran surat gagal disimpan.');
        }
        if ($path) {
            $data += $this->attachmentData($file, $path);
        }
        $data['created_by'] = $user->id;
        $data['status'] = 'draft';

        try {
            return DB::transaction(function () use ($data) {
                $letter = Correspondence::create($data);
                ActivityLogService::log('create', 'correspondence', 'Membuat surat: ' . $letter->reference_number, $letter);
                return $letter->load(['creator:id,name', 'reviewer:id,name']);
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function update(Correspondence $letter, array $data, ?UploadedFile $file): Correspondence
    {
        $newPath = $file?->store('correspondence', 'local');
        if ($file && !$newPath) {
            throw new RuntimeException('Lampiran surat gagal disimpan.');
        }
        $oldPath = $letter->attachment_path;
        if ($newPath) {
            $data += $this->attachmentData($file, $newPath);
        }

        try {
            $letter = DB::transaction(function () use ($letter, $data) {
                $letter->update($data);
                ActivityLogService::log('update', 'correspondence', 'Memperbarui surat: ' . $letter->reference_number, $letter);
                return $letter->load(['creator:id,name', 'reviewer:id,name']);
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return $letter;
    }

    public function submit(Correspondence $letter): Correspondence
    {
        DB::transaction(function () use ($letter) {
            $letter->update(['status' => 'submitted', 'review_note' => null]);
            ActivityLogService::log(
                'submit',
                'correspondence',
                'Mengajukan surat: ' . $letter->reference_number,
                $letter,
                ['status' => 'submitted']
            );
        });

        return $letter->fresh();
    }

    public function review(Correspondence $letter, User $user, string $status, ?string $note): Correspondence
    {
        DB::transaction(function () use ($letter, $user, $status, $note) {
            $letter->update([
                'status' => $status,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);
            ActivityLogService::log(
                'review',
                'correspondence',
                'Meninjau surat ' . $letter->reference_number . ': ' . $status,
                $letter,
                ['status' => $status, 'review_note' => $note]
            );
        });

        return $letter->fresh(['creator:id,name', 'reviewer:id,name']);
    }

    public function delete(Correspondence $letter): void
    {
        DB::transaction(function () use ($letter) {
            ActivityLogService::log('delete', 'correspondence', 'Menghapus surat: ' . $letter->reference_number, $letter);
            $letter->delete();
        });

        if ($letter->attachment_path) {
            Storage::disk('local')->delete($letter->attachment_path);
        }
    }

    private function attachmentData(UploadedFile $file, string $path): array
    {
        return [
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_mime_type' => $file->getMimeType(),
            'attachment_size' => $file->getSize(),
        ];
    }
}
