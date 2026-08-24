<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    // 1. Tampilkan daftar pengajuan registrasi Ormawa yang masuk
    public function pendingRegistrations()
    {
        $requests = PendingRegistration::where('status', 'pending')->latest()->get();
        return view('superadmin.pending_registrations', compact('requests'));
    }

    // 2. Setujui Pengajuan (Approve)
    public function approve($id)
    {
        $pending = PendingRegistration::findOrFail($id);

        DB::transaction(function () use ($pending) {
            // Buat Ormawa Baru
            $org = Organization::create([
                'nama'      => $pending->nama_ormawa,
                'jenis'     => $pending->jenis_ormawa,
                'subdomain' => $pending->subdomain,
            ]);

            // Buat Akun Admin Ormawa
            User::create([
                'name'            => $pending->admin_name,
                'email'           => $pending->admin_email,
                'password'        => $pending->admin_password,
                'organization_id' => $org->id,
                'status'          => 'active',
            ]);

            // Update status pengajuan
            $pending->update(['status' => 'approved']);
        });

        return back()->with('success', 'Pendaftaran Ormawa & Akun Admin berhasil disetujui!');
    }

    // 3. Tolak Pengajuan (Reject)
    public function reject(Request $request, $id)
    {
        $pending = PendingRegistration::findOrFail($id);
        
        $pending->update([
            'status'           => 'rejected',
            'alasan_penolakan' => $request->alasan_penolakan ?? 'Pengajuan tidak memenuhi syarat.',
        ]);

        return back()->with('success', 'Pengajuan pendaftaran berhasil ditolak.');
    }
}