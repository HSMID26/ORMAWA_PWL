<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogWebController extends Controller
{
    /**
     * Tampilkan Halaman Audit Trail / Riwayat Aktivitas
     */
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Security check: Only Super Admin and Admin Organisasi can access logs
        if (!$currentUser->hasRole(['Super Admin', 'Admin Organisasi'])) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengakses halaman Riwayat Aktivitas.');
        }

        $isSuperAdmin = $currentUser->hasRole('Super Admin');
        $query = ActivityLog::with(['user', 'organization']);

        // Tenant Isolation: Admin Organisasi only sees their own org's logs
        if (!$isSuperAdmin) {
            $query->where('organization_id', $currentUser->organization_id);
        } elseif ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter Search Keyword (description, user name, email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Modul
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter Action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        // Master lists for filter dropdowns
        $modules = ActivityLog::distinct()->pluck('module')->filter()->values();
        $actions = ActivityLog::distinct()->pluck('action')->filter()->values();
        $organizations = $isSuperAdmin ? Organization::where('status', 'active')->get() : collect();

        // Statistics
        $totalLogsCount = ActivityLog::when(!$isSuperAdmin, fn($q) => $q->where('organization_id', $currentUser->organization_id))->count();
        $todayLogsCount = ActivityLog::when(!$isSuperAdmin, fn($q) => $q->where('organization_id', $currentUser->organization_id))->whereDate('created_at', now()->toDateString())->count();
        $sensitiveLogsCount = ActivityLog::when(!$isSuperAdmin, fn($q) => $q->where('organization_id', $currentUser->organization_id))->whereIn('action', ['delete', 'destroy', 'deactivate', 'reject'])->count();

        return view('activity-logs.index', compact(
            'logs', 
            'modules', 
            'actions', 
            'organizations', 
            'isSuperAdmin',
            'totalLogsCount',
            'todayLogsCount',
            'sensitiveLogsCount'
        ));
    }
}
