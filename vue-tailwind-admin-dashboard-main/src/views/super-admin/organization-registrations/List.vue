<template>
  <AdminLayout>
    <div class="space-y-6">
      <PageBreadcrumb pageTitle="Pendaftaran Organisasi" />
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pendaftaran Organisasi</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Kelola persetujuan pengajuan organisasi baru.
        </p>
      </div>
    </div>

    <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-800">
      <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 overflow-x-auto">
          <button 
            @click="activeTab = 'pending'"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors',
              activeTab === 'pending' 
                ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-500' 
                : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'
            ]"
          >
            Pending
          </button>
          <button 
            @click="activeTab = 'all'"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors',
              activeTab === 'all' 
                ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white' 
                : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'
            ]"
          >
            Semua
          </button>
          <button 
            @click="activeTab = 'approved'"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors',
              activeTab === 'approved' 
                ? 'bg-success-50 text-success-700 dark:bg-success-900/20 dark:text-success-500' 
                : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'
            ]"
          >
            Approved
          </button>
          <button 
            @click="activeTab = 'rejected'"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors',
              activeTab === 'rejected' 
                ? 'bg-error-50 text-error-700 dark:bg-error-900/20 dark:text-error-500' 
                : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'
            ]"
          >
            Rejected
          </button>
        </div>

        <div class="relative w-full sm:w-64">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari organisasi/email..." 
            class="w-full h-10 pl-10 pr-4 text-sm bg-transparent border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:border-gray-700 dark:text-white dark:focus:border-brand-500"
          >
          <svg class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="text-xs font-medium text-gray-500 uppercase border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
              <th class="px-4 py-3">Organisasi</th>
              <th class="px-4 py-3">Jenis</th>
              <th class="px-4 py-3">Subdomain</th>
              <th class="px-4 py-3">Calon Admin</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Tanggal</th>
              <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            <tr v-if="isLoading" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                Memuat data...
              </td>
            </tr>
            <tr v-else-if="filteredRegistrations.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                Tidak ada pendaftaran ditemukan.
              </td>
            </tr>
            <tr v-else v-for="item in filteredRegistrations" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                {{ item.organization_name }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                  {{ item.organization_type }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ item.organization_subdomain }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ item.admin_first_name }} {{ item.admin_last_name }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ item.admin_email }}
              </td>
              <td class="px-4 py-3">
                <span 
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    item.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500' :
                    item.status === 'approved' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-500' :
                    'bg-error-100 text-error-800 dark:bg-error-900/30 dark:text-error-500'
                  ]"
                >
                  {{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="px-4 py-3 text-right">
                <button 
                  @click="openDetailModal(item)"
                  class="text-brand-500 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 font-medium text-sm"
                >
                  Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedRegistration" class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
      <div class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm" @click="closeModal"></div>
      
      <div class="relative w-full max-w-2xl mx-auto my-6 z-50 p-4">
        <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-lg outline-none dark:bg-gray-900 focus:outline-none">
          <!-- Header -->
          <div class="flex items-start justify-between p-5 border-b border-gray-200 border-solid rounded-t-xl dark:border-gray-800">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
              Detail Pendaftaran Organisasi
            </h3>
            <button @click="closeModal" class="p-1 ml-auto text-gray-400 bg-transparent border-0 float-right text-3xl leading-none font-semibold outline-none focus:outline-none hover:text-gray-900 dark:hover:text-white">
              <span class="text-2xl block outline-none focus:outline-none">×</span>
            </button>
          </div>
          
          <!-- Body -->
          <div class="relative p-6 flex-auto max-h-[70vh] overflow-y-auto">
            <div class="space-y-6">
              <!-- STATUS -->
              <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status Pendaftaran:</span>
                <span 
                  :class="[
                    'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                    selectedRegistration.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500' :
                    selectedRegistration.status === 'approved' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-500' :
                    'bg-error-100 text-error-800 dark:bg-error-900/30 dark:text-error-500'
                  ]"
                >
                  {{ selectedRegistration.status.charAt(0).toUpperCase() + selectedRegistration.status.slice(1) }}
                </span>
              </div>
              
              <div v-if="selectedRegistration.status === 'rejected'" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg border border-error-100 dark:border-error-800/30">
                <h4 class="text-sm font-medium text-error-800 dark:text-error-400 mb-1">Alasan Penolakan:</h4>
                <p class="text-sm text-error-700 dark:text-error-300">{{ selectedRegistration.rejection_reason }}</p>
                <p class="text-xs text-error-500 dark:text-error-500/70 mt-2">Ditolak oleh: {{ selectedRegistration.reviewer?.name || 'Sistem' }} pada {{ formatDate(selectedRegistration.reviewed_at || '') }}</p>
              </div>
              
              <div v-if="selectedRegistration.status === 'approved'" class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-100 dark:border-success-800/30">
                <p class="text-sm text-success-700 dark:text-success-300">Pendaftaran disetujui, organisasi dan admin telah aktif.</p>
                <p class="text-xs text-success-500 dark:text-success-500/70 mt-2">Disetujui oleh: {{ selectedRegistration.reviewer?.name || 'Sistem' }} pada {{ formatDate(selectedRegistration.reviewed_at || '') }}</p>
              </div>

              <!-- DATA ORGANISASI -->
              <div>
                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-gray-800 pb-2">Data Organisasi</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nama Organisasi</span>
                    <span class="block text-sm text-gray-900 dark:text-white">{{ selectedRegistration.organization_name }}</span>
                  </div>
                  <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Jenis</span>
                    <span class="block text-sm text-gray-900 dark:text-white">{{ selectedRegistration.organization_type }}</span>
                  </div>
                  <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Subdomain</span>
                    <span class="block text-sm text-gray-900 dark:text-white">{{ selectedRegistration.organization_subdomain }}</span>
                  </div>
                  <div class="sm:col-span-2">
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Deskripsi</span>
                    <span class="block text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ selectedRegistration.organization_description || '-' }}</span>
                  </div>
                </div>
              </div>

              <!-- DATA ADMIN -->
              <div>
                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-gray-800 pb-2">Data Calon Admin</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nama</span>
                    <span class="block text-sm text-gray-900 dark:text-white">{{ selectedRegistration.admin_first_name }} {{ selectedRegistration.admin_last_name }}</span>
                  </div>
                  <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Email</span>
                    <span class="block text-sm text-gray-900 dark:text-white">{{ selectedRegistration.admin_email }}</span>
                  </div>
                </div>
              </div>

            </div>
          </div>
          
          <!-- Footer -->
          <div class="flex items-center justify-end p-5 border-t border-gray-200 border-solid rounded-b-xl dark:border-gray-800">
            <button 
              @click="closeModal" 
              class="px-6 py-2 mb-1 mr-4 text-sm font-bold text-gray-500 uppercase transition-all duration-150 ease-linear outline-none background-transparent hover:text-gray-800 dark:hover:text-gray-300 focus:outline-none" 
              type="button"
            >
              Tutup
            </button>
            <template v-if="selectedRegistration.status === 'pending'">
              <button 
                @click="openRejectModal" 
                class="px-6 py-2 mb-1 mr-3 text-sm font-bold text-error-600 uppercase transition-all duration-150 ease-linear border border-error-500 rounded-lg outline-none hover:bg-error-50 dark:hover:bg-error-900/20 focus:outline-none" 
                type="button"
              >
                Tolak
              </button>
              <button 
                @click="openApproveModal" 
                class="px-6 py-2 mb-1 text-sm font-bold text-white uppercase transition-all duration-150 ease-linear rounded-lg shadow outline-none bg-brand-500 hover:bg-brand-600 hover:shadow-lg focus:outline-none" 
                type="button"
              >
                Setujui
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 z-[60] flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
      <div class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm" @click="showApproveModal = false"></div>
      
      <div class="relative w-full max-w-md mx-auto my-6 z-[60] p-4">
        <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-lg outline-none dark:bg-gray-900 focus:outline-none p-6">
          <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-100 mb-4 dark:bg-brand-900/30">
              <svg class="h-6 w-6 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Setujui Pendaftaran?</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Organisasi <b>{{ selectedRegistration?.organization_name }}</b> dan akun Admin Organisasi akan dibuat dan aktif setelah persetujuan.
              </p>
            </div>
          </div>
          <div class="flex justify-center gap-3">
            <button 
              @click="showApproveModal = false"
              :disabled="isProcessing"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 disabled:opacity-50"
            >
              Batal
            </button>
            <button 
              @click="handleApprove"
              :disabled="isProcessing"
              class="px-4 py-2 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg hover:bg-brand-600 flex items-center disabled:opacity-50"
            >
              <svg v-if="isProcessing" class="w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isProcessing ? 'Memproses...' : 'Setujui' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 z-[60] flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
      <div class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm" @click="showRejectModal = false"></div>
      
      <div class="relative w-full max-w-md mx-auto my-6 z-[60] p-4">
        <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-lg outline-none dark:bg-gray-900 focus:outline-none p-6">
          <div class="mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Tolak Pendaftaran</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Organisasi <b>{{ selectedRegistration?.organization_name }}</b>
            </p>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alasan Penolakan <span class="text-error-500">*</span></label>
            <textarea 
              v-model="rejectionReason"
              rows="3" 
              class="w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800"
              placeholder="Masukkan alasan penolakan untuk diinformasikan..."
            ></textarea>
            <p v-if="rejectError" class="mt-1 text-xs text-error-500">{{ rejectError }}</p>
          </div>
          <div class="flex justify-end gap-3">
            <button 
              @click="showRejectModal = false"
              :disabled="isProcessing"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 disabled:opacity-50"
            >
              Batal
            </button>
            <button 
              @click="handleReject"
              :disabled="isProcessing"
              class="px-4 py-2 text-sm font-medium text-white bg-error-600 border border-transparent rounded-lg hover:bg-error-700 flex items-center disabled:opacity-50"
            >
              <svg v-if="isProcessing" class="w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isProcessing ? 'Menolak...' : 'Tolak Pendaftaran' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { organizationRegistrationService, type OrganizationRegistration } from '@/services/organizationRegistrationService'
import { useToastStore } from '@/stores/toast'

const toastStore = useToastStore()
const registrations = ref<OrganizationRegistration[]>([])
const isLoading = ref(true)

const activeTab = ref('pending') // all, pending, approved, rejected
const searchQuery = ref('')

const selectedRegistration = ref<OrganizationRegistration | null>(null)
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const rejectionReason = ref('')
const rejectError = ref('')
const isProcessing = ref(false)

const loadData = async () => {
  isLoading.value = true
  try {
    const params: any = {}
    if (activeTab.value !== 'all') {
      params.status = activeTab.value
    }
    const response = await organizationRegistrationService.list(params)
    // Assuming backend returns paginated data in data property
    registrations.value = response.data || []
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal memuat data pendaftaran.')
  } finally {
    isLoading.value = false
  }
}

// Reload when tab changes
import { watch } from 'vue'
watch(activeTab, () => {
  loadData()
})

onMounted(() => {
  loadData()
})

const filteredRegistrations = computed(() => {
  if (!searchQuery.value) return registrations.value
  
  const query = searchQuery.value.toLowerCase()
  return registrations.value.filter(reg => 
    reg.organization_name.toLowerCase().includes(query) ||
    reg.admin_email.toLowerCase().includes(query) ||
    reg.organization_subdomain.toLowerCase().includes(query)
  )
})

const openDetailModal = (item: OrganizationRegistration) => {
  selectedRegistration.value = item
}

const closeModal = () => {
  selectedRegistration.value = null
}

const openApproveModal = () => {
  showApproveModal.value = true
}

const openRejectModal = () => {
  rejectionReason.value = ''
  rejectError.value = ''
  showRejectModal.value = true
}

const handleApprove = async () => {
  if (!selectedRegistration.value) return
  
  isProcessing.value = true
  try {
    await organizationRegistrationService.approve(selectedRegistration.value.id)
    toastStore.success('Pendaftaran organisasi berhasil disetujui.')
    showApproveModal.value = false
    closeModal()
    loadData()
  } catch (error: any) {
    if (error.response?.data?.message) {
      toastStore.error(error.response.data.message)
    } else {
      toastStore.error(error.message || 'Gagal menyetujui pendaftaran.')
    }
  } finally {
    isProcessing.value = false
  }
}

const handleReject = async () => {
  if (!selectedRegistration.value) return
  
  if (!rejectionReason.value.trim()) {
    rejectError.value = 'Alasan penolakan wajib diisi.'
    return
  }
  
  isProcessing.value = true
  try {
    await organizationRegistrationService.reject(selectedRegistration.value.id, rejectionReason.value)
    toastStore.success('Pendaftaran berhasil ditolak.')
    showRejectModal.value = false
    closeModal()
    loadData()
  } catch (error: any) {
    if (error.response?.data?.message) {
      toastStore.error(error.response.data.message)
    } else {
      toastStore.error(error.message || 'Gagal menolak pendaftaran.')
    }
  } finally {
    isProcessing.value = false
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}
</script>
