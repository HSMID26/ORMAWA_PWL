<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans selection:bg-slate-900 selection:text-white">
    <PublicNavbar />

    <main id="main-content" class="flex-grow py-12 sm:py-16 min-h-[65vh]">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <!-- Header & Intro (Centered Editorial) -->
        <div class="max-w-2xl mx-auto text-center space-y-2.5">
          <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Direktori Kemahasiswaan</span>
          <h1 class="text-2xl sm:text-4xl font-bold text-slate-950 tracking-tight">
            Organisasi Mahasiswa ITI
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
            Daftar resmi Himpunan Mahasiswa Program Studi (HMPS), Unit Kegiatan Mahasiswa (UKM), dan Badan Eksekutif Mahasiswa Institut Teknologi Indonesia.
          </p>
        </div>

        <!-- Filter & Search Toolbar (Centered & Compact) -->
        <div class="max-w-2xl mx-auto rounded-xl border border-slate-200 bg-white p-2.5 shadow-2xs flex flex-col sm:flex-row gap-2.5 items-center">
          <div class="flex-grow w-full relative">
            <input
              v-model="searchQuery"
              @input="onSearch"
              type="text"
              placeholder="Cari nama organisasi..."
              class="w-full h-9 rounded-lg border border-slate-200 pl-3 pr-8 text-xs text-slate-900 focus:border-blue-700 focus:outline-none"
            />
            <span class="absolute right-2.5 top-2.5 text-slate-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
          </div>

          <!-- Category Filter Tabs -->
          <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto justify-center sm:justify-start">
            <button
              v-for="j in jenisOptions"
              :key="j.value"
              @click="selectedJenis = j.value; loadOrganizations()"
              :class="[
                'rounded-lg px-3 py-1.5 text-xs font-semibold transition shrink-0',
                selectedJenis === j.value
                  ? 'bg-slate-900 text-white shadow-2xs'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              ]"
            >
              {{ j.label }}
            </button>
          </div>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="flex flex-wrap gap-6 justify-center">
          <div v-for="i in 3" :key="i" class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] max-w-sm h-44 rounded-xl bg-white border border-slate-200 animate-pulse p-5"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="organizations.length === 0" class="max-w-md mx-auto py-16 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
          Tidak ada organisasi yang sesuai dengan kriteria pencarian.
        </div>

        <!-- Balanced Centered Grid -->
        <div v-else class="flex flex-wrap gap-6 justify-center">
          <router-link
            v-for="org in organizations"
            :key="org.id"
            :to="`/organizations/${org.subdomain}`"
            class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] max-w-sm rounded-xl border border-slate-200 bg-white p-5 hover:border-slate-400 hover:shadow-2xs transition duration-150 flex flex-col justify-between group"
          >
            <div class="space-y-3.5">
              <div class="flex items-center justify-between">
                <div class="h-10 w-10 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                  <img v-if="org.logo" :src="org.logo" :alt="org.nama" class="h-full w-full object-cover" />
                  <span v-else class="font-bold text-sm text-slate-900">{{ org.nama.charAt(0) }}</span>
                </div>
                <span class="rounded bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-700 uppercase">
                  {{ org.jenis }}
                </span>
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-950 group-hover:text-blue-900 transition leading-snug">
                  {{ org.nama }}
                </h3>
                <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                  {{ org.slogan || org.deskripsi || 'Organisasi kemahasiswaan Institut Teknologi Indonesia.' }}
                </p>
              </div>
            </div>
            
            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-medium">
              <span>{{ org.subdomain }}.iti.ac.id</span>
              <span class="text-blue-900 font-bold group-hover:translate-x-0.5 transition">&rarr;</span>
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
]

useSeoMeta(() => ({
  title: 'Direktori Organisasi Mahasiswa ITI',
  description: 'Daftar lengkap seluruh UKM, HMPS, dan BEM di lingkungan Institut Teknologi Indonesia.',
  ogType: 'website',
}))

let debounceTimer: any = null
const onSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadOrganizations()
  }, 200)
}

const loadOrganizations = async () => {
  isLoading.value = true
  try {
    const res = await publicService.getOrganizations({
      search: searchQuery.value || undefined,
      jenis: selectedJenis.value || undefined,
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
