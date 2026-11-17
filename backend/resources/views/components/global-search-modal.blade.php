<div 
    x-data="globalSearch()" 
    x-cloak
    @keydown.window.prevent.ctrl.k="openModal()"
    @keydown.window.prevent.cmd.k="openModal()"
    @keydown.window.escape="closeModal()"
    @open-global-search.window="openModal()"
    class="relative z-50"
>
    <!-- Modal Backdrop -->
    <div 
        x-show="isOpen" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeModal()"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
    ></div>

    <!-- Modal Panel -->
    <div 
        x-show="isOpen" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 md:p-20 overflow-y-auto"
    >
        <div 
            @click.away="closeModal()"
            class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 transition-all border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700"
        >
            <!-- Search Input Bar -->
            <div class="relative flex items-center px-4 py-3">
                <span class="text-gray-400 text-lg mr-3">🔍</span>
                <input 
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @input.debounce.250ms="performSearch()"
                    @keydown.arrow-down.prevent="navigateDown()"
                    @keydown.arrow-up.prevent="navigateUp()"
                    @keydown.enter.prevent="selectCurrent()"
                    type="text" 
                    placeholder="Cari artikel, agenda, pengurus, menu, atau fitur... (Ketik minimal 2 huruf)"
                    class="h-10 w-full bg-transparent border-0 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0"
                >
                <div class="flex items-center gap-1.5 ml-2">
                    <span class="px-2 py-0.5 text-[10px] font-bold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 rounded-md border border-gray-200 dark:border-gray-600">
                        ESC
                    </span>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="p-6 text-center text-xs text-gray-400">
                <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent mb-2"></div>
                <p>Mencari di seluruh modul...</p>
            </div>

            <!-- Search Results List -->
            <div 
                x-show="!isLoading && results.length > 0" 
                class="max-h-96 overflow-y-auto p-2 divide-y divide-gray-50 dark:divide-gray-700/50"
            >
                <template x-for="(item, index) in results" :key="index">
                    <a 
                        :href="item.url" 
                        @mouseenter="selectedIndex = index"
                        :class="{ 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-900 dark:text-white': selectedIndex === index, 'text-gray-700 dark:text-gray-300': selectedIndex !== index }"
                        class="flex items-center justify-between p-3 rounded-xl transition cursor-pointer group"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-xl flex-shrink-0" x-text="item.icon || '📄'"></span>
                            <div class="truncate">
                                <div class="font-semibold text-sm truncate" x-text="item.title"></div>
                                <div class="text-xs text-gray-400 truncate" x-text="item.description"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                            <span 
                                class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase"
                                :class="getBadgeClass(item.badge)"
                                x-text="item.badge || item.category"
                            ></span>
                            <span class="text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">→</span>
                        </div>
                    </a>
                </template>
            </div>

            <!-- Empty Results -->
            <div x-show="!isLoading && searchQuery.length >= 2 && results.length === 0" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                <div class="text-3xl mb-2">🔎</div>
                <p class="font-semibold">Tidak ada hasil yang ditemukan untuk "<span x-text="searchQuery"></span>"</p>
                <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci lain seperti nama agenda, judul artikel, atau nama pengurus.</p>
            </div>

            <!-- Initial Suggestions / Quick Actions (When query is empty) -->
            <div x-show="!isLoading && searchQuery.length < 2" class="p-4">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 px-2">Pintasan Cepat Populer</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('posts.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 text-xs text-gray-700 dark:text-gray-200 transition">
                        <span>✍️</span>
                        <div>
                            <div class="font-bold">Buat Artikel Baru</div>
                            <div class="text-[10px] text-gray-400">Tulis artikel di TipTap Editor</div>
                        </div>
                    </a>
                    <a href="{{ route('activities.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 text-xs text-gray-700 dark:text-gray-200 transition">
                        <span>📅</span>
                        <div>
                            <div class="font-bold">Agenda Kegiatan & iCal</div>
                            <div class="text-[10px] text-gray-400">Kalender proker & kegiatan</div>
                        </div>
                    </a>
                    <a href="{{ route('organization.settings') }}" class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 text-xs text-gray-700 dark:text-gray-200 transition">
                        <span>⚙️</span>
                        <div>
                            <div class="font-bold">Pengaturan Ormawa</div>
                            <div class="text-[10px] text-gray-400">Logo, warna tema, modul & GA4</div>
                        </div>
                    </a>
                    <a href="{{ route('analytics.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 text-xs text-gray-700 dark:text-gray-200 transition">
                        <span>📊</span>
                        <div>
                            <div class="font-bold">Statistik Pengunjung</div>
                            <div class="text-[10px] text-gray-400">Traffic & Google Analytics</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Footer Toolbar -->
            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center text-[11px] text-gray-400">
                <div class="flex items-center gap-3">
                    <span><kbd class="font-mono bg-white dark:bg-gray-800 border px-1.5 py-0.5 rounded shadow-sm">↑</kbd> <kbd class="font-mono bg-white dark:bg-gray-800 border px-1.5 py-0.5 rounded shadow-sm">↓</kbd> Navigasi</span>
                    <span><kbd class="font-mono bg-white dark:bg-gray-800 border px-1.5 py-0.5 rounded shadow-sm">↵</kbd> Buka</span>
                </div>
                <template x-if="searchQuery.length >= 2 && seeAllUrl">
                    <a :href="seeAllUrl" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Lihat Halaman Hasil Penuh →
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function globalSearch() {
        return {
            isOpen: false,
            searchQuery: '',
            isLoading: false,
            results: [],
            selectedIndex: 0,
            seeAllUrl: '',

            openModal() {
                this.isOpen = true;
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                });
            },

            closeModal() {
                this.isOpen = false;
                this.searchQuery = '';
                this.results = [];
                this.selectedIndex = 0;
            },

            async performSearch() {
                if (this.searchQuery.trim().length < 2) {
                    this.results = [];
                    this.seeAllUrl = '';
                    this.isLoading = false;
                    return;
                }

                this.isLoading = true;
                try {
                    const response = await fetch(`/api/global-search?q=${encodeURIComponent(this.searchQuery)}`);
                    const data = await response.json();
                    if (data.status === 'success') {
                        this.results = data.results;
                        this.seeAllUrl = data.see_all_url;
                        this.selectedIndex = 0;
                    }
                } catch (e) {
                    console.error('Global search error:', e);
                } finally {
                    this.isLoading = false;
                }
            },

            navigateDown() {
                if (this.results.length === 0) return;
                this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
            },

            navigateUp() {
                if (this.results.length === 0) return;
                this.selectedIndex = (this.selectedIndex - 1 + this.results.length) % this.results.length;
            },

            selectCurrent() {
                if (this.results.length > 0 && this.results[this.selectedIndex]) {
                    window.location.href = this.results[this.selectedIndex].url;
                } else if (this.seeAllUrl) {
                    window.location.href = this.seeAllUrl;
                }
            },

            getBadgeClass(badge) {
                switch(badge) {
                    case 'Artikel':
                        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300';
                    case 'Agenda':
                        return 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300';
                    case 'Pengurus':
                        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300';
                    case 'User':
                        return 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300';
                    default:
                        return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                }
            }
        };
    }
</script>
