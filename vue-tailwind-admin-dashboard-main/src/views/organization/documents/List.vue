<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Arsip Dokumen
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola file proposal, LPJ, SK, dan dokumen organisasi lainnya.
          </p>
        </div>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Unggah Dokumen
        </button>
      </div>

      <!-- Search & Filter Bar -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row">
        <!-- Pencarian -->
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama dokumen..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          />
        </div>
        
        <!-- Filter Kategori -->
        <div class="sm:w-48">
          <select
            v-model="filterKategori"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Kategori</option>
            <option value="Surat Keputusan">Surat Keputusan</option>
            <option value="Proposal">Proposal</option>
            <option value="Laporan (LPJ)">Laporan (LPJ)</option>
            <option value="Template">Template</option>
          </select>
        </div>

        <!-- Filter Akses/Visibilitas -->
        <div class="sm:w-48">
          <select
            v-model="filterAkses"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Akses</option>
            <option value="Publik">Publik</option>
            <option value="Internal">Internal (Anggota)</option>
          </select>
        </div>
      </div>

      <!-- Tabel Data Dokumen -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama File</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Kategori</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tipe & Ukuran</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Visibilitas Akses</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Diunggah</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="doc in filteredDocuments" 
              :key="doc.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                    <span v-if="doc.tipe === 'PDF'" class="text-error-500 font-bold text-xs">PDF</span>
                    <span v-else-if="doc.tipe === 'DOCX'" class="text-blue-500 font-bold text-xs">DOC</span>
                    <span v-else class="text-gray-500 font-bold text-xs">FILE</span>
                  </div>
                  <p class="text-sm font-medium text-gray-800 dark:text-white/90 line-clamp-2" :title="doc.nama">
                    {{ doc.nama }}
                  </p>
                </div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                {{ doc.kategori }}
              </td>
              <td class="px-4 py-4">
                <span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ doc.tipe }}</span>
                <span class="block text-xs text-gray-500">{{ doc.ukuran }}</span>
              </td>
              <td class="px-4 py-4">
                <span 
                  class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="doc.akses === 'Publik' ? 'bg-success-100 text-success-800 dark:bg-success-500/20 dark:text-success-400' : 'bg-warning-100 text-warning-800 dark:bg-warning-500/20 dark:text-warning-400'"
                >
                  {{ doc.akses }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                {{ doc.tanggal }}
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors" title="Unduh File">Unduh</button>
                  <button class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 transition-colors" title="Edit Meta Data">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors" title="Hapus File">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredDocuments.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Dokumen tidak ditemukan.
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
const currentPageTitle = ref('Arsip Dokumen')

// State Pencarian dan Filter
const searchQuery = ref('')
const filterKategori = ref('')
const filterAkses = ref('')

// Simulasi Data Dokumen (Berdasarkan parameter PRD)
const documents = ref([
  {
    id: 1,
    nama: 'Surat Keputusan Kepengurusan HMIF Periode 2026/2027.pdf',
    kategori: 'Surat Keputusan',
    tipe: 'PDF',
    ukuran: '1.2 MB',
    akses: 'Publik',
    tanggal: '10 Jan 2026'
  },
  {
    id: 2,
    nama: 'Proposal Sponsorship Tech Fair 2026.pdf',
    kategori: 'Proposal',
    tipe: 'PDF',
    ukuran: '4.5 MB',
    akses: 'Internal',
    tanggal: '15 Jul 2026'
  },
  {
    id: 3,
    nama: 'LPJ Kegiatan Buka Bersama 2025.pdf',
    kategori: 'Laporan (LPJ)',
    tipe: 'PDF',
    ukuran: '3.1 MB',
    akses: 'Publik',
    tanggal: '20 Mei 2025'
  },
  {
    id: 4,
    nama: 'Template Notulensi Rapat Mingguan.docx',
    kategori: 'Template',
    tipe: 'DOCX',
    ukuran: '145 KB',
    akses: 'Internal',
    tanggal: '05 Feb 2026'
  }
])

// Logika Filter Reaktif
const filteredDocuments = computed(() => {
  return documents.value.filter(doc => {
    // Filter Pencarian Text
    const matchSearch = doc.nama.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // Filter Kategori (Dropdown)
    const matchKategori = filterKategori.value === '' || doc.kategori === filterKategori.value
    
    // Filter Akses Visibilitas (Dropdown)
    const matchAkses = filterAkses.value === '' || doc.akses === filterAkses.value

    return matchSearch && matchKategori && matchAkses
  })
})
</script>