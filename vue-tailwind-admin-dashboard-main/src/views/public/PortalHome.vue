<template>
  <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
    <!-- Public Navbar -->
    <header class="bg-white shadow-sm border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-brand-600 text-white rounded flex items-center justify-center font-bold">
              ITI
            </div>
            <span class="text-lg font-semibold text-gray-900 tracking-tight">Portal ORMAWA</span>
          </div>
          <nav class="hidden md:flex gap-6">
            <a href="#" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Beranda</a>
            <a href="#" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Direktori</a>
            <a href="#" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Berita Utama</a>
          </nav>
          <div>
            <router-link to="/login" class="text-sm font-medium text-brand-600 hover:text-brand-700 bg-brand-50 px-4 py-2 rounded-md transition-colors">
              Login Admin
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <!-- Hero Section -->
      <section class="bg-white border-b border-gray-200 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 tracking-tight mb-6">
            Pusat Informasi Organisasi Mahasiswa
          </h1>
          <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
            Temukan berbagai organisasi, unit kegiatan mahasiswa, dan berita terbaru seputar aktivitas kemahasiswaan di lingkungan kampus.
          </p>
          <div class="mt-8 max-w-xl mx-auto">
            <div class="relative flex items-center">
              <input
                type="text"
                placeholder="Cari organisasi atau kegiatan..."
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none"
              />
              <button class="absolute right-2 px-4 py-1.5 bg-brand-600 text-white rounded-md text-sm font-medium hover:bg-brand-700">
                Cari
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Organizations Directory (Data-Driven with Error State) -->
      <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="mb-10 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Direktori Organisasi</h2>
            <a href="#" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua &rarr;</a>
          </div>

          <div v-if="isLoading" class="flex justify-center py-12">
            <p class="text-gray-500">Memuat direktori...</p>
          </div>
          
          <div v-else-if="error" class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center max-w-2xl mx-auto">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 7H20"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Konten API Belum Tersedia</h3>
            <p class="text-gray-500">{{ error }}</p>
            <p class="text-sm text-gray-400 mt-4 border-t border-gray-200 pt-4">API Publik: GET /api/public/organizations belum diimplementasikan di sisi backend.</p>
            
            <div class="mt-6 text-left bg-white p-6 rounded-xl border border-brand-200 shadow-sm relative overflow-hidden">
              <div class="absolute top-0 right-0 bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">DEMO</div>
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold text-xl">
                  H
                </div>
                <div>
                  <h3 class="font-bold text-gray-900 text-lg">Himpunan Mahasiswa Informatika (HMIF)</h3>
                  <p class="text-sm text-gray-500">Himpunan Mahasiswa</p>
                </div>
              </div>
              <router-link to="/org/hmif" class="inline-flex items-center gap-2 text-sm font-medium text-brand-600 hover:text-brand-700 bg-brand-50 px-4 py-2 rounded-lg">
                Lihat Website Demo &rarr;
              </router-link>
            </div>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Normal layout when data is available -->
            <div v-for="org in organizations" :key="org.id" class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center font-bold text-gray-600">
                  {{ org.nama.charAt(0) }}
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ org.nama }}</h3>
                  <p class="text-sm text-gray-500">{{ org.jenis }}</p>
                </div>
              </div>
              <a :href="`http://${org.subdomain}.iti.ac.id`" class="text-sm font-medium text-brand-600 hover:text-brand-700">Kunjungi Situs &rarr;</a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-gray-200 py-12 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
          <div class="text-center md:text-left">
            <h3 class="text-lg font-bold text-gray-900">Portal ORMAWA</h3>
            <p class="text-sm text-gray-500 mt-1">&copy; {{ new Date().getFullYear() }} Institut Teknologi Indonesia</p>
          </div>
          <div class="flex gap-6">
            <a href="#" class="text-sm text-gray-500 hover:text-gray-900">Bantuan</a>
            <a href="#" class="text-sm text-gray-500 hover:text-gray-900">Kebijakan Privasi</a>
            <a href="#" class="text-sm text-gray-500 hover:text-gray-900">Kontak</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import type { Organization } from '@/types/api'

const organizations = ref<Organization[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    isLoading.value = true
    organizations.value = await publicService.getOrganizations()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Gagal memuat direktori.'
  } finally {
    isLoading.value = false
  }
})
</script>
