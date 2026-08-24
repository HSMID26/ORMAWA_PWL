<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Statistik Pengunjung & Traffic Analytics') }}
        </h2>
    </x-slot>

    <!-- Library Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Integration Status Google Analytics 4 -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="p-3 bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-200 rounded-lg text-2xl">📊</span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Google Analytics 4 (GA4) Integration</h3>
                            <p class="text-xs text-gray-400">Status Pelacakan External & Internal Traffic Analytics</p>
                        </div>
                    </div>
                    <div>
                        @if(!empty($setting->ga_tracking_id))
                            <span class="px-3 py-1.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 rounded-lg text-xs font-bold flex items-center gap-2">
                                ✅ GA4 Terhubung (ID: {{ $setting->ga_tracking_id }})
                            </span>
                        @else
                            <a href="{{ route('organization.settings') }}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold flex items-center gap-2">
                                ⚠️ Belum Dipasang (Atur Measurement ID)
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ringkasan Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Total Artikel</p>
                        <h4 class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalPosts }}</h4>
                    </div>
                    <span class="p-3 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200 rounded-full text-xl">📰</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Artikel Terpublikasi</p>
                        <h4 class="text-3xl font-bold text-emerald-500 mt-1">{{ $publishedPosts }}</h4>
                    </div>
                    <span class="p-3 bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-200 rounded-full text-xl">✅</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Draft Artikel</p>
                        <h4 class="text-3xl font-bold text-amber-500 mt-1">{{ $draftPosts }}</h4>
                    </div>
                    <span class="p-3 bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-200 rounded-full text-xl">📝</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Total Interaksi</p>
                        <h4 class="text-3xl font-bold text-purple-500 mt-1">{{ $totalActivities }}</h4>
                    </div>
                    <span class="p-3 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-200 rounded-full text-xl">⚡</span>
                </div>
            </div>

            <!-- SECTION GRAFIK VISUAL (CHART.JS) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Line Chart: Tren Aktivitas 7 Hari Terakhir -->
                <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        📈 Tren Aktivitas & Interaksi Portal (7 Hari Terakhir)
                    </h3>
                    <div class="relative h-64">
                        <canvas id="activityTrendChart"></canvas>
                    </div>
                </div>

                <!-- 2. Doughnut Chart: Distibusi Status Artikel -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        🍩 Distribusi Status Konten
                    </h3>
                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="postStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabel Konten Terpopuler / Terbaru -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    🔥 Ringkasan Performa Artikel Ormawa
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">Judul Artikel</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($popularPosts as $post)
                                <tr>
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $post->title }}
                                        <div class="text-xs text-gray-400 font-normal">/posts/{{ $post->slug }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs font-bold rounded {{ $post->status == 'published' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-gray-400">{{ $post->created_at ? $post->created_at->format('d M Y, H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-gray-400">Belum ada artikel untuk dianalisis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT CHART.JS INITIALIZATION -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data dari Controller
            const datesLabels = @json($dates);
            const viewsData   = @json($viewsData);
            const publishedCount = {{ $publishedPosts }};
            const draftCount = {{ $draftPosts }};

            // 1. Line Chart: Tren Aktivitas
            const ctxActivity = document.getElementById('activityTrendChart').getContext('2d');
            new Chart(ctxActivity, {
                type: 'line',
                data: {
                    labels: datesLabels,
                    datasets: [{
                        label: 'Jumlah Kunjungan Pengunjung (Page Views)',
                        data: viewsData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#059669',
                        pointRadius: 5
                    }]
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
                        legend: { labels: { color: '#9CA3AF' } }
                    }
                }
            });

            // 2. Doughnut Chart: Status Artikel
            const ctxStatus = document.getElementById('postStatusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Terpublikasi (Published)', 'Draf (Draft)'],
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