<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationRegistration;
use App\Models\Post;
use App\Models\Activity;
use App\Models\Announcement;

class DashboardController extends Controller
{
    /**
     * Get aggregate statistics for Super Admin dashboard
     */
    public function superAdminSummary(Request $request)
    {
        // Must be super admin
        if (!$request->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized. Super Admin only.');
        }

        // Organizations stats
        $totalOrganizations = Organization::count();

        // Periods stats
        $periodsStats = \App\Models\OrganizationPeriod::toBase()->selectRaw('
            sum(case when status = "active" then 1 else 0 end) as active,
            sum(case when status = "pending" then 1 else 0 end) as pending,
            sum(case when status = "expired" then 1 else 0 end) as expired,
            sum(case when status = "active" and end_date <= ? then 1 else 0 end) as expiring_soon
        ', [\Carbon\Carbon::today()->addDays(30)->toDateString()])->first();

        // Users stats
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();

        // Pending Registrations
        $pendingRegistrations = OrganizationRegistration::where('status', 'pending')->count();

        // Content Stats via Aggregation
        $postsStats = Post::toBase()->selectRaw('
            count(*) as total,
            sum(case when status = "published" then 1 else 0 end) as published,
            sum(case when status = "draft" then 1 else 0 end) as draft
        ')->first();

        $activitiesStats = Activity::toBase()->selectRaw('
            count(*) as total,
            sum(case when status = "published" then 1 else 0 end) as published,
            sum(case when status = "draft" then 1 else 0 end) as draft
        ')->first();

        $announcementsStats = Announcement::toBase()->selectRaw('
            count(*) as total,
            sum(case when status = "published" then 1 else 0 end) as published,
            sum(case when status = "draft" then 1 else 0 end) as draft
        ')->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'organizations' => [
                    'total' => $totalOrganizations,
                    'active' => Organization::where('status', 'active')->count()
                ],
                'periods' => [
                    'active' => (int) $periodsStats->active,
                    'pending' => (int) $periodsStats->pending,
                    'expired' => (int) $periodsStats->expired,
                    'expiring_soon' => (int) $periodsStats->expiring_soon
                ],
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers
                ],
                'registrations' => [
                    'pending' => $pendingRegistrations
                ],
                'content' => [
                    'posts' => (int) $postsStats->total,
                    'activities' => (int) $activitiesStats->total,
                    'announcements' => (int) $announcementsStats->total,
                    'published' => (int) $postsStats->published + (int) $activitiesStats->published + (int) $announcementsStats->published,
                    'draft' => (int) $postsStats->draft + (int) $activitiesStats->draft + (int) $announcementsStats->draft,
                ],
                'storage' => [
                    'available' => false,
                    'used' => null,
                    'capacity' => null,
                    'percentage' => null
                ]
            ]
        ]);
    }
}
