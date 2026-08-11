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

        btnImage.addEventListener('click', () => {
            imageInput.click();
        });

        imageInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const originalBtnText = btnImage.innerText;
            btnImage.innerText = '⏳ Uploading...';
            btnImage.disabled = true;

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

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    // Sisipkan Gambar Ke Dalam TipTap Editor!
                    editor.chain().focus().setImage({ src: data.url }).run();
                } else {
                    alert('Upload gambar gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat upload!');
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
                    const errorData = await response.json();
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
