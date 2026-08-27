@php
    $user = Auth::user();
    $org = $user?->organization;
    if (!$org && $user?->hasRole('Super Admin')) {
        $org = \App\Models\Organization::first();
    }
@endphp

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-40">
    <!-- Primary Navigation Menu (Full Width Responsive) -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- Left Side: Logo & Main Navigation Links -->
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-2 mr-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        @if($org && $org->logo && Storage::disk('public')->exists($org->logo))
                            <img src="{{ Storage::url($org->logo) }}" alt="Logo" class="h-8 w-auto rounded object-contain">
                        @else
                            <x-application-logo class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
                        @endif
                        @if($org)
                            <span class="font-bold text-xs lg:text-sm text-gray-800 dark:text-gray-200 hidden sm:inline truncate max-w-[160px]">{{ $org->nama }}</span>
                        @endif
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden lg:flex space-x-3 xl:space-x-6 sm:-my-px h-16">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Menu Pengajuan Ormawa (Khusus Super Admin) -->
                    @if($user && $user->hasRole('Super Admin'))
                        <x-nav-link :href="route('superadmin.pending.index')" :active="request()->routeIs('superadmin.pending.*')">
                            {{ __('Pengajuan') }}
                        </x-nav-link>
                    @endif

                    <!-- Modul Artikel / Konten -->
                    @if(!$org || $org->isModuleActive('posts'))
                        <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">
                            {{ $org ? $org->getMenuLabel('posts', 'Artikel') : __('Artikel') }}
                        </x-nav-link>
                    @endif

                    <!-- Modul Agenda Kegiatan -->
                    @if(!$org || $org->isModuleActive('activities'))
                        <x-nav-link :href="route('activities.index')" :active="request()->routeIs('activities.*')">
                            {{ $org ? $org->getMenuLabel('activities', 'Agenda') : __('Agenda') }}
                        </x-nav-link>
                    @endif

                    <!-- Modul Media Galeri -->
                    @if(!$org || $org->isModuleActive('media'))
                        <x-nav-link :href="route('media.index')" :active="request()->routeIs('media.*')">
                            {{ $org ? $org->getMenuLabel('media', 'Galeri') : __('Galeri') }}
                        </x-nav-link>
                    @endif

                    <!-- Modul Kelola Pengurus -->
                    @if(!$org || $org->isModuleActive('committees'))
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ $org ? $org->getMenuLabel('committees', 'Pengurus') : __('Pengurus') }}
                        </x-nav-link>
                    @endif

                    <!-- Fitur 11: Riwayat Aktivitas & Audit Trail -->
                    @if($user && $user->hasRole(['Super Admin', 'Admin Organisasi']))
                        <x-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">
                            {{ __('Log Aktivitas') }}
                        </x-nav-link>
                    @endif

                    <!-- Fitur 13: Statistik Pengunjung & Traffic Analytics -->
                    <x-nav-link :href="route('analytics.index')" :active="request()->routeIs('analytics.*')">
                        {{ __('Statistik') }}
                    </x-nav-link>

                    <!-- Fitur 10: Pengaturan Organisasi & Modul -->
                    <x-nav-link :href="route('organization.settings')" :active="request()->routeIs('organization.settings')">
                        {{ __('Pengaturan') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Right Side: Global Search Trigger & User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:gap-3 shrink-0 ml-4">
                <!-- Global Search Trigger Button (Pill Style) -->
                <button 
                    type="button"
                    @click="$dispatch('open-global-search')"
                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 transition cursor-pointer shadow-xs"
                    title="Pencarian Global (Ctrl+K)"
                >
                    <span>🔍</span>
                    <span class="hidden md:inline text-xs font-medium">Cari...</span>
                    <kbd class="hidden xl:inline-block px-1.5 py-0.5 text-[10px] font-mono bg-white dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-600 rounded">
                        Ctrl K
                    </kbd>
                </button>

                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-xs font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <button 
                            type="button"
                            @click="$dispatch('open-global-search')"
                            class="block w-full px-4 py-2 text-start text-xs leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition"
                        >
                            🔍 {{ __('Pencarian Global (Ctrl+K)') }}
                        </button>

                        <x-dropdown-link :href="route('analytics.index')">
                            📊 {{ __('Statistik Pengunjung') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('organization.settings')">
                            ⚙️ {{ __('Pengaturan Ormawa') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.edit')">
                            👤 {{ __('Profile Akun') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                🚪 {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button 
                    type="button"
                    @click="$dispatch('open-global-search')"
                    class="p-2 mr-1 text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    title="Cari"
                >
                    🔍
                </button>

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 dark:border-gray-700">
        <div class="pt-2 pb-3 space-y-1">
            <button 
                type="button"
                @click="$dispatch('open-global-search'); open = false;"
                class="w-full flex items-center justify-between px-4 py-2.5 text-start text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <span class="flex items-center gap-2">🔍 {{ __('Pencarian Global') }}</span>
                <span class="text-xs bg-gray-100 dark:bg-gray-800 border px-1.5 py-0.5 rounded text-gray-400">Ctrl+K</span>
            </button>

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if($user && $user->hasRole('Super Admin'))
                <x-responsive-nav-link :href="route('superadmin.pending.index')" :active="request()->routeIs('superadmin.pending.*')">
                    📋 {{ __('Pengajuan Ormawa') }}
                </x-responsive-nav-link>
            @endif

            @if(!$org || $org->isModuleActive('posts'))
                <x-responsive-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">
                    📝 {{ $org ? $org->getMenuLabel('posts', 'Artikel / Konten') : __('Artikel / Konten') }}
                </x-responsive-nav-link>
            @endif

            @if(!$org || $org->isModuleActive('activities'))
                <x-responsive-nav-link :href="route('activities.index')" :active="request()->routeIs('activities.*')">
                    📅 {{ $org ? $org->getMenuLabel('activities', 'Agenda Kegiatan') : __('Agenda Kegiatan') }}
                </x-responsive-nav-link>
            @endif

            @if(!$org || $org->isModuleActive('media'))
                <x-responsive-nav-link :href="route('media.index')" :active="request()->routeIs('media.*')">
                    🖼️ {{ $org ? $org->getMenuLabel('media', 'Galeri Media') : __('Galeri Media') }}
                </x-responsive-nav-link>
            @endif

            @if(!$org || $org->isModuleActive('committees'))
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    👥 {{ $org ? $org->getMenuLabel('committees', 'Kelola Pengurus') : __('Kelola Pengurus') }}
                </x-responsive-nav-link>
            @endif

            @if($user && $user->hasRole(['Super Admin', 'Admin Organisasi']))
                <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">
                    📜 {{ __('Riwayat Aktivitas') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('analytics.index')" :active="request()->routeIs('analytics.*')">
                📊 {{ __('Statistik Pengunjung') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('organization.settings')" :active="request()->routeIs('organization.settings')">
                ⚙️ {{ __('Pengaturan Ormawa') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    👤 {{ __('Profile Akun') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        🚪 {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>