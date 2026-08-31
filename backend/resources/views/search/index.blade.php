<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    🔍 {{ __('Pencarian Global') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Hasil penelusuran kata kunci di seluruh modul CMS Ormawa.
                </p>
            </div>
            @if(!empty($query))
                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 text-xs font-bold rounded-full">
                    {{ $totalResults }} hasil ditemukan
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Search Form Bar -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('search.index') }}" class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">🔍</span>
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ $query }}" 
                            placeholder="Cari artikel, agenda kegiatan, pengurus, akun, atau media..." 
                            required
                            class="w-full pl-10 pr-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow transition flex items-center gap-2">
                        Cari
                    </button>
                </form>
            </div>

            @if(!empty($query))
                <!-- Tabs Navigasi Kategori -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    <a href="{{ route('search.index', ['q' => $query, 'tab' => 'all']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ ($tab === 'all') ? 'bg-indigo-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                        Semua ({{ $totalResults }})
                    </a>
                    <a href="{{ route('search.index', ['q' => $query, 'tab' => 'posts']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ ($tab === 'posts') ? 'bg-blue-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                        📝 Artikel ({{ $posts->count() }})
                    </a>
                    <a href="{{ route('search.index', ['q' => $query, 'tab' => 'activities']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ ($tab === 'activities') ? 'bg-purple-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                        📅 Agenda ({{ $activities->count() }})
                    </a>
                    <a href="{{ route('search.index', ['q' => $query, 'tab' => 'committees']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ ($tab === 'committees') ? 'bg-emerald-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                        👥 Pengurus ({{ $committees->count() }})
                    </a>
                    @if($users->count() > 0)
                        <a href="{{ route('search.index', ['q' => $query, 'tab' => 'users']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ ($tab === 'users') ? 'bg-amber-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                            👤 Pengguna ({{ $users->count() }})
                        </a>
                    @endif
                    @if($media->count() > 0)
                        <a href="{{ route('search.index', ['q' => $query, 'tab' => 'media']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ ($tab === 'media') ? 'bg-slate-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                            🖼️ Media ({{ $media->count() }})
                        </a>
                    @endif
                </div>

                <!-- HASIL PENCARIAN -->
                <div class="space-y-6">
                    
                    <!-- 1. Kategori: Artikel & Konten -->
                    @if(($tab === 'all' || $tab === 'posts') && $posts->count() > 0)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                📝 Artikel / Konten Berita ({{ $posts->count() }})
                            </h3>
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($posts as $post)
                                    <div class="py-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                        <div>
                                            <a href="{{ route('posts.show', $post) }}" class="font-bold text-base text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                                {{ $post->judul ?? $post->title }}
                                            </a>
                                            <div class="text-xs text-gray-400 mt-1 flex items-center gap-3">
                                                <span>Penulis: {{ $post->user->name ?? 'Admin' }}</span>
                                                <span>•</span>
                                                <span>Tanggal: {{ $post->created_at ? $post->created_at->format('d M Y') : '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ ($post->status === 'published') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                            <a href="{{ route('posts.show', $post) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                                                Buka Artikel →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 2. Kategori: Agenda Kegiatan -->
                    @if(($tab === 'all' || $tab === 'activities') && $activities->count() > 0)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                📅 Agenda Kegiatan & Proker ({{ $activities->count() }})
                            </h3>
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($activities as $act)
                                    <div class="py-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                        <div>
                                            <h4 class="font-bold text-base text-gray-900 dark:text-white">
                                                {{ $act->title ?? $act->judul }}
                                            </h4>
                                            <div class="text-xs text-gray-400 mt-1 flex items-center gap-3">
                                                <span>📍 Lokasi: {{ $act->location ?: 'Online' }}</span>
                                                <span>•</span>
                                                <span>Waktu: {{ $act->start_time ? date('d M Y, H:i', strtotime($act->start_time)) : '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                                {{ ucfirst($act->status) }}
                                            </span>
                                            <a href="{{ route('activities.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                                                Buka Agenda →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 3. Kategori: Struktur Pengurus -->
                    @if(($tab === 'all' || $tab === 'committees') && $committees->count() > 0)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                👥 Pengurus Organisasi ({{ $committees->count() }})
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($committees as $com)
                                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-3 bg-gray-50 dark:bg-gray-900">
                                        @if($com->photo)
                                            <img src="{{ Storage::url($com->photo) }}" alt="{{ $com->name }}" class="w-12 h-12 rounded-full object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-base">
                                                {{ strtoupper(substr($com->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="truncate">
                                            <div class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $com->name }}</div>
                                            <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">{{ $com->position }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $com->department ? 'Divisi ' . $com->department : 'BPH' }} • Periode {{ $com->period }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 4. Kategori: Pengguna / User (Admin Only) -->
                    @if(($tab === 'all' || $tab === 'users') && $users->count() > 0)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                👤 Akun Pengguna / Pengurus ({{ $users->count() }})
                            </h3>
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($users as $u)
                                    <div class="py-3 flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $u->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $u->email }} • Status: {{ ucfirst($u->status) }}</div>
                                        </div>
                                        <a href="{{ route('users.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                                            Kelola di Manajemen Pengguna →
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Empty State Jika Tidak Ada Hasil Sama Sekali -->
                    @if($totalResults === 0)
                        <div class="bg-white dark:bg-gray-800 p-12 rounded-lg shadow border border-gray-100 dark:border-gray-700 text-center">
                            <div class="text-5xl mb-3">🔍</div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Tidak ada hasil yang cocok</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Kami tidak menemukan data apapun yang cocok dengan kata kunci "<span class="font-bold">{{ $query }}</span>". Silakan coba kata kunci lain.
                            </p>
                        </div>
                    @endif

                </div>
            @else
                <div class="bg-white dark:bg-gray-800 p-12 rounded-lg shadow border border-gray-100 dark:border-gray-700 text-center">
                    <div class="text-5xl mb-3">💡</div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Ketik kata kunci di atas untuk mencari</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Anda dapat menelusuri artikel berita, agenda proker, susunan pengurus, atau menu pengaturan.
                    </p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
