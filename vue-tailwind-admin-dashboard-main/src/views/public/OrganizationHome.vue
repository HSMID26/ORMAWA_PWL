<template>
  <div class="min-h-screen bg-white flex flex-col font-sans">
    <!-- Navbar -->
    <header class="bg-white sticky top-0 z-50 border-b border-gray-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center font-bold text-brand-600">
              {{ currentTenantSlug?.charAt(0).toUpperCase() || 'O' }}
            </div>
            <span class="text-lg font-bold text-gray-900 uppercase">{{ currentTenantSlug }}</span>
          </div>
          <nav class="hidden md:flex gap-8">
            <a href="#" class="text-sm font-semibold text-gray-900 border-b-2 border-brand-600 pb-5 pt-5">Beranda</a>
            <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900 pt-5 pb-5 transition-colors">Profil</a>
            <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900 pt-5 pb-5 transition-colors">Berita</a>
            <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900 pt-5 pb-5 transition-colors">Kegiatan</a>
          </nav>
          <div>
            <router-link to="/login" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
              Login Pengurus
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-grow">
      
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
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">
              {{ currentOrganization.nama }}
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto font-medium">
              {{ currentOrganization.jenis }}
            </p>
          </div>
        </section>

        <!-- Content Structure ready for when APIs exist -->
        <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="md:col-span-2 space-y-12">
              <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">Berita Terbaru</h2>
                <div class="bg-gray-50 rounded-xl p-8 text-center border border-gray-200">
                   <p class="text-gray-500">Belum ada berita yang diterbitkan.</p>
                </div>
              </div>
            </div>
            
            <div class="space-y-8">
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Kegiatan Mendatang</h3>
                <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-200">
                  <p class="text-gray-500 text-sm">Tidak ada kegiatan dalam waktu dekat.</p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
          <h3 class="text-lg font-bold uppercase tracking-wider">{{ currentTenantSlug }}</h3>
          <p class="text-sm text-gray-400 mt-2">&copy; {{ new Date().getFullYear() }} All rights reserved.</p>
        </div>
        <div class="flex gap-6">
          <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Kembali ke Portal Utama</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePublicStore } from '@/stores/public'
import { resolveTenantFromHostname } from '@/utils/tenantResolver'

const publicStore = usePublicStore()
const { currentTenantSlug, currentOrganization, isLoading, error } = storeToRefs(publicStore)

onMounted(async () => {
  // Resolve the tenant based on the hostname logic
  const slug = resolveTenantFromHostname() || 'hmif' // default to hmif for demo if no sub
  await publicStore.setTenant(slug)
})
</script>
