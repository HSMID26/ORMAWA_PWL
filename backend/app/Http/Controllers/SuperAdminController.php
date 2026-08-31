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
    /**
     * Tampilkan daftar pengajuan registrasi Ormawa yang masuk (Pending & Riwayat)
     */
    public function pendingRegistrations()
    {
        $requests = OrganizationRegistration::where('status', 'pending')->latest()->get();
        $history  = OrganizationRegistration::whereIn('status', ['approved', 'rejected'])->latest()->take(20)->get();

        return view('superadmin.pending_registrations', compact('requests', 'history'));
    }

    /**
     * Setujui Pengajuan Pendaftaran Ormawa & Aktifkan Akun Admin
     */
    public function approve($id)
    {
        $pending = OrganizationRegistration::findOrFail($id);

        DB::transaction(function () use ($pending) {
            // 1. Buat Organisasi Baru
            $org = Organization::create([
                'nama'        => $pending->organization_name,
                'jenis'       => $pending->organization_type,
                'subdomain'   => $pending->organization_subdomain,
                'email'       => $pending->admin_email,
                'status'      => 'active',
                'modul_aktif' => [
                    'posts'         => true,
                    'agenda'        => true,
                    'announcements' => true,
                    'galeri'        => true,
                    'documents'     => true,
                    'structure'     => true,
                ],
                'warna_tema'  => '#1d4ed8',
            ]);

            // Provision default baseline categories
            \App\Http\Controllers\CategoryController::ensureDefaultCategories($org->id);

            // 2. Buat Akun Admin Organisasi
            $user = User::create([
                'name'            => trim($pending->admin_first_name . ' ' . $pending->admin_last_name),
                'email'           => $pending->admin_email,
                'password'        => $pending->admin_password,
                'organization_id' => $org->id,
                'status'          => 'active',
            ]);

            // 3. Assign Role Spatie
            $user->assignRole('Admin Organisasi');

            // 4. Buat Periode Default
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

            // 5. Update status pengajuan
            $pending->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // 6. Catat Log Aktivitas
            ActivityLogService::log('approve', 'organization_registrations', "Menyetujui pendaftaran organisasi: {$org->nama}", $pending);
        });

        return back()->with('success', 'Pendaftaran Ormawa & Akun Admin berhasil disetujui dan diaktifkan!');
    }

    /**
     * Tolak Pengajuan Pendaftaran Ormawa
     */
    public function reject(Request $request, $id)
    {
        $pending = OrganizationRegistration::findOrFail($id);
        
        $alasan = $request->input('alasan_penolakan', $request->input('rejection_reason', 'Pengajuan pendaftaran tidak memenuhi kriteria dan persyaratan.'));

        $pending->update([
            'status'           => 'rejected',
            'rejection_reason' => $alasan,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        ActivityLogService::log('reject', 'organization_registrations', "Menolak pendaftaran: {$pending->organization_name}. Alasan: {$alasan}", $pending);

        return back()->with('success', 'Pengajuan pendaftaran Ormawa berhasil ditolak.');
    }
}
