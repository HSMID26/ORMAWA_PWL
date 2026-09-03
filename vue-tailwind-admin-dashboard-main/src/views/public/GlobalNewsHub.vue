<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow pt-20 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- ─── 01. BREADCRUMB ──────────────────────────────────────── -->
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-[#737783]">
          <router-link to="/" class="hover:text-[#00346F] transition">Beranda</router-link>
          <span>/</span>
          <span class="text-[#191C1D] font-semibold">Berita</span>
        </nav>

        <!-- ─── 02. PAGE HERO ───────────────────────────────────────── -->
        <header class="border-b border-[#C2C6D3] pb-6 space-y-2">
          <div class="inline-flex items-center gap-2 rounded-full border border-[#C2C6D3] bg-white px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#00346F] shadow-2xs">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            <span>WARTA MAHASISWA</span>
          </div>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-[#191C1D] tracking-tight">
            Kabar & Berita Organisasi Mahasiswa
          </h1>
          <p class="text-xs sm:text-sm text-[#424751] max-w-2xl leading-relaxed">
            Temukan publikasi terbaru, liputan kegiatan, dan artikel karya seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.
          </p>
        </header>

        <!-- ─── 03. FEATURED ARTICLE (LATEST LEAD) ───────────────────── -->
        <!-- Featured Article Skeleton (Initial Page Load) -->
        <div
          v-if="isLoading && !isFiltered && currentPage === 1"
          class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden shadow-2xs flex flex-col md:flex-row md:h-[320px] lg:h-[340px] animate-pulse"
        >
          <div class="w-full md:w-[46%] h-52 sm:h-60 md:h-full bg-[#E1E3E4]"></div>
          <div class="w-full md:w-[54%] p-5 sm:p-6 lg:p-7 flex flex-col justify-between space-y-4">
            <div class="space-y-3">
              <div class="h-4 bg-[#E1E3E4] rounded w-1/4"></div>
              <div class="h-6 bg-[#E1E3E4] rounded w-3/4"></div>
              <div class="h-4 bg-[#E1E3E4] rounded w-full"></div>
              <div class="h-4 bg-[#E1E3E4] rounded w-5/6"></div>
            </div>
            <div class="pt-3.5 border-t border-[#E1E3E4] flex items-center justify-between">
              <div class="h-3 bg-[#E1E3E4] rounded w-1/3"></div>
              <div class="h-3 bg-[#E1E3E4] rounded w-1/5"></div>
            </div>
          </div>
        </div>

        <!-- Featured Article Card -->
        <section
          v-else-if="featuredArticle && !isFiltered && currentPage === 1"
          aria-label="Artikel Utama"
          class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden hover:border-[#00346F] transition duration-200 shadow-2xs group flex flex-col md:flex-row md:h-[320px] lg:h-[340px]"
        >
          <!-- Cover Image Container (46% width on desktop/tablet, 16:9 / h-52-60 on mobile) -->
          <router-link
            :to="getArticleUrl(featuredArticle)"
            class="w-full md:w-[46%] shrink-0 h-52 sm:h-60 md:h-full bg-[#F3F4F5] overflow-hidden relative block"
          >
            <img
              v-if="featuredArticle.cover_image"
              :src="resolveImageUrl(featuredArticle.cover_image)"
              :alt="featuredArticle.judul || 'Artikel Utama'"
              class="h-full w-full object-cover object-center group-hover:scale-102 transition duration-500"
              loading="eager"
            />
            <div v-else class="h-full w-full flex flex-col items-center justify-center text-xs font-bold text-[#737783] bg-[#F3F4F5] p-4 text-center">
              <span class="text-xs uppercase tracking-wider text-[#00346F] font-extrabold mb-1">ORMAWA ITI</span>
              <span class="text-[11px] text-[#737783] font-medium">{{ featuredArticle.organization?.nama || 'Warta Mahasiswa' }}</span>
            </div>
            <span
              v-if="featuredArticle.category"
              class="absolute top-3.5 left-3.5 rounded bg-white/95 backdrop-blur-xs px-2.5 py-0.5 text-[10px] font-bold text-[#191C1D] shadow-2xs"
            >
              {{ featuredArticle.category.name }}
            </span>
          </router-link>

          <!-- Content Details (54% width on desktop/tablet) -->
          <div class="w-full md:w-[54%] p-5 sm:p-6 lg:p-7 flex flex-col justify-between overflow-hidden">
            <div class="space-y-2.5">
              <!-- Organization Badge -->
              <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shrink-0">
                  <img v-if="featuredArticle.organization?.logo" :src="resolveImageUrl(featuredArticle.organization.logo)" :alt="featuredArticle.organization.nama || 'Logo Organisasi'" class="h-full w-full object-cover" />
                  <span v-else class="text-[10px] font-bold text-[#00346F]">{{ featuredArticle.organization?.nama?.charAt(0) || 'O' }}</span>
                </div>
                <span class="text-xs font-bold text-[#00346F] tracking-wide truncate">
                  {{ featuredArticle.organization?.nama }}
                </span>
              </div>

              <!-- Headline -->
              <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug line-clamp-2">
                <router-link :to="getArticleUrl(featuredArticle)">
                  {{ featuredArticle.judul }}
                </router-link>
              </h2>

              <!-- Excerpt -->
              <p class="text-xs sm:text-sm text-[#424751] line-clamp-2 sm:line-clamp-3 leading-relaxed">
                {{ featuredArticle.excerpt }}
              </p>
            </div>

            <!-- Footer Metadata & CTA -->
            <div class="pt-3.5 mt-2 border-t border-[#E1E3E4] flex items-center justify-between text-xs">
              <div class="text-[11px] text-[#737783] truncate pr-2">
                <span>{{ formatDate(featuredArticle.published_at) }}</span>
                <span v-if="featuredArticle.author?.name" class="mx-1.5">&bull;</span>
                <span v-if="featuredArticle.author?.name" class="font-medium text-[#191C1D] truncate">{{ featuredArticle.author?.name }}</span>
              </div>

              <router-link
                :to="getArticleUrl(featuredArticle)"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00346F] hover:underline shrink-0"
              >
                <span>Baca Artikel</span>
                <span>&rarr;</span>
              </router-link>
            </div>
          </div>
        </section>

        <!-- ─── 04. SEARCH & FILTER TOOLBAR ──────────────────────────── -->
        <section aria-label="Filter dan Pencarian Berita" class="rounded-xl border border-[#C2C6D3] bg-white p-4 sm:p-5 space-y-4 shadow-2xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            
            <!-- Search Input (5 Cols) -->
            <div class="lg:col-span-5 relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#737783]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari berita, topik, atau kata kunci..."
                class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] pl-10 pr-4 py-2 text-xs text-[#191C1D] placeholder-[#737783] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition"
                @input="onSearchInput"
              />
            </div>

            <!-- Organization Filter (3 Cols) -->
            <div class="lg:col-span-3">
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

            <!-- Category Filter (2 Cols) -->
            <div class="lg:col-span-2">
              <select
                v-model="selectedCategory"
                class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] px-3 py-2 text-xs text-[#191C1D] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition cursor-pointer"
                @change="applyFilters"
              >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categoriesList" :key="cat.id" :value="cat.slug">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Sort Filter (2 Cols) -->
            <div class="lg:col-span-2">
              <select
                v-model="selectedSort"
                class="w-full rounded border border-[#C2C6D3] bg-[#F8F9FA] px-3 py-2 text-xs text-[#191C1D] focus:border-[#00346F] focus:bg-white focus:outline-hidden transition cursor-pointer"
                @change="applyFilters"
              >
                <option value="latest">Terbaru</option>
                <option value="oldest">Terlama</option>
              </select>
            </div>

          </div>

          <!-- Active Filter Indicators & Reset Action -->
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

              <span v-if="selectedCategory" class="inline-flex items-center gap-1 rounded bg-[#E7E8E9] px-2 py-0.5 text-[11px] text-[#191C1D]">
                Kategori: {{ selectedCategory }}
                <button @click="selectedCategory = ''; applyFilters()" class="hover:text-[#BA1A1A] font-bold ml-1">&times;</button>
              </span>
            </div>

            <button
              @click="resetAllFilters"
              class="text-xs font-semibold text-[#BA1A1A] hover:underline cursor-pointer"
            >
              Reset Semua Filter
            </button>
          </div>
        </section>

        <!-- ─── 05. ARTICLE RESULTS GRID ─────────────────────────────── -->
        <section aria-label="Daftar Warta" class="space-y-6">
          
          <!-- Header Bar with Count -->
          <div class="flex items-center justify-between text-xs text-[#737783] px-1">
            <span>
              Menampilkan <strong class="text-[#191C1D] font-bold">{{ articles.length }}</strong> dari <strong class="text-[#191C1D] font-bold">{{ totalItems }}</strong> warta
            </span>
          </div>

          <!-- Loading State (Skeleton Grid) -->
          <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i" class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden p-0 space-y-3 animate-pulse">
              <div class="h-48 bg-[#E1E3E4] w-full"></div>
              <div class="p-5 space-y-3">
                <div class="h-3 bg-[#E1E3E4] rounded w-1/3"></div>
                <div class="h-5 bg-[#E1E3E4] rounded w-3/4"></div>
                <div class="h-3 bg-[#E1E3E4] rounded w-full"></div>
              </div>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="isError" class="py-16 text-center rounded-xl border border-[#C2C6D3] bg-white p-8 space-y-3">
            <div class="text-[#BA1A1A] font-bold text-sm">Gagal memuat warta berita.</div>
            <p class="text-xs text-[#737783]">Terjadi kendala saat menghubungi server. Silakan coba kembali.</p>
            <button
              @click="fetchArticles"
              class="rounded bg-[#00346F] hover:bg-[#004A99] px-4 py-2 text-xs font-semibold text-white transition shadow-2xs cursor-pointer"
            >
              Coba Lagi
            </button>
          </div>

          <!-- Empty State -->
          <div v-else-if="articles.length === 0" class="py-16 text-center rounded-xl border border-dashed border-[#C2C6D3] bg-white p-8 space-y-3">
            <div class="h-12 w-12 rounded-full bg-[#F3F4F5] text-[#737783] flex items-center justify-center mx-auto">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
            </div>
            <h3 class="text-sm font-bold text-[#191C1D]">Belum ada berita yang sesuai</h3>
            <p class="text-xs text-[#737783] max-w-sm mx-auto leading-relaxed">
              Coba ubah kata kunci pencarian atau sesuaikan pilihan filter organisasi dan kategori.
            </p>
            <div class="pt-2">
              <button
                @click="resetAllFilters"
                class="rounded border border-[#C2C6D3] bg-[#F8F9FA] hover:bg-white px-4 py-2 text-xs font-semibold text-[#00346F] transition cursor-pointer"
              >
                Reset Filter
              </button>
            </div>
          </div>

          <!-- Article Cards Grid (3 Columns) -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <article
              v-for="art in articles"
              :key="art.id"
              class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden hover:border-[#00346F] transition duration-200 flex flex-col justify-between group shadow-2xs"
            >
              <!-- Top Image -->
              <div>
                <router-link :to="getArticleUrl(art)" class="block h-48 w-full bg-[#F3F4F5] overflow-hidden relative">
                  <img
                    v-if="art.cover_image"
                    :src="resolveImageUrl(art.cover_image)"
                    :alt="art.judul || 'Warta Mahasiswa'"
                    class="h-full w-full object-cover group-hover:scale-102 transition duration-300"
                    loading="lazy"
                  />
                  <div v-else class="h-full w-full flex items-center justify-center text-xs font-bold text-[#737783] bg-[#F3F4F5]">
                    ORMAWA ITI
                  </div>
                  
                  <span
                    v-if="art.category"
                    class="absolute top-3 left-3 rounded bg-white/95 backdrop-blur-xs px-2.5 py-0.5 text-[9px] font-bold text-[#191C1D] shadow-2xs"
                  >
                    {{ art.category.name }}
                  </span>
                </router-link>

                <!-- Article Body -->
                <div class="p-5 space-y-3">
                  <!-- Organization Identity Context -->
                  <div class="flex items-center gap-2">
                    <div class="h-5 w-5 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shrink-0">
                      <img v-if="art.organization?.logo" :src="resolveImageUrl(art.organization.logo)" :alt="art.organization.nama" class="h-full w-full object-cover" />
                      <span v-else class="text-[9px] font-bold text-[#00346F]">{{ art.organization?.nama?.charAt(0) || 'O' }}</span>
                    </div>
                    <router-link
                      :to="`/organizations/${art.organization?.subdomain}`"
                      class="text-[11px] font-bold text-[#00346F] hover:underline truncate"
                    >
                      {{ art.organization?.nama }}
                    </router-link>
                  </div>

                  <!-- Headline -->
                  <h3 class="text-sm sm:text-base font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug line-clamp-2">
                    <router-link :to="getArticleUrl(art)">
                      {{ art.judul }}
                    </router-link>
                  </h3>

                  <!-- Excerpt -->
                  <p class="text-xs text-[#424751] line-clamp-2 leading-relaxed">
                    {{ art.excerpt }}
                  </p>
                </div>
              </div>

              <!-- Card Footer -->
              <div class="px-5 pb-5 pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-[11px] text-[#737783]">
                <span>{{ formatDate(art.published_at) }}</span>
                <span class="font-semibold text-[#191C1D] truncate max-w-[120px]">{{ art.author?.name }}</span>
              </div>
            </article>
          </div>

        </section>

        <!-- ─── 06. SERVER-SIDE PAGINATION ───────────────────────────── -->
        <nav
          v-if="totalPages > 1 && !isLoading"
          aria-label="Navigasi Halaman Berita"
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
import { resolveImageUrl } from '@/utils/imageUrl'
import type { PublicArticle, PublicOrganization } from '@/types/public'

const route = useRoute()
const router = useRouter()

const articles = ref<PublicArticle[]>([])
const featuredArticle = ref<PublicArticle | null>(null)
const organizationsList = ref<PublicOrganization[]>([])
const categoriesList = ref<Array<{ id: number; name: string; slug: string }>>([])

const isLoading = ref(true)
const isError = ref(false)

const searchQuery = ref('')
const selectedOrganization = ref('')
const selectedCategory = ref('')
const selectedSort = ref<'latest' | 'oldest'>('latest')
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const perPage = 12

let searchDebounceTimer: any = null

useSeoMeta(() => ({
  title: 'Kabar & Berita Organisasi Mahasiswa — ORMAWA ITI',
  description: 'Temukan artikel, warta liputan, dan publikasi resmi dari seluruh organisasi mahasiswa Institut Teknologi Indonesia.',
  ogType: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'Kabar & Berita Organisasi Mahasiswa ITI',
    url: typeof window !== 'undefined' ? window.location.origin + '/berita' : 'https://ormawa.iti.ac.id/berita',
    description: 'Pusat publikasi warta resmi seluruh organisasi mahasiswa Institut Teknologi Indonesia.',
  }
}))

const isFiltered = computed(() => {
  return !!(searchQuery.value || selectedOrganization.value || selectedCategory.value || selectedSort.value !== 'latest')
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

const getArticleUrl = (art: PublicArticle) => {
  const orgSlug = art.organization?.subdomain || 'ormawa'
  return `/organizations/${orgSlug}/articles/${art.slug}`
}

const getOrgName = (subdomain: string) => {
  const org = organizationsList.value.find(o => o.subdomain === subdomain)
  return org ? org.nama : subdomain
}

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const syncStateFromUrl = () => {
  searchQuery.value = (route.query.search as string) || ''
  selectedOrganization.value = (route.query.organization as string) || ''
  selectedCategory.value = (route.query.category as string) || ''
  selectedSort.value = (route.query.sort as 'latest' | 'oldest') || 'latest'
  currentPage.value = parseInt((route.query.page as string) || '1', 10) || 1
}

const syncUrlFromState = () => {
  const query: Record<string, string> = {}
  if (searchQuery.value) query.search = searchQuery.value
  if (selectedOrganization.value) query.organization = selectedOrganization.value
  if (selectedCategory.value) query.category = selectedCategory.value
  if (selectedSort.value !== 'latest') query.sort = selectedSort.value
  if (currentPage.value > 1) query.page = String(currentPage.value)

  router.replace({ query })
}

const fetchArticles = async () => {
  isLoading.value = true
  isError.value = false

  try {
    const res = await publicService.getGlobalArticles({
      search: searchQuery.value || undefined,
      organization: selectedOrganization.value || undefined,
      category: selectedCategory.value || undefined,
      sort: selectedSort.value,
      page: currentPage.value,
      per_page: perPage,
    })

    articles.value = res.data || []
    totalPages.value = res.meta?.last_page || 1
    totalItems.value = res.meta?.total || 0

    // Set featured article if first page and has items
    if (currentPage.value === 1 && !isFiltered.value && articles.value.length > 0) {
      featuredArticle.value = articles.value[0]
    } else {
      featuredArticle.value = null
    }
  } catch (err) {
    console.error('Failed to load global articles:', err)
    isError.value = true
  } finally {
    isLoading.value = false
  }
}

const fetchFilterOptions = async () => {
  try {
    const [orgRes, catRes] = await Promise.allSettled([
      publicService.getOrganizations({ per_page: 50 }),
      publicService.getGlobalCategories(),
    ])

    if (orgRes.status === 'fulfilled') {
      organizationsList.value = orgRes.value.data || []
    }
    if (catRes.status === 'fulfilled') {
      categoriesList.value = catRes.value || []
    }
  } catch (err) {
    console.error('Failed to load filter options:', err)
  }
}

const onSearchInput = () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    currentPage.value = 1
    syncUrlFromState()
    fetchArticles()
  }, 350)
}

const clearSearch = () => {
  searchQuery.value = ''
  currentPage.value = 1
  syncUrlFromState()
  fetchArticles()
}

const applyFilters = () => {
  currentPage.value = 1
  syncUrlFromState()
  fetchArticles()
}

const resetAllFilters = () => {
  searchQuery.value = ''
  selectedOrganization.value = ''
  selectedCategory.value = ''
  selectedSort.value = 'latest'
  currentPage.value = 1
  syncUrlFromState()
  fetchArticles()
}

const changePage = (page: number) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  currentPage.value = page
  syncUrlFromState()
  fetchArticles()

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

watch(
  () => route.query,
  () => {
    syncStateFromUrl()
    fetchArticles()
  }
)

onMounted(async () => {
  syncStateFromUrl()
  await Promise.all([fetchFilterOptions(), fetchArticles()])
})
</script>
