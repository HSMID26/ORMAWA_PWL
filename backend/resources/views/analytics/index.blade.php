<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    📊 {{ __('Statistik Pengunjung & Traffic Analytics') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pantau metrik pengunjung, tren kunjungan halaman, dan integrasi Google Analytics 4 (GA4).
                </p>
            </div>

            <!-- Filter Organisasi Khusus Super Admin -->
            @if($isSuperAdmin && $organizations->count() > 0)
                <form method="GET" action="{{ route('analytics.index') }}" class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Pilih Ormawa:</label>
                    <select name="organization_id" onchange="this.form.submit()" class="p-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ ($setting?->id == $org->id) ? 'selected' : '' }}>
                                {{ $org->nama }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <!-- Library Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Integration Status Google Analytics 4 -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <span class="p-3 bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-300 rounded-xl text-2xl">📊</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Google Analytics 4 (GA4)</h3>
                                @if(!empty($setting?->ga_tracking_id))
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 rounded-full text-[10px] font-bold">
                                        TERHUBUNG
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 rounded-full text-[10px] font-bold">
                                        BELUM DIPASANG
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                @if(!empty($setting?->ga_tracking_id))
                                    Measurement ID: <code class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $setting->ga_tracking_id }}</code> — Tracking script aktif pada halaman publik portal.
                                @else
                                    Pasang Measurement ID GA4 untuk melacak demografi audiens dan event interaksi lengkap.
                                @endif
                            </p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('organization.settings') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold flex items-center gap-2 shadow transition">
                            ⚙️ {{ !empty($setting?->ga_tracking_id) ? 'Ubah ID GA4' : 'Hubungkan GA4 Sekarang' }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Stat Cards Traffic & Konten -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Total Kunjungan (Views)</p>
                        <h4 class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ number_format($totalViews) }}</h4>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">+{{ $todayViews }} hari ini</span>
                    </div>
                    <span class="p-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-2xl">👀</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Pengunjung Unik</p>
                        <h4 class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($uniqueVisitors) }}</h4>
                        <span class="text-[11px] text-gray-400">Berdasarkan Unique IP</span>
                    </div>
                    <span class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl text-2xl">👥</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Artikel Terpublikasi</p>
                        <h4 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">{{ $publishedPosts }}</h4>
                        <span class="text-[11px] text-gray-400">dari {{ $totalPosts }} total artikel</span>
                    </div>
                    <span class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl text-2xl">📰</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Total Interaksi / Log</p>
                        <h4 class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1">{{ number_format($totalActivities) }}</h4>
                        <span class="text-[11px] text-gray-400">Audit Trail tercatat</span>
                    </div>
                    <span class="p-3 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl text-2xl">⚡</span>
                </div>
            </div>

            <!-- SECTION GRAFIK VISUAL (CHART.JS) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Line Chart: Tren Kunjungan 7 Hari Terakhir -->
                <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                            📈 Tren Kunjungan Portal (7 Hari Terakhir)
                        </h3>
                        <span class="text-xs text-gray-400">Page Views vs Unique Visitors</span>
                    </div>
                    <div class="relative h-64">
                        <canvas id="activityTrendChart"></canvas>
                    </div>
                </div>

                <!-- 2. Doughnut Chart: Distribusi Status Artikel -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        🍩 Status Konten Ormawa
                    </h3>
                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="postStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- SECTION TABEL INFORMASI: ARTIKEL POPULER & TOP PAGES -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tabel 1: Artikel Terbaru / Performa -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        🔥 Artikel Ormawa Terbaru
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-[11px]">
                                <tr>
                                    <th class="p-3">Judul Artikel</th>
                                    <th class="p-3 text-center">Status</th>
                                    <th class="p-3 text-right">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                @forelse($popularPosts as $post)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                        <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                            <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600 transition">
                                                {{ $post->judul ?? $post->title }}
                                            </a>
                                            <div class="text-[10px] text-gray-400 font-mono mt-0.5">/posts/{{ $post->slug }}</div>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $post->status == 'published' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right text-gray-400 whitespace-nowrap">
                                            {{ $post->created_at ? $post->created_at->format('d M Y') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-4 text-center text-gray-400">Belum ada artikel untuk ditampilkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel 2: Top Visited URL Paths -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        🌐 Halaman Paling Banyak Dikunjungi
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-[11px]">
                                <tr>
                                    <th class="p-3">Jalur Halaman (URL Path)</th>
                                    <th class="p-3 text-right">Total Kunjungan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                @forelse($topPages as $page)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                        <td class="p-3 font-mono font-medium text-gray-900 dark:text-white">
                                            {{ $page->url_path }}
                                        </td>
                                        <td class="p-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($page->total_views) }} views
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="p-4 text-center text-gray-400">Belum ada catatan kunjungan halaman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT CHART.JS INITIALIZATION -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const datesLabels  = @json($dates);
            const viewsData    = @json($viewsData);
            const uniqueData   = @json($uniqueData ?? []);
            const publishedCount = {{ $publishedPosts }};
            const draftCount     = {{ $draftPosts }};

            // 1. Line Chart: Tren Kunjungan & Pengunjung Unik
            const ctxActivity = document.getElementById('activityTrendChart').getContext('2d');
            new Chart(ctxActivity, {
                type: 'line',
                data: {
                    labels: datesLabels,
                    datasets: [
                        {
                            label: 'Total Kunjungan (Page Views)',
                            data: viewsData,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#059669',
                            pointRadius: 4
                        },
                        {
                            label: 'Pengunjung Unik (Unique Visitors)',
                            data: uniqueData,
                            borderColor: '#6366F1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#4F46E5',
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#9CA3AF' },
                            grid: { color: 'rgba(156, 163, 175, 0.1)' }
                        },
                        x: {
                            ticks: { color: '#9CA3AF' },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { 
                            position: 'top',
                            labels: { color: '#9CA3AF', usePointStyle: true, boxWidth: 6 } 
                        }
                    }
                }
            });

            // 2. Doughnut Chart: Status Artikel
            const ctxStatus = document.getElementById('postStatusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Terpublikasi', 'Draf'],
                    datasets: [{
                        data: [publishedCount, draftCount],
                        backgroundColor: ['#10B981', '#F59E0B'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9CA3AF', padding: 15 }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>