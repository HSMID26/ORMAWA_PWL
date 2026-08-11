<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Daftar Pengumuman
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola informasi penting, batas waktu, dan tingkat prioritas pengumuman.
          </p>
        </div>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Buat Pengumuman
        </button>
      </div>

      <!-- Search & Filter Bar -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari judul pengumuman..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          />
        </div>
        <div class="sm:w-48">
          <select
            v-model="filterPriority"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Prioritas</option>
            <option value="Tinggi">Tinggi (High)</option>
            <option value="Sedang">Sedang (Medium)</option>
            <option value="Rendah">Rendah (Low)</option>
          </select>
        </div>
      </div>

      <!-- Tabel Data Pengumuman -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Judul Pengumuman</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Prioritas</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Berlaku</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="item in filteredAnnouncements" 
              :key="item.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-4">
                <p class="text-sm font-semibold text-gray-800 dark:text-white/90 line-clamp-1 max-w-[300px]" :title="item.judul">
                  {{ item.judul }}
                </p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-[300px]">
                  {{ item.isiSingkat }}
                </p>
              </td>
              <td class="px-4 py-4">
                <span 
                  class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium"
                  :class="{
                    'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400': item.prioritas === 'Tinggi',
                    'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400': item.prioritas === 'Sedang',
                    'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': item.prioritas === 'Rendah'
                  }"
                >
                  <span class="h-1.5 w-1.5 rounded-full" 
                    :class="{
                      'bg-error-600': item.prioritas === 'Tinggi',
                      'bg-warning-600': item.prioritas === 'Sedang',
                      'bg-blue-600': item.prioritas === 'Rendah'
                    }">
                  </span>
                  {{ item.prioritas }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  {{ item.tanggalBerlaku }}
                </div>
              </td>
              <td class="px-4 py-4">
                <span 
                  class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="item.status === 'Aktif' ? 'bg-success-100 text-success-800 dark:bg-success-500/20 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                >
                  {{ item.status }}
                </span>
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredAnnouncements.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data pengumuman tidak ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

// Title Halaman
const currentPageTitle = ref('Pengumuman')

// State Pencarian & Filter
const searchQuery = ref('')
const filterPriority = ref('')

// Simulasi Data Pengumuman (Sesuai Entitas PRD)
const announcements = ref([
  {
    id: 1,
    judul: 'Perpanjangan Pendaftaran Staff Muda HMIF',
    isiSingkat: 'Pendaftaran diperpanjang hingga akhir bulan karena kuota divisi belum terpenuhi.',
    prioritas: 'Tinggi',
    tanggalBerlaku: '15 Agustus 2026',
    status: 'Aktif'
  },
  {
    id: 2,
    judul: 'Informasi Beasiswa Prestasi ITI 2026',
    isiSingkat: 'Pengumpulan berkas fisik terakhir diserahkan ke ruang kemahasiswaan pada hari Jumat.',
    prioritas: 'Sedang',
    tanggalBerlaku: '20 Agustus 2026',
    status: 'Aktif'
  },
  {
    id: 3,
    judul: 'Pemeliharaan Server Website HMIF',
    isiSingkat: 'Website tidak dapat diakses sementara pada malam hari pukul 00:00 - 04:00 WIB.',
    prioritas: 'Rendah',
    tanggalBerlaku: '06 Agustus 2026',
    status: 'Aktif'
  },
  {
    id: 4,
    judul: 'Pengumuman Hasil Lomba Hackathon Nasional',
    isiSingkat: 'Selamat kepada tim yang lolos ke tahap final, silakan cek email masing-masing.',
    prioritas: 'Sedang',
    tanggalBerlaku: '01 Agustus 2026',
    status: 'Expired'
  }
])

// Logika Filter
const filteredAnnouncements = computed(() => {
  return announcements.value.filter(item => {
    const matchSearch = item.judul.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                        item.isiSingkat.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchPriority = filterPriority.value === '' || item.prioritas === filterPriority.value

    return matchSearch && matchPriority
  })
})
</script>