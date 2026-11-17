<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Daftar Artikel & Konten Ormawa') }}
            </h2>
            <a href="{{ route('posts.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm text-sm transition duration-150 flex items-center gap-2">
                <span>➕</span> Buat Artikel Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alert Flash Message -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-sm font-medium flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if($posts->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-5xl mb-4">📰</div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Belum ada artikel yang dibuat</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">Mulai bagikan kegiatan dan informasi organisasi Anda sekarang.</p>
                            <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium text-sm hover:bg-indigo-700">
                                Buat Artikel Pertama
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50">
                                        <th class="p-4">Artikel</th>
                                        <th class="p-4">Organisasi</th>
                                        <th class="p-4">Penulis</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4">Tanggal</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    @foreach($posts as $post)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                            <td class="p-4 font-medium text-gray-900 dark:text-white">
                                                <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-1 font-semibold">
                                                    {{ $post->judul }}
                                                </a>
                                                <span class="text-xs text-gray-400 font-mono block mt-0.5">/posts/{{ $post->slug }}</span>
                                            </td>
                                            <td class="p-4 text-gray-600 dark:text-gray-300">
                                                {{ $post->organization->nama ?? 'Pusat / Super Admin' }}
                                            </td>
                                            <td class="p-4 text-gray-600 dark:text-gray-300">
                                                {{ $post->user->name ?? 'Anonim' }}
                                            </td>
                                            <td class="p-4">
                                                @if($post->status === 'published')
                                                    <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full">
                                                        Published
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-full">
                                                        Draft
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-4 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $post->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="p-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <!-- Detail -->
                                                    <a href="{{ route('posts.show', $post) }}" title="Lihat Detail" class="p-1.5 text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400 transition">
                                                        👁️
                                                    </a>
                                                    
                                                    <!-- Edit -->
                                                    <a href="{{ route('posts.edit', $post) }}" title="Edit Artikel" class="p-1.5 text-gray-600 hover:text-amber-600 dark:text-gray-300 dark:hover:text-amber-400 transition">
                                                        ✏️
                                                    </a>

                                                    <!-- Hapus -->
                                                    <button type="button" onclick="confirmDelete('{{ route('posts.destroy', $post) }}', '{{ addslashes($post->judul) }}')" title="Hapus Artikel" class="p-1.5 text-gray-600 hover:text-rose-600 dark:text-gray-300 dark:hover:text-rose-400 transition">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Delete Request -->
    <form id="delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function confirmDelete(url, title) {
            if (confirm(`Apakah Anda yakin ingin menghapus artikel "${title}"?`)) {
                const form = document.getElementById('delete-form');
                form.action = url;
                form.submit();
            }
        }
    </script>
</x-app-layout>
