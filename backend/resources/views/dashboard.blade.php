<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-xl font-bold">Selamat Datang, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Anda terhubung ke sistem manajemen konten Ormawa.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('posts.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg text-sm transition">
                        📰 Kelola Artikel
                    </a>
                    <a href="{{ route('posts.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">
                        ➕ Buat Artikel Baru
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
