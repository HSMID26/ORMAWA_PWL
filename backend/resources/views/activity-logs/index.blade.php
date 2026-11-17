<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    📜 {{ __('Riwayat Aktivitas & Audit Trail') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Audit log transparan: Siapa yang mengubah data apa, modul yang terdampak, dan kapan perubahan terjadi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Ringkasan Statistik Audit Trail -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Log Terrekam</div>
                        <div class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">{{ number_format($totalLogsCount) }}</div>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xl">📜</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aktivitas Hari Ini</div>
                        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($todayLogsCount) }}</div>
                    </div>
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full text-xl">⚡</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi Hapus / Sensitif</div>
                        <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($sensitiveLogsCount) }}</div>
                    </div>
                    <div class="p-3 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-full text-xl">🛡️</div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        
                        <!-- Search Keyword -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Cari Kata Kunci / Nama User</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, nama user, atau email..." class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Modul Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Filter Modul</label>
                            <select name="module" onchange="this.form.submit()" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Semua Modul --</option>
                                @foreach($modules as $m)
                                    <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Aksi Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Filter Aksi</label>
                            <select name="action" onchange="this.form.submit()" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Semua Aksi --</option>
                                @foreach($actions as $a)
                                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end pt-2 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow transition duration-150 flex items-center justify-center gap-1">
                                🔍 Terapkan Filter
                            </button>
                            @if(request()->anyFilled(['search', 'module', 'action', 'date_from', 'date_to', 'organization_id']))
                                <a href="{{ route('activity-logs.index') }}" class="px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white font-bold text-xs rounded-lg shadow transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Log Audit Trail -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    📋 Catatan Riwayat Aktivitas & Perubahan
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">Waktu & Tanggal</th>
                                <th class="p-3">Pengguna (Aktor)</th>
                                <th class="p-3">Organisasi</th>
                                <th class="p-3">Modul</th>
                                <th class="p-3">Aksi</th>
                                <th class="p-3">Deskripsi Aktivitas</th>
                                <th class="p-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($logs as $log)
                                @php
                                    $actionBadges = [
                                        'create'        => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                        'update'        => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'delete'        => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
                                        'destroy'       => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
                                        'login'         => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                        'logout'        => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'publish'       => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                        'activate'      => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                        'deactivate'    => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                        'login_failed'  => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <td class="p-3 text-xs whitespace-nowrap">
                                        <div class="font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                        </div>
                                    </td>
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                        @if($log->user)
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                                    {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div>{{ $log->user->name }}</div>
                                                    <div class="text-[10px] text-gray-400 font-normal">{{ $log->user->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Sistem / Pengunjung</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-xs whitespace-nowrap">
                                        {{ $log->organization ? $log->organization->nama : 'PKA Pusat' }}
                                    </td>
                                    <td class="p-3 text-xs">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 font-semibold">
                                            {{ ucfirst($log->module) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs">
                                        <span class="px-2.5 py-1 rounded-full font-semibold {{ $actionBadges[$log->action] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-gray-800 dark:text-gray-200">
                                        {{ $log->description }}
                                    </td>
                                    <td class="p-3 text-[11px] font-mono text-gray-400 whitespace-nowrap">
                                        {{ $log->ip_address ?: '127.0.0.1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="text-3xl mb-2">📜</div>
                                        Belum ada riwayat aktivitas yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
