<template>
  <div class="min-h-screen bg-slate-50/60 flex flex-col font-sans selection:bg-slate-900 selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow">
      
      <!-- ─── 01. ACTIVE ANNOUNCEMENT BULLETIN TICKER ───────────────── -->
      <aside
        v-if="homeData?.active_announcements && homeData.active_announcements.length > 0"
        class="bg-amber-50 border-b border-amber-200/80 py-2 px-4 sm:px-6 lg:px-8"
        aria-label="Pengumuman Penting"
      >
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 text-xs">
          <div class="flex items-center gap-2 text-amber-950 font-medium truncate">
            <span class="rounded bg-amber-700 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white tracking-wide shrink-0">
              PENGUMUMAN
            </span>
            <span class="font-semibold text-slate-900 truncate">{{ homeData.active_announcements[0].title }}</span>
            <span class="text-slate-500 hidden sm:inline">&bull; {{ homeData.active_announcements[0].organization?.nama }}</span>
          </div>
          <router-link
            :to="`/organizations/${homeData.active_announcements[0].organization?.subdomain}/announcements`"
            class="font-semibold text-amber-900 hover:text-amber-700 underline shrink-0"
          >
            Selengkapnya &rarr;
          </router-link>
        </div>
      </aside>

      <!-- ─── 02. PROPORTIONATE EDITORIAL HERO ──────────────────────── -->
      <section class="bg-white border-b border-slate-200 py-12 sm:py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            
            <!-- Left Hero Content (65%) -->
            <div class="lg:col-span-8 space-y-5 text-left">
              <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-900">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-700"></span>
                <span>Portal Organisasi Kemahasiswaan ITI</span>
              </div>

              <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-950 tracking-tight leading-[1.18]">
                Temukan Organisasi, Kegiatan, dan Kabar Mahasiswa.
              </h1>

              <p class="text-sm sm:text-base text-slate-600 max-w-2xl leading-relaxed">
                Pusat informasi resmi seluruh himpunan mahasiswa program studi (HMPS), unit kegiatan mahasiswa (UKM), dan badan eksekutif di lingkungan Institut Teknologi Indonesia.
              </p>

              <!-- Actions & Integrated Facts Strip -->
              <div class="pt-2 flex flex-wrap items-center gap-4">
                <router-link
                  to="/organizations"
                  class="rounded-lg bg-slate-900 px-5 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-blue-900 transition focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900"
                >
                  Jelajahi Direktori Ormawa
                </router-link>
                <a
                  href="#kabar-terbaru"
                  class="text-xs font-semibold text-slate-700 hover:text-slate-950 transition py-2"
                >
                  Kabar Terbaru &darr;
                </a>
              </div>

              <!-- Compact Horizontal Metric Strip -->
              <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-4 sm:gap-6 text-xs text-slate-500">
                <div>
                  <strong class="font-bold text-slate-900">{{ homeData?.stats?.total_organizations || 0 }}</strong> Organisasi Terdaftar
                </div>
                <span class="text-slate-300 hidden sm:inline">&bull;</span>
                <div>
                  <strong class="font-bold text-slate-900">{{ homeData?.stats?.total_articles || 0 }}</strong> Warta Publik
                </div>
                <span class="text-slate-300 hidden sm:inline">&bull;</span>
                <div>
                  <strong class="font-bold text-slate-900">{{ homeData?.stats?.total_agenda || 0 }}</strong> Agenda Kampus
                </div>
              </div>
            </div>

            <!-- Right Hero Visual / Institutional Seal Preview -->
            <div class="lg:col-span-4 hidden lg:flex justify-end">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 space-y-4 max-w-xs w-full">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-sm tracking-wider">
                    ITI
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-900 block">PKA ITI</span>
                    <span class="text-[10px] text-slate-500 block">Pusat Kemahasiswaan & Alumni</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed border-t border-slate-200/70 pt-3">
                  Tata kelola multi-tenant terpadu untuk transparansi dokumentasi, warta, dan struktur kepengurusan organisasi mahasiswa.
                </p>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- ─── 03. EDITORIAL DIRECTORY SECTION ──────────────────────── -->
      <section class="py-12 sm:py-16 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
          
          <div class="flex items-end justify-between border-b border-slate-200 pb-3">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Direktori Ormawa</span>
              <h2 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5">Organisasi Mahasiswa Terdaftar</h2>
            </div>
            <router-link to="/organizations" class="text-xs font-semibold text-blue-900 hover:text-blue-700">
              Lihat Semua &rarr;
            </router-link>
          </div>

          <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
            <div v-for="i in 3" :key="i" class="h-36 rounded-xl bg-slate-50 border border-slate-200 animate-pulse p-4"></div>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
            <router-link
              v-for="org in homeData?.organizations"
              :key="org.id"
              :to="`/organizations/${org.subdomain}`"
              class="rounded-xl border border-slate-200 bg-white p-5 hover:border-slate-400 hover:shadow-2xs transition duration-150 flex flex-col justify-between group"
            >
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <div class="h-9 w-9 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                    <img v-if="org.logo" :src="org.logo" :alt="org.nama" class="h-full w-full object-cover" />
                    <span v-else class="font-bold text-xs text-slate-900">{{ org.nama.charAt(0) }}</span>
                  </div>
                  <span class="rounded bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-700 uppercase">
                    {{ org.jenis }}
                  </span>
                </div>
                <div>
                  <h3 class="text-xs sm:text-sm font-bold text-slate-950 group-hover:text-blue-900 transition leading-snug">
                    {{ org.nama }}
                  </h3>
                  <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                    {{ org.slogan || org.deskripsi || 'Organisasi kemahasiswaan Institut Teknologi Indonesia.' }}
                  </p>
                </div>
              </div>

              <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-medium">
                <span>{{ org.posts_count || 0 }} Warta</span>
                <span class="text-blue-900 font-bold group-hover:translate-x-0.5 transition">&rarr;</span>
              </div>
            </router-link>
          </div>

        </div>
      </section>

      <!-- ─── 04. EDITORIAL FEATURED NEWS ───────────────────────────── -->
      <section id="kabar-terbaru" class="py-12 sm:py-16 bg-slate-50/70 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
          <div class="flex items-end justify-between border-b border-slate-200 pb-3">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Publikasi Terkini</span>
              <h2 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5">Kabar & Warta Mahasiswa</h2>
            </div>
          </div>

          <div v-if="isLoading" class="py-12 text-center text-xs text-slate-500 font-medium">Memuat warta terbaru...</div>
          
          <div v-else-if="!homeData?.featured_articles?.length" class="py-12 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
            Belum ada publikasi berita terbaru.
          </div>

          <!-- Magazine Asymmetrical Grid -->
          <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Lead Article (7 Cols) -->
            <article
              v-if="homeData.featured_articles[0]"
              class="lg:col-span-7 rounded-xl border border-slate-200 bg-white overflow-hidden shadow-2xs hover:border-slate-300 transition flex flex-col group"
            >
              <router-link
                :to="`/organizations/${homeData.featured_articles[0].organization?.subdomain}/articles/${homeData.featured_articles[0].slug}`"
                class="block h-56 sm:h-72 w-full bg-slate-100 overflow-hidden relative"
              >
                <img
                  v-if="homeData.featured_articles[0].cover_image"
                  :src="homeData.featured_articles[0].cover_image"
                  :alt="homeData.featured_articles[0].judul"
                  class="h-full w-full object-cover group-hover:scale-102 transition duration-200"
                />
                <div v-else class="h-full w-full flex items-center justify-center text-slate-400 font-bold text-xs bg-slate-100">
                  CMS ORMAWA ITI
                </div>
                <span
                  v-if="homeData.featured_articles[0].category"
                  class="absolute top-3 left-3 rounded bg-white/95 px-2 py-0.5 text-[10px] font-bold text-slate-900 shadow-xs"
                >
                  {{ homeData.featured_articles[0].category.name }}
                </span>
              </router-link>

              <div class="p-5 sm:p-6 flex flex-col justify-between flex-grow space-y-3">
                <div class="space-y-1.5">
                  <span class="text-[10px] font-bold text-blue-900 uppercase tracking-wider block">
                    {{ homeData.featured_articles[0].organization?.nama }}
                  </span>
                  <h3 class="text-base sm:text-xl font-bold text-slate-950 group-hover:text-blue-900 transition leading-snug">
                    <router-link :to="`/organizations/${homeData.featured_articles[0].organization?.subdomain}/articles/${homeData.featured_articles[0].slug}`">
                      {{ homeData.featured_articles[0].judul }}
                    </router-link>
                  </h3>
                  <p class="text-xs sm:text-sm text-slate-600 line-clamp-3 leading-relaxed">
                    {{ homeData.featured_articles[0].excerpt }}
                  </p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-medium">
                  <span>{{ new Date(homeData.featured_articles[0].published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</span>
                  <span class="text-slate-700 font-semibold">{{ homeData.featured_articles[0].author.name }}</span>
                </div>
              </div>
            </article>

            <!-- Supporting Article Rows (5 Cols) -->
            <div class="lg:col-span-5 space-y-3">
              <article
                v-for="post in homeData.featured_articles.slice(1, 4)"
                :key="post.id"
                class="rounded-xl border border-slate-200 bg-white p-4 hover:border-slate-300 transition flex gap-3.5 group"
              >
                <router-link
                  :to="`/organizations/${post.organization?.subdomain}/articles/${post.slug}`"
                  class="h-20 w-20 shrink-0 rounded-lg bg-slate-100 overflow-hidden relative"
                >
                  <img
                    v-if="post.cover_image"
                    :src="post.cover_image"
                    :alt="post.judul"
                    class="h-full w-full object-cover group-hover:scale-103 transition"
                  />
                  <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 font-bold bg-slate-100">
                    ITI
                  </div>
                </router-link>

                <div class="flex flex-col justify-between flex-grow min-w-0">
                  <div>
                    <span class="text-[9px] font-bold text-blue-900 uppercase tracking-wider block mb-0.5 truncate">
                      {{ post.organization?.nama }}
                    </span>
                    <h4 class="text-xs font-bold text-slate-950 group-hover:text-blue-900 transition line-clamp-2 leading-snug">
                      <router-link :to="`/organizations/${post.organization?.subdomain}/articles/${post.slug}`">
                        {{ post.judul }}
                      </router-link>
                    </h4>
                  </div>
                  <span class="text-[10px] text-slate-400 mt-1">
                    {{ new Date(post.published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}
                  </span>
                </div>
              </article>
            </div>

          </div>
        </div>
      </section>

      <!-- ─── 05. UPCOMING EVENTS AGENDA ────────────────────────────── -->
      <section class="py-12 sm:py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
          <div class="border-b border-slate-200 pb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Kalender Mahasiswa</span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5">Agenda & Kegiatan Kampus</h2>
          </div>

          <div v-if="isLoading" class="py-10 text-center text-xs text-slate-500">Memuat agenda kegiatan...</div>
          
          <div v-else-if="!homeData?.upcoming_agenda?.length" class="py-10 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl">
            Tidak ada agenda kegiatan dalam waktu dekat.
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div
              v-for="act in homeData.upcoming_agenda"
              :key="act.id"
              class="rounded-xl border border-slate-200 bg-white p-5 hover:border-slate-300 transition flex flex-col justify-between space-y-3"
            >
              <div class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                  <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-800">
                    {{ act.tanggal_pelaksanaan ? new Date(act.tanggal_pelaksanaan).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-' }}
                  </span>
                  <span class="text-[10px] text-slate-500 truncate max-w-[110px]">{{ act.tempat }}</span>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-950 leading-snug">{{ act.judul }}</h3>
                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ act.deskripsi }}</p>
              </div>

              <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-[10px] text-slate-500 font-medium">{{ act.organization?.nama }}</span>
                <router-link
                  :to="`/organizations/${act.organization?.subdomain}/agenda/${act.id}`"
                  class="font-semibold text-blue-900 hover:text-blue-700"
                >
                  Detail &rarr;
                </router-link>
              </div>
            </div>
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
import type { PublicHomeSummary } from '@/types/public'

const homeData = ref<PublicHomeSummary | null>(null)
const isLoading = ref(true)

useSeoMeta(() => ({
  title: 'Portal Organisasi Kemahasiswaan ITI',
  description: 'Portal publik resmi seluruh organisasi kemahasiswaan (BEM, HMPS, UKM) Institut Teknologi Indonesia.',
  ogType: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'CMS ORMAWA ITI',
    url: window.location.origin,
  }
}))

const loadHome = async () => {
  isLoading.value = true
  try {
    const data = await publicService.getHome()
    homeData.value = data
  } catch (err) {
    console.error('Failed to load home data:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadHome()
})
</script>
