<template>
  <div class="flex-grow bg-white">
    <div class="bg-gray-50 py-16 border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
          Struktur Kepengurusan
        </h1>
        <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
          Susunan kepengurusan organisasi periode aktif saat ini.
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      
      <!-- State: Loading -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
        <p class="text-gray-500 font-medium">Memuat data kepengurusan...</p>
      </div>

      <!-- State: Error / Unavailable -->
      <div v-else-if="error" class="text-center py-20">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Data Belum Tersedia</h2>
        <p class="text-lg text-gray-600 max-w-lg mx-auto">
          Informasi struktur kepengurusan publik akan tampil setelah layanan konten terhubung.
        </p>
      </div>
      
      <!-- State: Empty (No structure data yet) -->
      <div v-else-if="structure.length === 0" class="text-center py-20">
        <p class="text-gray-500">Belum ada struktur kepengurusan yang dipublikasikan.</p>
      </div>

      <!-- State: Success -->
      <div v-else>
        <!-- The UI for structure will be generated here using the `structure` array data. -->
        <!-- Since the endpoint is currently blocked, this section is prepared structurally. -->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePublicStore } from '@/stores/public'
import { publicService } from '@/services/publicService'

const publicStore = usePublicStore()
const { currentTenantSlug } = storeToRefs(publicStore)

const isLoading = ref(true)
const error = ref<string | null>(null)
const structure = ref<any[]>([])

const fetchStructure = async () => {
  if (!currentTenantSlug.value) return
  
  isLoading.value = true
  error.value = null
  
  try {
    structure.value = await publicService.getStructureByTenant(currentTenantSlug.value)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Backend Endpoint Unavailable'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchStructure()
})
</script>
