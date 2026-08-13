<template>
  <div>
    <!-- State: Loading -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
      <div class="w-8 h-8 border-4 border-gray-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
      <p class="text-gray-500 font-medium">Memuat data organisasi...</p>
    </div>
    
    <!-- State: Error / Endpoint Unavailable -->
    <div v-else-if="error" class="max-w-3xl mx-auto py-24 px-4 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-4">Informasi Belum Tersedia</h1>
      <p class="text-lg text-gray-600 mb-8 max-w-xl mx-auto">
        {{ error }}
      </p>
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-500 text-left">
        <p class="font-medium text-gray-700 mb-1">Catatan Sistem:</p>
        <ul class="list-disc list-inside space-y-1">
          <li>Membutuhkan endpoint backend: <code>GET /api/public/organizations/{{ currentTenantSlug }}</code></li>
          <li>Membutuhkan endpoint backend: <code>GET /api/public/organizations/{{ currentTenantSlug }}/posts</code></li>
        </ul>
      </div>
    </div>

    <!-- State: Success (Data Loaded) -->
    <div v-else-if="currentOrganization">
      <!-- Hero Section -->
      <section class="bg-gray-50 py-20 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div class="flex justify-center mb-6">
            <!-- Fallback logo -->
            <div class="w-24 h-24 bg-white border border-gray-200 shadow-sm rounded-full flex items-center justify-center">
               <span class="text-3xl font-bold text-gray-400">{{ currentOrganization.nama.charAt(0) }}</span>
            </div>
          </div>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">
            {{ currentOrganization.nama }}
          </h1>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto font-medium mb-8">
            {{ currentOrganization.jenis }}
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <router-link :to="`/org/${currentTenantSlug}/berita`" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
              Lihat Berita
            </router-link>
            <router-link :to="`/org/${currentTenantSlug}/profil`" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-colors">
              Tentang Organisasi
            </router-link>
          </div>
        </div>
      </section>

      <!-- Content Structure ready for when APIs exist -->
      <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <div class="md:col-span-2 space-y-12">
            <div>
              <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-2">
                <h2 class="text-2xl font-bold text-gray-900">Berita Terbaru</h2>
                <router-link :to="`/org/${currentTenantSlug}/berita`" class="text-brand-600 hover:text-brand-700 font-medium text-sm">Lihat Semua &rarr;</router-link>
              </div>
              <div class="bg-gray-50 rounded-xl p-8 text-center border border-gray-200">
                 <p class="text-gray-500">Belum ada berita yang diterbitkan.</p>
              </div>
            </div>
          </div>
          
          <div class="space-y-8">
            <div>
              <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                <h3 class="text-lg font-bold text-gray-900">Kegiatan Mendatang</h3>
                <router-link :to="`/org/${currentTenantSlug}/kegiatan`" class="text-brand-600 hover:text-brand-700 font-medium text-sm">Semua</router-link>
              </div>
              <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-200">
                <p class="text-gray-500 text-sm">Tidak ada kegiatan dalam waktu dekat.</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()
const { currentTenantSlug, currentOrganization, isLoading, error } = storeToRefs(publicStore)
</script>
