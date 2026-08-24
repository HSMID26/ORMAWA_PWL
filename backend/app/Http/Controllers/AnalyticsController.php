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
    public function index()
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $orgId = $user->organization_id ?? 1;

        $setting = Organization::find($orgId);

        // 1. Ringkasan Stat Cards
        $totalPosts       = Post::where('organization_id', $orgId)->count();
        $publishedPosts   = Post::where('organization_id', $orgId)->where('status', 'published')->count();
        $draftPosts       = Post::where('organization_id', $orgId)->where('status', 'draft')->count();
        $totalActivities  = ActivityLog::where('organization_id', $orgId)->count();
        $totalViews       = PageView::where('organization_id', $orgId)->count();
        $popularPosts     = Post::where('organization_id', $orgId)->latest()->take(5)->get();

        // 2. Data Grafik Pengunjung (Page Views) 7 Hari Terakhir
        $dates      = [];
        $viewsData  = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[]     = date('d M', strtotime($date));
            $viewsData[] = PageView::where('organization_id', $orgId)
                ->whereDate('created_at', $date)
                ->count();
        }

        return view('analytics.index', compact(
            'setting', 
            'totalPosts', 
            'publishedPosts', 
            'draftPosts', 
            'totalActivities',
            'totalViews', 
            'popularPosts',
            'dates',
            'viewsData'
        ));
    }
}