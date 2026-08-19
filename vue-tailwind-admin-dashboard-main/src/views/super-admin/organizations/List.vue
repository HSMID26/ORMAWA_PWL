<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    
    <div class="p-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
          Data Organisasi
        </h2>
        <button @click="openModal()" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
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
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Organisasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Periode Aktif</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="org in filteredOrganizations" 
              :key="org.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-gray-100 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ org.nama.charAt(0).toUpperCase() }}
                  </div>
                  <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ org.nama }}</span>
                      <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ org.jenis }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ org.subdomain }}</span>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">
                <span v-if="org.current_period" class="font-medium">
                  {{ org.current_period.period_name }}<br/>
                  <span class="text-xs text-gray-500 font-normal">{{ formatDateOnly(org.current_period.start_date) }} - {{ formatDateOnly(org.current_period.end_date) }}</span>
                </span>
                <span v-else class="inline-flex rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-600 ring-1 ring-inset ring-orange-500/10 dark:bg-orange-400/10 dark:text-orange-400 dark:ring-orange-400/20">
                  Belum dikonfigurasi
                </span>
              </td>
              <td class="px-4 py-3">
                <span v-if="org.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                  Aktif
                </span>
                <span v-else class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                  Tidak Aktif
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center gap-3">
                  <router-link :to="`/super-admin/organizations/${org.id}/periods`" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400" title="Manajemen Periode">Periode</router-link>
                  <button @click="openModal(org)" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Edit</button>
                  <button v-if="org.status === 'inactive'" @click="activateOrganization(org.id)" class="text-sm font-medium text-green-600 hover:text-green-700 dark:text-green-400">Activate</button>
                  <button v-if="org.status === 'active'" @click="deactivateOrganization(org.id)" class="text-sm font-medium text-orange-500 hover:text-orange-600 dark:text-orange-400">Deactivate</button>
                  <button @click="deleteOrganization(org.id)" class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400" :disabled="deletingId === org.id">
                    {{ deletingId === org.id ? '...' : 'Hapus' }}
                  </button>
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

    <!-- Modal Form Organisasi -->
    <Modal v-if="isModalOpen" @close="closeModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            {{ isEditMode ? 'Edit Organisasi' : 'Tambah Organisasi' }}
          </h3>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Organisasi</label>
              <input v-model="form.nama" required type="text" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis</label>
              <select v-model="form.jenis" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:bg-gray-900">
                <option value="HMPS">HMPS</option>
                <option value="UKM">UKM</option>
                <option value="BEM">BEM</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subdomain</label>
              <input v-model="form.subdomain" required type="text" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="closeModal" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" :disabled="isSubmitting">
                Batal
              </button>
              <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50" :disabled="isSubmitting">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import Modal from '@/components/ui/Modal.vue'
import { organizationService } from '@/services/organizationService'
import { useToastStore } from '@/stores/toast'
import type { Organization } from '@/types/api'

const toastStore = useToastStore()
const currentPageTitle = ref('Organisasi')
const searchQuery = ref('')
const organizations = ref<Organization[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

// Form State
const isModalOpen = ref(false)
const isEditMode = ref(false)
const isSubmitting = ref(false)
const deletingId = ref<number | null>(null)

const form = ref<Partial<Organization>>({
  nama: '',
  jenis: 'HMPS',
  subdomain: ''
})
let editId: number | null = null

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

const openModal = (org?: Organization) => {
  if (org) {
    isEditMode.value = true
    editId = org.id
    form.value = { 
      nama: org.nama, 
      jenis: org.jenis, 
      subdomain: org.subdomain 
    }
  } else {
    isEditMode.value = false
    editId = null
    form.value = { nama: '', jenis: 'HMPS', subdomain: '' }
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  try {
    if (isEditMode.value && editId !== null) {
      await organizationService.update(editId, form.value)
      toastStore.success('Data berhasil diperbarui.')
    } else {
      await organizationService.create(form.value)
      toastStore.success('Organisasi berhasil ditambahkan.')
    }
    closeModal()
    await loadOrganizations()
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Data gagal disimpan.')
  } finally {
    isSubmitting.value = false
  }
}

const deleteOrganization = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus organisasi ini?')) {
    deletingId.value = id
    try {
      await organizationService.remove(id)
      toastStore.success('Data berhasil dihapus.')
      await loadOrganizations()
    } catch (error: any) {
      toastStore.error(error.response?.data?.message || 'Data gagal dihapus.')
    } finally {
      deletingId.value = null
    }
  }
}

const activateOrganization = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin mengaktifkan organisasi ini?')) {
    try {
      await organizationService.activate(id)
      toastStore.success('Organisasi berhasil diaktifkan.')
      await loadOrganizations()
    } catch (error: any) {
      toastStore.error(error.response?.data?.message || 'Gagal mengaktifkan organisasi.')
    }
  }
}

const deactivateOrganization = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menonaktifkan organisasi ini?')) {
    try {
      await organizationService.deactivate(id)
      toastStore.success('Organisasi berhasil dinonaktifkan.')
      await loadOrganizations()
    } catch (error: any) {
      toastStore.error(error.response?.data?.message || 'Gagal menonaktifkan organisasi.')
    }
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

const formatDateOnly = (dateString?: string | null) => {
  if (!dateString) return '-'
  
  const datePart = dateString.substring(0, 10)
  const date = new Date(datePart)
  
  if (isNaN(date.getTime())) return '-'
  
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  }).format(date)
}
</script>

