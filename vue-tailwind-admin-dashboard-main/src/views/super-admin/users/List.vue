<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div
      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6"
    >
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
          Data Pengguna
        </h3>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
          + Tambah User
        </button>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, atau role..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <!-- Tabel Data -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Email</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Role</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Organisasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="user in filteredUsers" 
              :key="user.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ user.nama }}</td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ user.organisasi }}</td>
              <td class="px-4 py-3">
                <span 
                  class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="user.status === 'Aktif' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400'"
                >
                  {{ user.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data pengguna tidak ditemukan.
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
const currentPageTitle = ref('Manajemen Pengguna')

// State Pencarian
const searchQuery = ref('')

// Simulasi Data User (Nantinya diintegrasikan dengan API Laravel, menggunakan relasi tabel User dan Organisasi)
const users = ref([
  {
    id: 1,
    nama: 'Nadia Prameswari',
    email: 'nadia@hmif.iti.ac.id',
    role: 'Admin Organisasi',
    organisasi: 'HMPS Teknik Informatika',
    status: 'Aktif'
  },
  {
    id: 2,
    nama: 'Bagas Wicaksono',
    email: 'bagas@basket.iti.ac.id',
    role: 'Editor',
    organisasi: 'UKM Basket',
    status: 'Aktif'
  },
  {
    id: 3,
    nama: 'Yoga Ardiansyah',
    email: 'yoga@hmif.iti.ac.id',
    role: 'Kontributor',
    organisasi: 'HMPS Teknik Informatika',
    status: 'Nonaktif'
  }
])

// Logika Pencarian Reaktif
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  
  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return users.value.filter(user => 
    user.nama.toLowerCase().includes(lowerCaseQuery) ||
    user.email.toLowerCase().includes(lowerCaseQuery) ||
    user.role.toLowerCase().includes(lowerCaseQuery) ||
    user.organisasi.toLowerCase().includes(lowerCaseQuery)
  )
})
</script>

