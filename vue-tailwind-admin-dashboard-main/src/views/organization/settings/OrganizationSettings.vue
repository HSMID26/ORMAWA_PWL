<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Pengaturan Website & Profil Organisasi'" />

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <SettingsIcon class="h-5 w-5 text-brand-600" />
          Manajemen Website & Profil Organisasi
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
          Pusat kendali resmi (Single Source of Truth) untuk identitas, branding visual, modul publik, dan SEO ormawa.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <a
          :href="publicUrl"
          target="_blank"
          class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition"
        >
          <ExternalLinkIcon class="h-4 w-4 text-brand-600" />
          <span>Lihat Website Publik</span>
        </a>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="h-48 w-full animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800"></div>
      <div class="h-64 w-full animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800"></div>
    </div>

    <form v-else @submit.prevent="saveSettings" class="space-y-6">
      
      <!-- Top Navigation Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-800">
        <button
          type="button"
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition',
            activeTab === tab.id
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 1: IDENTITAS ORGANISASI & KONTAK                                    -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'identity'" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        
        <!-- Left: Logo Management -->
        <div class="lg:col-span-4 space-y-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
              Logo Resmi Organisasi
            </h2>
            <div class="flex flex-col items-center text-center">
              <div class="relative flex h-28 w-28 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden dark:border-gray-700 dark:bg-gray-800 mb-4 shadow-inner">
                <img
                  v-if="logoPreview || form.logo"
                  :src="logoPreview || resolveImageUrl(form.logo) || ''"
                  alt="Logo Organisasi"
                  class="h-full w-full object-contain p-1"
                />
                <span v-else class="font-bold text-3xl text-brand-600">
                  {{ form.nama ? form.nama.substring(0, 2).toUpperCase() : 'OM' }}
                </span>
              </div>

              <div class="flex flex-wrap items-center justify-center gap-2">
                <label class="inline-flex cursor-pointer items-center rounded-xl bg-brand-50 px-3.5 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:bg-brand-900/30 dark:text-brand-300 transition">
                  <UploadIcon class="h-3.5 w-3.5 mr-1.5" />
                  {{ form.logo ? 'Ganti Logo' : 'Unggah Logo' }}
                  <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="handleLogoSelect" class="hidden" />
                </label>

                <button
                  v-if="form.logo || logoPreview"
                  type="button"
                  @click="removeLogo"
                  class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300 transition"
                >
                  <Trash2Icon class="h-3.5 w-3.5 mr-1" />
                  Hapus
                </button>
              </div>

              <p class="text-[10px] text-gray-400 mt-2.5">
                Format PNG, WebP, JPG, atau SVG (Maksimal 5MB)
              </p>
            </div>
          </div>
        </div>

        <!-- Right: Profile, Descriptions & Contacts -->
        <div class="lg:col-span-8 space-y-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 pb-2 border-b border-gray-100 dark:border-gray-800">
              Informasi Umum
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Organisasi</label>
                <input
                  v-model="form.nama"
                  type="text"
                  disabled
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-semibold text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400"
                />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subdomain Publik (Slug)</label>
                <input
                  v-model="form.subdomain"
                  type="text"
                  disabled
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Slogan / Tagline Organisasi</label>
              <input
                v-model="form.slogan"
                type="text"
                placeholder="Contoh: Berkarya, Berprestasi, dan Mengabdi untuk Bangsa"
                class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat (Ringkasan & Card Preview)</label>
              <textarea
                v-model="form.deskripsi"
                rows="2"
                placeholder="Penjelasan ringkas mengenai fokus dan profil ormawa..."
                class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Lengkap (Profil, Visi, Misi & Sejarah)</label>
              <textarea
                v-model="form.deskripsi_lengkap"
                rows="4"
                placeholder="Tuliskan latar belakang, visi, misi, dan program kerja unggulan ormawa..."
                class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              ></textarea>
            </div>

            <!-- Kontak Resmi -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
              <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Kontak Resmi & Sekretariat
              </h3>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Resmi Publik</label>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="himatif@iti.ac.id"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon / WhatsApp</label>
                  <input
                    v-model="form.telepon"
                    type="text"
                    placeholder="081234567890"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Sekretariat / Lokasi Kampus</label>
                <input
                  v-model="form.alamat"
                  type="text"
                  placeholder="Gedung PKM Lt. 2, Kampus Institut Teknologi Indonesia, Serpong"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>
            </div>

            <!-- Media Sosial -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
              <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Tautan Media Sosial & Eksternal
              </h3>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Instagram</label>
                  <input
                    v-model="form.media_sosial.instagram"
                    type="text"
                    placeholder="https://instagram.com/..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Facebook</label>
                  <input
                    v-model="form.media_sosial.facebook"
                    type="text"
                    placeholder="https://facebook.com/..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
                  <input
                    v-model="form.media_sosial.youtube"
                    type="text"
                    placeholder="https://youtube.com/@..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">TikTok</label>
                  <input
                    v-model="form.media_sosial.tiktok"
                    type="text"
                    placeholder="https://tiktok.com/@..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">LinkedIn</label>
                  <input
                    v-model="form.media_sosial.linkedin"
                    type="text"
                    placeholder="https://linkedin.com/company/..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Website Eksternal</label>
                  <input
                    v-model="form.media_sosial.website"
                    type="url"
                    placeholder="https://..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link WhatsApp</label>
                  <input
                    v-model="form.media_sosial.whatsapp"
                    type="text"
                    placeholder="https://wa.me/628..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 2: WEBSITE PUBLIK & BRANDING VISUAL                                 -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'branding'" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-6">
          
          <!-- Status Website Switch -->
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
            <div>
              <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Status Akses Website Publik
              </h2>
              <p class="text-[11px] text-gray-500 mt-0.5">
                Jika dinonaktifkan, pengunjung umum akan melihat pemberitahuan pemeliharaan resmi. Admin tetap dapat mengelola dashboard.
              </p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
              <input type="checkbox" v-model="form.public_website_enabled" class="peer sr-only" />
              <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-emerald-600 peer-checked:after:translate-x-full dark:bg-gray-700"></div>
            </label>
          </div>

          <!-- Visual Theme Color -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Warna Aksen Utama (Primary Brand Color)
              </label>
              <div class="flex items-center gap-4">
                <input
                  type="color"
                  v-model="form.warna_tema"
                  class="h-12 w-16 cursor-pointer rounded-xl border border-gray-200 bg-transparent p-1 dark:border-gray-700"
                />
                <div>
                  <span class="font-mono text-sm font-bold text-gray-800 dark:text-gray-200 uppercase">{{ form.warna_tema }}</span>
                  <p class="text-[10px] text-gray-400 mt-0.5">Warna aksen tombol, border aktif navbar, badge, dan gradien fallback hero.</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Pratinjau Elemen Aksen</label>
              <div class="flex items-center gap-3">
                <button
                  type="button"
                  :style="{ backgroundColor: form.warna_tema }"
                  class="rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs"
                >
                  Tombol Utama
                </button>
                <span
                  :style="{ color: form.warna_tema, borderColor: form.warna_tema }"
                  class="rounded-xl border px-3 py-1.5 text-xs font-semibold"
                >
                  Badge Ormawa
                </span>
              </div>
            </div>
          </div>

          <!-- Hero Customization: Text & Background Image -->
          <div class="space-y-5 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Kustomisasi Hero Banner Website
              </h3>
              <p class="text-[11px] text-gray-500 mt-0.5">
                Sesuaikan teks headline, subjudul, dan gambar latar belakang banner utama.
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Utama Hero (Opsional)</label>
                <input
                  v-model="form.hero_title"
                  type="text"
                  :placeholder="form.nama || 'Nama Organisasi'"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan nama resmi organisasi.</p>
              </div>
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subjudul Hero (Opsional)</label>
                <input
                  v-model="form.hero_subtitle"
                  type="text"
                  :placeholder="form.slogan || form.deskripsi || 'Portal Resmi Organisasi Mahasiswa ITI'"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan slogan atau deskripsi singkat.</p>
              </div>
            </div>

            <!-- Hero Background Image Upload & Controls -->
            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                  <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                    <ImageIcon class="h-4 w-4 text-brand-600" />
                    Foto Latar Belakang Hero (Hero Background Image)
                  </h4>
                  <p class="text-[10px] text-gray-500 mt-0.5">
                    Rasio disarankan landscape 16:9 (Maksimal 10MB). Jika kosong, hero otomatis menggunakan warna tema aksen.
                  </p>
                </div>

                <div class="flex items-center gap-2">
                  <label class="inline-flex cursor-pointer items-center rounded-xl bg-white border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition shadow-xs">
                    <UploadCloudIcon class="h-4 w-4 mr-1.5 text-brand-600" />
                    {{ form.hero_image || heroPreview ? 'Ganti Foto Hero' : 'Unggah Foto Hero' }}
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" @change="handleHeroSelect" class="hidden" />
                  </label>

                  <button
                    v-if="form.hero_image || heroPreview"
                    type="button"
                    @click="removeHeroImage"
                    class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300 transition"
                  >
                    <Trash2Icon class="h-3.5 w-3.5 mr-1" />
                    Hapus Foto
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ─── Live Preview Mini-Card (Stitch Style) ─────────────────────── -->
          <div class="space-y-2 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Pratinjau Langsung Hero (Live Preview)
              </span>
              <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded dark:bg-emerald-950/40 dark:text-emerald-400">
                Real-Time
              </span>
            </div>

            <div
              class="relative rounded-2xl overflow-hidden p-6 sm:p-8 text-white shadow-md transition-all duration-300 bg-cover bg-center"
              :style="livePreviewHeroStyle"
            >
              <!-- Contrast Dark Overlay -->
              <div
                v-if="heroPreview || form.hero_image"
                class="absolute inset-0 z-0 bg-gradient-to-tr from-black/80 via-black/55 to-black/35 pointer-events-none"
              ></div>
              <div
                v-else
                class="absolute inset-0 z-0 bg-gradient-to-tr from-black/40 via-transparent to-white/10 pointer-events-none"
              ></div>

              <div class="relative z-10 max-w-xl space-y-3">
                <div class="flex items-center gap-2">
                  <span class="rounded bg-white/20 backdrop-blur-xs px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                    {{ form.jenis }}
                  </span>
                  <span class="text-[11px] text-white/80 font-semibold tracking-wide">
                    &bull; Institut Teknologi Indonesia
                  </span>
                </div>

                <h4 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-snug">
                  {{ form.hero_title || form.nama || 'Nama Organisasi' }}
                </h4>
                <p class="text-xs text-white/90 leading-relaxed font-normal">
                  {{ form.hero_subtitle || form.slogan || form.deskripsi || 'Membangun sinergi, inovasi, dan prestasi mahasiswa di lingkungan Institut Teknologi Indonesia.' }}
                </p>

                <div class="flex items-center gap-3 pt-2">
                  <span class="rounded-lg bg-white px-4 py-2 text-[11px] font-bold text-gray-900 shadow-xs">
                    Tentang Kami
                  </span>
                  <span class="rounded-lg border border-white/80 bg-white/10 px-4 py-2 text-[11px] font-semibold text-white">
                    {{ form.label_menu?.posts || 'Baca Warta' }} &rarr;
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 3: KONFIGURASI MODUL PUBLIK & LABEL MENU                            -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'modules'" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-5">
          <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
              Pilihan Modul & Kustomisasi Label Menu
            </h2>
            <p class="text-[11px] text-gray-500 mt-0.5">
              Aktifkan/nonaktifkan modul website serta ubah teks label navigasi yang tampil pada navbar dan beranda publik.
            </p>
          </div>

          <!-- Master Switch Website Publik -->
          <div class="p-4 rounded-xl border border-brand-200 bg-brand-50/50 dark:border-brand-900/50 dark:bg-brand-950/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Status Website Publik Organisasi (Master Switch)</h3>
              <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                Jika dinonaktifkan, seluruh website publik organisasi akan menampilkan status tidak tersedia tanpa menghapus data internal ormawa.
              </p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center shrink-0">
              <input type="checkbox" v-model="form.public_website_enabled" class="peer sr-only" />
              <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700 dark:border-gray-600"></div>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            
            <!-- Modul Berita -->
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Berita & Artikel</h3>
                  <p class="text-[10px] text-gray-500 mt-0.5">Mempublikasikan artikel, rilis warta, dan berita.</p>
                </div>
                <input type="checkbox" v-model="form.modul_aktif.posts" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
              </div>
              <div v-if="form.modul_aktif.posts" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
                <input v-model="form.label_menu.posts" type="text" placeholder="Berita / Warta" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>

            <!-- Modul Agenda -->
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Agenda Acara</h3>
                  <p class="text-[10px] text-gray-500 mt-0.5">Menampilkan kalender kegiatan mendatang.</p>
                </div>
                <input type="checkbox" v-model="form.modul_aktif.agenda" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
              </div>
              <div v-if="form.modul_aktif.agenda" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
                <input v-model="form.label_menu.agenda" type="text" placeholder="Agenda / Kalender Acara" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>

            <!-- Modul Pengumuman -->
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Pengumuman</h3>
                  <p class="text-[10px] text-gray-500 mt-0.5">Maklumat resmi dan pengumuman ormawa.</p>
                </div>
                <input type="checkbox" v-model="form.modul_aktif.announcements" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
              </div>
              <div v-if="form.modul_aktif.announcements" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
                <input v-model="form.label_menu.announcements" type="text" placeholder="Pengumuman / Maklumat" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>

            <!-- Modul Galeri -->
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Galeri Foto</h3>
                  <p class="text-[10px] text-gray-500 mt-0.5">Dokumentasi foto kegiatan dan album ormawa.</p>
                </div>
                <input type="checkbox" v-model="form.modul_aktif.galeri" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
              </div>
              <div v-if="form.modul_aktif.galeri" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
                <input v-model="form.label_menu.galeri" type="text" placeholder="Galeri / Dokumentasi" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>

            <!-- Modul Dokumen -->
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Dokumen Publik</h3>
                  <p class="text-[10px] text-gray-500 mt-0.5">Arsip berkas SK, proposal, SOP, dan pedoman.</p>
                </div>
                <input type="checkbox" v-model="form.modul_aktif.documents" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
              </div>
              <div v-if="form.modul_aktif.documents" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
                <input v-model="form.label_menu.documents" type="text" placeholder="Dokumen / Arsip" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>

            <!-- Modul Struktur Kepengurusan -->
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Struktur Kepengurusan</h3>
                  <p class="text-[10px] text-gray-500 mt-0.5">Bagan organisasi dan daftar fungsionaris aktif.</p>
                </div>
                <input type="checkbox" v-model="form.modul_aktif.structure" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
              </div>
              <div v-if="form.modul_aktif.structure" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
                <input v-model="form.label_menu.structure" type="text" placeholder="Struktur / Pengurus" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 4: KUSTOMISASI FOOTER ORMAWA                                        -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'footer'" class="space-y-6">
        
        <!-- Live Preview Mockup for Organization Footer -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div>
              <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <SparklesIcon class="h-4 w-4 text-brand-600" />
                Pratinjau Langsung Footer Website Organisasi (Live Preview)
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Tampilan footer dinamis yang dilihat pengunjung saat membuka website resmi {{ form.nama || 'organisasi' }}.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
              Live Preview
            </span>
          </div>

          <!-- Live Mockup Component Container -->
          <div class="rounded-xl bg-[#191C1D] text-[#C6C5CF] p-6 text-xs border border-slate-800 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
              
              <!-- Col 1: Identity & Description -->
              <div class="md:col-span-4 space-y-2">
                <div class="flex items-center gap-2.5">
                  <div class="h-9 w-9 rounded-xl bg-white overflow-hidden flex items-center justify-center p-0.5 shrink-0">
                    <img
                      v-if="logoPreview || form.logo"
                      :src="logoPreview || resolveImageUrl(form.logo) || ''"
                      alt="Logo"
                      class="h-full w-full object-contain"
                    />
                    <span v-else class="font-bold text-xs text-[#00346F]">
                      {{ form.nama ? form.nama.substring(0, 2).toUpperCase() : 'OM' }}
                    </span>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-white block">{{ form.nama || 'Nama Organisasi' }}</span>
                    <span class="text-[9px] text-[#A0A0A8] block">{{ form.jenis }} &bull; Institut Teknologi Indonesia</span>
                  </div>
                </div>
                <p class="text-[11px] text-[#A0A0A8] leading-relaxed">
                  {{ form.footer_description || form.deskripsi || form.slogan || 'Portal publik resmi tata kelola dan warta kegiatan mahasiswa.' }}
                </p>
                <div v-if="form.slogan" class="text-[10px] text-slate-400 italic">
                  &ldquo;{{ form.slogan }}&rdquo;
                </div>
              </div>

              <!-- Col 2: Navigation -->
              <div class="md:col-span-2 space-y-2">
                <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Navigasi Ormawa</h5>
                <ul class="space-y-1 text-[11px] text-slate-400">
                  <li>Beranda</li>
                  <li v-if="form.modul_aktif.posts">{{ form.label_menu.posts || 'Berita' }}</li>
                  <li v-if="form.modul_aktif.agenda">{{ form.label_menu.agenda || 'Agenda' }}</li>
                  <li v-if="form.modul_aktif.announcements">{{ form.label_menu.announcements || 'Pengumuman' }}</li>
                  <li v-if="form.modul_aktif.galeri">{{ form.label_menu.galeri || 'Galeri' }}</li>
                  <li v-if="form.modul_aktif.documents">{{ form.label_menu.documents || 'Dokumen' }}</li>
                  <li v-if="form.modul_aktif.structure">{{ form.label_menu.structure || 'Struktur' }}</li>
                </ul>
              </div>

              <!-- Col 3: Media Sosial -->
              <div class="md:col-span-3 space-y-2">
                <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Media Sosial & Tautan</h5>
                <div class="flex flex-wrap gap-1.5 pt-0.5">
                  <span v-if="form.media_sosial.instagram" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-pink-300">Instagram</span>
                  <span v-if="form.media_sosial.facebook" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-blue-300">Facebook</span>
                  <span v-if="form.media_sosial.youtube" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-red-300">YouTube</span>
                  <span v-if="form.media_sosial.tiktok" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-cyan-300">TikTok</span>
                  <span v-if="form.media_sosial.linkedin" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-sky-300">LinkedIn</span>
                  <span v-if="form.media_sosial.website" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-indigo-300">Website</span>
                  <span v-if="form.media_sosial.whatsapp" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-emerald-300">WhatsApp</span>
                </div>
              </div>

              <!-- Col 4: Sekretariat & Kontak -->
              <div class="md:col-span-3 space-y-1.5 text-[11px]">
                <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Sekretariat & Kontak</h5>
                <p class="text-[#A0A0A8] leading-tight">{{ form.alamat || 'Gedung PKM Kampus ITI, Serpong' }}</p>
                <p v-if="form.email" class="text-slate-300">{{ form.email }}</p>
                <p v-if="form.telepon" class="text-slate-300">{{ form.telepon }}</p>
              </div>

            </div>

            <!-- Bottom Strip -->
            <div class="pt-4 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-500">
              <p>&copy; {{ new Date().getFullYear() }} {{ form.copyright || `${form.nama || 'Organisasi'}. Hak Cipta Dilindungi.` }}</p>
              <p>ORMAWA Institut Teknologi Indonesia</p>
            </div>
          </div>
        </div>

        <!-- Settings Cards -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          
          <!-- Card 1: Informasi Deskripsi & Hak Cipta Footer -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
              <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                1. Deskripsi & Hak Cipta Footer
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Penyesuaian teks ringkasan dan kalimat hak cipta khusus untuk ormawa ini.</p>
            </div>

            <div class="space-y-3.5 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Footer (Opsional)</label>
                <textarea
                  v-model="form.footer_description"
                  rows="3"
                  :placeholder="form.deskripsi || 'Penjelasan singkat fokus organisasi pada kolom footer...'"
                  class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                ></textarea>
                <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan Deskripsi Singkat pada Tab 1.</p>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Teks Hak Cipta / Copyright Footer</label>
                <input
                  v-model="form.copyright"
                  type="text"
                  :placeholder="`${form.nama || 'Organisasi'}. Hak Cipta Dilindungi.`"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <p class="text-[10px] text-gray-400 mt-1">Tahun akan disematkan secara otomatis di bagian depan kalimat.</p>
              </div>
            </div>
          </div>

          <!-- Card 2: Kontak & Media Sosial Ringkas -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
              <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                2. Kontak Sekretariat & Media Sosial
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Data terhubung langsung dengan Tab 1 Identitas Organisasi.</p>
            </div>

            <div class="space-y-3 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Resmi</label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="organisasi@iti.ac.id"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon / WhatsApp</label>
                <input
                  v-model="form.telepon"
                  type="text"
                  placeholder="081234567890"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Sekretariat</label>
                <input
                  v-model="form.alamat"
                  type="text"
                  placeholder="Gedung PKM Lt. 2 Kampus ITI"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 5: PENGATURAN SEO & ANALYTICS                                       -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'seo'" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-5">
          <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
              Optimasi Mesin Pencari (Search Engine Optimization) & Analytics
            </h2>
            <p class="text-[11px] text-gray-500 mt-0.5">
              Metadata yang dibaca oleh Google, Bing, pratinjau tautan WhatsApp/Twitter, serta pelacakan pengunjung.
            </p>
          </div>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">SEO Title Tag</label>
              <input
                v-model="form.seo_title"
                type="text"
                :placeholder="`${form.nama} | ORMAWA Institut Teknologi Indonesia`"
                class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
              <p class="text-[10px] text-gray-400 mt-1">Judul yang muncul pada tab peramban dan hasil pencarian Google.</p>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">SEO Meta Description</label>
              <textarea
                v-model="form.seo_description"
                rows="3"
                :placeholder="form.deskripsi || `Website resmi ${form.nama} Institut Teknologi Indonesia`"
                class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              ></textarea>
              <p class="text-[10px] text-gray-400 mt-1">Ringkasan artikel/halaman yang tampil di bawah judul hasil pencarian (150-160 karakter disarankan).</p>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Google Analytics Tracking ID (Opsional)</label>
              <input
                v-model="form.ga_tracking_id"
                type="text"
                placeholder="G-XXXXXXXXXX"
                class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
              <p class="text-[10px] text-gray-400 mt-1">Hanya akan dimuat pada website publik yang aktif.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Save Action Bar -->
      <div class="flex items-center justify-between border-t border-gray-200 pt-5 dark:border-gray-800">
        <span v-if="hasPendingUploads" class="text-xs text-amber-600 font-semibold dark:text-amber-400 flex items-center gap-1.5">
          <AlertCircleIcon class="h-4 w-4" />
          Terdapat berkas gambar baru yang siap disimpan.
        </span>
        <span v-else class="text-xs text-gray-400">
          Perubahan akan langsung diterapkan ke website publik setelah disimpan.
        </span>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm disabled:opacity-50 transition cursor-pointer"
        >
          <SaveIcon class="h-4 w-4" />
          {{ isSubmitting ? 'Menyimpan Pengaturan...' : 'Simpan Semua Pengaturan' }}
        </button>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { organizationService } from '@/services/organizationService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { resolveImageUrl } from '@/utils/imageUrl'
import {
  SettingsIcon,
  SaveIcon,
  ExternalLinkIcon,
  UploadIcon,
  UploadCloudIcon,
  ImageIcon,
  Trash2Icon,
  AlertCircleIcon,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toastStore = useToastStore()

const isLoading = ref(true)
const isSubmitting = ref(false)
const activeTab = ref('identity')

const tabs = [
  { id: 'identity', label: '1. Identitas Organisasi & Kontak' },
  { id: 'branding', label: '2. Website & Branding' },
  { id: 'modules', label: '3. Pilihan Modul & Label Menu' },
  { id: 'footer', label: '4. Kustomisasi Footer Ormawa' },
  { id: 'seo', label: '5. Optimasi SEO & Analytics' },
]

// Pending file uploads
const selectedLogoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)

const selectedHeroFile = ref<File | null>(null)
const heroPreview = ref<string | null>(null)

const form = ref({
  id: 0,
  nama: '',
  subdomain: '',
  jenis: 'HMPS',
  warna_tema: '#1d4ed8',
  logo: null as string | null,
  hero_image: null as string | null,
  slogan: '',
  deskripsi: '',
  deskripsi_lengkap: '',
  hero_title: '',
  hero_subtitle: '',
  email: '',
  telepon: '',
  alamat: '',
  copyright: '',
  footer_description: '',
  media_sosial: {
    instagram: '',
    facebook: '',
    youtube: '',
    tiktok: '',
    linkedin: '',
    website: '',
    whatsapp: '',
  },
  label_menu: {
    posts: '',
    agenda: '',
    announcements: '',
    galeri: '',
    documents: '',
    structure: '',
  },
  seo_title: '',
  seo_description: '',
  ga_tracking_id: '',
  public_website_enabled: true,
  modul_aktif: {
    posts: true,
    agenda: true,
    announcements: true,
    galeri: true,
    documents: true,
    structure: true,
  }
})

const hasPendingUploads = computed(() => {
  return !!selectedLogoFile.value || !!selectedHeroFile.value
})

const publicUrl = computed(() => {
  return `/organizations/${form.value.subdomain}`
})

const livePreviewHeroStyle = computed(() => {
  const bg = heroPreview.value || resolveImageUrl(form.value.hero_image)
  if (bg) {
    return {
      backgroundImage: `url("${bg}")`,
      backgroundColor: form.value.warna_tema || '#00346F'
    }
  }
  return {
    backgroundColor: form.value.warna_tema || '#00346F'
  }
})

const loadOrganization = async () => {
  isLoading.value = true
  try {
    const orgId = authStore.organization_id || (authStore.user as any)?.organization_id || authStore.user?.organization?.id
    if (!orgId) {
      toastStore.error('Organisasi tidak ditemukan.')
      return
    }

    const org = await organizationService.getById(orgId)
    const m = (org.modul_aktif || {}) as Record<string, any>
    const labels = (org.label_menu || {}) as Record<string, string>
    const socials = (org.media_sosial || {}) as Record<string, string>

    form.value = {
      id: org.id,
      nama: org.nama,
      subdomain: org.subdomain,
      jenis: org.jenis,
      warna_tema: org.warna_tema || '#1d4ed8',
      logo: org.logo || null,
      hero_image: (org as any).hero_image || m.hero_image || null,
      slogan: typeof m.slogan === 'string' ? m.slogan : '',
      deskripsi: typeof m.deskripsi === 'string' ? m.deskripsi : '',
      deskripsi_lengkap: typeof m.deskripsi_lengkap === 'string' ? m.deskripsi_lengkap : '',
      hero_title: typeof m.hero_title === 'string' ? m.hero_title : '',
      hero_subtitle: typeof m.hero_subtitle === 'string' ? m.hero_subtitle : '',
      email: (org as any).email || (typeof m.email_publik === 'string' ? m.email_publik : ''),
      telepon: (org as any).telepon || '',
      alamat: (org as any).alamat || '',
      copyright: typeof m.copyright === 'string' ? m.copyright : (typeof m.footer_copyright === 'string' ? m.footer_copyright : ''),
      footer_description: typeof m.footer_description === 'string' ? m.footer_description : '',
      media_sosial: {
        instagram: socials.instagram || (typeof m.instagram === 'string' ? m.instagram : ''),
        facebook: socials.facebook || (typeof m.facebook === 'string' ? m.facebook : ''),
        youtube: socials.youtube || '',
        tiktok: socials.tiktok || '',
        linkedin: socials.linkedin || '',
        website: socials.website || (typeof m.website_eksternal === 'string' ? m.website_eksternal : ''),
        whatsapp: socials.whatsapp || '',
      },
      label_menu: {
        posts: labels.posts || labels.berita || '',
        agenda: labels.agenda || labels.kegiatan || '',
        announcements: labels.announcements || labels.pengumuman || '',
        galeri: labels.galeri || labels.gallery || '',
        documents: labels.documents || labels.dokumen || '',
        structure: labels.structure || labels.struktur || '',
      },
      seo_title: typeof m.seo_title === 'string' ? m.seo_title : '',
      seo_description: typeof m.seo_description === 'string' ? m.seo_description : '',
      ga_tracking_id: (org as any).ga_tracking_id || '',
      public_website_enabled: m.public_website_enabled !== false,
      modul_aktif: {
        posts: m.posts !== false && m.articles !== false,
        agenda: m.agenda !== false,
        announcements: m.announcements !== false && m.pengumuman !== false,
        galeri: m.galeri !== false && m.gallery !== false,
        documents: m.documents !== false && m.dokumen !== false,
        structure: m.structure !== false && m.struktur !== false,
      }
    }

    selectedLogoFile.value = null
    logoPreview.value = null
    selectedHeroFile.value = null
    heroPreview.value = null
  } catch (error: any) {
    toastStore.error('Gagal memuat pengaturan organisasi: ' + error.message)
  } finally {
    isLoading.value = false
  }
}

const handleLogoSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    selectedLogoFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeLogo = () => {
  selectedLogoFile.value = null
  logoPreview.value = null
  form.value.logo = null
}

const handleHeroSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    selectedHeroFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      heroPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeHeroImage = async () => {
  selectedHeroFile.value = null
  heroPreview.value = null
  form.value.hero_image = null

  if (form.value.id) {
    try {
      await organizationService.removeHero(form.value.id)
      toastStore.success('Foto hero berhasil dihapus.')
    } catch (e: any) {
      // Ignored if unsaved
    }
  }
}

const saveSettings = async () => {
  if (!form.value.id) return

  isSubmitting.value = true
  try {
    // 1. Upload logo if newly selected
    if (selectedLogoFile.value) {
      const logoRes = await organizationService.uploadLogo(form.value.id, selectedLogoFile.value)
      form.value.logo = (logoRes as any).logo || form.value.logo
      selectedLogoFile.value = null
      logoPreview.value = null
    }

    // 2. Upload hero if newly selected
    if (selectedHeroFile.value) {
      const heroRes = await organizationService.uploadHero(form.value.id, selectedHeroFile.value)
      form.value.hero_image = (heroRes as any).hero_image || form.value.hero_image
      selectedHeroFile.value = null
      heroPreview.value = null
    }

    // 3. Save standard settings
    const payloadModul = {
      ...form.value.modul_aktif,
      slogan: form.value.slogan,
      deskripsi: form.value.deskripsi,
      deskripsi_lengkap: form.value.deskripsi_lengkap,
      hero_title: form.value.hero_title,
      hero_subtitle: form.value.hero_subtitle,
      hero_image: form.value.hero_image,
      email_publik: form.value.email,
      instagram: form.value.media_sosial.instagram,
      facebook: form.value.media_sosial.facebook,
      youtube: form.value.media_sosial.youtube,
      tiktok: form.value.media_sosial.tiktok,
      linkedin: form.value.media_sosial.linkedin,
      website_eksternal: form.value.media_sosial.website,
      copyright: form.value.copyright,
      footer_copyright: form.value.copyright,
      footer_description: form.value.footer_description,
      seo_title: form.value.seo_title,
      seo_description: form.value.seo_description,
      public_website_enabled: form.value.public_website_enabled,
    }

    await organizationService.update(form.value.id, {
      nama: form.value.nama,
      subdomain: form.value.subdomain,
      jenis: form.value.jenis as any,
      warna_tema: form.value.warna_tema,
      logo: form.value.logo,
      hero_image: form.value.hero_image,
      email: form.value.email,
      telepon: form.value.telepon,
      alamat: form.value.alamat,
      media_sosial: form.value.media_sosial,
      label_menu: form.value.label_menu,
      ga_tracking_id: form.value.ga_tracking_id,
      modul_aktif: payloadModul,
    } as any)

    toastStore.success('Pengaturan website dan profil organisasi berhasil disimpan!')
    await loadOrganization()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menyimpan pengaturan.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadOrganization()
})
</script>
