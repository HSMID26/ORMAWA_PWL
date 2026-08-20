<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    ⚙️ {{ __('Pengaturan Organisasi & Modul') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola logo, warna tema aksen, sakelar modul aktif, dan custom label menu Ormawa.</p>
            </div>
            <div>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 text-xs font-bold rounded-full">
                    {{ $organization->nama }} ({{ $organization->jenis }})
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Success / Error -->
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

            <form method="POST" action="{{ route('organization.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="organization_id" value="{{ $organization->id }}">

                <!-- 1. Identitas Organisasi & Logo -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        🖼️ Identitas Organisasi & Logo
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <!-- Preview Logo -->
                        <div class="flex flex-col items-center justify-center p-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900">
                            <div class="w-32 h-32 mb-3 rounded-lg overflow-hidden bg-white flex items-center justify-center border shadow-sm">
                                @if($organization->logo && Storage::disk('public')->exists($organization->logo))
                                    <img id="logo-preview" src="{{ Storage::url($organization->logo) }}" alt="Logo Ormawa" class="w-full h-full object-contain p-2">
                                @else
                                    <div id="logo-placeholder" class="text-gray-400 text-center text-xs">
                                        <div class="text-3xl mb-1">🏛️</div>
                                        Belum Ada Logo
                                    </div>
                                    <img id="logo-preview" src="" alt="Logo Preview" class="w-full h-full object-contain p-2 hidden">
                                @endif
                            </div>
                            <label class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded cursor-pointer transition">
                                📤 Upload Logo Baru
                                <input type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogoImage(this)">
                            </label>
                            <p class="text-[10px] text-gray-400 mt-2 text-center">Format: PNG, JPG, WEBP, SVG (Max 5MB)</p>
                        </div>

                        <!-- Form Input Nama & Jenis -->
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Nama Organisasi <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" value="{{ old('nama', $organization->nama) }}" required class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Jenis Organisasi <span class="text-red-500">*</span></label>
                                    <select name="jenis" required class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                        <option value="HMPS" {{ $organization->jenis == 'HMPS' ? 'selected' : '' }}>HMPS (Himpunan Mahasiswa Program Studi)</option>
                                        <option value="UKM" {{ $organization->jenis == 'UKM' ? 'selected' : '' }}>UKM (Unit Kegiatan Mahasiswa)</option>
                                        <option value="BEM" {{ $organization->jenis == 'BEM' ? 'selected' : '' }}>BEM (Badan Eksekutif Mahasiswa)</option>
                                        <option value="Senat" {{ $organization->jenis == 'Senat' ? 'selected' : '' }}>Senat / DPM</option>
                                        <option value="Lainnya" {{ $organization->jenis == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Subdomain Portal</label>
                                    <input type="text" value="{{ $organization->subdomain }}.kampus.ac.id" readonly class="w-full p-2.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-100 dark:bg-gray-950 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Pengaturan Warna Tema (Theme Accent Color) -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        🎨 Warna Tema & Identitas Visual
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-2">Pilih Warna Akses Utama (Theme Accent)</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="theme_picker" value="{{ old('warna_tema', $organization->warna_tema ?? '#1d4ed8') }}" class="w-12 h-10 p-1 border rounded cursor-pointer" onchange="updateThemeColor(this.value)">
                                <input type="text" id="warna_tema" name="warna_tema" value="{{ old('warna_tema', $organization->warna_tema ?? '#1d4ed8') }}" required class="w-36 p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white uppercase font-mono" onchange="updateThemeColor(this.value)">
                            </div>
                        </div>

                        <!-- Preset Palette -->
                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-2">Preset Warna Pilihan:</label>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="setPresetColor('#1d4ed8')" class="w-8 h-8 rounded-full bg-blue-700 border-2 border-white shadow hover:scale-110 transition" title="Royal Blue"></button>
                                <button type="button" onclick="setPresetColor('#059669')" class="w-8 h-8 rounded-full bg-emerald-600 border-2 border-white shadow hover:scale-110 transition" title="Emerald Green"></button>
                                <button type="button" onclick="setPresetColor('#7c3aed')" class="w-8 h-8 rounded-full bg-purple-600 border-2 border-white shadow hover:scale-110 transition" title="Purple"></button>
                                <button type="button" onclick="setPresetColor('#dc2626')" class="w-8 h-8 rounded-full bg-rose-600 border-2 border-white shadow hover:scale-110 transition" title="Rose Red"></button>
                                <button type="button" onclick="setPresetColor('#d97706')" class="w-8 h-8 rounded-full bg-amber-600 border-2 border-white shadow hover:scale-110 transition" title="Amber Orange"></button>
                                <button type="button" onclick="setPresetColor('#0284c7')" class="w-8 h-8 rounded-full bg-sky-600 border-2 border-white shadow hover:scale-110 transition" title="Sky Blue"></button>
                                <button type="button" onclick="setPresetColor('#475569')" class="w-8 h-8 rounded-full bg-slate-600 border-2 border-white shadow hover:scale-110 transition" title="Slate Gray"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Sakelar Aktifkan / Nonaktifkan Modul & Menu -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1 flex items-center gap-2">
                        🧩 Aktifkan / Nonaktifkan Modul & Menu Navbar
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Jika sakelar dimatikan, modul dan menu tersebut akan tersembunyi dari navbar portal Ormawa ini.</p>

                    <div class="space-y-4 divide-y divide-gray-100 dark:divide-gray-700">

                        <!-- Modul Artikel -->
                        <div class="pt-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 text-blue-700 rounded-lg text-lg">📝</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-200">Modul Artikel & Konten</div>
                                    <div class="text-xs text-gray-400">Pengelolaan berita, pengumuman artikel, dan editor TipTap.</div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="modul_aktif[posts]" value="1" {{ ($activeModules['posts'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <!-- Modul Agenda -->
                        <div class="pt-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-100 text-purple-700 rounded-lg text-lg">📅</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-200">Modul Agenda Kegiatan & iCal</div>
                                    <div class="text-xs text-gray-400">Jadwal proker, waktu kegiatan, dan feed sinkronisasi Google Calendar.</div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="modul_aktif[activities]" value="1" {{ ($activeModules['activities'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <!-- Modul Galeri -->
                        <div class="pt-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg text-lg">🖼️</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-200">Modul Galeri Media & Dokumentasi</div>
                                    <div class="text-xs text-gray-400">Perpustakaan media gambar dokumentasi kegiatan Ormawa.</div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="modul_aktif[media]" value="1" {{ ($activeModules['media'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <!-- Modul Pengurus -->
                        <div class="pt-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-100 text-amber-700 rounded-lg text-lg">👥</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-200">Modul Kelola Pengurus & Akun</div>
                                    <div class="text-xs text-gray-400">Pengelolaan anggota pengurus, peran (Editor, Kontributor), dan status akun.</div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="modul_aktif[committees]" value="1" {{ ($activeModules['committees'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                    </div>
                </div>

                <!-- 4. Kustomisasi Label Teks Menu Navbar -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1 flex items-center gap-2">
                        ✏️ Kustomisasi Label Teks Menu Navbar
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Ubah teks nama menu yang tampil di navbar sesuai istilah khas Ormawa Anda.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Label Menu Artikel / Konten</label>
                            <input type="text" name="label_menu[posts]" value="{{ $menuLabels['posts'] ?? 'Artikel / Konten' }}" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Default: Artikel / Konten">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Label Menu Agenda Kegiatan</label>
                            <input type="text" name="label_menu[activities]" value="{{ $menuLabels['activities'] ?? 'Agenda Kegiatan' }}" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Default: Agenda Kegiatan">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Label Menu Galeri Media</label>
                            <input type="text" name="label_menu[media]" value="{{ $menuLabels['media'] ?? 'Galeri Media' }}" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Default: Galeri Media">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold dark:text-gray-300 mb-1">Label Menu Kelola Pengurus</label>
                            <input type="text" name="label_menu[committees]" value="{{ $menuLabels['committees'] ?? 'Kelola Pengurus' }}" class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Default: Kelola Pengurus">
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="flex justify-end gap-3">
                    <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-lg transition duration-150 flex items-center gap-2">
                        💾 Simpan Semua Pengaturan Ormawa
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function updateThemeColor(color) {
            document.getElementById('warna_tema').value = color.toUpperCase();
            document.getElementById('theme_picker').value = color;
        }

        function setPresetColor(color) {
            updateThemeColor(color);
        }

        function previewLogoImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logo-preview');
                    const placeholder = document.getElementById('logo-placeholder');
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>
