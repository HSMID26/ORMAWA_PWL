<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan riwayat aktivitas berdasarkan filter & role
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Authorization: Requires activity_logs.view permission or Super Admin
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('activity_logs.view'))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = ActivityLog::with(['user:id,name', 'organization:id,nama']);

        // 2. Tenant Isolation
        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        } elseif ($request->has('organization_id') && $request->organization_id) {
            $query->where('organization_id', $request->organization_id);
        }

        // 3. Filter Query Params
        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 4. Pagination & Sorting
        $perPage = $request->input('per_page', 20);
        $logs = $query->latest('created_at')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $logs->items(),
            'meta'   => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ]
        ]);
    }
}
