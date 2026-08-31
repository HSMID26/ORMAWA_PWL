<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow pt-16">
      
      <!-- ─── 01. FULL-WIDTH INSTITUTIONAL HERO BACKGROUND ─────────── -->
      <section
        class="relative w-full overflow-hidden min-h-[520px] sm:min-h-[540px] lg:min-h-[560px] flex items-center bg-[#001B3F]"
        aria-label="Hero Portal Kemahasiswaan ITI"
      >
        <!-- Full-Width Background Image -->
        <img
          src="/images/campus/iti-campus-hero.jpg"
          alt="Institut Teknologi Indonesia dan kawasan kegiatan kemahasiswaan"
          class="absolute inset-0 w-full h-full object-cover object-center scale-100 transition-transform duration-700 motion-safe:hover:scale-[1.01]"
          loading="eager"
        />

        <!-- Refined Dark Institutional Gradient Overlay for High Text Contrast -->
        <div
          class="absolute inset-0 bg-gradient-to-r from-[#001B3F]/90 via-[#00346F]/80 to-[#001B3F]/50 md:from-[#001B3F]/95 md:via-[#00346F]/75 md:to-transparent"
          aria-hidden="true"
        ></div>

        <!-- Hero Content Container -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 lg:py-20">
          <div class="max-w-2xl lg:max-w-[680px] space-y-5 sm:space-y-6 text-left">
            
            <!-- Eyebrow Badge -->
            <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 backdrop-blur-xs px-3.5 py-1 text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-white shadow-2xs">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
              <span>PORTAL ORGANISASI KEMAHASISWAAN ITI</span>
            </div>

            <!-- Headline H1 -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[52px] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
              Temukan Organisasi,<br class="hidden sm:inline" />
              Kegiatan, dan Kabar Mahasiswa
            </h1>

            <!-- Description -->
            <p class="text-sm sm:text-base text-white/90 max-w-xl leading-relaxed font-normal">
              Platform resmi untuk menemukan organisasi mahasiswa, warta, agenda, dan informasi kemahasiswaan Institut Teknologi Indonesia.
            </p>

            <!-- Actions -->
            <div class="pt-2 flex flex-wrap items-center gap-3.5">
              <router-link
                to="/organizations"
                class="rounded-lg bg-white text-[#00346F] hover:bg-[#F3F4F5] px-6 py-3 text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition duration-150 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white flex items-center gap-2"
              >
                <span>Jelajahi Organisasi</span>
                <span>&rarr;</span>
              </router-link>

              <router-link
                to="/berita"
                class="rounded-lg border border-white/30 bg-white/10 backdrop-blur-xs hover:bg-white/20 px-5 py-3 text-xs sm:text-sm font-semibold text-white transition duration-150 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white shadow-2xs"
              >
                Lihat Berita
              </router-link>
            </div>

            <!-- Live Compact Fact Strip (Counters) -->
            <div class="pt-4 border-t border-white/20 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-white/85 font-medium">
              <div class="flex items-center gap-1.5">
                <span class="font-bold text-white">{{ stats.total_organizations }}</span>
                <span>Organisasi Terdaftar</span>
              </div>
              <span class="text-white/40 hidden sm:inline">&bull;</span>
              <div class="flex items-center gap-1.5">
                <span class="font-bold text-white">{{ stats.total_articles }}</span>
                <span>Warta Publik</span>
              </div>
              <span class="text-white/40 hidden sm:inline">&bull;</span>
              <div class="flex items-center gap-1.5">
                <span class="font-bold text-white">{{ stats.total_agenda }}</span>
                <span>Agenda Kampus</span>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- ─── 02. PRIMARY PUBLIC NAVIGATION ("Jelajahi Portal") ─────── -->
      <section class="py-10 sm:py-12 bg-white border-y border-[#C2C6D3]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
          
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Akses Cepat</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#191C1D] mt-0.5">Jelajahi Portal</h2>
            <p class="text-xs text-[#737783] mt-1">Pilih layanan informasi kemahasiswaan yang ingin Anda akses.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Tile 1: Direktori Ormawa -->
            <router-link
              to="/organizations"
              class="rounded border border-[#C2C6D3] bg-[#F8F9FA] p-5 hover:border-[#00346F] hover:bg-white hover:shadow-xs transition duration-150 flex flex-col justify-between group shadow-2xs"
            >
              <div class="space-y-3">
                <div class="h-10 w-10 rounded bg-white text-[#00346F] flex items-center justify-center border border-[#C2C6D3] group-hover:bg-[#00346F] group-hover:text-white transition duration-150 shadow-2xs">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-[#191C1D] group-hover:text-[#00346F] transition">Direktori Ormawa</h3>
                  <p class="text-xs text-[#737783] mt-1 leading-relaxed">Temukan organisasi mahasiswa ITI</p>
                </div>
              </div>
              <div class="mt-4 pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-xs font-semibold text-[#00346F]">
                <span>Buka Direktori</span>
                <span class="group-hover:translate-x-0.5 transition duration-150">&rarr;</span>
              </div>
            </router-link>

            <!-- Tile 2: Berita & Warta -->
            <router-link
              to="/berita"
              class="rounded border border-[#C2C6D3] bg-[#F8F9FA] p-5 hover:border-[#00346F] hover:bg-white hover:shadow-xs transition duration-150 flex flex-col justify-between group shadow-2xs"
            >
              <div class="space-y-3">
                <div class="h-10 w-10 rounded bg-white text-[#00346F] flex items-center justify-center border border-[#C2C6D3] group-hover:bg-[#00346F] group-hover:text-white transition duration-150 shadow-2xs">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-[#191C1D] group-hover:text-[#00346F] transition">Berita</h3>
                  <p class="text-xs text-[#737783] mt-1 leading-relaxed">Ikuti warta dan publikasi mahasiswa</p>
                </div>
              </div>
              <div class="mt-4 pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-xs font-semibold text-[#00346F]">
                <span>Baca Berita</span>
                <span class="group-hover:translate-x-0.5 transition duration-150">&rarr;</span>
              </div>
            </router-link>

            <!-- Tile 3: Agenda Kegiatan -->
            <router-link
              to="/agenda"
              class="rounded border border-[#C2C6D3] bg-[#F8F9FA] p-5 hover:border-[#00346F] hover:bg-white hover:shadow-xs transition duration-150 flex flex-col justify-between group shadow-2xs"
            >
              <div class="space-y-3">
                <div class="h-10 w-10 rounded bg-white text-[#00346F] flex items-center justify-center border border-[#C2C6D3] group-hover:bg-[#00346F] group-hover:text-white transition duration-150 shadow-2xs">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-[#191C1D] group-hover:text-[#00346F] transition">Agenda</h3>
                  <p class="text-xs text-[#737783] mt-1 leading-relaxed">Lihat kegiatan mahasiswa</p>
                </div>
              </div>
              <div class="mt-4 pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-xs font-semibold text-[#00346F]">
                <span>Lihat Agenda</span>
                <span class="group-hover:translate-x-0.5 transition duration-150">&rarr;</span>
              </div>
            </router-link>

            <!-- Tile 4: Pengumuman Resmi -->
            <router-link
              to="/pengumuman"
              class="rounded border border-[#C2C6D3] bg-[#F8F9FA] p-5 hover:border-[#00346F] hover:bg-white hover:shadow-xs transition duration-150 flex flex-col justify-between group shadow-2xs"
            >
              <div class="space-y-3">
                <div class="h-10 w-10 rounded bg-white text-[#00346F] flex items-center justify-center border border-[#C2C6D3] group-hover:bg-[#00346F] group-hover:text-white transition duration-150 shadow-2xs">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-[#191C1D] group-hover:text-[#00346F] transition">Pengumuman</h3>
                  <p class="text-xs text-[#737783] mt-1 leading-relaxed">Informasi resmi organisasi</p>
                </div>
              </div>
              <div class="mt-4 pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-xs font-semibold text-[#00346F]">
                <span>Lihat Maklumat</span>
                <span class="group-hover:translate-x-0.5 transition duration-150">&rarr;</span>
              </div>
            </router-link>

          </div>

        </div>
      </section>

      <!-- ─── 03. SHORT INSTITUTIONAL INTRODUCTION ──────────────────── -->
      <section class="py-12 sm:py-16 bg-[#F8F9FA]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          
          <div class="max-w-2xl space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Pusat Kemahasiswaan ITI</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#191C1D]">Ekosistem Organisasi Mahasiswa ITI</h2>
            <p class="text-xs sm:text-sm text-[#424751] leading-relaxed">
              ORMAWA ITI menghimpun informasi publik organisasi mahasiswa Institut Teknologi Indonesia dalam satu portal yang terintegrasi, transparan, dan mudah diakses.
            </p>
          </div>

          <!-- 3 Compact Value Points -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <div class="rounded border border-[#C2C6D3] bg-white p-6 space-y-3 shadow-2xs hover:border-[#00346F] transition duration-150">
              <span class="inline-block rounded bg-[#E7E8E9] px-2.5 py-0.5 text-[10px] font-bold text-[#00346F] font-mono">01</span>
              <h3 class="text-base font-bold text-[#191C1D]">Tata Kelola Lembaga</h3>
              <p class="text-xs text-[#424751] leading-relaxed">
                Wadah koordinasi resmi bagi Himpunan Mahasiswa Program Studi (HMPS), Unit Kegiatan Mahasiswa (UKM), dan Badan Eksekutif Mahasiswa (BEM).
              </p>
            </div>

            <div class="rounded border border-[#C2C6D3] bg-white p-6 space-y-3 shadow-2xs hover:border-[#00346F] transition duration-150">
              <span class="inline-block rounded bg-[#E7E8E9] px-2.5 py-0.5 text-[10px] font-bold text-[#00346F] font-mono">02</span>
              <h3 class="text-base font-bold text-[#191C1D]">Warta & Literasi Publik</h3>
              <p class="text-xs text-[#424751] leading-relaxed">
                Kanal penyebaran kabar terkini, liputan aktivitas, dan warta karya mahasiswa di lingkungan kampus Institut Teknologi Indonesia.
              </p>
            </div>

            <div class="rounded border border-[#C2C6D3] bg-white p-6 space-y-3 shadow-2xs hover:border-[#00346F] transition duration-150">
              <span class="inline-block rounded bg-[#E7E8E9] px-2.5 py-0.5 text-[10px] font-bold text-[#00346F] font-mono">03</span>
              <h3 class="text-base font-bold text-[#191C1D]">Agenda & Kolaborasi</h3>
              <p class="text-xs text-[#424751] leading-relaxed">
                Pusat publikasi jadwal program kerja, seminar, pelatihan kepemimpinan, dan forum kegiatan mahasiswa secara terbuka.
              </p>
            </div>

          </div>

          <div class="pt-2">
            <router-link
              to="/organizations"
              class="inline-flex items-center gap-2 rounded bg-[#00346F] hover:bg-[#004A99] px-5 py-2.5 text-xs font-semibold text-white transition shadow-2xs"
            >
              <span>Jelajahi Direktori Ormawa</span>
              <span>&rarr;</span>
            </router-link>
          </div>

        </div>
      </section>

    </main>

    <PublicFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PublicNavbar from '@/components/public/PublicNavbar.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'

const stats = ref({
  total_organizations: 0,
  total_articles: 0,
  total_agenda: 0,
})

const isLoadingStats = ref(true)

onMounted(async () => {
  try {
    const data = await publicService.getHome()
    if (data?.stats) {
      stats.value = {
        total_organizations: data.stats.total_organizations ?? 0,
        total_articles: data.stats.total_articles ?? 0,
        total_agenda: data.stats.total_agenda ?? 0,
      }
    }
  } catch (err) {
    console.error('Failed to load portal stats:', err)
  } finally {
    isLoadingStats.value = false
  }
})

useSeoMeta(() => ({
  title: 'Portal Kemahasiswaan — ORMAWA ITI',
  description: 'Platform resmi untuk menemukan organisasi mahasiswa, warta, agenda, dan informasi kemahasiswaan Institut Teknologi Indonesia.',
  ogType: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'ORMAWA ITI',
    url: typeof window !== 'undefined' ? window.location.origin : 'https://ormawa.iti.ac.id',
  }
}))
</script>
