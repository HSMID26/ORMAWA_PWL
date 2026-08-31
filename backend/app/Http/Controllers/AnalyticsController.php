<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\PageView;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /**
     * Halaman Dashboard Statistik Pengunjung & Integrasi Google Analytics
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Multi-tenant scoping
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        
        if ($isSuperAdmin && $request->filled('organization_id')) {
            $orgId = $request->organization_id;
        } else {
            $orgId = $user->organization_id ?? Organization::first()?->id ?? 1;
        }

        $setting = Organization::find($orgId);
        $organizations = $isSuperAdmin ? Organization::where('status', 'active')->get() : collect();

        // 1. Ringkasan Statistik Konten & Interaksi
        $totalPosts       = Post::where('organization_id', $orgId)->count();
        $publishedPosts   = Post::where('organization_id', $orgId)->where('status', 'published')->count();
        $draftPosts       = Post::where('organization_id', $orgId)->where('status', 'draft')->count();
        $totalActivities  = ActivityLog::where('organization_id', $orgId)->count();

        // 2. Ringkasan Traffic Internal (Page Views & Unique Visitors)
        $totalViews       = PageView::where('organization_id', $orgId)->count();
        $uniqueVisitors   = PageView::where('organization_id', $orgId)->distinct('ip_address')->count('ip_address');
        $todayViews       = PageView::where('organization_id', $orgId)->whereDate('created_at', now()->toDateString())->count();

        // 3. Konten Terpopuler & Halaman yang Paling Banyak Diakses
        $popularPosts     = Post::where('organization_id', $orgId)->latest()->take(5)->get();
        $topPages         = PageView::where('organization_id', $orgId)
            ->selectRaw('url_path, count(*) as total_views')
            ->groupBy('url_path')
            ->orderByDesc('total_views')
            ->take(5)
            ->get();

        // 4. Data Grafik Tren 7 Hari Terakhir
        $dates          = [];
        $viewsData      = [];
        $uniqueData     = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[]      = date('d M', strtotime($date));
            
            $viewsData[]  = PageView::where('organization_id', $orgId)
                ->whereDate('created_at', $date)
                ->count();

            $uniqueData[] = PageView::where('organization_id', $orgId)
                ->whereDate('created_at', $date)
                ->distinct('ip_address')
                ->count('ip_address');
        }

        $responseData = [
            'total_posts'       => $totalPosts,
            'published_posts'   => $publishedPosts,
            'draft_posts'       => $draftPosts,
            'total_activities'  => $totalActivities,
            'total_views'       => $totalViews,
            'unique_visitors'   => $uniqueVisitors,
            'today_views'       => $todayViews,
            'popular_posts'     => $popularPosts,
            'top_pages'         => $topPages,
            'trend'             => [
                'dates'  => $dates,
                'views'  => $viewsData,
                'unique' => $uniqueData,
            ],
            'organization_id'   => $orgId,
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'data'   => $responseData,
            ]);
        }

        return view('analytics.index', compact(
            'setting', 
            'organizations',
            'isSuperAdmin',
            'totalPosts', 
            'publishedPosts', 
            'draftPosts', 
            'totalActivities',
            'totalViews', 
            'uniqueVisitors',
            'todayViews',
            'popularPosts',
            'topPages',
            'dates',
            'viewsData',
            'uniqueData'
        ));
    }
}