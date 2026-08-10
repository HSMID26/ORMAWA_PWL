<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <a href="{{ route('posts.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 mb-1">
                    ← Kembali ke Daftar Artikel
                </a>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ $post->judul }}
                </h2>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('posts.edit', $post) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow-sm text-sm transition">
                    ✏️ Edit Artikel
                </a>
                <button type="button" onclick="confirmDelete('{{ route('posts.destroy', $post) }}', '{{ addslashes($post->judul) }}')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg shadow-sm text-sm transition">
                    🗑️ Hapus
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 sm:p-8">
                
                <!-- Metadata Section -->
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-6 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Penulis:</span>
                            {{ $post->user->name ?? 'Anonim' }}
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Organisasi:</span>
                            {{ $post->organization->nama ?? 'Pusat / Super Admin' }}
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Diterbitkan:</span>
                            {{ $post->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                    <div>
                        @if($post->status === 'published')
                            <span class="px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full">
                                Published
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold text-amber-700 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-full">
                                Draft
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Content Rendered HTML from TipTap -->
                <article class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200">
                    {!! $post->konten !!}
                </article>

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
