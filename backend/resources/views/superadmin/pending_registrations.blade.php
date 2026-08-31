<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    📋 {{ __('Alur Persetujuan Registrasi Ormawa') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Review dan kelola persetujuan akun pendaftaran Ormawa baru oleh Super Admin PKA.
                </p>
            </div>
            <div>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 text-xs font-bold rounded-full">
                    {{ $requests->count() }} Pengajuan Menunggu Review
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 rounded-xl text-sm flex items-center justify-between shadow-xs">
                    <span>✅ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 font-bold">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 rounded-xl text-sm flex items-center justify-between shadow-xs">
                    <span>⚠️ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 font-bold">&times;</button>
                </div>
            @endif

            <!-- 1. TABEL PENGAJUAN PENDING (MENUNGGU PERSETUJUAN) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        ⏳ Pengajuan Baru Menunggu Persetujuan
                    </h3>
                    <span class="text-xs text-gray-400">Status: Pending</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-5 py-3">Nama Ormawa</th>
                                <th class="px-5 py-3">Jenis</th>
                                <th class="px-5 py-3">Subdomain Portal</th>
                                <th class="px-5 py-3">Nama Admin</th>
                                <th class="px-5 py-3">Email Akun</th>
                                <th class="px-5 py-3 text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($requests as $req)
                                <tr class="bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-750 transition">
                                    <td class="px-5 py-4 font-bold text-gray-900 dark:text-white">
                                        {{ $req->nama_ormawa }}
                                        <div class="text-[11px] text-gray-400 font-normal">Diajukan: {{ $req->created_at ? $req->created_at->format('d M Y, H:i') : '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                            {{ $req->jenis_ormawa }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-indigo-600 dark:text-indigo-400">
                                        {{ $req->subdomain }}.kampus.ac.id
                                    </td>
                                    <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">{{ $req->admin_name }}</td>
                                    <td class="px-5 py-4">{{ $req->admin_email }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            <!-- Tombol Approve -->
                                            <form action="{{ route('superadmin.approve', $req->id) }}" method="POST">
                                                @csrf
                                                <button 
                                                    type="submit" 
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-1.5 px-3 rounded-lg shadow-xs transition flex items-center gap-1"
                                                    onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI pendaftaran Ormawa {{ addslashes($req->nama_ormawa) }}? Akun admin akan langsung aktif.')"
                                                >
                                                    ✅ Setujui
                                                </button>
                                            </form>

                                            <!-- Tombol Tolak (Modal Trigger) -->
                                            <button 
                                                type="button"
                                                onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->nama_ormawa) }}')"
                                                class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-1.5 px-3 rounded-lg shadow-xs transition flex items-center gap-1"
                                            >
                                                ❌ Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        <div class="text-3xl mb-2">🎉</div>
                                        <p class="font-semibold text-gray-600 dark:text-gray-300">Tidak ada pengajuan pendaftaran baru yang menunggu persetujuan.</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Semua permohonan registrasi Ormawa telah selesai diproses.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. RIWAYAT PERSETUJUAN TERAKHIR (APPROVED / REJECTED) -->
            @if(isset($history) && $history->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        📜 Riwayat Keputusan Pendaftaran Sebelumnya
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-[11px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2.5">Nama Ormawa</th>
                                    <th class="px-4 py-2.5">Subdomain</th>
                                    <th class="px-4 py-2.5">Admin</th>
                                    <th class="px-4 py-2.5">Status</th>
                                    <th class="px-4 py-2.5">Alasan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($history as $item)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $item->nama_ormawa }}</td>
                                        <td class="px-4 py-3 font-mono">{{ $item->subdomain }}</td>
                                        <td class="px-4 py-3">{{ $item->admin_email }}</td>
                                        <td class="px-4 py-3">
                                            @if($item->status === 'approved')
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 rounded-full font-bold">
                                                    Disetujui
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300 rounded-full font-bold">
                                                    Ditolak
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-400">{{ $item->alasan_penolakan ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- MODAL ALASAN PENOLAKAN -->
    <div id="reject-modal" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">❌ Tolak Pendaftaran Ormawa</h3>
            <p id="reject-modal-title" class="text-xs text-gray-500 dark:text-gray-400 mb-4"></p>

            <form id="reject-form" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-300 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea 
                        name="alasan_penolakan" 
                        rows="3" 
                        required 
                        placeholder="Contoh: Dokumen SK kepengurusan tidak valid atau subdomain sudah digunakan."
                        class="w-full p-2.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id, ormawaName) {
            document.getElementById('reject-modal-title').innerText = `Tentukan alasan penolakan untuk pendaftaran "${ormawaName}".`;
            document.getElementById('reject-form').action = `/superadmin/pending-registrations/${id}/reject`;
            const modal = document.getElementById('reject-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeRejectModal() {
            const modal = document.getElementById('reject-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-app-layout>