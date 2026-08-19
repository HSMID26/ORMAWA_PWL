<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Artikel / Konten Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form id="post-form">
                    @csrf
                    <!-- Judul Artikel -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Judul Artikel</label>
                        <input type="text" id="judul" class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white" placeholder="Masukkan judul..." required>
                    </div>

                    <!-- 🚀 Dropdown Kategori & Checkbox Tag -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Kategori -->
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Kategori Konten</label>
                            <select id="category_id" class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white">
                                <option value="">-- Pilih Kategori --</option>
                            </select>
                        </div>

                        <!-- Tag Pilihan -->
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Tag / Pengelompokan</label>
                            <div id="tags-container" class="flex flex-wrap gap-2 p-2 border rounded-lg dark:bg-gray-900 min-h-[42px] items-center">
                                <span class="text-xs text-gray-400">Memuat tag...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar TipTap -->
                    <div class="mb-2 flex gap-2 flex-wrap bg-gray-100 dark:bg-gray-700 p-2 rounded-t-lg">
                        <button type="button" id="btn-bold" class="px-3 py-1 bg-white dark:bg-gray-800 rounded shadow font-bold text-sm dark:text-white">B</button>
                        <button type="button" id="btn-italic" class="px-3 py-1 bg-white dark:bg-gray-800 rounded shadow italic text-sm dark:text-white">I</button>
                        
                        <!-- Upload Gambar via Label -->
                        <label for="image-input" id="lbl-image" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded shadow text-sm font-semibold flex items-center gap-1 cursor-pointer">
                            📷 Upload Gambar
                        </label>
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
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow">
                        Simpan Artikel
                    </button>
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

        // Initialize TipTap
        const editor = new Editor({
            element: document.querySelector('#editor'),
            extensions: [
                StarterKit,
                Image,
            ],
            content: '<p>Ketik konten artikel ormawa kamu di sini...</p>',
        });

        // Fetch Categories & Tags dari Backend saat halaman di-load
        document.addEventListener('DOMContentLoaded', () => {
            fetchCategories();
            fetchTags();
        });

        async function fetchCategories() {
            try {
                const res = await fetch('/categories');
                const data = await res.json();
                const select = document.getElementById('category_id');
                if (data.status === 'success') {
                    select.innerHTML = '<option value="">-- Pilih Kategori --</option>';
                    data.data.forEach(cat => {
                        select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
                    });
                }
            } catch (err) {
                console.error('Gagal memuat kategori:', err);
            }
        }

        async function fetchTags() {
            try {
                const res = await fetch('/tags');
                const data = await res.json();
                const container = document.getElementById('tags-container');
                if (data.status === 'success') {
                    container.innerHTML = '';
                    if (data.data.length === 0) {
                        container.innerHTML = '<span class="text-xs text-gray-400">Belum ada tag</span>';
                        return;
                    }
                    data.data.forEach(tag => {
                        container.innerHTML += `
                            <label class="inline-flex items-center gap-1 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded text-xs cursor-pointer dark:text-white">
                                <input type="checkbox" name="tags[]" value="${tag.id}" class="rounded text-blue-600">
                                ${tag.name}
                            </label>
                        `;
                    });
                }
            } catch (err) {
                console.error('Gagal memuat tag:', err);
            }
        }

        // Event Formatting
        document.getElementById('btn-bold').addEventListener('click', () => editor.chain().focus().toggleBold().run());
        document.getElementById('btn-italic').addEventListener('click', () => editor.chain().focus().toggleItalic().run());

        // Event Upload Gambar Ke Backend
        const imageInput = document.getElementById('image-input');
        const lblImage = document.getElementById('lbl-image');
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
                setUploadState(`Ukuran gambar maksimal ${maxImageSizeMb} MB.`, 'error');
                imageInput.value = '';
                return;
            }

            previewObjectUrl = URL.createObjectURL(file);
            uploadPreviewImage.src = previewObjectUrl;
            uploadPreviewImage.classList.remove('hidden');
            uploadPreviewMeta.textContent = `${file.name} • ${formatFileSize(file.size)} • akan dikompresi otomatis sebelum disimpan`;
            uploadPreview.classList.remove('hidden');

            const originalBtnText = lblImage.innerHTML;
            lblImage.innerHTML = '⏳ Mengunggah...';
            lblImage.classList.add('opacity-80', 'pointer-events-none');
            setUploadState('Sedang mengompresi dan mengunggah gambar...', 'info');

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/upload-image', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.status === 'success') {
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
                lblImage.innerHTML = originalBtnText;
                lblImage.classList.remove('opacity-80', 'pointer-events-none');
                imageInput.value = '';
            }
        });

        // Submit Form Artikel ke Backend (Include Kategori & Tag)
        document.getElementById('post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const judul = document.getElementById('judul').value;
            const category_id = document.getElementById('category_id').value;
            const konten = editor.getHTML();

            // Ambil ID tag yang di-check
            const selectedTags = Array.from(document.querySelectorAll('input[name="tags[]"]:checked'))
                                      .map(cb => parseInt(cb.value));

            if (!judul.trim()) {
                alert('Judul artikel wajib diisi.');
                return;
            }

            if (!konten || konten === '<p></p>') {
                alert('Konten artikel masih kosong.');
                return;
            }

            try {
                const response = await fetch('/posts-store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        judul: judul,
                        category_id: category_id || null,
                        tags: selectedTags,
                        konten: konten,
                        status: 'published'
                    })
                });

                if (response.ok) {
                    alert('Artikel berhasil disimpan!');
                    window.location.href = '/posts';
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    alert('Gagal menyimpan artikel: ' + (errorData.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan artikel!');
            }
        });
    </script>
</x-app-layout>