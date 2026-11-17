<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Manajemen Agenda & Kegiatan') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola jadwal kegiatan Ormawa & sinkronisasi otomatis ke kalender publik.</p>
            </div>
            <!-- Link Feed iCal untuk Kalender Publik -->
            <div class="flex items-center gap-2">
                <a href="{{ route('calendar.ics') }}" target="_blank" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg shadow flex items-center gap-2 transition duration-150">
                    📅 Feed iCal (Sinkronisasi Google/Apple Calendar)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Form Input / Edit Agenda Baru -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="form-title" class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        ➕ Tambah Agenda Kegiatan Baru
                    </h3>
                    <button type="button" id="btn-cancel-edit" onclick="resetForm()" class="hidden px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded-md">
                        Batal Edit
                    </button>
                </div>

                <form id="form-activity" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <input type="hidden" id="activity_id" value="">

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Judul Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" id="title" required class="w-full p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500" placeholder="Contoh: Workshop Web Development 2026">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
                        <input type="datetime-local" id="start_time" required class="w-full p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Waktu Selesai</label>
                        <input type="datetime-local" id="end_time" class="w-full p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Lokasi / Link Meeting</label>
                        <input type="text" id="location" class="w-full p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500" placeholder="Contoh: Auditoriom Utama / Zoom Meeting">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Status Kegiatan</label>
                        <select id="status" class="w-full p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="upcoming">Upcoming (Akan Datang)</option>
                            <option value="ongoing">Ongoing (Berlangsung)</option>
                            <option value="completed">Completed (Selesai)</option>
                            <option value="cancelled">Cancelled (Dibatalkan)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Deskripsi Ringkas</label>
                        <textarea id="description" rows="3" class="w-full p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500" placeholder="Penjelasan rincian agenda kegiatan..."></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" id="btn-submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition duration-150 flex justify-center items-center gap-2">
                            💾 Simpan Agenda Kegiatan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Agenda -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    📋 Daftar Agenda Kegiatan
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">Kegiatan</th>
                                <th class="p-3">Lokasi</th>
                                <th class="p-3">Waktu Mulai</th>
                                <th class="p-3">Waktu Selesai</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($activities as $act)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $act->title }}
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ Str::limit($act->description, 60) ?: '-' }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center gap-1 text-xs">
                                            📍 {{ $act->location ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs whitespace-nowrap">
                                        {{ $act->start_time ? $act->start_time->format('d M Y, H:i') : ($act->tanggal_pelaksanaan ? $act->tanggal_pelaksanaan->format('d M Y, H:i') : '-') }}
                                    </td>
                                    <td class="p-3 text-xs whitespace-nowrap">
                                        {{ $act->end_time ? $act->end_time->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="p-3">
                                        @php
                                            $badges = [
                                                'upcoming'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                'ongoing'   => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                                'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                                'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
                                                'published' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                                'draft'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-1 text-xs rounded-full font-semibold {{ $badges[$act->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($act->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" onclick="editActivity({{ json_encode($act) }})" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded transition">
                                                ✏️ Edit
                                            </button>
                                            <button type="button" onclick="deleteActivity({{ $act->id }})" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded transition">
                                                🗑️ Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="text-3xl mb-2">📅</div>
                                        Belum ada agenda kegiatan yang dicatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Form Submit Handler (Tambah & Edit Agenda)
        document.getElementById('form-activity').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('activity_id').value;
            const isEdit = Boolean(id);
            const url = isEdit ? `/activities/${id}` : '/activities';
            const method = isEdit ? 'PUT' : 'POST';

            const payload = {
                title: document.getElementById('title').value,
                start_time: document.getElementById('start_time').value,
                end_time: document.getElementById('end_time').value || null,
                location: document.getElementById('location').value || null,
                status: document.getElementById('status').value,
                description: document.getElementById('description').value || null,
            };

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (res.ok) {
                    alert(data.message || 'Agenda kegiatan berhasil disimpan!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan server!');
            }
        });

        // Edit Agenda: Isi form dengan data yang dipilih
        function editActivity(act) {
            document.getElementById('activity_id').value = act.id;
            document.getElementById('title').value = act.title || act.judul || '';
            document.getElementById('location').value = act.location || '';
            document.getElementById('status').value = act.status || 'upcoming';
            document.getElementById('description').value = act.description || act.deskripsi || '';

            if (act.start_time) {
                document.getElementById('start_time').value = formatDatetimeLocal(act.start_time);
            } else if (act.tanggal_pelaksanaan) {
                document.getElementById('start_time').value = formatDatetimeLocal(act.tanggal_pelaksanaan);
            }

            if (act.end_time) {
                document.getElementById('end_time').value = formatDatetimeLocal(act.end_time);
            } else {
                document.getElementById('end_time').value = '';
            }

            document.getElementById('form-title').innerHTML = '✏️ Edit Agenda Kegiatan';
            document.getElementById('btn-submit').innerHTML = '💾 Perbarui Agenda Kegiatan';
            document.getElementById('btn-cancel-edit').classList.remove('hidden');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Batal Edit Mode
        function resetForm() {
            document.getElementById('activity_id').value = '';
            document.getElementById('form-activity').reset();
            document.getElementById('form-title').innerHTML = '➕ Tambah Agenda Kegiatan Baru';
            document.getElementById('btn-submit').innerHTML = '💾 Simpan Agenda Kegiatan';
            document.getElementById('btn-cancel-edit').classList.add('hidden');
        }

        // Format Date string into YYYY-MM-DDTHH:MM for datetime-local input
        function formatDatetimeLocal(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return '';
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }

        // Hapus Agenda Kegiatan
        async function deleteActivity(id) {
            if (!confirm('Yakin ingin menghapus agenda kegiatan ini?')) return;

            try {
                const res = await fetch(`/activities/${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    }
                });
                const data = await res.json();
                if (res.ok) {
                    alert(data.message || 'Agenda berhasil dihapus!');
                    window.location.reload();
                } else {
                    alert('Gagal menghapus: ' + (data.message || 'Error'));
                }
            } catch (err) {
                alert('Terjadi kesalahan!');
            }
        }
    </script>
</x-app-layout>