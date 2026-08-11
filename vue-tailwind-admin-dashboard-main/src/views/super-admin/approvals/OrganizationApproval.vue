<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div
      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6"
    >
      <!-- Header -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
          Daftar Persetujuan Konten
        </h3>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari judul konten atau organisasi..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <!-- Tabel Data Approval -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Judul Konten</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Organisasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tipe</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="item in filteredApprovals" 
              :key="item.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                {{ item.judul }}
                <div class="text-xs font-normal text-gray-500 mt-0.5">Oleh: {{ item.penulis }}</div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                <span class="font-semibold">{{ item.organisasi }}</span>
              </td>
              <td class="px-4 py-4">
                <span class="inline-flex rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                  {{ item.tipe }}
                </span>
              </td>
              <td class="px-4 py-4">
                <span class="inline-flex rounded-full bg-warning-100 px-2.5 py-0.5 text-xs font-medium text-warning-800 dark:bg-warning-500/20 dark:text-warning-400">
                  {{ item.status }}
                </span>
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex justify-center items-center gap-2">
                  <!-- Tombol Detail -->
                  <button class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Detail
                  </button>
                  <!-- Tombol Approve -->
                  <button class="rounded-lg bg-success-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-success-600 transition-colors">
                    Approve
                  </button>
                  <!-- Tombol Reject -->
                  <button class="rounded-lg bg-error-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-error-600 transition-colors">
                    Reject
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredApprovals.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Tidak ada antrean konten yang membutuhkan persetujuan saat ini.
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
const currentPageTitle = ref('Approval Konten')

// State Pencarian
const searchQuery = ref('')

// Simulasi Data Approval (Draft konten yang diajukan oleh Kontributor/Editor)
const approvals = ref([
  {
    id: 1,
    judul: 'Open Recruitment Staff Muda 2026',
    penulis: 'Bagas Wicaksono',
    organisasi: 'HMIF',
    tipe: 'Pengumuman',
    status: 'Menunggu Approval'
  },
  {
    id: 2,
    judul: 'Jadwal Latihan Persiapan Turnamen Nasional',
    penulis: 'Kevin Sanjaya',
    organisasi: 'UKM Basket',
    tipe: 'Agenda',
    status: 'Menunggu Approval'
  },
  {
    id: 3,
    judul: 'Notulensi Rapat Kerja Triwulan I',
    penulis: 'Salsabila Rania',
    organisasi: 'HMIF',
    tipe: 'Dokumen',
    status: 'Menunggu Approval'
  }
])

// Logika Pencarian Reaktif
const filteredApprovals = computed(() => {
  if (!searchQuery.value) return approvals.value
  
  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return approvals.value.filter(item => 
    item.judul.toLowerCase().includes(lowerCaseQuery) ||
    item.organisasi.toLowerCase().includes(lowerCaseQuery) ||
    item.penulis.toLowerCase().includes(lowerCaseQuery)
  )
})
</script>

