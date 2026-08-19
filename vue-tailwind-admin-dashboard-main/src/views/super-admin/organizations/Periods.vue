<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between">
      <div class="flex flex-col gap-1">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
          Manajemen Periode
        </h2>
        <nav>
          <ol class="flex items-center gap-1.5">
            <li>
              <router-link class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400" to="/dashboard">
                Home
              </router-link>
            </li>
            <li>
              <span class="text-gray-400">/</span>
            </li>
            <li>
              <router-link class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400" to="/super-admin/organizations">
                Organisasi
              </router-link>
            </li>
            <li v-if="organization">
              <span class="text-gray-400">/</span>
            </li>
            <li v-if="organization" class="text-sm text-gray-800 dark:text-white/90 font-medium">
              {{ organization.nama }}
            </li>
            <li>
              <span class="text-gray-400">/</span>
            </li>
            <li class="text-sm text-gray-800 dark:text-white/90 font-medium">
              Manajemen Periode
            </li>
          </ol>
        </nav>
      </div>
    </div>
    
    <div v-if="isLoading && !organization" class="py-12 flex flex-col items-center justify-center space-y-4">
       <div class="h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-brand-500"></div>
       <p class="text-sm text-gray-500">Memuat data organisasi...</p>
    </div>

    <div v-else-if="errorMessage && !organization" class="py-12 text-center flex flex-col items-center justify-center">
       <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Organisasi tidak ditemukan.</h3>
       <p class="text-sm text-gray-500 mb-6">{{ errorMessage }}</p>
       <router-link to="/super-admin/organizations" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
         Kembali ke Daftar Organisasi
       </router-link>
    </div>

    <div v-else class="space-y-6">
      
      <!-- Header Info Organisasi -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ organization?.nama }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
           <div>
              <p class="text-sm text-gray-500 mb-1">Status Organisasi</p>
              <div>
                <span v-if="organization?.status === 'active'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                  Aktif
                </span>
                <span v-else class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                  Tidak Aktif
                </span>
              </div>
           </div>
           <div>
              <p class="text-sm text-gray-500 mb-1">Periode Aktif</p>
              <p class="font-medium text-gray-900 dark:text-white">
                 <template v-if="currentPeriod">{{ currentPeriod.period_name }}</template>
                 <template v-else><span class="text-gray-400">Belum Ada</span></template>
              </p>
           </div>
        </div>
      </div>

      <!-- Tabel Data -->
      <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Daftar Periode
          </h3>
          <button v-if="!currentPeriod" @click="openModal()" class="flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Set Initial Period
          </button>
        </div>

        <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
          Memuat periode...
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama Periode</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Mulai</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Berakhir</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="period in periods" 
                :key="period.id"
                class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
              >
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90 font-medium">{{ period.period_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ formatDateOnly(period.start_date) }}</td>
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ formatDateOnly(period.end_date) }}</td>
                <td class="px-4 py-3">
                  <span v-if="period.status === 'active'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                    Aktif
                  </span>
                  <span v-else-if="period.status === 'pending'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400">
                    Pending
                  </span>
                  <span v-else-if="period.status === 'rejected'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                    Ditolak
                  </span>
                  <span v-else class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-400">
                    Expired
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex justify-center items-center gap-3">
                    <button v-if="period.status === 'pending'" @click="approvePeriod(period.id)" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Approve</button>
                    <button v-if="period.status === 'pending'" @click="openRejectModal(period)" class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400">Reject</button>
                    <button @click="openNotes(period)" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">Detail</button>
                  </div>
                </td>
              </tr>
              <tr v-if="periods.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                  Belum ada data periode.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form Initial Period -->
    <Modal v-if="isModalOpen" @close="closeModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Set Initial Period
          </h3>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Periode</label>
              <input v-model="form.period_name" required type="text" placeholder="Misal: Kepengurusan 2026" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                <input v-model="form.start_date" required type="date" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                <input v-model="form.end_date" required type="date" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
              <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"></textarea>
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
    
    <!-- Modal Reject -->
    <Modal v-if="isRejectModalOpen" @close="closeRejectModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Tolak Pengajuan
          </h3>
          
          <form @submit.prevent="submitReject" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan Penolakan</label>
              <textarea v-model="rejectReason" required rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-error-500 focus:outline-none dark:border-gray-700 dark:text-white"></textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="closeRejectModal" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" :disabled="isSubmitting">
                Batal
              </button>
              <button type="submit" class="rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600 disabled:opacity-50" :disabled="isSubmitting">
                {{ isSubmitting ? 'Menyimpan...' : 'Tolak' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
    
    <!-- Modal Notes Detail -->
    <Modal v-if="isNotesModalOpen" @close="closeNotesModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Detail Periode
          </h3>
          <div class="space-y-4">
             <div>
                <p class="text-sm text-gray-500">Nama Organisasi</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ organization?.nama || '-' }}</p>
             </div>
             <div>
                <p class="text-sm text-gray-500">Nama Periode</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedPeriod?.period_name || '-' }}</p>
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div>
                  <p class="text-sm text-gray-500">Tanggal Mulai</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateOnly(selectedPeriod?.start_date) }}</p>
               </div>
               <div>
                  <p class="text-sm text-gray-500">Tanggal Berakhir</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateOnly(selectedPeriod?.end_date) }}</p>
               </div>
             </div>
             <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="text-sm font-medium mt-1">
                  <span v-if="selectedPeriod?.status === 'active'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                    Aktif
                  </span>
                  <span v-else-if="selectedPeriod?.status === 'pending'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400">
                    Pending
                  </span>
                  <span v-else-if="selectedPeriod?.status === 'rejected'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                    Ditolak
                  </span>
                  <span v-else class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-400">
                    Expired
                  </span>
                </p>
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div>
                  <p class="text-sm text-gray-500">Disetujui Oleh</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedPeriod?.approved_by || '-' }}</p>
               </div>
               <div>
                  <p class="text-sm text-gray-500">Tanggal Persetujuan</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateOnly(selectedPeriod?.approved_at) }}</p>
               </div>
             </div>
             <div>
                <p class="text-sm text-gray-500">Catatan</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ selectedPeriod?.notes || '-' }}</p>
             </div>
             <div v-if="selectedPeriod?.status === 'rejected'">
                <p class="text-sm text-gray-500">Alasan Penolakan</p>
                <p class="text-sm text-red-600 font-medium">{{ selectedPeriod?.rejection_reason || '-' }}</p>
             </div>
          </div>
          <div class="mt-6 flex justify-end">
              <button type="button" @click="closeNotesModal" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Tutup</button>
          </div>
        </div>
      </template>
    </Modal>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import Modal from '@/components/ui/Modal.vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import type { Organization, OrganizationPeriod } from '@/types/api'

const route = useRoute()
const toastStore = useToastStore()
const organizationId = Number(route.params.id)

const organization = ref<Organization | null>(null)
const periods = ref<OrganizationPeriod[]>([])
const currentPeriod = ref<OrganizationPeriod | null>(null)

const isLoading = ref(false)
const errorMessage = ref('')

const isModalOpen = ref(false)
const isRejectModalOpen = ref(false)
const isNotesModalOpen = ref(false)
const isSubmitting = ref(false)

const form = ref<Partial<OrganizationPeriod>>({
  period_name: '',
  start_date: '',
  end_date: '',
  notes: ''
})

const rejectReason = ref('')
const selectedPeriod = ref<OrganizationPeriod | null>(null)

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const orgRes = await api.get(`/organizations/${organizationId}`)
    organization.value = orgRes.data.data

    const perRes = await api.get(`/organizations/${organizationId}/periods`)
    periods.value = perRes.data.data
    currentPeriod.value = perRes.data.current_period
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat data.'
  } finally {
    isLoading.value = false
  }
}

const openModal = () => {
  form.value = { period_name: '', start_date: '', end_date: '', notes: '' }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  try {
    await api.post(`/organizations/${organizationId}/periods`, form.value)
    toastStore.success('Periode awal berhasil dikonfigurasi.')
    closeModal()
    await loadData()
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Data gagal disimpan.')
  } finally {
    isSubmitting.value = false
  }
}

const approvePeriod = async (id: number) => {
    if(!confirm('Setujui pengajuan perpanjangan periode ini?')) return;
    
    try {
        await api.post(`/organization-periods/${id}/approve`)
        toastStore.success('Periode berhasil disetujui.')
        await loadData()
    } catch(error: any) {
        toastStore.error(error.response?.data?.message || 'Gagal menyetujui.')
    }
}

const openRejectModal = (period: OrganizationPeriod) => {
    selectedPeriod.value = period
    rejectReason.value = ''
    isRejectModalOpen.value = true
}

const closeRejectModal = () => {
    isRejectModalOpen.value = false
}

const submitReject = async () => {
    if(!selectedPeriod.value) return
    isSubmitting.value = true
    try {
        await api.post(`/organization-periods/${selectedPeriod.value.id}/reject`, { reason: rejectReason.value })
        toastStore.success('Pengajuan berhasil ditolak.')
        closeRejectModal()
        await loadData()
    } catch(error: any) {
        toastStore.error(error.response?.data?.message || 'Gagal menolak.')
    } finally {
        isSubmitting.value = false
    }
}

const openNotes = (period: OrganizationPeriod) => {
    selectedPeriod.value = period
    isNotesModalOpen.value = true
}
const closeNotesModal = () => {
    isNotesModalOpen.value = false
}

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
