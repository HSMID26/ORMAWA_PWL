<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Artikel') }}: {{ $post->judul }}
            </h2>
            <a href="{{ route('posts.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Batal / Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form id="post-form">
                    <!-- Judul Artikel -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Judul Artikel</label>
                        <input type="text" id="judul" value="{{ $post->judul }}" class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white" placeholder="Masukkan judul..." required>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Status Artikel</label>
                        <select id="status" class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white">
                            <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <!-- Toolbar TipTap -->
                    <div class="mb-2 flex gap-2 flex-wrap bg-gray-100 dark:bg-gray-700 p-2 rounded-t-lg">
                        <button type="button" id="btn-bold" class="px-3 py-1 bg-white dark:bg-gray-800 rounded shadow font-bold text-sm dark:text-white">B</button>
                        <button type="button" id="btn-italic" class="px-3 py-1 bg-white dark:bg-gray-800 rounded shadow italic text-sm dark:text-white">I</button>
                        <button type="button" id="btn-image" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded shadow text-sm font-semibold flex items-center gap-1 cursor-pointer">📷 Upload Gambar Baru</button>
                        <input type="file" id="image-input" class="hidden" accept="image/*">
                    </div>

                    <div id="upload-status" class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></div>

                    <div id="upload-preview" class="hidden mb-4 overflow-hidden rounded-lg border border-dashed border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pratinjau file</div>
                            <button type="button" id="clear-upload-preview" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300">Hapus</button>
                        </div>
                        <img id="upload-preview-image" alt="Pratinjau upload" class="hidden h-52 w-full object-cover">
                        <div id="upload-preview-meta" class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300"></div>
                    </div>

                    <!-- Area Editor TipTap -->
                    <div id="editor" class="min-h-[250px] p-4 border rounded-b-lg bg-white dark:bg-gray-900 dark:text-white prose dark:prose-invert max-w-none focus:outline-none mb-4"></div>

                    <!-- Button Submit -->
                    <div class="flex items-center gap-4">
                        <button type="submit" id="btn-submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow">
                            Perbarui Artikel
                        </button>
                        <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:underline">
                            Batal
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <style>
        #editor img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1rem 0;
            display: block;
        }
        #editor .ProseMirror:focus {
            outline: none;
        }
    </style>

    <!-- Script TipTap Integration via ESM CDN -->
    <script type="module">
        import { Editor } from 'https://esm.sh/@tiptap/core';
        import StarterKit from 'https://esm.sh/@tiptap/starter-kit';
        import Image from 'https://esm.sh/@tiptap/extension-image';

        // Konten artikel dari backend
        const initialContent = {!! json_encode($post->konten) !!};

        // Initialize TipTap dengan Konten yang sudah ada
        const editor = new Editor({
            element: document.querySelector('#editor'),
            extensions: [
                StarterKit,
                Image,
            ],
            content: initialContent,
        });

        // Event Formatting
        document.getElementById('btn-bold').addEventListener('click', () => editor.chain().focus().toggleBold().run());
        document.getElementById('btn-italic').addEventListener('click', () => editor.chain().focus().toggleItalic().run());

        // Event Upload Gambar Ke Backend
        const imageInput = document.getElementById('image-input');
        const btnImage = document.getElementById('btn-image');
        const uploadStatus = document.getElementById('upload-status');
        const uploadPreview = document.getElementById('upload-preview');
        const uploadPreviewImage = document.getElementById('upload-preview-image');
        const uploadPreviewMeta = document.getElementById('upload-preview-meta');
        const clearUploadPreviewButton = document.getElementById('clear-upload-preview');
        const maxImageSizeMb = 5;
        let previewObjectUrl = null;

        const formatFileSize = (bytes) => {
            const mb = bytes / (1024 * 1024);
            return mb >= 1 ? `${mb.toFixed(2)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;
        };

        const setUploadState = (message, variant = 'info') => {
            const styles = {
                info: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-200',
                error: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200',
                success: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
            };

            uploadStatus.className = `mb-3 rounded-lg border px-4 py-3 text-sm ${styles[variant]}`;
            uploadStatus.textContent = message;
            uploadStatus.classList.remove('hidden');
        };

        const clearUploadState = () => {
            uploadStatus.classList.add('hidden');
            uploadStatus.textContent = '';
        };

        const clearUploadPreview = () => {
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }

            uploadPreview.classList.add('hidden');
            uploadPreviewImage.classList.add('hidden');
            uploadPreviewImage.src = '';
            uploadPreviewMeta.textContent = '';
        };

        clearUploadPreviewButton.addEventListener('click', () => {
            imageInput.value = '';
            clearUploadState();
            clearUploadPreview();
        });

        btnImage.addEventListener('click', () => {
            imageInput.click();
        });

        imageInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            clearUploadState();
            clearUploadPreview();

            if (!file.type.startsWith('image/')) {
                setUploadState('File harus berupa gambar.', 'error');
                imageInput.value = '';
                return;
            }

            if (file.size > maxImageSizeMb * 1024 * 1024) {
                setUploadState(`Ukuran gambar maksimal ${maxImageSizeMb} MB. File ini ${formatFileSize(file.size)}.`, 'error');
                imageInput.value = '';
                return;
            }

            previewObjectUrl = URL.createObjectURL(file);
            uploadPreviewImage.src = previewObjectUrl;
            uploadPreviewImage.classList.remove('hidden');
            uploadPreviewMeta.textContent = `${file.name} • ${formatFileSize(file.size)} • akan dikompresi otomatis sebelum disimpan`;
            uploadPreview.classList.remove('hidden');

            const originalBtnText = btnImage.innerText;
            btnImage.innerText = '⏳ Mengunggah...';
            btnImage.disabled = true;
            setUploadState('Sedang mengompresi dan mengunggah gambar...', 'info');

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/upload-image', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.status === 'success') {
                    // Sisipkan Gambar Ke Dalam TipTap Editor!
                    editor.chain().focus().setImage({ src: data.url }).run();
                    setUploadState('Gambar berhasil diunggah dan dikompresi otomatis.', 'success');
                    clearUploadPreview();
                } else {
                    setUploadState(`Upload gambar gagal: ${data.message || 'Terjadi kesalahan'}`, 'error');
                }
            } catch (err) {
                console.error(err);
                setUploadState('Terjadi kesalahan saat upload. Coba lagi beberapa saat.', 'error');
            } finally {
                btnImage.innerText = originalBtnText;
                btnImage.disabled = false;
                imageInput.value = '';
            }
        });

        // Submit Form Update Artikel
        document.getElementById('post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSubmit = document.getElementById('btn-submit');
            btnSubmit.innerText = 'Memproses...';
            btnSubmit.disabled = true;

            const judul = document.getElementById('judul').value;
            const status = document.getElementById('status').value;
            const konten = editor.getHTML();

            if (!judul.trim()) {
                alert('Judul artikel wajib diisi.');
                return;
            }

            if (!konten || konten === '<p></p>') {
                alert('Konten artikel masih kosong.');
                return;
            }

            try {
                const response = await fetch('{{ route("posts.update", $post) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        judul: judul,
                        konten: konten,
                        status: status
                    })
                });

                if (response.ok) {
                    alert('Artikel berhasil diperbarui!');
                    window.location.href = '{{ route("posts.index") }}';
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    alert('Gagal memperbarui artikel: ' + (errorData.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memperbarui artikel!');
            } finally {
                btnSubmit.innerText = 'Perbarui Artikel';
                btnSubmit.disabled = false;
            }
        });
    </script>
</x-app-layout>
