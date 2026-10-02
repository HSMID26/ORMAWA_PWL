<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <SettingsIcon class="h-5 w-5 text-brand-600" />
          Pengaturan Sistem & Portal Publik
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
          Pusat kendali konfigurasi multi-tenant, kuota storage, dan kustomisasi homepage portal publik.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <a
          href="/"
          target="_blank"
          class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition"
        >
          <ExternalLinkIcon class="h-4 w-4 text-brand-600" />
          <span>Lihat Portal Publik</span>
        </a>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="h-48 w-full animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800"></div>
      <div class="h-64 w-full animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Tabs Navigation -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-800">
        <button
          type="button"
          @click="activeTab = 'system'"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition flex items-center gap-2',
            activeTab === 'system'
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          <ServerIcon class="h-4 w-4" />
          <span>Konfigurasi Sistem & Server</span>
        </button>

        <button
          type="button"
          @click="activeTab = 'homepage'"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition flex items-center gap-2',
            activeTab === 'homepage'
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          <LayoutTemplateIcon class="h-4 w-4" />
          <span>Kustomisasi Homepage Publik</span>
        </button>

        <button
          type="button"
          @click="activeTab = 'footer'"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition flex items-center gap-2',
            activeTab === 'footer'
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          <PanelBottomIcon class="h-4 w-4" />
          <span>Kustomisasi Footer Publik</span>
        </button>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 1: KONFIGURASI SISTEM & SERVER                                      -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'system'" class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        
        <!-- Panel 1: Konfigurasi Institusi & Domain -->
        <div class="flex flex-col gap-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] space-y-5">
            <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
              <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Identitas Institusi & Jaringan
              </h3>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pengaturan dasar identitas kampus dan konfigurasi subdomain wildcard.
              </p>
            </div>

            <div class="flex flex-col gap-4">
              <!-- Nama Institusi -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                  Nama Institusi / Kampus
                </label>
                <input
                  v-model="globalSettings.campusName"
                  type="text"
                  class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
              </div>

              <!-- Domain Utama -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                  Domain Utama (Root Domain)
                </label>
                <div class="flex items-center">
                  <span class="inline-flex h-10 items-center rounded-l-xl border border-r-0 border-gray-300 bg-gray-50 px-3 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800">
                    https://*.
                  </span>
                  <input
                    v-model="globalSettings.mainDomain"
                    type="text"
                    placeholder="iti.ac.id"
                    class="h-10 w-full rounded-r-xl border border-gray-300 bg-transparent px-3.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  />
                </div>
                <p class="mt-1 text-[11px] text-gray-400">Subdomain tiap organisasi akan merujuk ke domain ini.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel 2: Batasan Sistem & Keamanan -->
        <div class="flex flex-col gap-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] space-y-5">
            <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
              <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Alokasi & Pembatasan Sistem
              </h3>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Manajemen kuota sumber daya multi-tenant dan status operasional sistem.
              </p>
            </div>

            <div class="flex flex-col gap-4">
              <!-- Limit Storage -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                  Default Batas Penyimpanan (per Organisasi)
                </label>
                <div class="relative">
                  <input
                    v-model="globalSettings.defaultStorageLimit"
                    type="number"
                    min="1"
                    max="1000"
                    class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 pr-12 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  />
                  <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500">
                    GB
                  </span>
                </div>
              </div>

              <!-- Toggles -->
              <div class="flex flex-col gap-3 pt-2">
                <!-- Approval Ormawa Baru -->
                <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
                  <div>
                    <h4 class="text-xs font-bold text-gray-800 dark:text-white/90">Otomatisasi Pendaftaran</h4>
                    <p class="text-[11px] text-gray-500">HMPS/UKM baru langsung aktif tanpa review manual Super Admin.</p>
                  </div>
                  <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" v-model="globalSettings.autoApproveNewOrg" class="peer sr-only" />
                    <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700 dark:border-gray-600"></div>
                  </label>
                </div>

                <!-- Maintenance Mode -->
                <div class="flex items-center justify-between rounded-xl bg-rose-50/80 p-3 border border-rose-200 dark:bg-rose-950/20 dark:border-rose-900">
                  <div>
                    <h4 class="text-xs font-bold text-rose-800 dark:text-rose-400">Mode Pemeliharaan (Maintenance)</h4>
                    <p class="text-[11px] text-rose-600 dark:text-rose-500">Nonaktifkan seluruh akses web publik sementara waktu.</p>
                  </div>
                  <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" v-model="globalSettings.maintenanceMode" class="peer sr-only" />
                    <div class="h-6 w-11 rounded-full bg-rose-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-white after:bg-white after:transition-all after:content-[''] peer-checked:bg-rose-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700"></div>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 2: KUSTOMISASI HOMEPAGE PUBLIK (TASK 4)                              -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'homepage'" class="space-y-6">
        
        <!-- Live Preview Mockup -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div>
              <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <SparklesIcon class="h-4 w-4 text-brand-600" />
                Pratinjau Langsung Hero Portal (Live Preview)
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Tampilan langsung banner atas yang dilihat oleh pengunjung portal publik.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
              Live Preview
            </span>
          </div>

          <!-- Hero Mockup Container -->
          <div class="relative overflow-hidden rounded-xl bg-[#001B3F] text-white p-6 sm:p-8 min-h-[260px] flex flex-col justify-center shadow-inner">
            <div class="relative z-10 max-w-2xl space-y-3">
              <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 backdrop-blur-xs px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                <span>{{ globalSettings.heroBadge || 'PORTAL ORGANISASI KEMAHASISWAAN ITI' }}</span>
              </div>

              <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white tracking-tight leading-tight whitespace-pre-line">
                {{ globalSettings.heroTitle || 'Temukan Organisasi,\nKegiatan, dan Kabar Mahasiswa' }}
              </h2>

              <p class="text-xs text-white/90 leading-relaxed max-w-lg font-normal">
                {{ globalSettings.heroSubtitle || 'Platform resmi untuk menemukan organisasi mahasiswa, warta, agenda, dan informasi kemahasiswaan Institut Teknologi Indonesia.' }}
              </p>

              <div class="flex flex-wrap items-center gap-3 pt-2">
                <span class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-[#00346F] shadow-xs">
                  {{ globalSettings.heroCtaText || 'Jelajahi Organisasi' }} &rarr;
                </span>
                <span class="rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold text-white">
                  {{ globalSettings.heroSecondaryCtaText || 'Lihat Berita' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          
          <!-- Section 1: Hero Banner Settings -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
              <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                1. Hero Banner Portal Utama
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Teks judul, badge eyebrow, dan tombol ajakan bertindak (Call-to-Action).</p>
            </div>

            <div class="space-y-3.5 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Eyebrow / Badge Label</label>
                <input
                  v-model="globalSettings.heroBadge"
                  type="text"
                  placeholder="PORTAL ORGANISASI KEMAHASISWAAN ITI"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Headline Utama (H1 Title)</label>
                <textarea
                  v-model="globalSettings.heroTitle"
                  rows="2"
                  placeholder="Temukan Organisasi, Kegiatan, dan Kabar Mahasiswa"
                  class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                ></textarea>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subjudul / Deskripsi Hero</label>
                <textarea
                  v-model="globalSettings.heroSubtitle"
                  rows="2"
                  placeholder="Platform resmi untuk menemukan organisasi mahasiswa, warta, agenda, dan informasi kemahasiswaan..."
                  class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Teks Tombol CTA 1 (Primary)</label>
                  <input
                    v-model="globalSettings.heroCtaText"
                    type="text"
                    placeholder="Jelajahi Organisasi"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link Tombol CTA 1</label>
                  <input
                    v-model="globalSettings.heroCtaLink"
                    type="text"
                    placeholder="/organizations"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Teks Tombol CTA 2 (Secondary)</label>
                  <input
                    v-model="globalSettings.heroSecondaryCtaText"
                    type="text"
                    placeholder="Lihat Berita"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link Tombol CTA 2</label>
                  <input
                    v-model="globalSettings.heroSecondaryCtaLink"
                    type="text"
                    placeholder="/berita"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Intro Section & Section Toggles -->
          <div class="space-y-6">
            
            <!-- Section Intro -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
              <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                  2. Seksi Intro / Tentang Ekosistem Ormawa
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Penjelasan umum tentang fungsi portal dan peranan organisasi mahasiswa.</p>
              </div>

              <div class="space-y-3.5 text-xs">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Badge / Kategori Intro</label>
                  <input
                    v-model="globalSettings.introBadge"
                    type="text"
                    placeholder="Pusat Kemahasiswaan ITI"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Seksi Intro</label>
                  <input
                    v-model="globalSettings.introTitle"
                    type="text"
                    placeholder="Ekosistem Organisasi Mahasiswa ITI"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Seksi Intro</label>
                  <textarea
                    v-model="globalSettings.introDescription"
                    rows="3"
                    placeholder="ORMAWA ITI menghimpun informasi publik organisasi mahasiswa Institut Teknologi Indonesia..."
                    class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Section 3: Visibility Toggles -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
              <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                  3. Visibilitas Bagian (Section Toggles)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Atur komponen apa saja yang ditampilkan di beranda publik portal.</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                
                <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 flex items-center justify-between">
                  <div>
                    <span class="font-bold text-gray-900 dark:text-white block">Statistik Cepat Hero</span>
                    <span class="text-[10px] text-gray-500">Jumlah ormawa, warta & agenda</span>
                  </div>
                  <input type="checkbox" v-model="globalSettings.showStatsSection" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                </div>

                <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 flex items-center justify-between">
                  <div>
                    <span class="font-bold text-gray-900 dark:text-white block">Akses Cepat (Shortcut)</span>
                    <span class="text-[10px] text-gray-500">4 Card navigasi utama</span>
                  </div>
                  <input type="checkbox" v-model="globalSettings.showQuickLinks" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                </div>

                <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 flex items-center justify-between">
                  <div>
                    <span class="font-bold text-gray-900 dark:text-white block">Seksi Intro Ekosistem</span>
                    <span class="text-[10px] text-gray-500">Profil & 3 nilai keunggulan</span>
                  </div>
                  <input type="checkbox" v-model="globalSettings.showIntroSection" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                </div>

                <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 flex items-center justify-between">
                  <div>
                    <span class="font-bold text-gray-900 dark:text-white block">Warta Publik Terkini</span>
                    <span class="text-[10px] text-gray-500">Highlight artikel di portal</span>
                  </div>
                  <input type="checkbox" v-model="globalSettings.showLatestArticles" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                </div>

              </div>
            </div>

          </div>

        </div>

      </div>

      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <!-- TAB 3: KUSTOMISASI FOOTER WEBSITE PUBLIK UTAMA                         -->
      <!-- ═════════════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'footer'" class="space-y-6">
        
        <!-- Live Preview Mockup for Platform Footer -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div>
              <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <SparklesIcon class="h-4 w-4 text-brand-600" />
                Pratinjau Langsung Footer Portal Utama (Live Preview)
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Tampilan footer yang dilihat pengunjung di seluruh halaman portal publik utama.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
              Live Preview
            </span>
          </div>

          <!-- Footer Mockup Container -->
          <div class="rounded-xl bg-[#191C1D] text-[#C6C5CF] p-6 text-xs border border-slate-800 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
              
              <!-- Col 1: Identity -->
              <div class="md:col-span-4 space-y-2">
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 rounded-lg bg-white flex items-center justify-center p-0.5 shrink-0">
                    <img src="/images/logo/iti-logo.png" alt="ITI" class="h-full w-full object-contain" />
                  </div>
                  <div>
                    <span class="text-xs font-bold text-white block">ORMAWA ITI</span>
                    <span class="text-[9px] text-[#A0A0A8] block">{{ globalSettings.campusName || 'Institut Teknologi Indonesia' }}</span>
                  </div>
                </div>
                <p class="text-[11px] text-[#A0A0A8] leading-relaxed">
                  {{ globalSettings.footerDescription || 'Portal publik resmi tata kelola, warta berita, agenda kegiatan, dan dokumen seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.' }}
                </p>
              </div>

              <!-- Col 2: Navigasi -->
              <div class="md:col-span-2 space-y-2">
                <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Navigasi</h5>
                <ul class="space-y-1 text-[11px] text-slate-400">
                  <li>Beranda</li>
                  <li>Direktori Ormawa</li>
                  <li>Warta Mahasiswa</li>
                  <li>Agenda Kegiatan</li>
                </ul>
              </div>

              <!-- Col 3: Media Sosial -->
              <div class="md:col-span-3 space-y-2">
                <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Media Sosial Resmi</h5>
                <div class="flex flex-wrap gap-1.5 pt-0.5">
                  <span v-if="globalSettings.footerInstagram" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-pink-300">Instagram</span>
                  <span v-if="globalSettings.footerFacebook" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-blue-300">Facebook</span>
                  <span v-if="globalSettings.footerYoutube" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-red-300">YouTube</span>
                  <span v-if="globalSettings.footerTiktok" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-cyan-300">TikTok</span>
                  <span v-if="globalSettings.footerWhatsapp" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-emerald-300">WhatsApp</span>
                </div>
              </div>

              <!-- Col 4: Alamat & Kontak -->
              <div class="md:col-span-3 space-y-1.5 text-[11px]">
                <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Sekretariat & Kontak</h5>
                <p class="text-[#A0A0A8] leading-tight whitespace-pre-line">{{ globalSettings.footerAddress || 'Jl. Raya Puspiptek Serpong, Tangerang Selatan' }}</p>
                <p v-if="globalSettings.footerEmail" class="text-slate-300">{{ globalSettings.footerEmail }}</p>
                <p v-if="globalSettings.footerPhone" class="text-slate-300">{{ globalSettings.footerPhone }}</p>
              </div>

            </div>

            <!-- Strip -->
            <div class="pt-4 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-500">
              <p>&copy; {{ new Date().getFullYear() }} {{ globalSettings.footerCopyright || 'Institut Teknologi Indonesia. Hak Cipta Dilindungi.' }}</p>
              <p>Sistem Informasi Manajemen Organisasi Kemahasiswaan</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          
          <!-- Section 1: Profil & Deskripsi Footer -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
              <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                1. Profil & Informasi Footer Portal
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Deskripsi institusi dan kalimat hak cipta (copyright) pada portal utama.</p>
            </div>

            <div class="space-y-3.5 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat Portal / Institusi</label>
                <textarea
                  v-model="globalSettings.footerDescription"
                  rows="3"
                  placeholder="Portal publik resmi tata kelola, warta berita, agenda kegiatan..."
                  class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                ></textarea>
                <p class="text-[10px] text-gray-400 mt-1">Muncul di kolom pertama footer portal utama.</p>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Teks Hak Cipta (Copyright)</label>
                <input
                  v-model="globalSettings.footerCopyright"
                  type="text"
                  placeholder="Institut Teknologi Indonesia. Hak Cipta Dilindungi."
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <p class="text-[10px] text-gray-400 mt-1">Tahun akan otomatis disematkan di depan kalimat ini.</p>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Website Resmi Kampus</label>
                <input
                  v-model="globalSettings.footerWebsite"
                  type="text"
                  placeholder="https://iti.ac.id"
                  class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>
            </div>
          </div>

          <!-- Section 2: Kontak & Media Sosial Platform -->
          <div class="space-y-6">
            
            <!-- Kontak Resmi -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
              <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                  2. Alamat Kampus & Kontak Resmi (PKA ITI)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Informasi sekretariat dan jalur komunikasi publik kampus.</p>
              </div>

              <div class="space-y-3.5 text-xs">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Kampus / Sekretariat PKA</label>
                  <textarea
                    v-model="globalSettings.footerAddress"
                    rows="2"
                    placeholder="Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314..."
                    class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  ></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Resmi PKA / Kampus</label>
                    <input
                      v-model="globalSettings.footerEmail"
                      type="email"
                      placeholder="pka@iti.ac.id"
                      class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                  </div>
                  <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon Kantor</label>
                    <input
                      v-model="globalSettings.footerPhone"
                      type="text"
                      placeholder="021-7561092"
                      class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                  </div>
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor WhatsApp Resmi (Opsional)</label>
                  <input
                    v-model="globalSettings.footerWhatsapp"
                    type="text"
                    placeholder="081234567890"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>
            </div>

            <!-- Media Sosial Platform -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
              <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                  3. Tautan Media Sosial Resmi Kampus
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Tautan profil media sosial resmi Institut Teknologi Indonesia.</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link Instagram</label>
                  <input
                    v-model="globalSettings.footerInstagram"
                    type="text"
                    placeholder="https://instagram.com/iti_official"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link Facebook</label>
                  <input
                    v-model="globalSettings.footerFacebook"
                    type="text"
                    placeholder="https://facebook.com/itiofficial"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link YouTube</label>
                  <input
                    v-model="globalSettings.footerYoutube"
                    type="text"
                    placeholder="https://youtube.com/@itiofficial"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link TikTok</label>
                  <input
                    v-model="globalSettings.footerTiktok"
                    type="text"
                    placeholder="https://tiktok.com/@iti_official"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>

      <!-- Action Button (Full Width Sticky Footer) -->
      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <span v-if="statusMessage" :class="statusType === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" class="text-xs font-semibold">
            {{ statusMessage }}
          </span>
          <span v-else class="text-xs text-gray-400">
            Perubahan akan langsung berlaku pada sistem dan portal publik.
          </span>
        </div>
        <button 
          @click="saveGlobalSettings"
          :disabled="isSaving || isLoading"
          class="flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-8 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 transition-colors shadow-theme-xs disabled:opacity-50 cursor-pointer"
        >
          <SaveIcon class="h-4 w-4" />
          <span v-if="isSaving">Menyimpan...</span>
          <span v-else>Simpan Semua Konfigurasi</span>
        </button>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import api from '@/services/api'
import {
  SettingsIcon,
  ServerIcon,
  LayoutTemplateIcon,
  SaveIcon,
  ExternalLinkIcon,
  SparklesIcon,
  PanelBottomIcon,
} from 'lucide-vue-next'

const currentPageTitle = ref('Pengaturan Sistem Pusat')
const activeTab = ref<'system' | 'homepage' | 'footer'>('system')
const isLoading = ref(true)
const isSaving = ref(false)
const statusMessage = ref('')
const statusType = ref<'success' | 'error'>('success')

// State Global Setting (Level Super Admin)
const globalSettings = ref({
  campusName: 'Institut Teknologi Indonesia',
  mainDomain: 'iti.ac.id',
  defaultStorageLimit: 20,
  autoApproveNewOrg: false,
  maintenanceMode: false,
  // Homepage Customization
  heroBadge: 'PORTAL ORGANISASI KEMAHASISWAAN ITI',
  heroTitle: 'Temukan Organisasi,\nKegiatan, dan Kabar Mahasiswa',
  heroSubtitle: 'Platform resmi untuk menemukan organisasi mahasiswa, warta, agenda, dan informasi kemahasiswaan Institut Teknologi Indonesia.',
  heroImage: '',
  heroCtaText: 'Jelajahi Organisasi',
  heroCtaLink: '/organizations',
  heroSecondaryCtaText: 'Lihat Berita',
  heroSecondaryCtaLink: '/berita',
  introBadge: 'Pusat Kemahasiswaan ITI',
  introTitle: 'Ekosistem Organisasi Mahasiswa ITI',
  introDescription: 'ORMAWA ITI menghimpun informasi publik organisasi mahasiswa Institut Teknologi Indonesia dalam satu portal yang terintegrasi, transparan, dan mudah diakses.',
  showStatsSection: true,
  showIntroSection: true,
  showQuickLinks: true,
  showLatestArticles: true,
  showUpcomingAgenda: true,
  showAnnouncements: true,
  // Platform Footer Settings
  footerDescription: 'Portal publik resmi tata kelola, warta berita, agenda kegiatan, dan dokumen seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.',
  footerAddress: 'Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314.\nPusat Kemahasiswaan & Alumni (PKA) ITI',
  footerEmail: 'pka@iti.ac.id',
  footerPhone: '021-7561092',
  footerWhatsapp: '081234567890',
  footerInstagram: 'https://instagram.com/iti_official',
  footerFacebook: 'https://facebook.com/itiofficial',
  footerYoutube: 'https://youtube.com/@itiofficial',
  footerTiktok: 'https://tiktok.com/@iti_official',
  footerWebsite: 'https://iti.ac.id',
  footerCopyright: 'Institut Teknologi Indonesia. Hak Cipta Dilindungi.',
})

const loadSettings = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/platform-settings')
    if (res.data?.data) {
      globalSettings.value = { ...globalSettings.value, ...res.data.data }
    }
  } catch (err) {
    console.error('Failed to load platform settings:', err)
  } finally {
    isLoading.value = false
  }
}

const saveGlobalSettings = async () => {
  isSaving.value = true
  statusMessage.value = ''
  try {
    const res = await api.put('/platform-settings', globalSettings.value)
    statusType.value = 'success'
    statusMessage.value = res.data?.message || 'Konfigurasi sistem & homepage berhasil diperbarui.'
    setTimeout(() => { statusMessage.value = '' }, 4000)
  } catch (err: any) {
    statusType.value = 'error'
    statusMessage.value = err.response?.data?.message || 'Gagal menyimpan konfigurasi platform.'
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>
