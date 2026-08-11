<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Daftar Agenda Kegiatan
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola jadwal acara organisasi yang akan tampil di halaman publik.
          </p>
        </div>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs">
          + Tambah Agenda
        </button>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama kegiatan atau lokasi..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <!-- Tabel Data Agenda -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Poster</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama Kegiatan</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal & Waktu</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Lokasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi Singkat</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="agenda in filteredAgendas" 
              :key="agenda.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3">
                <img :src="agenda.poster" alt="Poster" class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700" />
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                {{ agenda.nama }}
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-brand-600 dark:text-brand-400">{{ agenda.tanggal }}</div>
                <div class="text-xs text-gray-500">{{ agenda.waktu }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ agenda.lokasi }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-[200px] truncate">
                {{ agenda.deskripsi }}
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredAgendas.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data agenda kegiatan tidak ditemukan.
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
const currentPageTitle = ref('Manajemen Agenda')

// State Pencarian
const searchQuery = ref('')

// Simulasi Data Agenda (Mewakili entitas Event/Agenda)
const agendas = ref([
  {
    id: 1,
    poster: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=300&h=300&fit=crop',
    nama: 'Tech Fair Kampus 2026',
    tanggal: '12 Agustus 2026',
    waktu: '09:00 - 17:00 WIB',
    lokasi: 'Auditorium Utama ITI',
    deskripsi: 'Pameran teknologi karya mahasiswa dan seminar nasional.'
  },
  {
    id: 2,
    poster: 'https://images.unsplash.com/photo-1515169067868-5387ec356754?w=300&h=300&fit=crop',
    nama: 'Upgrading Pengurus Harian',
    tanggal: '19 Agustus 2026',
    waktu: '13:00 - 16:00 WIB',
    lokasi: 'Ruang Rapat Gedung B',
    deskripsi: 'Pelatihan internal untuk peningkatan kapasitas pengurus HMIF periode baru.'
  },
  {
    id: 3,
    poster: 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=300&h=300&fit=crop',
    nama: 'Webinar Karier Data & AI',
    tanggal: '27 Agustus 2026',
    waktu: '19:00 - 21:00 WIB',
    lokasi: 'Zoom Meeting (Online)',
    deskripsi: 'Diskusi panel bersama pakar industri mengenai peluang karier di bidang kecerdasan buatan.'
  }
])

// Logika Pencarian Reaktif
const filteredAgendas = computed(() => {
  if (!searchQuery.value) return agendas.value
  
  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return agendas.value.filter(agenda => 
    agenda.nama.toLowerCase().includes(lowerCaseQuery) ||
    agenda.lokasi.toLowerCase().includes(lowerCaseQuery) ||
    agenda.tanggal.toLowerCase().includes(lowerCaseQuery)
  )
})
</script>