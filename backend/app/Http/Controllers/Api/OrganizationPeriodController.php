<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class OrganizationPeriodController extends Controller
{
    /**
     * Get periods of an organization
     */
    public function index(Request $request, $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);

        // Check Tenant isolation - Only Super Admin or Admin Organisasi of THIS organization can view
        if (!auth()->user()->hasRole('Super Admin') && auth()->user()->organization_id != $organization->id) {
            abort(403, 'Anda tidak memiliki akses ke periode organisasi ini.');
        }

        $periods = $organization->periods()->orderBy('end_date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $periods,
            'current_period' => $organization->currentPeriod()
        ]);
    }

    /**
     * Store (Submit Renewal or Initial Setup)
     */
    public function store(Request $request, $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);

        if (!auth()->user()->hasRole('Super Admin') && auth()->user()->organization_id != $organization->id) {
            abort(403, 'Anda tidak diizinkan membuat periode untuk organisasi ini.');
        }

        if (auth()->user()->hasRole('Kontributor') || auth()->user()->hasRole('Editor')) {
             abort(403, 'Editor atau Kontributor tidak dapat mengajukan perpanjangan periode.');
        }

        $validated = $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        // Validation against overlap if it's active immediately? 
        // Renewal is pending, but Super Admin might setup active directly if "Set Initial Period".
        $status = auth()->user()->hasRole('Super Admin') ? 'active' : 'pending';
        
        // Cek overlap jika akan langsung aktif
        if ($status === 'active') {
             $overlap = $organization->periods()->where('status', 'active')
                  ->where(function($q) use ($validated) {
                       $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                         ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                         ->orWhere(function($q2) use ($validated) {
                              $q2->where('start_date', '<=', $validated['start_date'])
                                 ->where('end_date', '>=', $validated['end_date']);
                         });
                  })->exists();
                  
             if ($overlap) {
                 return response()->json(['message' => 'Tanggal periode bertabrakan dengan periode aktif lainnya.'], 422);
             }
        }

        $period = $organization->periods()->create([
            'period_name' => $validated['period_name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
            'approved_by' => $status === 'active' ? auth()->id() : null,
            'approved_at' => $status === 'active' ? now() : null,
        ]);

        if ($status === 'pending') {
            ActivityLogService::log('create', 'organization_periods', 'Mengajukan perpanjangan periode: ' . $period->period_name, clone $period);
            
            // Notify Super Admin
            NotificationService::sendToSuperAdmins(
                'period_renewal_submitted',
                'Pengajuan Perpanjangan Periode',
                "{$organization->nama} mengajukan perpanjangan periode {$period->period_name}.",
                '/super-admin/organizations/periods',
                ['organization_id' => $organization->id, 'period_id' => $period->id]
            );
        } else {
            ActivityLogService::log('create', 'organization_periods', 'Menyiapkan periode awal: ' . $period->period_name, clone $period);
        }

        return response()->json([
            'status' => 'success',
            'message' => $status === 'active' ? 'Periode berhasil ditambahkan.' : 'Pengajuan periode berhasil dikirim.',
            'data' => $period
        ], 201);
    }

    /**
     * Show single period
     */
    public function show($id)
    {
        $period = OrganizationPeriod::with('organization')->findOrFail($id);

        if (!auth()->user()->hasRole('Super Admin') && auth()->user()->organization_id != $period->organization_id) {
            abort(403, 'Anda tidak memiliki akses ke periode ini.');
        }

        return response()->json([
            'status' => 'success',
            'data' => $period
        ]);
    }

    /**
     * Update period (allowed for Pending, or Super Admin)
     */
    public function update(Request $request, $id)
    {
        $period = OrganizationPeriod::findOrFail($id);

        if (!auth()->user()->hasRole('Super Admin')) {
             if (auth()->user()->organization_id != $period->organization_id) {
                 abort(403, 'Anda tidak memiliki akses.');
             }
             if ($period->status !== 'pending') {
                 abort(403, 'Hanya periode dengan status pending yang dapat diubah.');
             }
        }

        $validated = $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $period->update($validated);

        ActivityLogService::log('update', 'organization_periods', 'Memperbarui data periode: ' . $period->period_name, clone $period);

        return response()->json([
            'status' => 'success',
            'message' => 'Periode berhasil diperbarui.',
            'data' => $period
        ]);
    }

    /**
     * Super Admin Approve
     */
    public function approve($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat melakukan persetujuan.');
        }

        $period = OrganizationPeriod::with('organization.users')->findOrFail($id);

        if ($period->status === 'active') {
            return response()->json(['message' => 'Periode ini sudah aktif.'], 422);
        }

        // Cek overlap
        $overlap = $period->organization->periods()->where('status', 'active')
            ->where(function($q) use ($period) {
                $q->whereBetween('start_date', [$period->start_date, $period->end_date])
                  ->orWhereBetween('end_date', [$period->start_date, $period->end_date])
                  ->orWhere(function($q2) use ($period) {
                       $q2->where('start_date', '<=', $period->start_date)
                          ->where('end_date', '>=', $period->end_date);
                  });
            })->exists();

        if ($overlap) {
            return response()->json(['message' => 'Gagal approve: Tanggal periode ini tumpang tindih dengan periode aktif lain milik organisasi ini.'], 422);
        }

        $period->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ActivityLogService::log('approve', 'organization_periods', 'Persetujuan perpanjangan periode: ' . $period->period_name, clone $period);

        // Notify Admin Organisasi
        $adminUsers = $period->organization->users()->whereHas('roles', function($q) {
            $q->where('name', 'Admin Organisasi');
        })->get();

        foreach ($adminUsers as $admin) {
            NotificationService::send(
                $admin,
                'period_renewal_approved',
                'Periode Disetujui',
                "Periode {$period->period_name} organisasi {$period->organization->nama} telah disetujui.",
                '/organization/period'
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Periode berhasil disetujui.',
            'data' => $period
        ]);
    }

    /**
     * Super Admin Reject
     */
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat menolak pengajuan.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $period = OrganizationPeriod::with('organization.users')->findOrFail($id);

        if ($period->status !== 'pending') {
            return response()->json(['message' => 'Hanya periode berstatus pending yang dapat ditolak.'], 422);
        }

        $period->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ActivityLogService::log('reject', 'organization_periods', 'Menolak pengajuan periode: ' . $period->period_name, clone $period, ['reason' => $validated['reason']]);

        // Notify Admin Organisasi
        $adminUsers = $period->organization->users()->whereHas('roles', function($q) {
            $q->where('name', 'Admin Organisasi');
        })->get();

        foreach ($adminUsers as $admin) {
            NotificationService::send(
                $admin,
                'period_renewal_rejected',
                'Pengajuan Periode Ditolak',
                "Pengajuan periode {$period->period_name} ditolak. Alasan: {$validated['reason']}.",
                '/organization/period'
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Periode berhasil ditolak.',
            'data' => $period
        ]);
    }
}
