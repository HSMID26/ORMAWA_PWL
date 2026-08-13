<template>
  <div class="flex-grow bg-white">
    <div class="bg-gray-50 py-16 border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
          Berita & Artikel
        </h1>
        <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
          Publikasi terkini seputar kegiatan, opini, dan informasi dari {{ currentOrganization?.nama || 'organisasi' }}.
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      
      <!-- State: Loading -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
        <p class="text-gray-500 font-medium">Memuat berita terkini...</p>
      </div>

      <!-- State: Error / Unavailable -->
      <div v-else-if="error" class="text-center py-20">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Berita Belum Tersedia</h2>
        <p class="text-lg text-gray-600 max-w-lg mx-auto">
          Informasi publik organisasi terkait berita dan artikel akan tampil setelah layanan konten terhubung.
        </p>
      </div>
      
      <!-- State: Empty (No news yet) -->
      <div v-else-if="posts.length === 0" class="text-center py-20">
        <p class="text-gray-500">Belum ada berita yang diterbitkan saat ini.</p>
      </div>

      <!-- State: Success -->
      <div v-else>
        <!-- The UI for news will use ArticleCard.vue mapped to the posts array -->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePublicStore } from '@/stores/public'
import { publicService } from '@/services/publicService'
import type { Post } from '@/types/api'

const publicStore = usePublicStore()
const { currentTenantSlug, currentOrganization } = storeToRefs(publicStore)

const isLoading = ref(true)
const error = ref<string | null>(null)
const posts = ref<Post[]>([])

const fetchPosts = async () => {
  if (!currentTenantSlug.value) return
  
  isLoading.value = true
  error.value = null
  
  try {
    posts.value = await publicService.getPostsByTenant(currentTenantSlug.value)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Backend Endpoint Unavailable'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchPosts()
})
</script>
