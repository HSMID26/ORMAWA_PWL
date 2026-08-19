<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Struktur Organisasi & Pengurus') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Form Input Pengurus Baru -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">➕ Tambah Pengurus Baru</h3>
                <form id="form-committee" class="grid grid-cols-1 md:grid-cols-3 gap-4" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Nama Lengkap</label>
                        <input type="text" id="name" required class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white" placeholder="Contoh: Ahmad Subagja">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Jabatan</label>
                        <input type="text" id="position" required class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white" placeholder="Contoh: Ketua Umum / Sekretaris">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Divisi / Departemen</label>
                        <input type="text" id="department" class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white" placeholder="Contoh: Medinfo (Opsional)">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Periode Kepengurusan</label>
                        <input type="text" id="period" required class="w-full p-2 border rounded-lg dark:bg-gray-900 dark:text-white" placeholder="Contoh: 2025/2026">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Foto Profil</label>
                        <input type="file" id="photo" accept="image/*" class="w-full p-1 border rounded-lg dark:bg-gray-900 dark:text-white text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow">
                            Simpan Pengurus
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filter Periode & Daftar Pengurus -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">👥 Daftar Pengurus</h3>
                    
                    <!-- Filter Periode -->
                    <form method="GET" action="{{ route('committees.index') }}" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()" class="p-2 border rounded-lg dark:bg-gray-900 dark:text-white text-sm">
                            <option value="">-- Semua Periode --</option>
                            @foreach($periods as $p)
                                <option value="{{ $p }}" {{ $selectedPeriod == $p ? 'selected' : '' }}>Periode {{ $p }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">Foto</th>
                                <th class="p-3">Nama</th>
                                <th class="p-3">Jabatan</th>
                                <th class="p-3">Divisi</th>
                                <th class="p-3">Periode</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($committees as $c)
                                <tr>
                                    <td class="p-3">
                                        @if($c->photo)
                                            <img src="{{ Storage::url($c->photo) }}" class="w-10 h-10 object-cover rounded-full">
                                        @else
                                            <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center font-bold text-gray-700 dark:text-white">
                                                {{ substr($c->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">{{ $c->name }}</td>
                                    <td class="p-3">{{ $c->position }}</td>
                                    <td class="p-3">{{ $c->department ?? '-' }}</td>
                                    <td class="p-3"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-200 text-xs">{{ $c->period }}</span></td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded {{ $c->status == 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ ucfirst($c->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button onclick="deleteCommittee({{ $c->id }})" class="text-rose-500 hover:text-rose-700 font-bold">🗑️ Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-gray-400">Belum ada data pengurus untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Submit Form Tambah Pengurus via Fetch API
        document.getElementById('form-committee').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);
            formData.append('position', document.getElementById('position').value);
            formData.append('department', document.getElementById('department').value);
            formData.append('period', document.getElementById('period').value);
            
            const photoInput = document.getElementById('photo');
            if(photoInput.files[0]) {
                formData.append('photo', photoInput.files[0]);
            }

            try {
                const res = await fetch('/committees', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const data = await res.json();
                
                if (res.ok) {
                    alert('Pengurus berhasil ditambahkan!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan server!');
            }
        });

        // Hapus Data Pengurus
        async function deleteCommittee(id) {
            if (!confirm('Yakin ingin menghapus pengurus ini?')) return;

            try {
                const res = await fetch(`/committees/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (res.ok) {
                    alert('Pengurus berhasil dihapus!');
                    window.location.reload();
                }
            } catch (err) {
                alert('Terjadi kesalahan!');
            }
        }
    </script>
</x-app-layout>