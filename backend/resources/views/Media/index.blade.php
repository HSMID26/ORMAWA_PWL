<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Galeri & Manajemen Media Ormawa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Direct Upload Box -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Upload Media Baru</h3>
                <form id="upload-form" class="flex gap-4 items-center">
                    @csrf
                    <input type="file" id="media-file" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" accept="image/*,video/*" required>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow whitespace-nowrap">
                        Upload
                    </button>
                </form>
            </div>

            <!-- Grid Galeri Media -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Daftar Berkas Media</h3>
                
                @if($mediaList->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">Belum ada media yang diunggah.</p>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @foreach($mediaList as $media)
                            <div class="relative group border dark:border-gray-700 rounded-lg overflow-hidden bg-gray-900">
                                @if(str_starts_with($media->mime_type ?? '', 'video/'))
                                    <video src="{{ $media->url }}" class="w-full h-36 object-cover" controls></video>
                                @else
                                    <img src="{{ $media->url }}" alt="{{ $media->filename }}" class="w-full h-36 object-cover">
                                @endif
                                
                                <div class="p-2 text-xs text-gray-300 truncate">
                                    {{ $media->filename }}
                                </div>

                                <!-- Overlay Hover Buttons -->
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button onclick="navigator.clipboard.writeText('{{ $media->url }}'); alert('URL disalin!')" class="p-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs" title="Salin Link">
                                        📋 Copy Link
                                    </button>
                                    <button onclick="deleteMedia({{ $media->id }})" class="p-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs" title="Hapus Media">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        // Upload File Handler
        document.getElementById('upload-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fileInput = document.getElementById('media-file');
            if(!fileInput.files[0]) return;

            const formData = new FormData();
            formData.append('image', fileInput.files[0]);

            const res = await fetch('/api/upload-image', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });

            if(res.ok) {
                alert('Media berhasil diunggah!');
                window.location.reload();
            } else {
                alert('Gagal unggah media!');
            }
        });

        // Delete File Handler
        async function deleteMedia(id) {
            if(!confirm('Yakin ingin menghapus media ini?')) return;

            const res = await fetch(`/api/media/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if(res.ok) {
                alert('Media berhasil dihapus!');
                window.location.reload();
            } else {
                alert('Gagal menghapus media!');
            }
        }
    </script>
</x-app-layout>