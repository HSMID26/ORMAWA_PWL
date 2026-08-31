<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\OrganizationRegistration;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    // 1. Tampilkan daftar pengajuan registrasi Ormawa yang masuk
    public function pendingRegistrations()
    {
        $requests = OrganizationRegistration::where('status', 'pending')->latest()->get();
        return view('superadmin.pending_registrations', compact('requests'));
    }

    // 2. Setujui Pengajuan (Approve)
    public function approve($id)
    {
        $pending = OrganizationRegistration::findOrFail($id);

        DB::transaction(function () use ($pending) {
            // Buat Ormawa Baru
            $org = Organization::create([
                'nama'        => $pending->organization_name,
                'jenis'       => $pending->organization_type,
                'subdomain'   => $pending->organization_subdomain,
                'status'      => 'active',
                'modul_aktif' => [
                    'posts'         => true,
                    'agenda'        => true,
                    'announcements' => true,
                    'galeri'        => true,
                    'documents'     => true,
                    'structure'     => true,
                ],
            ]);

            // Buat Akun Admin Ormawa
            $user = User::create([
                'name'            => trim($pending->admin_first_name . ' ' . $pending->admin_last_name),
                'email'           => $pending->admin_email,
                'password'        => $pending->admin_password,
                'organization_id' => $org->id,
                'status'          => 'active',
            ]);
            $user->assignRole('Admin Organisasi');

            // Buat Periode Default
            $currentYear = Carbon::now()->year;
            $periodName = "Periode {$currentYear}/" . ($currentYear + 1);
            OrganizationPeriod::create([
                'organization_id' => $org->id,
                'period_name'     => $periodName,
                'start_date'      => Carbon::now()->toDateString(),
                'end_date'        => Carbon::now()->addYear()->toDateString(),
                'status'          => 'active',
                'created_by'      => auth()->id(),
            ]);

            // Update status pengajuan
            $pending->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            ActivityLogService::log('approve', 'organization_registrations', "Menyetujui pendaftaran organisasi: {$org->nama}", $pending);
        });

        return back()->with('success', 'Pendaftaran Ormawa & Akun Admin berhasil disetujui!');
    }

    // 3. Tolak Pengajuan (Reject)
    public function reject(Request $request, $id)
    {
        $pending = OrganizationRegistration::findOrFail($id);
        
        $pending->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->alasan_penolakan ?? 'Pengajuan tidak memenuhi syarat.',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        ActivityLogService::log('reject', 'organization_registrations', "Menolak pendaftaran: {$pending->organization_name}", $pending);

        return back()->with('success', 'Pengajuan pendaftaran berhasil ditolak.');
    }
}