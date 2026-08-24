<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Post;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    // 1. Ekspor Seluruh Data Konten ke File JSON
    public function export()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orgId = $user->organization_id ?? 1;

        $organization = Organization::find($orgId);

        $exportData = [
            'meta' => [
                'exported_at'      => now()->toDateTimeString(),
                'exported_by'      => $user->name,
                'organization_id'  => $orgId,
                'cms_version'      => '1.0.0',
            ],
            'posts'        => Post::where('organization_id', $orgId)->get(),
            'activities'   => Activity::where('organization_id', $orgId)->get(),
            'committees'   => Committee::where('organization_id', $orgId)->get(),
            'media'        => Media::where('organization_id', $orgId)->get(),
            'settings'     => $organization ? $organization->only([
                'nama',
                'jenis',
                'subdomain',
                'logo',
                'warna_tema',
                'modul_aktif',
                'label_menu',
                'status',
            ]) : null,
        ];

        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename    = 'backup-cms-ormawa-' . date('Y-m-d_H-i-s') . '.json';

        // Catat ke Audit Log
        ActivityLogService::log('EXPORT', 'Backup', "Mengekspor data arsip konten organisasi ke {$filename}");

        return response($jsonContent, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // 2. Impor Data Konten dari File JSON
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json,txt|max:10240', // Max 10MB
        ]);

        $file    = $request->file('backup_file');
        $content = json_decode(file_get_contents($file->getRealPath()), true);

        if (!$content || !isset($content['meta'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Format file backup JSON tidak valid!'
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $orgId = $user->organization_id ?? 1;

        if (!empty($content['settings']) && $organization = Organization::find($orgId)) {
            $organization->fill([
                'nama'        => $content['settings']['nama'] ?? $organization->nama,
                'jenis'       => $content['settings']['jenis'] ?? $organization->jenis,
                'subdomain'   => $content['settings']['subdomain'] ?? $organization->subdomain,
                'logo'        => $content['settings']['logo'] ?? $organization->logo,
                'warna_tema'  => $content['settings']['warna_tema'] ?? $organization->warna_tema,
                'modul_aktif' => $content['settings']['modul_aktif'] ?? $organization->modul_aktif,
                'label_menu'  => $content['settings']['label_menu'] ?? $organization->label_menu,
                'status'      => $content['settings']['status'] ?? $organization->status,
            ]);
            $organization->save();
        }

        // Restore Posts / Artikel
        if (!empty($content['posts'])) {
            foreach ($content['posts'] as $p) {
                Post::updateOrCreate(
                    ['slug' => $p['slug'] ?? Str::slug($p['judul'] ?? ($p['title'] ?? 'post'))],
                    [
                        'organization_id' => $orgId,
                        'user_id'         => $user->id,
                        'judul'           => $p['judul'] ?? $p['title'] ?? 'Judul belum diisi',
                        'slug'            => $p['slug'] ?? Str::slug($p['judul'] ?? ($p['title'] ?? 'post')),
                        'konten'          => $p['konten'] ?? $p['content'] ?? '',
                        'excerpt'         => $p['excerpt'] ?? null,
                        'cover_image'     => $p['cover_image'] ?? null,
                        'status'          => $p['status'] ?? 'draft',
                        'published_at'    => $p['published_at'] ?? null,
                    ]
                );
            }
        }

        // Restore Activities / Agenda
        if (!empty($content['activities'])) {
            foreach ($content['activities'] as $act) {
                Activity::updateOrCreate(
                    ['judul' => $act['judul'] ?? $act['title'] ?? 'Agenda'],
                    [
                        'organization_id'      => $orgId,
                        'user_id'              => $user->id,
                        'judul'                => $act['judul'] ?? $act['title'] ?? 'Agenda',
                        'deskripsi'            => $act['deskripsi'] ?? $act['description'] ?? null,
                        'description'          => $act['description'] ?? $act['deskripsi'] ?? null,
                        'location'             => $act['location'] ?? null,
                        'tanggal_pelaksanaan'  => $act['tanggal_pelaksanaan'] ?? $act['start_time'] ?? now()->toDateString(),
                        'start_time'           => $act['start_time'] ?? ($act['tanggal_pelaksanaan'] ?? now()),
                        'end_time'             => $act['end_time'] ?? ($act['tanggal_pelaksanaan'] ?? now()),
                        'status'               => $act['status'] ?? 'published',
                    ]
                );
            }
        }

        // Restore Committees / Pengurus
        if (!empty($content['committees'])) {
            foreach ($content['committees'] as $c) {
                Committee::updateOrCreate(
                    [
                        'organization_id' => $orgId,
                        'name'            => $c['name'] ?? 'Nama belum diisi',
                        'position'        => $c['position'] ?? 'Anggota',
                    ],
                    [
                        'organization_id' => $orgId,
                        'user_id'         => $user->id,
                        'name'            => $c['name'] ?? 'Nama belum diisi',
                        'position'        => $c['position'] ?? 'Anggota',
                        'department'      => $c['department'] ?? null,
                        'period'          => $c['period'] ?? date('Y'),
                        'photo'           => $c['photo'] ?? null,
                        'status'          => $c['status'] ?? 'active',
                    ]
                );
            }
        }

        // Catat ke Audit Log
        ActivityLogService::log('IMPORT', 'Backup', 'Mengimpor dan memulihkan data konten arsip dari file JSON');

        return response()->json([
            'status'  => 'success',
            'message' => 'Data arsip konten berhasil diimpor dan dipulihkan!'
        ]);
    }
}