<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    /**
     * Tampilkan daftar pengajuan registrasi Ormawa yang masuk (Pending)
     */
    public function pendingRegistrations()
    {
        $requests = PendingRegistration::where('status', 'pending')->latest()->get();
        $history  = PendingRegistration::whereIn('status', ['approved', 'rejected'])->latest()->take(20)->get();

        return view('superadmin.pending_registrations', compact('requests', 'history'));
    }

    /**
     * Setujui Pengajuan Pendaftaran Ormawa & Aktifkan Akun Admin
     */
    public function approve($id)
    {
        $pending = PendingRegistration::findOrFail($id);

        DB::transaction(function () use ($pending) {
            // 1. Buat Organisasi Baru
            $org = Organization::create([
                'nama'        => $pending->nama_ormawa,
                'jenis'       => $pending->jenis_ormawa,
                'subdomain'   => $pending->subdomain,
                'email'       => $pending->admin_email,
                'status'      => 'active',
                'modul_aktif' => [
                    'posts'         => true,
                    'activities'    => true,
                    'committees'    => true,
                    'media'         => true,
                    'announcements' => true,
                ],
                'warna_tema'  => '#1d4ed8',
            ]);

            // 2. Buat Akun Admin Organisasi
            $user = User::create([
                'name'            => $pending->admin_name,
                'email'           => $pending->admin_email,
                'password'        => $pending->admin_password,
                'organization_id' => $org->id,
                'status'          => 'active',
            ]);

            // 3. Assign Role Spatie
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('Admin Organisasi');
            }

            // 4. Update status pengajuan
            $pending->update(['status' => 'approved']);

            // 5. Catat Log Aktivitas
            ActivityLogService::log('create', 'organizations', "Menyetujui pendaftaran Ormawa baru: {$org->nama} dan membuat akun admin {$user->email}", $org);
        });

        return back()->with('success', 'Pendaftaran Ormawa & Akun Admin berhasil disetujui dan diaktifkan!');
    }

    /**
     * Tolak Pengajuan Pendaftaran Ormawa
     */
    public function reject(Request $request, $id)
    {
        $pending = PendingRegistration::findOrFail($id);
        
        $alasan = $request->input('alasan_penolakan', 'Pengajuan pendaftaran tidak memenuhi kriteria dan persyaratan.');

        $pending->update([
            'status'           => 'rejected',
            'alasan_penolakan' => $alasan,
        ]);

        ActivityLogService::log('update', 'organizations', "Menolak pendaftaran Ormawa: {$pending->nama_ormawa}. Alasan: {$alasan}");

        return back()->with('success', 'Pengajuan pendaftaran Ormawa berhasil ditolak.');
    }
}