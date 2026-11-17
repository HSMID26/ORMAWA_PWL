<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow pt-20 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- ─── 01. BREADCRUMB ──────────────────────────────────────── -->
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-[#737783]">
          <router-link to="/" class="hover:text-[#00346F] transition">Beranda</router-link>
          <span>/</span>
          <span class="text-[#191C1D] font-semibold">Pengumuman</span>
        </nav>

        <!-- ─── 02. PAGE HERO ───────────────────────────────────────── -->
        <header class="border-b border-[#C2C6D3] pb-6 space-y-2">
          <div class="inline-flex items-center gap-2 rounded-full border border-[#C2C6D3] bg-white px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#00346F] shadow-2xs">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            <span>INFORMASI RESMI</span>
          </div>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-[#191C1D] tracking-tight">
            Pengumuman
          </h1>
          <p class="text-xs sm:text-sm text-[#424751] max-w-2xl leading-relaxed">
            Informasi, maklumat, dan pengumuman resmi dari seluruh organisasi kemahasiswaan Institut Teknologi Indonesia.
          </p>
        </header>

        <!-- ─── 03. FEATURED IMPORTANT ANNOUNCEMENT ─────────────────── -->
        <section
          v-if="featuredAnnouncement && !isFiltered && currentPage === 1 && !isLoading"
          aria-label="Pengumuman Penting"
          class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden shadow-2xs group relative pl-3 sm:pl-4 border-l-4 sm:border-l-6"
          :class="getPriorityBorderClass(featuredAnnouncement.priority)"
        >
          <div class="p-5 sm:p-7 space-y-3">
            
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span
                  :class="[
                    'inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider',
                    getPriorityBadgeClass(featuredAnnouncement.priority)
                  ]"
                >
                  {{ getPriorityLabel(featuredAnnouncement.priority) }}
                </span>

                <div class="flex items-center gap-1.5 text-xs text-[#00346F] font-bold">
                  <div class="h-4 w-4 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shrink-0">
                    <img v-if="featuredAnnouncement.organization?.logo" :src="featuredAnnouncement.organization.logo" :alt="featuredAnnouncement.organization.nama" class="h-full w-full object-cover" />
                    <span v-else class="text-[8px] font-bold text-[#00346F]">{{ featuredAnnouncement.organization?.nama?.charAt(0) || 'O' }}</span>
                  </div>
                  <router-link :to="`/organizations/${featuredAnnouncement.organization?.subdomain}`" class="hover:underline">
                    {{ featuredAnnouncement.organization?.nama }}
                  </router-link>
                </div>
              </div>

              <span class="text-[11px] text-[#737783] font-medium">
                {{ formatDate(featuredAnnouncement.published_at || featuredAnnouncement.effective_date) }}
              </span>
            </div>

            <h2 class="text-lg sm:text-xl font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
              <router-link :to="getAnnouncementUrl(featuredAnnouncement)">
                {{ featuredAnnouncement.title }}
              </router-link>
            </h2>

            <p class="text-xs sm:text-sm text-[#424751] line-clamp-3 leading-relaxed">
              {{ featuredAnnouncement.content }}
            </p>

            <div class="pt-2 flex items-center justify-end">
              <router-link
                :to="getAnnouncementUrl(featuredAnnouncement)"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00346F] hover:underline"
              >
                <span>Buka Pengumuman</span>
                <span>&rarr;</span>
              </router-link>
            </div>

          </div>
        </section>

        <!-- ─── 04. SEARCH & FILTER TOOLBAR ──────────────────────────── -->
        <section aria-label="Filter dan Pencarian Pengumuman" class="rounded-xl border border-[#C2C6D3] bg-white p-4 sm:p-5 space-y-4 shadow-2xs">
          <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5">
            
            <!-- Search Input (6 Cols) -->
            <div class="sm:col-span-6 relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#737783]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari pengumuman atau kata kunci..."
                class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] pl-10 pr-4 py-2 text-xs text-[#191C1D] placeholder-[#737783] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition"
                @input="onSearchInput"
              />
            </div>

            <!-- Organization Filter (4 Cols) -->
            <div class="sm:col-span-4">
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

            <!-- Priority Filter (2 Cols) -->
            <div class="sm:col-span-2">
              <select
                v-model="selectedPriority"
                class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] px-3 py-2 text-xs text-[#191C1D] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition cursor-pointer"
                @change="applyFilters"
              >
                <option value="">Semua Prioritas</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
                <option value="low">Low</option>
              </select>
            </div>

          </div>

          <!-- Active Filters & Reset -->
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

              <span v-if="selectedPriority" class="inline-flex items-center gap-1 rounded bg-[#E7E8E9] px-2 py-0.5 text-[11px] text-[#191C1D]">
                Prioritas: {{ selectedPriority.toUpperCase() }}
                <button @click="selectedPriority = ''; applyFilters()" class="hover:text-[#BA1A1A] font-bold ml-1">&times;</button>
              </span>
            </div>

            <button
              @click="resetAllFilters"
              class="text-xs font-semibold text-[#BA1A1A] hover:underline cursor-pointer"
            >
              Reset Filter
            </button>
          </div>
        </section>

        <!-- ─── 05. ANNOUNCEMENT BULLETIN LIST ───────────────────────── -->
        <section aria-label="Papan Pengumuman Resmi" class="space-y-4">
          
          <div class="flex items-center justify-between text-xs text-[#737783] px-1">
            <span>
              Menampilkan <strong class="text-[#191C1D] font-bold">{{ announcements.length }}</strong> dari <strong class="text-[#191C1D] font-bold">{{ totalItems }}</strong> pengumuman
            </span>
          </div>

          <!-- Loading State (Skeleton Rows) -->
          <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 5" :key="i" class="rounded-xl border border-[#C2C6D3] bg-white p-5 space-y-3 animate-pulse">
              <div class="flex items-center justify-between">
                <div class="h-4 bg-[#E1E3E4] rounded w-1/4"></div>
                <div class="h-3 bg-[#E1E3E4] rounded w-1/6"></div>
              </div>
              <div class="h-5 bg-[#E1E3E4] rounded w-2/3"></div>
              <div class="h-3 bg-[#E1E3E4] rounded w-full"></div>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="isError" class="py-16 text-center rounded-xl border border-[#C2C6D3] bg-white p-8 space-y-3">
            <div class="text-[#BA1A1A] font-bold text-sm">Gagal memuat pengumuman.</div>
            <p class="text-xs text-[#737783]">Terjadi kendala saat menghubungi server. Silakan coba kembali.</p>
            <button
              @click="fetchAnnouncements"
              class="rounded bg-[#00346F] hover:bg-[#004A99] px-4 py-2 text-xs font-semibold text-white transition shadow-2xs cursor-pointer"
            >
              Coba Lagi
            </button>
          </div>

          <!-- Empty State -->
          <div v-else-if="announcements.length === 0" class="py-16 text-center rounded-xl border border-dashed border-[#C2C6D3] bg-white p-8 space-y-3">
            <div class="h-12 w-12 rounded-full bg-[#F3F4F5] text-[#737783] flex items-center justify-center mx-auto">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
              </svg>
            </div>
            <h3 class="text-sm font-bold text-[#191C1D]">
              {{ isFiltered ? 'Belum ada pengumuman yang sesuai' : 'Belum ada pengumuman aktif' }}
            </h3>
            <p class="text-xs text-[#737783] max-w-sm mx-auto leading-relaxed">
              {{ isFiltered ? 'Coba ubah kata kunci pencarian atau filter yang dipilih.' : 'Pengumuman dan edaran resmi dari organisasi kemahasiswaan akan ditampilkan di sini.' }}
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

          <!-- Announcement Bulletin Rows -->
          <div v-else class="space-y-3">
            <article
              v-for="ann in announcements"
              :key="ann.id"
              class="rounded-xl border border-[#C2C6D3] bg-white p-5 hover:border-[#00346F] transition duration-150 shadow-2xs group flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
            >
              <div class="space-y-2 flex-1">
                
                <div class="flex flex-wrap items-center gap-2">
                  <span
                    :class="[
                      'inline-block px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider',
                      getPriorityBadgeClass(ann.priority)
                    ]"
                  >
                    {{ getPriorityLabel(ann.priority) }}
                  </span>

                  <div class="flex items-center gap-1.5 text-xs text-[#00346F] font-bold">
                    <div class="h-4 w-4 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shrink-0">
                      <img v-if="ann.organization?.logo" :src="ann.organization.logo" :alt="ann.organization.nama" class="h-full w-full object-cover" />
                      <span v-else class="text-[8px] font-bold text-[#00346F]">{{ ann.organization?.nama?.charAt(0) || 'O' }}</span>
                    </div>
                    <router-link :to="`/organizations/${ann.organization?.subdomain}`" class="hover:underline">
                      {{ ann.organization?.nama }}
                    </router-link>
                  </div>

                  <span class="text-[11px] text-[#737783]">
                    &bull; {{ formatDate(ann.published_at || ann.effective_date) }}
                    <span v-if="ann.expires_at" class="ml-1 text-[#00346F] font-semibold">
                      (s/d {{ formatDate(ann.expires_at) }})
                    </span>
                  </span>
                </div>

                <h3 class="text-base font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
                  <router-link :to="getAnnouncementUrl(ann)">
                    {{ ann.title }}
                  </router-link>
                </h3>

                <p class="text-xs text-[#424751] line-clamp-2 leading-relaxed">
                  {{ ann.content }}
                </p>

              </div>

              <div class="sm:shrink-0 pt-2 sm:pt-0">
                <router-link
                  :to="getAnnouncementUrl(ann)"
                  class="rounded border border-[#C2C6D3] bg-[#F8F9FA] group-hover:bg-[#00346F] group-hover:text-white group-hover:border-[#00346F] px-3.5 py-1.5 text-xs font-semibold text-[#191C1D] transition flex items-center gap-1.5 shadow-2xs"
                >
                  <span>Baca Pengumuman</span>
                  <span>&rarr;</span>
                </router-link>
              </div>

            </article>
          </div>

        </section>

        <!-- ─── 06. SERVER-SIDE PAGINATION ───────────────────────────── -->
        <nav
          v-if="totalPages > 1 && !isLoading"
          aria-label="Navigasi Halaman Pengumuman"
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
import type { PublicAnnouncement, PublicOrganization } from '@/types/public'

const route = useRoute()
const router = useRouter()

const announcements = ref<PublicAnnouncement[]>([])
const featuredAnnouncement = ref<PublicAnnouncement | null>(null)
const organizationsList = ref<PublicOrganization[]>([])

const isLoading = ref(true)
const isError = ref(false)

const searchQuery = ref('')
const selectedOrganization = ref('')
const selectedPriority = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const perPage = 12

let searchDebounceTimer: any = null

useSeoMeta(() => ({
  title: 'Pengumuman Resmi — ORMAWA ITI',
  description: 'Pusat informasi dan pengumuman resmi seluruh organisasi mahasiswa Institut Teknologi Indonesia.',
  ogType: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'Pengumuman Resmi Organisasi Mahasiswa ITI',
    url: typeof window !== 'undefined' ? window.location.origin + '/pengumuman' : 'https://ormawa.iti.ac.id/pengumuman',
    description: 'Papan buletin resmi kemahasiswaan Institut Teknologi Indonesia.',
  }
}))

const isFiltered = computed(() => {
  return !!(searchQuery.value || selectedOrganization.value || selectedPriority.value)
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

const getAnnouncementUrl = (ann: PublicAnnouncement) => {
  const orgSlug = ann.organization?.subdomain || 'ormawa'
  return `/organizations/${orgSlug}/announcements`
}

const getOrgName = (subdomain: string) => {
  const org = organizationsList.value.find(o => o.subdomain === subdomain)
  return org ? org.nama : subdomain
}

const formatDate = (dateStr?: string | null) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const getPriorityLabel = (priority?: string) => {
  switch (priority) {
    case 'urgent': return 'Urgent'
    case 'high': return 'Penting'
    case 'low': return 'Info'
    default: return 'Pengumuman'
  }
}

const getPriorityBadgeClass = (priority?: string) => {
  switch (priority) {
    case 'urgent':
      return 'bg-red-100 text-red-800 border border-red-200'
    case 'high':
      return 'bg-amber-100 text-amber-800 border border-amber-200'
    case 'low':
      return 'bg-gray-100 text-gray-700 border border-gray-200'
    default:
      return 'bg-blue-50 text-[#00346F] border border-blue-200'
  }
}

const getPriorityBorderClass = (priority?: string) => {
  switch (priority) {
    case 'urgent': return 'border-l-red-600'
    case 'high': return 'border-l-amber-500'
    default: return 'border-l-[#00346F]'
  }
}

const syncStateFromUrl = () => {
  searchQuery.value = (route.query.search as string) || ''
  selectedOrganization.value = (route.query.organization as string) || ''
  selectedPriority.value = (route.query.priority as string) || ''
  currentPage.value = parseInt((route.query.page as string) || '1', 10) || 1
}

const syncUrlFromState = () => {
  const query: Record<string, string> = {}
  if (searchQuery.value) query.search = searchQuery.value
  if (selectedOrganization.value) query.organization = selectedOrganization.value
  if (selectedPriority.value) query.priority = selectedPriority.value
  if (currentPage.value > 1) query.page = String(currentPage.value)

  router.replace({ query })
}

const fetchAnnouncements = async () => {
  isLoading.value = true
  isError.value = false

  try {
    const res = await publicService.getGlobalAnnouncements({
      priority: (selectedPriority.value as any) || undefined,
      search: searchQuery.value || undefined,
      organization: selectedOrganization.value || undefined,
      page: currentPage.value,
      per_page: perPage,
    })

    announcements.value = res.data || []
    totalPages.value = res.meta?.last_page || 1
    totalItems.value = res.meta?.total || 0

    // Set featured if first page, not filtered, and is urgent or high
    if (currentPage.value === 1 && !isFiltered.value && announcements.value.length > 0) {
      const top = announcements.value[0]
      if (top.priority === 'urgent' || top.priority === 'high') {
        featuredAnnouncement.value = top
      } else {
        featuredAnnouncement.value = null
      }
    } else {
      featuredAnnouncement.value = null
    }
  } catch (err) {
    console.error('Failed to load global announcements:', err)
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

const onSearchInput = () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    currentPage.value = 1
    syncUrlFromState()
    fetchAnnouncements()
  }, 350)
}

const clearSearch = () => {
  searchQuery.value = ''
  currentPage.value = 1
  syncUrlFromState()
  fetchAnnouncements()
}

const applyFilters = () => {
  currentPage.value = 1
  syncUrlFromState()
  fetchAnnouncements()
}

const resetAllFilters = () => {
  searchQuery.value = ''
  selectedOrganization.value = ''
  selectedPriority.value = ''
  currentPage.value = 1
  syncUrlFromState()
  fetchAnnouncements()
}

const changePage = (page: number) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  currentPage.value = page
  syncUrlFromState()
  fetchAnnouncements()

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

watch(
  () => route.query,
  () => {
    syncStateFromUrl()
    fetchAnnouncements()
  }
)

onMounted(async () => {
  syncStateFromUrl()
  await Promise.all([fetchOrganizations(), fetchAnnouncements()])
})
</script>
