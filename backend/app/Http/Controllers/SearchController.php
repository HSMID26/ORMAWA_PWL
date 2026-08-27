<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Halaman Hasil Pencarian Penuh
     */
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));
        $tab   = $request->input('tab', 'all');

        /** @var User $user */
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $orgId = $user->organization_id ?? Organization::first()?->id ?? 1;

        $posts       = collect();
        $activities  = collect();
        $committees  = collect();
        $users       = collect();
        $media       = collect();

        if (strlen($query) >= 2) {
            // 1. Cari Artikel
            $postsQuery = Post::query();
            if (!$isSuperAdmin) {
                $postsQuery->where('organization_id', $orgId);
            }
            $posts = $postsQuery->where(function ($q) use ($query) {
                $q->where('judul', 'like', "%{$query}%")
                  ->orWhere('konten', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%");
            })->latest()->take(20)->get();

            // 2. Cari Agenda Kegiatan
            $activitiesQuery = Activity::query();
            if (!$isSuperAdmin) {
                $activitiesQuery->where('organization_id', $orgId);
            }
            $activities = $activitiesQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('judul', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })->latest()->take(20)->get();

            // 3. Cari Struktur Pengurus
            $committeesQuery = Committee::query();
            if (!$isSuperAdmin) {
                $committeesQuery->where('organization_id', $orgId);
            }
            $committees = $committeesQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('position', 'like', "%{$query}%")
                  ->orWhere('department', 'like', "%{$query}%")
                  ->orWhere('period', 'like', "%{$query}%");
            })->latest()->take(20)->get();

            // 4. Cari Pengguna / Akun (Hanya Admin)
            if ($user && $user->hasRole(['Super Admin', 'Admin Organisasi'])) {
                $usersQuery = User::query();
                if (!$isSuperAdmin) {
                    $usersQuery->where('organization_id', $orgId);
                }
                $users = $usersQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })->latest()->take(20)->get();
            }

            // 5. Cari Galeri Media
            $mediaQuery = Media::query();
            if (!$isSuperAdmin) {
                $mediaQuery->where('organization_id', $orgId);
            }
            $media = $mediaQuery->where('filename', 'like', "%{$query}%")
                ->latest()
                ->take(20)
                ->get();
        }

        $totalResults = $posts->count() + $activities->count() + $committees->count() + $users->count() + $media->count();

        return view('search.index', compact(
            'query',
            'tab',
            'posts',
            'activities',
            'committees',
            'users',
            'media',
            'totalResults'
        ));
    }

    /**
     * Live Instant Search Endpoint (JSON) untuk Modal Ctrl+K
     */
    public function liveSearch(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'status'  => 'success',
                'results' => [],
                'total'   => 0
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $orgId = $user->organization_id ?? Organization::first()?->id ?? 1;

        $results = [];

        // 1. Menu Navigasi & Pintasan Cepat (Quick Actions)
        $quickActions = [
            [
                'title'       => 'Buat Artikel Baru',
                'description' => 'Buka TipTap Editor untuk menulis artikel baru',
                'url'         => route('posts.create'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '✍️'
            ],
            [
                'title'       => 'Kelola Artikel / Konten',
                'description' => 'Daftar semua artikel dan berita Ormawa',
                'url'         => route('posts.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '📰'
            ],
            [
                'title'       => 'Agenda Kegiatan & Proker',
                'description' => 'Kalender kegiatan dan sinkronisasi iCal',
                'url'         => route('activities.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '📅'
            ],
            [
                'title'       => 'Galeri Media & Dokumentasi',
                'description' => 'Perpustakaan gambar terkompresi otomatis',
                'url'         => route('media.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '🖼️'
            ],
            [
                'title'       => 'Struktur Pengurus Organisasi',
                'description' => 'Daftar BPH dan divisi kepengurusan',
                'url'         => route('committees.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '👥'
            ],
            [
                'title'       => 'Kelola Akun Pengurus (User Management)',
                'description' => 'Tambah akun pengurus, kelola peran, aktifkan/nonaktifkan',
                'url'         => route('users.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '👤'
            ],
            [
                'title'       => 'Pengaturan Organisasi & Modul',
                'description' => 'Logo, warna tema, sakelar modul, label menu, dan ID GA4',
                'url'         => route('organization.settings'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '⚙️'
            ],
            [
                'title'       => 'Statistik Pengunjung & Traffic Analytics',
                'description' => 'Grafik tren kunjungan 7 hari dan Google Analytics 4',
                'url'         => route('analytics.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '📊'
            ],
            [
                'title'       => 'Riwayat Aktivitas & Audit Trail',
                'description' => 'Log aktivitas perubahan data, aktor, dan IP',
                'url'         => route('activity-logs.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '📜'
            ],
            [
                'title'       => 'Backup & Restore Konten',
                'description' => 'Ekspor dan impor data arsip JSON organisasi',
                'url'         => route('backup.index'),
                'category'    => 'Pintasan Menu',
                'badge'       => 'Menu',
                'icon'        => '📦'
            ],
        ];

        foreach ($quickActions as $action) {
            if (stripos($action['title'], $query) !== false || stripos($action['description'], $query) !== false) {
                $results[] = $action;
            }
        }

        // 2. Artikel / Posts
        $postsQuery = Post::query();
        if (!$isSuperAdmin) {
            $postsQuery->where('organization_id', $orgId);
        }
        $posts = $postsQuery->where(function ($q) use ($query) {
            $q->where('judul', 'like', "%{$query}%")
              ->orWhere('konten', 'like', "%{$query}%");
        })->latest()->take(5)->get();

        foreach ($posts as $post) {
            $results[] = [
                'title'       => $post->judul ?? $post->title,
                'description' => 'Status: ' . ucfirst($post->status) . ' • ' . ($post->created_at ? $post->created_at->format('d M Y') : ''),
                'url'         => route('posts.show', $post),
                'category'    => 'Artikel / Konten',
                'badge'       => 'Artikel',
                'icon'        => '📝'
            ];
        }

        // 3. Agenda Kegiatan
        $activitiesQuery = Activity::query();
        if (!$isSuperAdmin) {
            $activitiesQuery->where('organization_id', $orgId);
        }
        $activities = $activitiesQuery->where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('judul', 'like', "%{$query}%")
              ->orWhere('location', 'like', "%{$query}%");
        })->latest()->take(5)->get();

        foreach ($activities as $act) {
            $results[] = [
                'title'       => $act->title ?? $act->judul,
                'description' => 'Lokasi: ' . ($act->location ?: 'Online/Kampus') . ' • Status: ' . ucfirst($act->status),
                'url'         => route('activities.index'),
                'category'    => 'Agenda Kegiatan',
                'badge'       => 'Agenda',
                'icon'        => '📅'
            ];
        }

        // 4. Pengurus Organisasi
        $committeesQuery = Committee::query();
        if (!$isSuperAdmin) {
            $committeesQuery->where('organization_id', $orgId);
        }
        $committees = $committeesQuery->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('position', 'like', "%{$query}%")
              ->orWhere('department', 'like', "%{$query}%");
        })->latest()->take(5)->get();

        foreach ($committees as $com) {
            $results[] = [
                'title'       => $com->name,
                'description' => $com->position . ($com->department ? ' (' . $com->department . ')' : '') . ' • Periode ' . $com->period,
                'url'         => route('committees.index'),
                'category'    => 'Pengurus Ormawa',
                'badge'       => 'Pengurus',
                'icon'        => '👥'
            ];
        }

        // 5. Akun Pengguna (Hanya jika role Admin)
        if ($user && $user->hasRole(['Super Admin', 'Admin Organisasi'])) {
            $usersQuery = User::query();
            if (!$isSuperAdmin) {
                $usersQuery->where('organization_id', $orgId);
            }
            $users = $usersQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })->latest()->take(5)->get();

            foreach ($users as $u) {
                $results[] = [
                    'title'       => $u->name,
                    'description' => $u->email . ' • Status: ' . ucfirst($u->status),
                    'url'         => route('users.index'),
                    'category'    => 'Akun Pengguna',
                    'badge'       => 'User',
                    'icon'        => '👤'
                ];
            }
        }

        return response()->json([
            'status'  => 'success',
            'query'   => $query,
            'total'   => count($results),
            'results' => $results,
            'see_all_url' => route('search.index', ['q' => $query]),
        ]);
    }
}
