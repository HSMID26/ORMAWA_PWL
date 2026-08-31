<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow pt-20 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- ─── 01. BREADCRUMB ──────────────────────────────────────── -->
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-[#737783]">
          <router-link to="/" class="hover:text-[#00346F] transition">Beranda</router-link>
          <span>/</span>
          <span class="text-[#191C1D] font-semibold">Agenda</span>
        </nav>

        <!-- ─── 02. PAGE HERO ───────────────────────────────────────── -->
        <header class="border-b border-[#C2C6D3] pb-6 space-y-2">
          <div class="inline-flex items-center gap-2 rounded-full border border-[#C2C6D3] bg-white px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#00346F] shadow-2xs">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            <span>KALENDER MAHASISWA</span>
          </div>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-[#191C1D] tracking-tight">
            Agenda & Kegiatan Mahasiswa
          </h1>
          <p class="text-xs sm:text-sm text-[#424751] max-w-2xl leading-relaxed">
            Temukan kegiatan, seminar, lokakarya, dan agenda publik dari berbagai organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.
          </p>
        </header>

        <!-- ─── 03. UPCOMING HIGHLIGHT (FEATURED NEAREST EVENT) ──────── -->
        <section
          v-if="featuredEvent && !isFiltered && activeTab === 'upcoming' && currentPage === 1 && !isLoading"
          aria-label="Kegiatan Utama Terdekat"
          class="rounded-xl border border-[#C2C6D3] bg-white p-6 sm:p-8 hover:border-[#00346F] transition duration-200 shadow-2xs group"
        >
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- Date Anchor Block (3 Cols) -->
            <div class="lg:col-span-3 flex sm:flex-col items-center sm:items-start justify-between sm:justify-center p-4 sm:p-6 rounded-lg bg-[#F8F9FA] border border-[#C2C6D3] text-center">
              <div>
                <span class="block text-4xl sm:text-5xl font-black text-[#00346F] font-mono leading-none">
                  {{ parseEventDay(featuredEvent.tanggal_pelaksanaan) }}
                </span>
                <span class="block text-xs font-bold uppercase tracking-widest text-[#737783] mt-1">
                  {{ parseEventMonth(featuredEvent.tanggal_pelaksanaan) }} {{ parseEventYear(featuredEvent.tanggal_pelaksanaan) }}
                </span>
              </div>
              <div class="mt-0 sm:mt-3 inline-flex items-center gap-1.5 rounded bg-[#D7E2FF] px-2.5 py-0.5 text-[10px] font-bold text-[#001B3F]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#00346F]"></span>
                <span>Kegiatan Terdekat</span>
              </div>
            </div>

            <!-- Details (9 Cols) -->
            <div class="lg:col-span-9 space-y-3">
              <!-- Organization Context -->
              <div class="flex items-center gap-2">
                <div class="h-5 w-5 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shrink-0">
                  <img v-if="featuredEvent.organization?.logo" :src="featuredEvent.organization.logo" :alt="featuredEvent.organization.nama" class="h-full w-full object-cover" />
                  <span v-else class="text-[9px] font-bold text-[#00346F]">{{ featuredEvent.organization?.nama?.charAt(0) || 'O' }}</span>
                </div>
                <router-link
                  :to="`/organizations/${featuredEvent.organization?.subdomain}`"
                  class="text-xs font-bold text-[#00346F] hover:underline"
                >
                  {{ featuredEvent.organization?.nama }}
                </router-link>
              </div>

              <h2 class="text-xl sm:text-2xl font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
                <router-link :to="getEventUrl(featuredEvent)">
                  {{ featuredEvent.judul }}
                </router-link>
              </h2>

              <p class="text-xs sm:text-sm text-[#424751] line-clamp-2 leading-relaxed">
                {{ featuredEvent.deskripsi }}
              </p>

              <!-- Meta: Location & Time -->
              <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-[#737783]">
                <div class="flex items-center gap-1.5">
                  <svg class="h-4 w-4 text-[#00346F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="font-medium text-[#191C1D]">{{ featuredEvent.tempat || 'Kampus ITI' }}</span>
                </div>

                <router-link
                  :to="getEventUrl(featuredEvent)"
                  class="ml-auto inline-flex items-center gap-1.5 text-xs font-bold text-[#00346F] hover:underline"
                >
                  <span>Lihat Rincian Kegiatan</span>
                  <span>&rarr;</span>
                </router-link>
              </div>
            </div>

          </div>
        </section>

        <!-- ─── 04. EVENT TABS & FILTER TOOLBAR ──────────────────────── -->
        <section aria-label="Navigasi Waktu dan Filter Agenda" class="space-y-4">
          
          <!-- Event Tabs -->
          <div class="flex items-center border-b border-[#C2C6D3] gap-2">
            <button
              v-for="tab in tabsList"
              :key="tab.key"
              @click="selectTab(tab.key)"
              :class="[
                'px-4 py-2.5 text-xs font-bold transition border-b-2 -mb-px flex items-center gap-1.5 cursor-pointer',
                activeTab === tab.key
                  ? 'border-[#00346F] text-[#00346F] bg-white rounded-t-md'
                  : 'border-transparent text-[#737783] hover:text-[#191C1D]'
              ]"
            >
              <span>{{ tab.label }}</span>
            </button>
          </div>

          <!-- Filter Toolbar -->
          <div class="rounded-xl border border-[#C2C6D3] bg-white p-4 sm:p-5 space-y-4 shadow-2xs">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5">
              
              <!-- Search Input (7 Cols) -->
              <div class="sm:col-span-7 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#737783]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari agenda, kegiatan, atau organisasi..."
                  class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] pl-10 pr-4 py-2 text-xs text-[#191C1D] placeholder-[#737783] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition"
                  @input="onSearchInput"
                />
              </div>

              <!-- Organization Filter (5 Cols) -->
              <div class="sm:col-span-5">
                <select
                  v-model="selectedOrganization"
                  class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] px-3 py-2 text-xs text-[#191C1D] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition cursor-pointer"
                  @change="applyFilters"
                >
                  <option value="">Semua Organisasi</option>
                  <option v-for="org in organizationsList" :key="org.id" :value="org.subdomain">
                    {{ org.nama }} ({{ org.jenis }})
                  </option>
                </select>
              </div>

            </div>

            <!-- Active Filters Chip & Reset -->
            <div v-if="isFiltered" class="pt-2 border-t border-[#E1E3E4] flex flex-wrap items-center justify-between gap-2 text-xs">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-[#737783] text-[11px] font-semibold">Filter aktif:</span>
                
                <span v-if="searchQuery" class="inline-flex items-center gap-1 rounded bg-[#E7E8E9] px-2 py-0.5 text-[11px] text-[#191C1D]">
                  Kata Kunci: "{{ searchQuery }}"
                  <button @click="clearSearch" class="hover:text-[#BA1A1A] font-bold ml-1">&times;</button>
                </span>

                <span v-if="selectedOrganization" class="inline-flex items-center gap-1 rounded bg-[#D7E2FF] px-2 py-0.5 text-[11px] text-[#001B3F]">
                  Organisasi: {{ getOrgName(selectedOrganization) }}
                  <button @click="selectedOrganization = ''; applyFilters()" class="hover:text-[#BA1A1A] font-bold ml-1">&times;</button>
                </span>
              </div>

              <button
                @click="resetAllFilters"
                class="text-xs font-semibold text-[#BA1A1A] hover:underline cursor-pointer"
              >
                Reset Filter
              </button>
            </div>
          </div>

        </section>

        <!-- ─── 05. AGENDA RESULT LIST ───────────────────────────────── -->
        <section aria-label="Daftar Agenda Kegiatan" class="space-y-4">
          
          <div class="flex items-center justify-between text-xs text-[#737783] px-1">
            <span>
              Menampilkan <strong class="text-[#191C1D] font-bold">{{ events.length }}</strong> dari <strong class="text-[#191C1D] font-bold">{{ totalItems }}</strong> agenda
            </span>
          </div>

          <!-- Loading State (Skeleton Rows) -->
          <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 4" :key="i" class="rounded-xl border border-[#C2C6D3] bg-white p-5 space-y-3 animate-pulse">
              <div class="flex items-center gap-4">
                <div class="h-16 w-16 bg-[#E1E3E4] rounded-lg shrink-0"></div>
                <div class="space-y-2 flex-1">
                  <div class="h-4 bg-[#E1E3E4] rounded w-1/4"></div>
                  <div class="h-5 bg-[#E1E3E4] rounded w-2/3"></div>
                  <div class="h-3 bg-[#E1E3E4] rounded w-1/2"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="isError" class="py-16 text-center rounded-xl border border-[#C2C6D3] bg-white p-8 space-y-3">
            <div class="text-[#BA1A1A] font-bold text-sm">Gagal memuat agenda kegiatan.</div>
            <p class="text-xs text-[#737783]">Terjadi kendala saat menghubungi server. Silakan coba kembali.</p>
            <button
              @click="fetchAgenda"
              class="rounded bg-[#00346F] hover:bg-[#004A99] px-4 py-2 text-xs font-semibold text-white transition shadow-2xs cursor-pointer"
            >
              Coba Lagi
            </button>
          </div>

          <!-- Empty State -->
          <div v-else-if="events.length === 0" class="py-16 text-center rounded-xl border border-dashed border-[#C2C6D3] bg-white p-8 space-y-3">
            <div class="h-12 w-12 rounded-full bg-[#F3F4F5] text-[#737783] flex items-center justify-center mx-auto">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-sm font-bold text-[#191C1D]">
              {{ isFiltered ? 'Belum ada agenda yang sesuai' : 'Belum ada agenda pada periode ini' }}
            </h3>
            <p class="text-xs text-[#737783] max-w-sm mx-auto leading-relaxed">
              {{ isFiltered ? 'Coba ubah kata kunci pencarian atau filter organisasi yang dipilih.' : 'Agenda kegiatan mahasiswa terbaru akan dipublikasikan secara berkala.' }}
            </p>
            <div v-if="isFiltered" class="pt-2">
              <button
                @click="resetAllFilters"
                class="rounded border border-[#C2C6D3] bg-[#F8F9FA] hover:bg-white px-4 py-2 text-xs font-semibold text-[#00346F] transition cursor-pointer"
              >
                Reset Filter
              </button>
            </div>
          </div>

          <!-- Event Cards List -->
          <div v-else class="space-y-4">
            <article
              v-for="ev in events"
              :key="ev.id"
              class="rounded-xl border border-[#C2C6D3] bg-white p-5 sm:p-6 hover:border-[#00346F] transition duration-200 shadow-2xs group flex flex-col sm:flex-row gap-5 items-start"
            >
              <!-- Date Anchor Block -->
              <div class="w-full sm:w-20 shrink-0 p-3 sm:py-4 rounded-lg bg-[#F8F9FA] border border-[#C2C6D3] text-center flex sm:flex-col items-center justify-between sm:justify-center">
                <span class="block text-2xl sm:text-3xl font-black text-[#00346F] font-mono leading-none">
                  {{ parseEventDay(ev.tanggal_pelaksanaan) }}
                </span>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-[#737783] mt-0.5">
                  {{ parseEventMonth(ev.tanggal_pelaksanaan) }}
                </span>
                <span class="block text-[10px] text-[#737783] font-mono sm:mt-0.5">
                  {{ parseEventYear(ev.tanggal_pelaksanaan) }}
                </span>
              </div>

              <!-- Event Details -->
              <div class="flex-1 space-y-2">
                <!-- Org Badge -->
                <div class="flex items-center gap-2">
                  <div class="h-4 w-4 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shrink-0">
                    <img v-if="ev.organization?.logo" :src="ev.organization.logo" :alt="ev.organization.nama" class="h-full w-full object-cover" />
                    <span v-else class="text-[8px] font-bold text-[#00346F]">{{ ev.organization?.nama?.charAt(0) || 'O' }}</span>
                  </div>
                  <router-link
                    :to="`/organizations/${ev.organization?.subdomain}`"
                    class="text-[11px] font-bold text-[#00346F] hover:underline"
                  >
                    {{ ev.organization?.nama }}
                  </router-link>
                </div>

                <!-- Title -->
                <h3 class="text-base sm:text-lg font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
                  <router-link :to="getEventUrl(ev)">
                    {{ ev.judul }}
                  </router-link>
                </h3>

                <!-- Description -->
                <p class="text-xs text-[#424751] line-clamp-2 leading-relaxed">
                  {{ ev.deskripsi }}
                </p>

                <!-- Location & Action -->
                <div class="pt-2 flex flex-wrap items-center justify-between gap-3 text-xs">
                  <div class="flex items-center gap-1.5 text-[#737783] text-[11px]">
                    <svg class="h-3.5 w-3.5 text-[#00346F] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="font-medium text-[#191C1D]">{{ ev.tempat || 'Kampus ITI' }}</span>
                  </div>

                  <router-link
                    :to="getEventUrl(ev)"
                    class="inline-flex items-center gap-1 text-xs font-bold text-[#00346F] hover:underline"
                  >
                    <span>Rincian</span>
                    <span>&rarr;</span>
                  </router-link>
                </div>
              </div>

            </article>
          </div>

        </section>

        <!-- ─── 06. SERVER-SIDE PAGINATION ───────────────────────────── -->
        <nav
          v-if="totalPages > 1 && !isLoading"
          aria-label="Navigasi Halaman Agenda"
          class="pt-6 border-t border-[#C2C6D3] flex items-center justify-between gap-4"
        >
          <button
            :disabled="currentPage <= 1"
            @click="changePage(currentPage - 1)"
            class="rounded border border-[#C2C6D3] bg-white px-4 py-2 text-xs font-semibold text-[#191C1D] hover:bg-[#F3F4F5] disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer"
          >
            &larr; Sebelumnya
          </button>

          <div class="flex items-center gap-1.5 text-xs font-semibold">
            <template v-for="p in visiblePages" :key="p">
              <span v-if="p === '...'" class="px-2 text-[#737783]">...</span>
              <button
                v-else
                @click="changePage(p as number)"
                :class="[
                  'h-8 w-8 rounded transition cursor-pointer flex items-center justify-center text-xs font-bold',
                  currentPage === p
                    ? 'bg-[#00346F] text-white'
                    : 'bg-white border border-[#C2C6D3] text-[#191C1D] hover:bg-[#F3F4F5]'
                ]"
              >
                {{ p }}
              </button>
            </template>
          </div>

          <button
            :disabled="currentPage >= totalPages"
            @click="changePage(currentPage + 1)"
            class="rounded border border-[#C2C6D3] bg-white px-4 py-2 text-xs font-semibold text-[#191C1D] hover:bg-[#F3F4F5] disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer"
          >
            Berikutnya &rarr;
          </button>
        </nav>

      </div>
    </main>

    <PublicFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PublicNavbar from '@/components/public/PublicNavbar.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicAgenda, PublicOrganization } from '@/types/public'

const route = useRoute()
const router = useRouter()

const events = ref<PublicAgenda[]>([])
const featuredEvent = ref<PublicAgenda | null>(null)
const organizationsList = ref<PublicOrganization[]>([])

const isLoading = ref(true)
const isError = ref(false)

const tabsList: Array<{ key: 'upcoming' | 'today' | 'past'; label: string }> = [
  { key: 'upcoming', label: 'Mendatang' },
  { key: 'today', label: 'Hari Ini' },
  { key: 'past', label: 'Lampau' },
]

const activeTab = ref<'upcoming' | 'today' | 'past'>('upcoming')
const searchQuery = ref('')
const selectedOrganization = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const perPage = 12

let searchDebounceTimer: any = null

useSeoMeta(() => ({
  title: 'Agenda & Kegiatan Mahasiswa — ORMAWA ITI',
  description: 'Temukan kegiatan, seminar, lokakarya, dan agenda publik seluruh organisasi mahasiswa Institut Teknologi Indonesia.',
  ogType: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'Agenda & Kegiatan Mahasiswa ITI',
    url: typeof window !== 'undefined' ? window.location.origin + '/agenda' : 'https://ormawa.iti.ac.id/agenda',
    description: 'Kalender kegiatan dan agenda kemahasiswaan Institut Teknologi Indonesia.',
  }
}))

const isFiltered = computed(() => {
  return !!(searchQuery.value || selectedOrganization.value)
})

const visiblePages = computed(() => {
  const pages: (number | string)[] = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)
    for (let i = start; i <= end; i++) pages.push(i)
    if (current < total - 2) pages.push('...')
    pages.push(total)
  }
  return pages
})

const parseEventDay = (dateStr?: string | null) => {
  if (!dateStr) return '01'
  const d = new Date(dateStr)
  return isNaN(d.getDate()) ? '01' : String(d.getDate()).padStart(2, '0')
}

const parseEventMonth = (dateStr?: string | null) => {
  if (!dateStr) return 'JAN'
  const d = new Date(dateStr)
  return isNaN(d.getMonth()) ? 'JAN' : d.toLocaleDateString('id-ID', { month: 'short' }).toUpperCase()
}

const parseEventYear = (dateStr?: string | null) => {
  if (!dateStr) return '2026'
  const d = new Date(dateStr)
  return isNaN(d.getFullYear()) ? '2026' : String(d.getFullYear())
}

const getEventUrl = (ev: PublicAgenda) => {
  const orgSlug = ev.organization?.subdomain || 'ormawa'
  return `/organizations/${orgSlug}/agenda/${ev.id}`
}

const getOrgName = (subdomain: string) => {
  const org = organizationsList.value.find(o => o.subdomain === subdomain)
  return org ? org.nama : subdomain
}

const syncStateFromUrl = () => {
  activeTab.value = (route.query.tab as 'upcoming' | 'today' | 'past') || 'upcoming'
  searchQuery.value = (route.query.search as string) || ''
  selectedOrganization.value = (route.query.organization as string) || ''
  currentPage.value = parseInt((route.query.page as string) || '1', 10) || 1
}

const syncUrlFromState = () => {
  const query: Record<string, string> = {}
  if (activeTab.value !== 'upcoming') query.tab = activeTab.value
  if (searchQuery.value) query.search = searchQuery.value
  if (selectedOrganization.value) query.organization = selectedOrganization.value
  if (currentPage.value > 1) query.page = String(currentPage.value)

  router.replace({ query })
}

const fetchAgenda = async () => {
  isLoading.value = true
  isError.value = false

  try {
    const res = await publicService.getGlobalAgenda({
      tab: activeTab.value,
      search: searchQuery.value || undefined,
      organization: selectedOrganization.value || undefined,
      page: currentPage.value,
      per_page: perPage,
    })

    events.value = res.data || []
    totalPages.value = res.meta?.last_page || 1
    totalItems.value = res.meta?.total || 0

    if (currentPage.value === 1 && !isFiltered.value && activeTab.value === 'upcoming' && events.value.length > 0) {
      featuredEvent.value = events.value[0]
    } else {
      featuredEvent.value = null
    }
  } catch (err) {
    console.error('Failed to load global agenda:', err)
    isError.value = true
  } finally {
    isLoading.value = false
  }
}

const fetchOrganizations = async () => {
  try {
    const res = await publicService.getOrganizations({ per_page: 50 })
    organizationsList.value = res.data || []
  } catch (err) {
    console.error('Failed to load organizations list:', err)
  }
}

const selectTab = (tab: 'upcoming' | 'today' | 'past') => {
  if (activeTab.value === tab) return
  activeTab.value = tab
  currentPage.value = 1
  syncUrlFromState()
  fetchAgenda()
}

const onSearchInput = () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    currentPage.value = 1
    syncUrlFromState()
    fetchAgenda()
  }, 350)
}

const clearSearch = () => {
  searchQuery.value = ''
  currentPage.value = 1
  syncUrlFromState()
  fetchAgenda()
}

const applyFilters = () => {
  currentPage.value = 1
  syncUrlFromState()
  fetchAgenda()
}

const resetAllFilters = () => {
  searchQuery.value = ''
  selectedOrganization.value = ''
  currentPage.value = 1
  syncUrlFromState()
  fetchAgenda()
}

const changePage = (page: number) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  currentPage.value = page
  syncUrlFromState()
  fetchAgenda()

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

watch(
  () => route.query,
  () => {
    syncStateFromUrl()
    fetchAgenda()
  }
)

onMounted(async () => {
  syncStateFromUrl()
  await Promise.all([fetchOrganizations(), fetchAgenda()])
})
</script>
