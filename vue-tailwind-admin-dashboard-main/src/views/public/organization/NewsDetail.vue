<template>
  <div class="flex-grow bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      
      <!-- State: Loading -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
        <p class="text-gray-500 font-medium">Memuat artikel...</p>
      </div>

      <!-- State: Error / Unavailable -->
      <div v-else-if="error" class="text-center py-32">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Artikel Belum Tersedia</h2>
        <p class="text-lg text-gray-600 mb-8 max-w-xl mx-auto">
          Layanan detail berita untuk saat ini belum terhubung.
        </p>
        <router-link :to="`/org/${currentTenantSlug}/berita`" class="text-brand-600 font-medium hover:text-brand-700">
          &larr; Kembali ke Daftar Berita
        </router-link>
      </div>

      <!-- State: Success -->
      <article v-else-if="post">
        <!-- Structural representation of a news detail article -->
      </article>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import { usePublicStore } from '@/stores/public'
import { publicService } from '@/services/publicService'
import type { Post } from '@/types/api'

const route = useRoute()
const publicStore = usePublicStore()
const { currentTenantSlug } = storeToRefs(publicStore)

const isLoading = ref(true)
const error = ref<string | null>(null)
const post = ref<Post | null>(null)

const fetchPost = async () => {
  const tenantSlug = route.params.slug as string
  const postSlug = route.params.postSlug as string
  
  if (!tenantSlug || !postSlug) return
  
  isLoading.value = true
  error.value = null
  
  try {
    post.value = await publicService.getPostBySlug(tenantSlug, postSlug)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Backend Endpoint Unavailable'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchPost()
})
</script>
