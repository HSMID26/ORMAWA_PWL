<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Backup & Restore Konten Organisasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Card 1: Ekspor Data (Backup) -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="p-3 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200 rounded-lg text-2xl">📦</span>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Ekspor Data Arsip</h3>
                                <p class="text-xs text-gray-400">Unduh seluruh arsip artikel, agenda, pengurus, & pengaturan.</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
                            Gunakan fitur ini sebelum terjadi pergantian kepengurusan tahunan untuk menyimpan backup data dalam format file JSON.
                        </p>
                    </div>

                    <a href="{{ route('backup.export') }}" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-center rounded-lg shadow flex items-center justify-center gap-2">
                        ⬇️ Unduh File Backup JSON
                    </a>
                </div>

                <!-- Card 2: Impor Data (Restore) -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="p-3 bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-200 rounded-lg text-2xl">📤</span>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Impor & Pulihkan Data</h3>
                                <p class="text-xs text-gray-400">Pulihkan arsip konten dari file JSON yang sudah diunduh.</p>
                            </div>
                        </div>
                        <form id="form-import" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold dark:text-gray-300 mb-2">Pilih File Backup (.json)</label>
                                <input type="file" id="backup_file" name="backup_file" accept=".json" required class="w-full text-xs p-2 border rounded dark:bg-gray-900 dark:text-white">
                            </div>

                            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow flex items-center justify-center gap-2">
                                ♻️ Impor & Sinkronkan Data
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        document.getElementById('form-import').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!confirm('Apakah kamu yakin ingin mengimpor data dari file ini? Data yang ada akan diperbarui.')) return;

            const formData = new FormData(e.target);

            try {
                const res = await fetch('{{ route("backup.import") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan server!');
            }
        });
    </script>
</x-app-layout>