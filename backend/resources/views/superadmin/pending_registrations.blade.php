<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            📋 Pengajuan Pendaftaran Ormawa (Pending)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Nama Ormawa</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Subdomain</th>
                                <th class="px-6 py-3">Admin</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $req->nama_ormawa }}</td>
                                    <td class="px-6 py-4">{{ $req->jenis_ormawa }}</td>
                                    <td class="px-6 py-4"><code>{{ $req->subdomain }}</code></td>
                                    <td class="px-6 py-4">{{ $req->admin_name }}</td>
                                    <td class="px-6 py-4">{{ $req->admin_email }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <!-- Tombol Approve -->
                                            <form action="{{ route('superadmin.approve', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-1.5 px-3 rounded" onclick="return confirm('Setujui pendaftaran ini?')">
                                                    Approve
                                                </button>
                                            </form>

                                            <!-- Tombol Reject -->
                                            <form action="{{ route('superadmin.reject', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1.5 px-3 rounded" onclick="return confirm('Tolak pendaftaran ini?')">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                        Belum ada pengajuan pendaftaran Ormawa baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>