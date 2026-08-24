<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationRegistration;
use App\Models\OrganizationPeriod;
use App\Models\Post;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Committee;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get aggregate statistics for Super Admin dashboard
     */
    public function superAdminSummary(Request $request)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized. Super Admin only.');
        }

        $today = Carbon::today()->toDateString();
        $thirtyDaysFromNow = Carbon::today()->addDays(30)->toDateString();

        // 1. Organization Overview
        $totalOrganizations = Organization::count();
        $activeOrganizations = Organization::where('status', 'active')->count();
        $inactiveOrganizations = Organization::where('status', 'inactive')->count();
        $pendingRegistrations = OrganizationRegistration::where('status', 'pending')->count();

        // Periods breakdown
        $activePeriods = OrganizationPeriod::where('status', 'active')->count();
        $pendingPeriods = OrganizationPeriod::where('status', 'pending')->count();
        $expiredPeriods = OrganizationPeriod::where('status', 'expired')
            ->orWhere(function ($q) use ($today) {
                $q->where('status', 'active')->where('end_date', '<', $today);
            })->count();
        $expiringSoonPeriods = OrganizationPeriod::where('status', 'active')
            ->where('end_date', '>=', $today)
            ->where('end_date', '<=', $thirtyDaysFromNow)
            ->count();

        // 2. User Overview (Excluding Super Admin from operational count)
        $operationalUsersQuery = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'Super Admin');
        });

        $totalUsers = (clone $operationalUsersQuery)->count();
        $activeUsers = (clone $operationalUsersQuery)->where('status', 'active')->count();
        $inactiveUsers = (clone $operationalUsersQuery)->where('status', 'inactive')->count();

        $orgAdmins = User::role('Admin Organisasi')->count();
        $editors = User::role('Editor')->count();
        $contributors = User::role('Kontributor')->count();

        // 3. Content Overview (Articles / Posts)
        $postsStats = Post::toBase()->selectRaw('
            count(*) as total,
            sum(case when status = "published" then 1 else 0 end) as published,
            sum(case when status = "review" then 1 else 0 end) as review,
            sum(case when status = "draft" then 1 else 0 end) as draft,
            sum(case when status = "rejected" then 1 else 0 end) as rejected
        ')->first();

        // Agendas (Activities)
        $activitiesStats = Activity::toBase()->selectRaw('
            count(*) as total,
            sum(case when tanggal_pelaksanaan > ? then 1 else 0 end) as upcoming,
            sum(case when tanggal_pelaksanaan = ? then 1 else 0 end) as today,
            sum(case when tanggal_pelaksanaan < ? then 1 else 0 end) as past
        ', [$today, $today, $today])->first();

        // Announcements
        $announcementsStats = Announcement::toBase()->selectRaw('
            count(*) as total,
            sum(case when status = "published" then 1 else 0 end) as published,
            sum(case when status = "draft" then 1 else 0 end) as draft,
            sum(case when status = "archived" then 1 else 0 end) as archived
        ')->first();

        // Media & Documents
        $mediaTotal = \App\Models\Media::count();
        $imagesTotal = \App\Models\Media::where('mime_type', 'like', 'image/%')->count();
        $documentsTotal = \App\Models\Media::where('mime_type', 'not like', 'image/%')->count();

        // 4. Governance Alerts
        $governanceAlerts = [
            'pending_registrations' => $pendingRegistrations,
            'pending_renewals' => $pendingPeriods,
            'expiring_periods' => $expiringSoonPeriods,
            'expired_periods' => $expiredPeriods,
            'inactive_organizations' => $inactiveOrganizations,
        ];

        // 5. Recent Global Activities
        $recentActivities = ActivityLog::with(['user:id,name', 'organization:id,nama'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'actor' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'module' => $log->module,
                    'description' => $log->description,
                    'organization' => $log->organization?->nama ?? 'Platform',
                    'organization_id' => $log->organization_id,
                    'created_at' => $log->created_at?->toIso8601String(),
                    'metadata' => $log->metadata,
                    'ip_address' => $log->ip_address,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'organizations' => [
                    'total' => $totalOrganizations,
                    'active' => $activeOrganizations,
                    'inactive' => $inactiveOrganizations,
                    'pending_registrations' => $pendingRegistrations,
                ],
                'periods' => [
                    'active' => $activePeriods,
                    'pending' => $pendingPeriods,
                    'expired' => $expiredPeriods,
                    'expiring_soon' => $expiringSoonPeriods,
                ],
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'inactive' => $inactiveUsers,
                    'org_admins' => $orgAdmins,
                    'editors' => $editors,
                    'contributors' => $contributors,
                ],
                'content' => [
                    'posts' => [
                        'total' => (int) ($postsStats->total ?? 0),
                        'published' => (int) ($postsStats->published ?? 0),
                        'review' => (int) ($postsStats->review ?? 0),
                        'draft' => (int) ($postsStats->draft ?? 0),
                        'rejected' => (int) ($postsStats->rejected ?? 0),
                    ],
                    'agendas' => [
                        'total' => (int) ($activitiesStats->total ?? 0),
                        'upcoming' => (int) ($activitiesStats->upcoming ?? 0),
                        'today' => (int) ($activitiesStats->today ?? 0),
                        'past' => (int) ($activitiesStats->past ?? 0),
                    ],
                    'announcements' => [
                        'total' => (int) ($announcementsStats->total ?? 0),
                        'published' => (int) ($announcementsStats->published ?? 0),
                        'draft' => (int) ($announcementsStats->draft ?? 0),
                        'archived' => (int) ($announcementsStats->archived ?? 0),
                    ],
                    'media' => [
                        'total' => $mediaTotal,
                        'images' => $imagesTotal,
                        'documents' => $documentsTotal,
                    ],
                ],
                'governance_alerts' => $governanceAlerts,
                'recent_activities' => $recentActivities,
            ]
        ]);
    }

    /**
     * Get aggregate statistics for Admin Organisasi dashboard (Strictly Tenant Isolated)
     */
    public function organizationSummary(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $organizationId = $user->organization_id;
        if (!$organizationId && $user->hasRole('Super Admin') && $request->has('organization_id')) {
            $organizationId = (int) $request->organization_id;
        }

        if (!$organizationId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengguna tidak terhubung dengan organisasi manapun.'
            ], 400);
        }

        $organization = Organization::find($organizationId);
        if (!$organization) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organisasi tidak ditemukan.'
            ], 404);
        }

        // Active Period info
        $activePeriod = $organization->currentPeriod();
        $remainingDays = null;
        if ($activePeriod && $activePeriod->end_date) {
            $endDate = Carbon::parse($activePeriod->end_date)->endOfDay();
            $now = Carbon::now();
            $remainingDays = $now->diffInDays($endDate, false);
            if ($remainingDays < 0) {
                $remainingDays = 0;
            }
        }

        // Content statistics
        $postsStats = Post::where('organization_id', $organizationId)->selectRaw('
            count(*) as total,
            sum(case when status = "draft" then 1 else 0 end) as draft,
            sum(case when status = "review" then 1 else 0 end) as review,
            sum(case when status = "published" then 1 else 0 end) as published,
            sum(case when status = "rejected" then 1 else 0 end) as rejected
        ')->first();

        // Agenda statistics
        $today = Carbon::today()->toDateString();
        $agendaStats = Activity::where('organization_id', $organizationId)->selectRaw('
            count(*) as total,
            sum(case when tanggal_pelaksanaan > ? then 1 else 0 end) as upcoming,
            sum(case when tanggal_pelaksanaan = ? then 1 else 0 end) as today,
            sum(case when tanggal_pelaksanaan < ? then 1 else 0 end) as past
        ', [$today, $today, $today])->first();

        // Upcoming agendas (next 3)
        $upcomingAgendas = Activity::where('organization_id', $organizationId)
            ->whereDate('tanggal_pelaksanaan', '>=', $today)
            ->orderBy('tanggal_pelaksanaan', 'asc')
            ->limit(3)
            ->get(['id', 'judul', 'tanggal_pelaksanaan', 'deskripsi', 'status']);

        // Announcement statistics
        $announcementStats = Announcement::where('organization_id', $organizationId)->selectRaw('
            count(*) as total,
            sum(case when status = "published" then 1 else 0 end) as active,
            sum(case when priority = "urgent" then 1 else 0 end) as urgent
        ')->first();

        // Members stats
        $totalUsers = User::where('organization_id', $organizationId)->count();
        $totalEditors = User::where('organization_id', $organizationId)->whereHas('roles', fn($q) => $q->where('name', 'Editor'))->count();
        $totalContributors = User::where('organization_id', $organizationId)->whereHas('roles', fn($q) => $q->where('name', 'Kontributor'))->count();
        $totalPengurus = Committee::where('organization_id', $organizationId)->count();

        // Contributor specific stats
        $myPostsStats = null;
        $myRecentPosts = null;
        if ($user->hasRole('Kontributor')) {
            $myStats = Post::where('organization_id', $organizationId)->where('user_id', $user->id)->selectRaw('
                count(*) as total,
                sum(case when status = "draft" then 1 else 0 end) as draft,
                sum(case when status = "review" then 1 else 0 end) as review,
                sum(case when status = "published" then 1 else 0 end) as published,
                sum(case when status = "rejected" then 1 else 0 end) as rejected
            ')->first();

            $myPostsStats = [
                'total' => (int) ($myStats->total ?? 0),
                'draft' => (int) ($myStats->draft ?? 0),
                'review' => (int) ($myStats->review ?? 0),
                'published' => (int) ($myStats->published ?? 0),
                'rejected' => (int) ($myStats->rejected ?? 0),
            ];

            $myRecentPosts = Post::where('organization_id', $organizationId)
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'judul', 'status', 'created_at', 'updated_at']);
        }

        // Recent Activities (last 6 logs)
        $recentActivities = ActivityLog::where('organization_id', $organizationId)
            ->with('user:id,name')
            ->latest()
            ->limit(6)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_role' => $user->roles->first()?->name ?? 'User',
                'organization' => [
                    'id' => $organization->id,
                    'nama' => $organization->nama,
                    'jenis' => $organization->jenis,
                    'subdomain' => $organization->subdomain,
                    'status' => $organization->status,
                    'logo' => $organization->logo,
                    'warna_tema' => $organization->warna_tema ?? '#4f46e5',
                ],
                'period' => $activePeriod ? [
                    'id' => $activePeriod->id,
                    'period_name' => $activePeriod->period_name,
                    'start_date' => $activePeriod->start_date,
                    'end_date' => $activePeriod->end_date,
                    'status' => $activePeriod->status,
                    'remaining_days' => (int) $remainingDays,
                ] : null,
                'posts' => [
                    'total' => (int) ($postsStats->total ?? 0),
                    'draft' => (int) ($postsStats->draft ?? 0),
                    'review' => (int) ($postsStats->review ?? 0),
                    'published' => (int) ($postsStats->published ?? 0),
                    'rejected' => (int) ($postsStats->rejected ?? 0),
                ],
                'my_posts' => $myPostsStats,
                'my_recent_posts' => $myRecentPosts,
                'agenda' => [
                    'total' => (int) ($agendaStats->total ?? 0),
                    'upcoming' => (int) ($agendaStats->upcoming ?? 0),
                    'today' => (int) ($agendaStats->today ?? 0),
                    'past' => (int) ($agendaStats->past ?? 0),
                    'upcoming_list' => $upcomingAgendas,
                ],
                'announcements' => [
                    'total' => (int) ($announcementStats->total ?? 0),
                    'active' => (int) ($announcementStats->active ?? 0),
                    'urgent' => (int) ($announcementStats->urgent ?? 0),
                ],
                'members' => [
                    'total_users' => $totalUsers,
                    'total_editors' => $totalEditors,
                    'total_contributors' => $totalContributors,
                    'total_pengurus' => $totalPengurus,
                ],
                'recent_activities' => $recentActivities,
            ]
        ]);
    }
}
