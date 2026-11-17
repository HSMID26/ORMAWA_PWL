<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    🖼️ {{ __('Media Library & Galeri Ormawa') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kelola seluruh aset gambar dan video untuk konten artikel, agenda, dan portal publik.
                </p>
            </div>
            <button 
                type="button" 
                onclick="document.getElementById('file-input').click()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Unggah Media Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- 1. STATS METRIC CARDS -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Berkas -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-3.5">
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-xl text-xl">
                        📁
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Berkas</div>
                        <div class="text-xl font-black text-gray-800 dark:text-gray-100">{{ number_format($totalFiles) }}</div>
                    </div>
                </div>

                <!-- Storage Terpakai -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-3.5">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-xl text-xl">
                        💾
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Storage Terpakai</div>
                        <div class="text-xl font-black text-gray-800 dark:text-gray-100">{{ $totalStorageFormatted }}</div>
                    </div>
                </div>

                <!-- Gambar -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-3.5">
                    <div class="p-3 bg-pink-50 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400 rounded-xl text-xl">
                        📸
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Berkas Gambar</div>
                        <div class="text-xl font-black text-gray-800 dark:text-gray-100">{{ number_format($imagesCount) }}</div>
                    </div>
                </div>

                <!-- Video -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-3.5">
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 rounded-xl text-xl">
                        🎥
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Berkas Video</div>
                        <div class="text-xl font-black text-gray-800 dark:text-gray-100">{{ number_format($videosCount) }}</div>
                    </div>
                </div>
            </div>

            <!-- 2. DRAG & DROP MULTI-FILE UPLOAD ZONE -->
            <div 
                id="drop-zone"
                class="bg-white dark:bg-gray-800 border-2 border-dashed border-indigo-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 rounded-2xl p-8 text-center transition cursor-pointer relative group"
                onclick="document.getElementById('file-input').click()"
            >
                <input 
                    type="file" 
                    id="file-input" 
                    class="hidden" 
                    multiple 
                    accept="image/*,video/*"
                    onchange="handleFilesUpload(this.files)"
                >

                <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center text-2xl group-hover:scale-110 transition">
                        ☁️
                    </div>
                    <div class="font-bold text-gray-800 dark:text-gray-100 text-sm">
                        Seret & Tarik berkas gambar/video ke sini, atau <span class="text-indigo-600 dark:text-indigo-400 underline">pilih dari perangkat</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Mendukung WebP, PNG, JPG, GIF, SVG, dan MP4 (Kompresi otomatis ke WebP hemat storage).
                    </p>
                </div>

                <!-- Upload Progress Overlay -->
                <div id="upload-progress-container" class="hidden mt-4 max-w-md mx-auto">
                    <div class="flex justify-between text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">
                        <span id="upload-status-text">Mengunggah berkas...</span>
                        <span id="upload-percentage">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div id="upload-progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- 3. SEARCH & FILTER TOOLBAR -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
                <!-- Search Form -->
                <form method="GET" action="{{ route('media.index') }}" class="flex-1 max-w-md relative">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $search }}"
                        placeholder="Cari berkas media..." 
                        class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    >
                    <span class="absolute left-3 top-2.5 text-gray-400 text-xs">🔍</span>
                </form>

                <!-- Filter Type Buttons -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <a 
                        href="{{ route('media.index', ['type' => 'all', 'sort' => $sort, 'q' => $search]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $type === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        Semua
                    </a>
                    <a 
                        href="{{ route('media.index', ['type' => 'image', 'sort' => $sort, 'q' => $search]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $type === 'image' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        📸 Gambar ({{ $imagesCount }})
                    </a>
                    <a 
                        href="{{ route('media.index', ['type' => 'video', 'sort' => $sort, 'q' => $search]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $type === 'video' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        🎥 Video ({{ $videosCount }})
                    </a>

                    <!-- Sort Select -->
                    <div class="ml-2 border-l border-gray-200 dark:border-gray-700 pl-3">
                        <select 
                            onchange="location.href=this.value"
                            class="text-xs border border-gray-200 dark:border-gray-700 rounded-lg py-1.5 px-2 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="{{ route('media.index', ['sort' => 'latest', 'type' => $type, 'q' => $search]) }}" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="{{ route('media.index', ['sort' => 'oldest', 'type' => $type, 'q' => $search]) }}" {{ $sort === 'oldest' ? 'selected' : '' }}>Terlama</option>
                            <option value="{{ route('media.index', ['sort' => 'size_desc', 'type' => $type, 'q' => $search]) }}" {{ $sort === 'size_desc' ? 'selected' : '' }}>Ukuran Terbesar</option>
                            <option value="{{ route('media.index', ['sort' => 'size_asc', 'type' => $type, 'q' => $search]) }}" {{ $sort === 'size_asc' ? 'selected' : '' }}>Ukuran Terkecil</option>
                            <option value="{{ route('media.index', ['sort' => 'name_asc', 'type' => $type, 'q' => $search]) }}" {{ $sort === 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 4. MEDIA GRID GALLERY -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                @if($mediaList->isEmpty())
                    <div class="py-16 text-center text-gray-400">
                        <div class="text-4xl mb-3">🖼️</div>
                        <p class="font-bold text-gray-600 dark:text-gray-300 text-sm">Tidak ada berkas media ditemukan</p>
                        <p class="text-xs text-gray-400 mt-1">Unggah berkas pertama Anda atau ubah kata kunci pencarian.</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($mediaList as $media)
                            <div 
                                class="group relative bg-gray-50 dark:bg-gray-900 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition cursor-pointer"
                                onclick="openMediaDetailModal({{ json_encode($media) }})"
                            >
                                <!-- Thumbnail Media -->
                                <div class="aspect-square w-full relative bg-gray-200 dark:bg-gray-950 flex items-center justify-center overflow-hidden">
                                    @if($media->is_video)
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/40 z-10">
                                            <span class="w-8 h-8 rounded-full bg-black/70 text-white flex items-center justify-center text-sm">▶</span>
                                        </div>
                                        <video src="{{ $media->url }}" class="w-full h-full object-cover"></video>
                                    @else
                                        <img 
                                            src="{{ $media->url }}" 
                                            alt="{{ $media->filename }}" 
                                            loading="lazy" 
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                        >
                                    @endif

                                    <!-- Format Badge -->
                                    <div class="absolute bottom-1.5 left-1.5 z-10 px-1.5 py-0.5 rounded bg-black/70 text-[9px] font-mono text-white uppercase tracking-wider backdrop-blur-xs">
                                        {{ pathinfo($media->filename, PATHINFO_EXTENSION) ?: 'file' }}
                                    </div>

                                    <!-- Size Badge -->
                                    <div class="absolute bottom-1.5 right-1.5 z-10 px-1.5 py-0.5 rounded bg-black/70 text-[9px] font-mono text-white backdrop-blur-xs">
                                        {{ $media->formatted_size }}
                                    </div>
                                </div>

                                <!-- Caption / Info -->
                                <div class="p-2.5">
                                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $media->filename }}">
                                        {{ $media->filename }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 truncate mt-0.5">
                                        {{ $media->created_at ? $media->created_at->diffForHumans() : '-' }}
                                    </div>
                                </div>

                                <!-- Hover Actions Overlay -->
                                <div class="absolute inset-0 bg-indigo-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2 z-20">
                                    <button 
                                        type="button"
                                        onclick="event.stopPropagation(); copyToClipboard('{{ $media->url }}', 'URL Gambar disalin!')"
                                        class="p-2 bg-white/90 hover:bg-white text-gray-800 rounded-lg text-xs shadow hover:scale-105 transition"
                                        title="Salin URL"
                                    >
                                        📋
                                    </button>
                                    <button 
                                        type="button"
                                        onclick="event.stopPropagation(); deleteMedia({{ $media->id }}, '{{ addslashes($media->filename) }}')"
                                        class="p-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs shadow hover:scale-105 transition"
                                        title="Hapus Media"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $mediaList->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- 5. MEDIA DETAIL INSPECTOR MODAL -->
    <div id="media-detail-modal" class="fixed inset-0 z-50 bg-gray-950/75 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-4xl w-full overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row max-h-[90vh]">
            
            <!-- Preview Column -->
            <div class="md:w-3/5 bg-gray-950 flex items-center justify-center p-4 relative min-h-[300px]">
                <div id="modal-preview-container" class="w-full h-full flex items-center justify-center">
                    <!-- Dynamic Image / Video injected here -->
                </div>
            </div>

            <!-- Metadata & Actions Column -->
            <div class="md:w-2/5 p-6 flex flex-col justify-between overflow-y-auto">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            Detail Berkas Media
                        </h3>
                        <button onclick="closeMediaDetailModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold">
                            &times;
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block mb-0.5">Nama Berkas:</span>
                            <span id="modal-filename" class="font-semibold text-gray-800 dark:text-gray-200 break-all"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-gray-400 block mb-0.5">Tipe MIME:</span>
                                <span id="modal-mimetype" class="font-mono text-gray-700 dark:text-gray-300"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block mb-0.5">Ukuran:</span>
                                <span id="modal-size" class="font-semibold text-emerald-600 dark:text-emerald-400"></span>
                            </div>
                        </div>

                        <div>
                            <span class="text-gray-400 block mb-0.5">Pengunggah:</span>
                            <span id="modal-uploader" class="text-gray-700 dark:text-gray-300"></span>
                        </div>

                        <div>
                            <span class="text-gray-400 block mb-0.5">URL Berkas:</span>
                            <input 
                                type="text" 
                                id="modal-url-input" 
                                readonly 
                                class="w-full text-[11px] p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-500 font-mono"
                            >
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
                    <button 
                        type="button" 
                        onclick="copyModalUrl()" 
                        class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 shadow-xs transition"
                    >
                        📋 Salin URL Berkas
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            onclick="copyModalHtml()" 
                            class="py-2 px-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition text-center"
                            title="Salin tag <img>"
                        >
                            &lt;img&gt; Tag HTML
                        </button>
                        <button 
                            type="button" 
                            onclick="copyModalMarkdown()" 
                            class="py-2 px-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition text-center"
                            title="Salin syntax Markdown"
                        >
                            Markdown ![]()
                        </button>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a 
                            id="modal-open-tab" 
                            href="#" 
                            target="_blank" 
                            class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                        >
                            <span>↗ Buka Tab Baru</span>
                        </a>
                        <button 
                            type="button" 
                            onclick="deleteCurrentModalMedia()" 
                            class="text-xs text-rose-600 hover:text-rose-700 font-semibold"
                        >
                            🗑️ Hapus Berkas
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        let currentModalMedia = null;

        // 1. Drag and Drop Handler
        const dropZone = document.getElementById('drop-zone');
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.add('border-indigo-600', 'bg-indigo-50/20');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-indigo-600', 'bg-indigo-50/20');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFilesUpload(files);
        });

        // 2. Multi-file Upload via AJAX
        async function handleFilesUpload(files) {
            if (!files || files.length === 0) return;

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            const progressContainer = document.getElementById('upload-progress-container');
            const progressBar = document.getElementById('upload-progress-bar');
            const percentageText = document.getElementById('upload-percentage');
            const statusText = document.getElementById('upload-status-text');

            progressContainer.classList.remove('hidden');
            progressBar.style.width = '20%';
            percentageText.innerText = '20%';
            statusText.innerText = `Mengunggah ${files.length} berkas...`;

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("media.upload") }}', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        percentageText.innerText = percent + '%';
                    }
                };

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        progressBar.style.width = '100%';
                        percentageText.innerText = '100%';
                        statusText.innerText = 'Selesai! Memperbarui galeri...';
                        setTimeout(() => {
                            window.location.reload();
                        }, 600);
                    } else {
                        alert('Terjadi kesalahan saat mengunggah media.');
                        progressContainer.classList.add('hidden');
                    }
                };

                xhr.onerror = function() {
                    alert('Gagal mengunggah media. Periksa koneksi Anda.');
                    progressContainer.classList.add('hidden');
                };

                xhr.send(formData);
            } catch (err) {
                console.error(err);
                alert('Gagal mengunggah berkas.');
                progressContainer.classList.add('hidden');
            }
        }

        // 3. Modal Inspector
        function openMediaDetailModal(media) {
            currentModalMedia = media;
            const modal = document.getElementById('media-detail-modal');
            const previewContainer = document.getElementById('modal-preview-container');

            if (media.is_video) {
                previewContainer.innerHTML = `<video src="${media.url}" class="max-h-[70vh] max-w-full rounded-lg shadow" controls autoplay muted></video>`;
            } else {
                previewContainer.innerHTML = `<img src="${media.url}" alt="${media.filename}" class="max-h-[70vh] max-w-full object-contain rounded-lg shadow">`;
            }

            document.getElementById('modal-filename').innerText = media.filename;
            document.getElementById('modal-mimetype').innerText = media.mime_type || 'image/webp';
            document.getElementById('modal-size').innerText = media.formatted_size || '-';
            document.getElementById('modal-uploader').innerText = (media.user ? media.user.name : 'System');
            document.getElementById('modal-url-input').value = media.url;
            document.getElementById('modal-open-tab').href = media.url;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeMediaDetailModal() {
            const modal = document.getElementById('media-detail-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('modal-preview-container').innerHTML = '';
            currentModalMedia = null;
        }

        function copyModalUrl() {
            if (!currentModalMedia) return;
            copyToClipboard(currentModalMedia.url, 'URL media disalin!');
        }

        function copyModalHtml() {
            if (!currentModalMedia) return;
            const html = `<img src="${currentModalMedia.url}" alt="${currentModalMedia.filename}">`;
            copyToClipboard(html, 'Tag HTML <img> disalin!');
        }

        function copyModalMarkdown() {
            if (!currentModalMedia) return;
            const md = `![${currentModalMedia.filename}](${currentModalMedia.url})`;
            copyToClipboard(md, 'Syntax Markdown disalin!');
        }

        function deleteCurrentModalMedia() {
            if (!currentModalMedia) return;
            deleteMedia(currentModalMedia.id, currentModalMedia.filename);
        }

        // 4. Delete Media
        async function deleteMedia(id, filename) {
            if (!confirm(`Apakah Anda yakin ingin menghapus berkas "${filename}"?`)) return;

            try {
                const res = await fetch(`/media/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                if (res.ok) {
                    window.location.reload();
                } else {
                    alert('Gagal menghapus berkas media.');
                }
            } catch (err) {
                alert('Terjadi kesalahan saat menghapus media.');
            }
        }

        // 5. Utility Copy to Clipboard
        function copyToClipboard(text, message) {
            navigator.clipboard.writeText(text).then(() => {
                alert(message);
            }).catch(() => {
                prompt('Salin teks secara manual:', text);
            });
        }
    </script>
</x-app-layout>
