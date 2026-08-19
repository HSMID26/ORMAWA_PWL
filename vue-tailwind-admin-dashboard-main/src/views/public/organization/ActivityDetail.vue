<template>
  <div class="flex-grow bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      
      <!-- State: Loading -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
        <p class="text-gray-500 font-medium">Memuat detail kegiatan...</p>
      </div>

      <!-- State: Error / Unavailable -->
      <div v-else-if="error" class="text-center py-32">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Detail Kegiatan Belum Tersedia</h2>
        <p class="text-lg text-gray-600 mb-8 max-w-xl mx-auto">
          Layanan detail kegiatan untuk saat ini belum terhubung.
        </p>
        <router-link :to="`/org/${currentTenantSlug}/kegiatan`" class="text-brand-600 font-medium hover:text-brand-700">
          &larr; Kembali ke Daftar Kegiatan
        </router-link>
      </div>

      <!-- State: Success -->
      <article v-else-if="activity">
        <!-- Structural representation of an activity detail -->
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
import type { Activity } from '@/types/api'

const route = useRoute()
const publicStore = usePublicStore()
const { currentTenantSlug } = storeToRefs(publicStore)

const isLoading = ref(true)
const error = ref<string | null>(null)
const activity = ref<Activity | null>(null)

const fetchActivity = async () => {
  const tenantSlug = route.params.slug as string
  const activityId = route.params.id as string
  
  if (!tenantSlug || !activityId) return
  
  isLoading.value = true
  error.value = null
  
  try {
    activity.value = await publicService.getActivityById(tenantSlug, activityId)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Backend Endpoint Unavailable'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchActivity()
})
</script>
