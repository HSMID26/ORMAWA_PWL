<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    
    <div class="p-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
          Data Organisasi
        </h2>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
          + Tambah Organisasi
        </button>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search organisasi..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <!-- Tabel Data -->
      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Memuat organisasi...
      </div>
      <div v-else-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
        {{ errorMessage }}
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Logo</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Jenis</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Subdomain</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="org in filteredOrganizations" 
              :key="org.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-gray-100 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                  {{ org.nama.charAt(0) }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ org.nama }}</td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ org.jenis }}</td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ org.subdomain }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                  Aktif
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredOrganizations.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data tidak ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { organizationService } from '@/services/organizationService'
import type { Organization } from '@/types/api'

const currentPageTitle = ref('Organisasi')
const searchQuery = ref('')
const organizations = ref<Organization[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  await loadOrganizations()
})

const loadOrganizations = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    organizations.value = await organizationService.list()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Gagal memuat organisasi.'
  } finally {
    isLoading.value = false
  }
}

const filteredOrganizations = computed(() => {
  if (!searchQuery.value) return organizations.value

  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return organizations.value.filter((org) =>
    org.nama.toLowerCase().includes(lowerCaseQuery) ||
    org.jenis.toLowerCase().includes(lowerCaseQuery) ||
    org.subdomain.toLowerCase().includes(lowerCaseQuery),
  )
})
</script>

