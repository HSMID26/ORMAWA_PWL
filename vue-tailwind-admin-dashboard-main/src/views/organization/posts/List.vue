<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Daftar Berita & Artikel
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola publikasi tulisan, pengumuman, dan liputan kegiatan organisasi.
          </p>
        </div>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Tulis Artikel Baru
        </button>
      </div>

      <!-- Search & Filter Bar -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari judul artikel atau penulis..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          />
        </div>
        <div class="sm:w-48">
          <select
            v-model="filterStatus"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Status</option>
            <option value="Published">Published</option>
            <option value="Review">Menunggu Review</option>
            <option value="Draft">Draft</option>
          </select>
        </div>
      </div>

      <!-- Tabel Data Artikel -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Judul Artikel</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Kategori</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Penulis</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="post in filteredPosts" 
              :key="post.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-4">
                <p class="text-sm font-semibold text-gray-800 dark:text-white/90 line-clamp-1 max-w-[250px]" :title="post.judul">
                  {{ post.judul }}
                </p>
                <div class="mt-1 flex gap-1">
                  <span v-for="tag in post.tags" :key="tag" class="text-[10px] text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">
                    #{{ tag }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                {{ post.kategori }}
              </td>
              <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                {{ post.penulis }}
              </td>
              <td class="px-4 py-4">
                <span 
                  class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="{
                    'bg-success-100 text-success-800 dark:bg-success-500/20 dark:text-success-400': post.status === 'Published',
                    'bg-warning-100 text-warning-800 dark:bg-warning-500/20 dark:text-warning-400': post.status === 'Review',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': post.status === 'Draft'
                  }"
                >
                  {{ post.status }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                {{ post.tanggal }}
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredPosts.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Artikel tidak ditemukan.
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
const currentPageTitle = ref('Berita & Artikel')

// State Pencarian dan Filter
const searchQuery = ref('')
const filterStatus = ref('')

// Simulasi Data Post/Artikel
const posts = ref([
  {
    id: 1,
    judul: 'Recap: National Hackathon 2026',
    kategori: 'Liputan Kegiatan',
    tags: ['Prestasi', 'Lomba'],
    penulis: 'Nadia Prameswari',
    status: 'Published',
    tanggal: '03 Ags 2026'
  },
  {
    id: 2,
    judul: 'Panduan Instalasi Laravel 11 untuk Pemula',
    kategori: 'Tutorial Edukasi',
    tags: ['Akademik', 'WebDev'],
    penulis: 'Yoga Ardiansyah',
    status: 'Published',
    tanggal: '01 Ags 2026'
  },
  {
    id: 3,
    judul: 'Open Recruitment Staff Muda HMIF 2026',
    kategori: 'Pengumuman',
    tags: ['Oprec', 'Internal'],
    penulis: 'Bagas Wicaksono',
    status: 'Review',
    tanggal: '-'
  },
  {
    id: 4,
    judul: 'Draft: Laporan Pertanggungjawaban Seminar IT',
    kategori: 'Dokumentasi',
    tags: ['LPJ'],
    penulis: 'Dimas Prasetyo',
    status: 'Draft',
    tanggal: '-'
  }
])

// Logika Pencarian dan Filter Reaktif
const filteredPosts = computed(() => {
  return posts.value.filter(post => {
    const matchSearch = post.judul.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                        post.penulis.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchStatus = filterStatus.value === '' || post.status === filterStatus.value

    return matchSearch && matchStatus
  })
})
</script>