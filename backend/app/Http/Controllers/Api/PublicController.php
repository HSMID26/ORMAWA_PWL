<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Helper: Resolve active organization by subdomain slug
     */
    private function resolveOrganization(string $slug): Organization
    {
        $org = Organization::where('subdomain', strtolower(trim($slug)))
            ->where('status', 'active')
            ->first();

        if (!$org) {
            abort(404, 'Organisasi tidak ditemukan atau belum aktif.');
        }

        return $org;
    }

    /**
     * Helper: Check if specific public module is enabled
     */
    private function ensureModuleEnabled(Organization $org, string $moduleKey): void
    {
        $modules = $org->modul_aktif ?? [];
        
        $aliasMap = [
            'galeri' => ['galeri', 'gallery'],
            'gallery' => ['galeri', 'gallery'],
            'announcements' => ['announcements', 'pengumuman'],
            'pengumuman' => ['announcements', 'pengumuman'],
            'documents' => ['documents', 'dokumen'],
            'dokumen' => ['documents', 'dokumen'],
            'posts' => ['posts', 'berita', 'articles'],
            'agenda' => ['agenda', 'kegiatan', 'activities'],
            'structure' => ['structure', 'struktur'],
        ];

        $keysToCheck = $aliasMap[$moduleKey] ?? [$moduleKey];
        foreach ($keysToCheck as $key) {
            if (isset($modules[$key]) && $modules[$key] === false) {
                abort(404, "Modul {$moduleKey} tidak diaktifkan pada organisasi ini.");
            }
        }
    }

    /**
     * 1. GET /api/public/home
     * Aggregated portal homepage data
     */
    public function home()
    {
        // 1. Active Organizations for Directory Preview
        $organizations = Organization::where('status', 'active')
            ->select(['id', 'nama', 'jenis', 'subdomain', 'logo', 'warna_tema', 'modul_aktif'])
            ->withCount([
                'posts' => fn($q) => $q->where('status', 'published'),
                'activities' => fn($q) => $q->where('status', 'published'),
            ])
            ->latest('created_at')
            ->take(12)
            ->get()
            ->map(function ($org) {
                return [
                    'id' => $org->id,
                    'nama' => $org->nama,
                    'jenis' => $org->jenis,
                    'subdomain' => $org->subdomain,
                    'logo' => $org->logo,
                    'warna_tema' => $org->warna_tema,
                    'slogan' => $org->modul_aktif['slogan'] ?? null,
                    'deskripsi' => $org->modul_aktif['deskripsi'] ?? null,
                    'posts_count' => $org->posts_count,
                    'activities_count' => $org->activities_count,
                ];
            });

        // 2. Featured / Latest Published Articles
        $articles = Post::withoutGlobalScopes()
            ->where('status', 'published')
            ->whereHas('organization', fn($q) => $q->where('status', 'active'))
            ->with([
                'organization:id,nama,subdomain,logo,warna_tema',
                'user:id,name',
                'category:id,name,slug',
                'tags:id,name,slug',
            ])
            ->latest('published_at')
            ->take(6)
            ->get()
            ->map(fn($post) => $this->formatArticle($post));

        // 3. Upcoming Published Agenda
        $today = Carbon::today()->toDateString();
        $agenda = Activity::withoutGlobalScopes()
            ->where('status', 'published')
            ->whereDate('tanggal_pelaksanaan', '>=', $today)
            ->whereHas('organization', fn($q) => $q->where('status', 'active'))
            ->with('organization:id,nama,subdomain,logo,warna_tema')
            ->orderBy('tanggal_pelaksanaan', 'asc')
            ->take(6)
            ->get()
            ->map(fn($act) => $this->formatAgenda($act));

        // 4. Active Announcements (Within effective date range)
        $now = Carbon::now();
        $announcements = Announcement::withoutGlobalScopes()
            ->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_date')
                  ->orWhere('effective_date', '<=', $now);
            })
            ->whereHas('organization', fn($q) => $q->where('status', 'active'))
            ->with('organization:id,nama,subdomain,logo,warna_tema')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(fn($ann) => $this->formatAnnouncement($ann));

        return response()->json([
            'status' => 'success',
            'data' => [
                'organizations' => $organizations,
                'featured_articles' => $articles,
                'upcoming_agenda' => $agenda,
                'active_announcements' => $announcements,
                'stats' => [
                    'total_organizations' => Organization::where('status', 'active')->count(),
                    'total_articles' => Post::withoutGlobalScopes()->where('status', 'published')->count(),
                    'total_agenda' => Activity::withoutGlobalScopes()->where('status', 'published')->count(),
                ]
            ]
        ]);
    }

    /**
     * 2. GET /api/public/organizations
     * Filterable list of active organizations
     */
    public function organizations(Request $request)
    {
        $query = Organization::where('status', 'active')
            ->select(['id', 'nama', 'jenis', 'subdomain', 'logo', 'warna_tema', 'modul_aktif'])
            ->withCount([
                'posts' => fn($q) => $q->where('status', 'published'),
                'activities' => fn($q) => $q->where('status', 'published'),
                'committees' => fn($q) => $q->where('status', 'active'),
            ]);

        if ($request->has('jenis') && $request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int)($request->per_page ?? 12), 1), 50);
        $paginator = $query->latest('created_at')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($org) {
            return [
                'id' => $org->id,
                'nama' => $org->nama,
                'jenis' => $org->jenis,
                'subdomain' => $org->subdomain,
                'logo' => $org->logo,
                'warna_tema' => $org->warna_tema,
                'slogan' => $org->modul_aktif['slogan'] ?? null,
                'deskripsi' => $org->modul_aktif['deskripsi'] ?? null,
                'posts_count' => $org->posts_count,
                'activities_count' => $org->activities_count,
                'committees_count' => $org->committees_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * 3. GET /api/public/organizations/{slug}
     * Public organization profile with current period & content counts
     */
    public function organization(string $slug)
    {
        $org = $this->resolveOrganization($slug);

        $currentPeriod = $org->currentPeriod();
        $modules = $org->modul_aktif ?? [];

        // Check if website is enabled
        $websiteEnabled = $modules['public_website_enabled'] ?? true;
        if (!$websiteEnabled) {
            return response()->json([
                'status' => 'unavailable',
                'message' => 'Website publik organisasi ini sedang dinonaktifkan sementara.',
                'data' => [
                    'nama' => $org->nama,
                    'jenis' => $org->jenis,
                    'subdomain' => $org->subdomain,
                    'logo' => $org->logo,
                    'public_website_enabled' => false,
                ]
            ], 200);
        }

        // Preview counts of published items
        $publishedPostsCount = Post::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->count();

        $publishedActivitiesCount = Activity::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->count();

        $activeAnnouncementsCount = Announcement::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->count();

        $publicMediaCount = Media::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->count();

        $activeCommitteesCount = Committee::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'active')
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $org->id,
                'nama' => $org->nama,
                'jenis' => $org->jenis,
                'subdomain' => $org->subdomain,
                'logo' => $org->logo,
                'warna_tema' => $org->warna_tema ?? '#4f46e5',
                'status' => $org->status,
                'slogan' => $modules['slogan'] ?? null,
                'deskripsi' => $modules['deskripsi'] ?? null,
                'deskripsi_lengkap' => $modules['deskripsi_lengkap'] ?? null,
                'hero_title' => $modules['hero_title'] ?? null,
                'hero_subtitle' => $modules['hero_subtitle'] ?? null,
                'email_publik' => $modules['email_publik'] ?? null,
                'instagram' => $modules['instagram'] ?? null,
                'website_eksternal' => $modules['website_eksternal'] ?? null,
                'seo_title' => $modules['seo_title'] ?? "{$org->nama} | CMS ORMAWA ITI",
                'seo_description' => $modules['seo_description'] ?? ($modules['deskripsi'] ?? "Website resmi {$org->nama}"),
                'og_image' => $modules['og_image'] ?? $org->logo,
                'modules' => [
                    'posts' => $modules['posts'] ?? true,
                    'agenda' => $modules['agenda'] ?? true,
                    'announcements' => $modules['announcements'] ?? true,
                    'galeri' => $modules['galeri'] ?? true,
                    'documents' => $modules['documents'] ?? true,
                    'structure' => $modules['structure'] ?? true,
                    'public_website_enabled' => $websiteEnabled,
                ],
                'current_period' => $currentPeriod ? [
                    'id' => $currentPeriod->id,
                    'period_name' => $currentPeriod->period_name,
                    'start_date' => $currentPeriod->start_date,
                    'end_date' => $currentPeriod->end_date,
                    'status' => $currentPeriod->status,
                ] : null,
                'stats' => [
                    'posts_count' => $publishedPostsCount,
                    'activities_count' => $publishedActivitiesCount,
                    'announcements_count' => $activeAnnouncementsCount,
                    'media_count' => $publicMediaCount,
                    'committees_count' => $activeCommitteesCount,
                ]
            ]
        ]);
    }

    /**
     * 4. GET /api/public/organizations/{slug}/articles
     * Published articles for specific organization
     */
    public function articles(string $slug, Request $request)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'posts');

        $query = Post::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->with([
                'user:id,name',
                'category:id,name,slug',
                'tags:id,name,slug',
                'organization:id,nama,subdomain,logo,warna_tema',
            ]);

        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $cat = $request->category;
            $query->whereHas('category', function ($qc) use ($cat) {
                $qc->where('slug', $cat)->orWhere('name', $cat)->orWhere('id', $cat);
            });
        }

        if ($request->has('tag') && $request->tag) {
            $tag = $request->tag;
            $query->whereHas('tags', function ($qt) use ($tag) {
                $qt->where('slug', $tag)->orWhere('name', $tag)->orWhere('id', $tag);
            });
        }

        $perPage = min(max((int)($request->per_page ?? 12), 1), 50);
        $paginator = $query->latest('published_at')->paginate($perPage);

        $items = collect($paginator->items())->map(fn($p) => $this->formatArticle($p));

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * 5. GET /api/public/organizations/{slug}/articles/{articleSlug}
     * Single published article detail with related posts
     */
    public function article(string $slug, string $articleSlug)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'posts');

        $post = Post::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->where(function ($q) use ($articleSlug) {
                $q->where('slug', $articleSlug)
                  ->orWhere('id', $articleSlug);
            })
            ->with([
                'user:id,name',
                'category:id,name,slug',
                'tags:id,name,slug',
                'organization:id,nama,subdomain,logo,warna_tema',
            ])
            ->first();

        if (!$post) {
            abort(404, 'Artikel tidak ditemukan atau belum dipublikasikan.');
        }

        // 3 Related Articles from same organization
        $related = Post::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->with(['category:id,name,slug', 'organization:id,nama,subdomain'])
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn($p) => $this->formatArticle($p));

        return response()->json([
            'status' => 'success',
            'data' => array_merge($this->formatArticle($post), [
                'konten' => $post->konten,
                'related_articles' => $related,
            ])
        ]);
    }

    /**
     * 6. GET /api/public/organizations/{slug}/agenda
     * Published agenda items
     */
    public function agenda(string $slug, Request $request)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'agenda');

        $query = Activity::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->with('organization:id,nama,subdomain,logo,warna_tema');

        $today = Carbon::today()->toDateString();
        $tab = $request->get('tab', 'upcoming');

        if ($tab === 'upcoming') {
            $query->whereDate('tanggal_pelaksanaan', '>=', $today)->orderBy('tanggal_pelaksanaan', 'asc');
        } elseif ($tab === 'today') {
            $query->whereDate('tanggal_pelaksanaan', '=', $today);
        } elseif ($tab === 'past') {
            $query->whereDate('tanggal_pelaksanaan', '<', $today)->orderBy('tanggal_pelaksanaan', 'desc');
        } else {
            $query->orderBy('tanggal_pelaksanaan', 'desc');
        }

        $perPage = min(max((int)($request->per_page ?? 10), 1), 50);
        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(fn($a) => $this->formatAgenda($a));

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * 7. GET /api/public/organizations/{slug}/agenda/{id}
     * Single published agenda detail
     */
    public function agendaDetail(string $slug, string $id)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'agenda');

        $activity = Activity::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->where('id', $id)
            ->with('organization:id,nama,subdomain,logo,warna_tema')
            ->first();

        if (!$activity) {
            abort(404, 'Agenda tidak ditemukan atau belum dipublikasikan.');
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->formatAgenda($activity),
        ]);
    }

    /**
     * 8. GET /api/public/organizations/{slug}/announcements
     * Active published announcements
     */
    public function announcements(string $slug)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'announcements');

        $now = Carbon::now();
        $announcements = Announcement::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_date')
                  ->orWhere('effective_date', '<=', $now);
            })
            ->with('organization:id,nama,subdomain,logo,warna_tema')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->latest('published_at')
            ->get()
            ->map(fn($ann) => $this->formatAnnouncement($ann));

        return response()->json([
            'status' => 'success',
            'data' => $announcements,
        ]);
    }

    /**
     * 9. GET /api/public/organizations/{slug}/gallery
     * Public image media items
     */
    public function gallery(string $slug, Request $request)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'galeri');

        $query = Media::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where(function ($q) {
                $q->where('mime_type', 'like', 'image/%')
                  ->orWhereNull('mime_type');
            })
            ->with('organization:id,nama,subdomain');

        $perPage = min(max((int)($request->per_page ?? 12), 1), 50);
        $paginator = $query->latest('created_at')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($m) {
            return [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => $m->url,
                'mime_type' => $m->mime_type,
                'size' => $m->size,
                'created_at' => $m->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * 10. GET /api/public/organizations/{slug}/documents
     * Public downloadable documents
     */
    public function documents(string $slug, Request $request)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'documents');

        $query = Media::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where(function ($q) {
                $q->where('mime_type', 'not like', 'image/%')
                  ->orWhere('filename', 'like', '%.pdf')
                  ->orWhere('filename', 'like', '%.doc%')
                  ->orWhere('filename', 'like', '%.xls%')
                  ->orWhere('filename', 'like', '%.zip%');
            })
            ->with('organization:id,nama,subdomain');

        $perPage = min(max((int)($request->per_page ?? 12), 1), 50);
        $paginator = $query->latest('created_at')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($m) {
            return [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => $m->url,
                'mime_type' => $m->mime_type ?? 'application/octet-stream',
                'size' => $m->size,
                'formatted_size' => $m->size ? round($m->size / 1024, 1) . ' KB' : 'N/A',
                'created_at' => $m->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * 11. GET /api/public/organizations/{slug}/structure
     * Public organizational structure (Committees for active period)
     */
    public function structure(string $slug)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'structure');

        $committees = Committee::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('status', 'active')
            ->orderBy('department', 'asc')
            ->orderBy('position', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'position' => $c->position,
                    'department' => $c->department ?? 'Pengurus Inti',
                    'period' => $c->period,
                    'photo' => $c->photo,
                ];
            });

        // Group by department
        $grouped = $committees->groupBy('department');

        return response()->json([
            'status' => 'success',
            'data' => [
                'organization' => [
                    'nama' => $org->nama,
                    'subdomain' => $org->subdomain,
                    'logo' => $org->logo,
                    'current_period' => $org->currentPeriod()?->period_name,
                ],
                'members' => $committees,
                'by_department' => $grouped,
            ]
        ]);
    }

    /**
     * 12. GET /api/public/sitemap
     * Generate dynamic sitemap payload
     */
    public function sitemap()
    {
        $urls = [];
        $appUrl = config('app.url', 'https://ormawa.iti.ac.id');

        // Static index routes
        $urls[] = ['loc' => "{$appUrl}/", 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => "{$appUrl}/organizations", 'priority' => '0.9', 'changefreq' => 'daily'];

        // Active Organizations
        $activeOrgs = Organization::where('status', 'active')->get();
        foreach ($activeOrgs as $org) {
            $modules = $org->modul_aktif ?? [];
            if (($modules['public_website_enabled'] ?? true) === false) {
                continue;
            }

            $orgBase = "{$appUrl}/organizations/{$org->subdomain}";
            $urls[] = ['loc' => $orgBase, 'priority' => '0.8', 'changefreq' => 'weekly'];

            if (($modules['posts'] ?? true) !== false) {
                $urls[] = ['loc' => "{$orgBase}/articles", 'priority' => '0.8', 'changefreq' => 'daily'];
            }
            if (($modules['agenda'] ?? true) !== false) {
                $urls[] = ['loc' => "{$orgBase}/agenda", 'priority' => '0.7', 'changefreq' => 'weekly'];
            }
            if (($modules['announcements'] ?? true) !== false) {
                $urls[] = ['loc' => "{$orgBase}/announcements", 'priority' => '0.7', 'changefreq' => 'weekly'];
            }
            if (($modules['galeri'] ?? true) !== false) {
                $urls[] = ['loc' => "{$orgBase}/gallery", 'priority' => '0.6', 'changefreq' => 'weekly'];
            }
            if (($modules['structure'] ?? true) !== false) {
                $urls[] = ['loc' => "{$orgBase}/structure", 'priority' => '0.6', 'changefreq' => 'monthly'];
            }
        }

        // Published Articles (Only from active orgs with posts module enabled)
        $articles = Post::withoutGlobalScopes()
            ->where('status', 'published')
            ->whereHas('organization', fn($q) => $q->where('status', 'active'))
            ->with('organization:id,subdomain,modul_aktif')
            ->get();

        foreach ($articles as $art) {
            $orgModules = $art->organization?->modul_aktif ?? [];
            if ($art->organization && ($orgModules['posts'] ?? true) !== false && ($orgModules['public_website_enabled'] ?? true) !== false) {
                $urls[] = [
                    'loc' => "{$appUrl}/organizations/{$art->organization->subdomain}/articles/{$art->slug}",
                    'lastmod' => $art->updated_at?->toAtomString(),
                    'priority' => '0.9',
                    'changefreq' => 'monthly',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $urls,
        ]);
    }

    // ─── Formatters (Ensuring Zero Credential / Internal Metadata Leakage) ───

    /**
     * 10b. GET /api/public/organizations/{slug}/documents/{id}/download
     * Secure public download for document
     */
    public function downloadDocument(string $slug, int|string $id)
    {
        $org = $this->resolveOrganization($slug);
        $this->ensureModuleEnabled($org, 'documents');

        $doc = Media::withoutGlobalScopes()
            ->where('organization_id', $org->id)
            ->where('id', $id)
            ->firstOrFail();

        // Check if file exists in storage
        if ($doc->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($doc->path, $doc->filename);
        }

        if ($doc->url) {
            return redirect()->away($doc->url);
        }

        abort(404, 'Berkas dokumen tidak ditemukan.');
    }

    private function formatArticle(Post $post): array
    {
        return [
            'id' => $post->id,
            'judul' => $post->judul,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?? substr(strip_tags($post->konten), 0, 160) . '...',
            'cover_image' => $post->cover_image,
            'published_at' => $post->published_at?->toIso8601String() ?? $post->created_at?->toIso8601String(),
            'author' => [
                'id' => $post->user?->id,
                'name' => $post->user?->name ?? 'Tim Redaksi',
            ],
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
                'slug' => $post->category->slug,
            ] : null,
            'tags' => $post->tags->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ]),
            'organization' => $post->organization ? [
                'id' => $post->organization->id,
                'nama' => $post->organization->nama,
                'subdomain' => $post->organization->subdomain,
                'logo' => $post->organization->logo,
                'warna_tema' => $post->organization->warna_tema,
            ] : null,
            'seo' => [
                'meta_title' => $post->meta_title ?? "{$post->judul} | {$post->organization?->nama}",
                'meta_description' => $post->meta_description ?? ($post->excerpt ?? substr(strip_tags($post->konten), 0, 160)),
                'og_image' => $post->cover_image ?? $post->organization?->logo,
            ]
        ];
    }

    private function formatAgenda(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'judul' => $activity->judul,
            'deskripsi' => $activity->deskripsi,
            'tanggal_pelaksanaan' => $activity->tanggal_pelaksanaan ? Carbon::parse($activity->tanggal_pelaksanaan)->toDateString() : null,
            'tempat' => $activity->tempat ?? 'Kampus ITI',
            'published_at' => $activity->published_at?->toIso8601String() ?? $activity->created_at?->toIso8601String(),
            'organization' => $activity->organization ? [
                'id' => $activity->organization->id,
                'nama' => $activity->organization->nama,
                'subdomain' => $activity->organization->subdomain,
                'logo' => $activity->organization->logo,
                'warna_tema' => $activity->organization->warna_tema,
            ] : null,
        ];
    }

    private function formatAnnouncement(Announcement $announcement): array
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'slug' => $announcement->slug,
            'content' => $announcement->content,
            'priority' => $announcement->priority ?? 'normal',
            'effective_date' => $announcement->effective_date?->toDateString(),
            'published_at' => $announcement->published_at?->toIso8601String() ?? $announcement->created_at?->toIso8601String(),
            'organization' => $announcement->organization ? [
                'id' => $announcement->organization->id,
                'nama' => $announcement->organization->nama,
                'subdomain' => $announcement->organization->subdomain,
                'logo' => $announcement->organization->logo,
                'warna_tema' => $announcement->organization->warna_tema,
            ] : null,
        ];
    }
}
