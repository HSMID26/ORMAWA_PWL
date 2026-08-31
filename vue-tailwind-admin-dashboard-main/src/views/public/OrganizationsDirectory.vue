<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow py-12 md:py-16 pt-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header & Intro (Centered Editorial) -->
        <div class="max-w-3xl mx-auto text-center space-y-2.5">
          <div class="inline-flex items-center gap-2 rounded border border-[#C2C6D3] bg-white px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#00346F]">
            <span>Direktori Kemahasiswaan</span>
          </div>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-[#191C1D] tracking-tight">
            Organisasi Mahasiswa Institut Teknologi Indonesia
          </h1>
          <p class="text-xs sm:text-sm text-[#424751] leading-relaxed max-w-2xl mx-auto">
            Daftar resmi seluruh Himpunan Mahasiswa Program Studi (HMPS), Unit Kegiatan Mahasiswa (UKM), Badan Eksekutif Mahasiswa (BEM), dan Senat Mahasiswa ITI.
          </p>
        </div>

        <!-- Filter & Search Toolbar (Stitch Minimal Surface) -->
        <div class="max-w-3xl mx-auto rounded border border-[#C2C6D3] bg-white p-3 shadow-2xs space-y-3">
          <div class="relative w-full">
            <input
              v-model="searchQuery"
              @input="onSearch"
              type="text"
              placeholder="Cari nama atau deskripsi organisasi..."
              class="w-full h-10 rounded border border-[#C2C6D3] bg-white pl-3.5 pr-9 text-xs text-[#191C1D] placeholder:text-[#737783] focus:border-[#00346F] focus:outline-none focus:ring-1 focus:ring-[#00346F] transition"
              aria-label="Cari organisasi"
            />
            <span class="absolute right-3 top-3 text-[#737783]">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
          </div>

          <!-- Category Filter Tabs -->
          <div class="flex items-center gap-2 overflow-x-auto justify-start sm:justify-center pt-1 border-t border-[#E1E3E4]">
            <button
              v-for="j in jenisOptions"
              :key="j.value"
              @click="selectedJenis = j.value; loadOrganizations()"
              :class="[
                'rounded px-3 py-1 text-xs font-semibold transition shrink-0 cursor-pointer',
                selectedJenis === j.value
                  ? 'bg-[#00346F] text-white shadow-2xs'
                  : 'bg-[#F8F9FA] text-[#424751] hover:bg-[#E7E8E9] hover:text-[#191C1D] border border-[#C2C6D3]'
              ]"
            >
              {{ j.label }}
            </button>
          </div>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="i in 6" :key="i" class="h-44 rounded bg-white border border-[#C2C6D3] animate-pulse p-5"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="organizations.length === 0" class="max-w-md mx-auto py-12 text-center text-xs text-[#737783] border border-dashed border-[#C2C6D3] rounded bg-white space-y-2">
          <p class="font-bold text-[#191C1D] text-sm">Tidak Ada Hasil</p>
          <p>Tidak ada organisasi kemahasiswaan yang sesuai dengan kriteria pencarian.</p>
        </div>

        <!-- Modular Grid with Balanced Symmetry -->
        <div
          v-else
          :class="[
            'grid gap-6',
            organizations.length === 1 ? 'grid-cols-1 max-w-md mx-auto' :
            organizations.length === 2 ? 'grid-cols-1 sm:grid-cols-2 max-w-3xl mx-auto' :
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'
          ]"
        >
          <router-link
            v-for="org in organizations"
            :key="org.id"
            :to="`/organizations/${org.subdomain}`"
            class="rounded border border-[#C2C6D3] bg-white p-5 hover:border-[#00346F] hover:shadow-xs transition duration-150 flex flex-col justify-between group shadow-2xs"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="h-12 w-12 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center shadow-2xs">
                  <img v-if="org.logo" :src="org.logo" :alt="org.nama" class="h-full w-full object-cover" />
                  <span v-else class="font-bold text-sm text-[#00346F]">{{ org.nama.charAt(0) }}</span>
                </div>
                <span class="rounded bg-[#E7E8E9] px-2.5 py-0.5 text-[10px] font-bold text-[#191C1D] uppercase tracking-wide">
                  {{ org.jenis }}
                </span>
              </div>
              <div>
                <h3 class="text-sm sm:text-base font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
                  {{ org.nama }}
                </h3>
                <p class="text-xs text-[#424751] line-clamp-2 mt-1.5 leading-relaxed">
                  {{ org.slogan || org.deskripsi || 'Organisasi kemahasiswaan Institut Teknologi Indonesia.' }}
                </p>
              </div>
            </div>

            <div class="mt-5 pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-xs text-[#737783] font-medium">
              <span class="text-[#424751]">{{ org.subdomain }}.iti.ac.id</span>
              <span class="text-[#00346F] font-bold group-hover:translate-x-0.5 transition duration-150">&rarr;</span>
            </div>
          </router-link>
        </div>

      </div>
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
import type { PublicOrganization } from '@/types/public'

const organizations = ref<PublicOrganization[]>([])
const isLoading = ref(true)
const searchQuery = ref('')
const selectedJenis = ref('')

const jenisOptions = [
  { label: 'Semua', value: '' },
  { label: 'HMPS', value: 'HMPS' },
  { label: 'UKM', value: 'UKM' },
  { label: 'BEM', value: 'BEM' },
  { label: 'Senat', value: 'Senat' },
  { label: 'Lainnya', value: 'Lainnya' },
]

useSeoMeta(() => ({
  title: 'Direktori Ormawa | ORMAWA ITI',
  description: 'Daftar resmi seluruh Himpunan Mahasiswa Program Studi (HMPS), Unit Kegiatan Mahasiswa (UKM), dan Badan Eksekutif Mahasiswa ITI.',
  ogType: 'website',
}))

let debounceTimer: any = null
const onSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadOrganizations()
  }, 250)
}

const loadOrganizations = async () => {
  isLoading.value = true
  try {
    const res = await publicService.getOrganizations({
      jenis: selectedJenis.value || undefined,
      search: searchQuery.value || undefined,
      per_page: 50,
    })
    organizations.value = res.data || []
  } catch (err) {
    console.error('Failed to load organizations:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadOrganizations()
})
</script>
