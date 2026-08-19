<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Galeri Dokumentasi
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola album foto dan video dari setiap kegiatan organisasi.
          </p>
        </div>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Buat Album Baru
        </button>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama album atau kegiatan terkait..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <!-- Grid Album Galeri -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
        
        <div 
          v-for="album in filteredAlbums" 
          :key="album.id"
          class="group relative flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow dark:border-gray-700 dark:bg-gray-800/50"
        >
          <!-- Cover Album (Thumbnail) -->
          <div class="relative h-48 w-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
            <img 
              :src="album.cover" 
              :alt="album.namaAlbum" 
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <!-- Overlay Label Jumlah Media -->
            <div class="absolute bottom-3 right-3 rounded-md bg-black/60 px-2.5 py-1 text-xs font-medium text-white backdrop-blur-sm">
              {{ album.jumlahMedia }} File
            </div>
          </div>

          <!-- Informasi Album -->
          <div class="flex flex-1 flex-col p-4">
            <div class="mb-1 flex items-center justify-between">
              <span class="text-[10px] font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">
                Event Terkait
              </span>
              <span class="text-xs text-gray-400">{{ album.tanggal }}</span>
            </div>
            
            <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90 line-clamp-1" :title="album.eventTerkait">
              {{ album.eventTerkait }}
            </h4>
            
            <p class="text-lg font-bold text-gray-900 dark:text-white line-clamp-1 mb-4" :title="album.namaAlbum">
              {{ album.namaAlbum }}
            </p>

            <!-- Aksi Bawah -->
            <div class="mt-auto flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
              <button class="flex-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                Kelola Media
              </button>
              <button class="flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 hover:text-brand-500 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
              </button>
              <button class="flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-error-50 hover:text-error-500 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- State Kosong (Tampil jika pencarian tidak ditemukan) -->
      <div v-if="filteredAlbums.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <h4 class="text-sm font-medium text-gray-800 dark:text-white/90">Tidak ada album ditemukan</h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian Anda.</p>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

// Title Halaman
const currentPageTitle = ref('Galeri Dokumentasi')

// State Pencarian
const searchQuery = ref('')

// Simulasi Data Album Galeri
const albums = ref([
  {
    id: 1,
    namaAlbum: 'Dokumentasi Tech Fair 2026',
    eventTerkait: 'Tech Fair Kampus 2026',
    cover: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&h=400&fit=crop',
    jumlahMedia: 42,
    tanggal: '14 Ags 2026'
  },
  {
    id: 2,
    namaAlbum: 'Seminar Nasional AI',
    eventTerkait: 'Webinar Karier Data & AI',
    cover: 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=600&h=400&fit=crop',
    jumlahMedia: 15,
    tanggal: '28 Ags 2026'
  },
  {
    id: 3,
    namaAlbum: 'Rapat Kerja Pengurus Baru',
    eventTerkait: 'Upgrading Pengurus Harian',
    cover: 'https://images.unsplash.com/photo-1515169067868-5387ec356754?w=600&h=400&fit=crop',
    jumlahMedia: 28,
    tanggal: '20 Ags 2026'
  },
  {
    id: 4,
    namaAlbum: 'Buka Bersama HMIF 2025',
    eventTerkait: 'Kegiatan Rutin Internal',
    cover: 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=600&h=400&fit=crop',
    jumlahMedia: 56,
    tanggal: '10 Apr 2025'
  }
])

// Logika Filter Pencarian
const filteredAlbums = computed(() => {
  if (!searchQuery.value) return albums.value
  
  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return albums.value.filter(album => 
    album.namaAlbum.toLowerCase().includes(lowerCaseQuery) ||
    album.eventTerkait.toLowerCase().includes(lowerCaseQuery)
  )
})
</script>