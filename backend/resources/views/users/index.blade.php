<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Manajemen Akun Pengurus & Pengguna') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tambah, edit peran, dan aktifkan/nonaktifkan akun pengurus Ormawa.</p>
            </div>
            <div>
                <button type="button" onclick="openAddModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow flex items-center gap-2 transition duration-150">
                    ➕ Tambah Akun Pengurus Baru
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Flash Session -->
            @if(session('success'))
                <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 rounded-lg text-sm flex items-center justify-between shadow">
                    <span>✅ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 font-bold">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 rounded-lg text-sm flex items-center justify-between shadow">
                    <span>⚠️ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 font-bold">&times;</button>
                </div>
            @endif

            <!-- Ringkasan Statistik Akun -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Akun</div>
                        <div class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">{{ $users->count() }}</div>
                    </div>
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full text-xl">👥</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Akun Aktif</div>
                        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ $users->where('status', 'active')->count() }}</div>
                    </div>
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full text-xl">✅</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Akun Nonaktif</div>
                        <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">{{ $users->where('status', 'inactive')->count() }}</div>
                    </div>
                    <div class="p-3 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-full text-xl">🚫</div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email pengurus..." class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <select name="role" onchange="this.form.submit()" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Semua Role --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="status" onchange="this.form.submit()" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Akun Pengurus -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    📋 Daftar Pengurus & Hak Akses
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">Pengurus</th>
                                <th class="p-3">Organisasi</th>
                                <th class="p-3">Role / Peran</th>
                                <th class="p-3">Status Akun</th>
                                <th class="p-3">Terdaftar</th>
                                <th class="p-3 text-center">Aksi / Kontrol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $user)
                                @php
                                    $userRole = $user->roles->first()?->name ?? 'None';
                                    $roleColors = [
                                        'Super Admin'     => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                        'Admin Organisasi'=> 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                        'Editor'          => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'Kontributor'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    ];
                                    $isSelf = (Auth::id() === $user->id);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div>{{ $user->name }} @if($isSelf) <span class="text-xs text-indigo-500 font-normal">(Anda)</span> @endif</div>
                                                <div class="text-xs text-gray-400 font-normal">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-xs">
                                        {{ $user->organization ? $user->organization->nama : ($userRole === 'Super Admin' ? 'PKA Pusat' : '-') }}
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 text-xs rounded-full font-semibold {{ $roleColors[$userRole] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $userRole }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        @if($user->status === 'active')
                                            <span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                                ● Aktif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200">
                                                ● Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-xs whitespace-nowrap">
                                        {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Toggle Button Status -->
                                            @if(!$isSelf)
                                                <button type="button" onclick="toggleStatus({{ $user->id }}, '{{ $user->status }}')" class="px-2.5 py-1 text-xs font-semibold rounded shadow transition flex items-center gap-1 {{ $user->status === 'active' ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                                    {{ $user->status === 'active' ? '🚫 Nonaktifkan' : '✅ Aktifkan' }}
                                                </button>
                                            @endif

                                            <!-- Button Edit -->
                                            <button type="button" onclick="editUser({{ json_encode($user) }}, '{{ $userRole }}')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded shadow transition">
                                                ✏️ Edit
                                            </button>

                                            <!-- Button Hapus -->
                                            @if(!$isSelf)
                                                <button type="button" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded shadow transition">
                                                    🗑️ Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="text-3xl mb-2">👤</div>
                                        Tidak ada akun pengurus yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Form Tambah / Edit User -->
    <div id="user-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 dark:bg-opacity-70 hidden items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 border border-gray-100 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-lg font-bold text-gray-800 dark:text-gray-200">
                    ➕ Tambah Akun Pengurus Baru
                </h3>
                <button type="button" onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold text-xl">&times;</button>
            </div>

            <form id="form-user">
                @csrf
                <input type="hidden" id="user_id" value="">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="name" required class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Budi Santoso">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Alamat Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" required class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="email@ormawa.ac.id">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Password <span id="password-req" class="text-red-500">*</span></label>
                        <input type="password" id="password" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Minimal 8 karakter">
                        <p id="password-help" class="text-xs text-gray-400 mt-1 hidden">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Role / Peran <span class="text-red-500">*</span></label>
                        <select id="role" required class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($isSuperAdmin)
                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Organisasi Terkait</label>
                            <select id="organization_id" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- PKA Pusat (Super Admin Only) --</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Status Akun <span class="text-red-500">*</span></label>
                        <select id="user_status" required class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Aktif (Dapat Login)</option>
                            <option value="inactive">Nonaktif (Dilarang Login)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeUserModal()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold rounded-lg shadow">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('user_id').value = '';
            document.getElementById('form-user').reset();
            document.getElementById('modal-title').innerText = '➕ Tambah Akun Pengurus Baru';
            document.getElementById('password').required = true;
            document.getElementById('password-req').classList.remove('hidden');
            document.getElementById('password-help').classList.add('hidden');

            const modal = document.getElementById('user-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeUserModal() {
            const modal = document.getElementById('user-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function editUser(user, roleName) {
            document.getElementById('user_id').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('password-req').classList.add('hidden');
            document.getElementById('password-help').classList.remove('hidden');
            document.getElementById('role').value = roleName;
            document.getElementById('user_status').value = user.status;

            const orgSelect = document.getElementById('organization_id');
            if (orgSelect) {
                orgSelect.value = user.organization_id || '';
            }

            document.getElementById('modal-title').innerText = '✏️ Edit Akun Pengurus';

            const modal = document.getElementById('user-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Submit Form Add/Edit User
        document.getElementById('form-user').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('user_id').value;
            const isEdit = Boolean(id);
            const url = isEdit ? `/users/${id}` : '/users';
            const method = isEdit ? 'PUT' : 'POST';

            const payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                role: document.getElementById('role').value,
                status: document.getElementById('user_status').value,
            };

            const password = document.getElementById('password').value;
            if (password) {
                payload.password = password;
            }

            const orgSelect = document.getElementById('organization_id');
            if (orgSelect) {
                payload.organization_id = orgSelect.value || null;
            }

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
                    alert(data.message || 'Berhasil menyimpan akun pengurus!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan server!');
            }
        });

        // Toggle Status Aktif / Nonaktif
        async function toggleStatus(id, currentStatus) {
            const actionText = currentStatus === 'active' ? 'menonaktifkan' : 'mengaktifkan';
            if (!confirm(`Yakin ingin ${actionText} akun pengurus ini?`)) return;

            try {
                const res = await fetch(`/users/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();

                if (res.ok) {
                    alert(data.message || 'Status akun berhasil diperbarui!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan server!');
            }
        }

        // Hapus Akun User
        async function deleteUser(id, name) {
            if (!confirm(`Yakin ingin menghapus akun ${name}? Tindakan ini tidak dapat dibatalkan.`)) return;

            try {
                const res = await fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();

                if (res.ok) {
                    alert(data.message || 'Akun berhasil dihapus!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan server!');
            }
        }
    </script>
</x-app-layout>
