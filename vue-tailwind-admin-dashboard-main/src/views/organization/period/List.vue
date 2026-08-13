<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Manajemen Kepengurusan" />

    <div class="p-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500">Memuat data periode...</div>
      
      <div v-else>
        <!-- Warning Banner -->
        <div v-if="daysRemaining !== null && daysRemaining <= 30 && daysRemaining >= 0 && currentPeriod?.status === 'active'" 
             class="mb-6 rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-900/50 dark:bg-warning-900/20">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 text-warning-600 dark:text-warning-400">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-warning-800 dark:text-warning-400">Peringatan Periode Kepengurusan</h3>
              <p class="mt-1 text-sm text-warning-700 dark:text-warning-500">
                Periode organisasi Anda akan berakhir dalam <span class="font-bold">{{ daysRemaining }} hari</span>. Segera ajukan perpanjangan agar operasional organisasi tidak terganggu.
              </p>
            </div>
          </div>
        </div>
        
        <div v-else-if="currentPeriod?.status === 'expired'"
             class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-900/20">
           <div class="flex items-start gap-3">
            <div class="mt-0.5 text-red-600 dark:text-red-400">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-red-800 dark:text-red-400">Periode Telah Berakhir</h3>
              <p class="mt-1 text-sm text-red-700 dark:text-red-500">
                Periode organisasi Anda telah berakhir. Anda tidak dapat melakukan beberapa aktivitas operasional. Segera ajukan perpanjangan.
              </p>
            </div>
          </div>
        </div>
        
        <!-- Header & Action -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b pb-4 dark:border-gray-800">
          <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Periode Aktif</h2>
            <p class="text-sm text-gray-500" v-if="currentPeriod">
              Berlaku: {{ formatDateOnly(currentPeriod.start_date) }} s/d {{ formatDateOnly(currentPeriod.end_date) }}
            </p>
          </div>
          <button v-if="!hasPendingRenewal" @click="openModal" class="flex justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
            Ajukan Perpanjangan
          </button>
          <span v-else class="inline-flex rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm font-medium text-yellow-800 dark:border-yellow-900/50 dark:bg-yellow-900/20 dark:text-yellow-500">
            Perpanjangan Menunggu Persetujuan
          </span>
        </div>
        
        <!-- History -->
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 mt-8">Riwayat Kepengurusan</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama Periode</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Start Date</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">End Date</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="period in periods" :key="period.id" class="border-b border-gray-100 dark:border-gray-800">
                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">
                  {{ period.period_name }}
                  <span v-if="period.status === 'rejected'" class="block mt-1 text-xs text-red-500 font-normal">Ditolak: {{ period.rejection_reason }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatDateOnly(period.start_date) }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatDateOnly(period.end_date) }}</td>
                <td class="px-4 py-3">
                  <span v-if="period.status === 'active'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">Aktif</span>
                  <span v-else-if="period.status === 'pending'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400">Pending</span>
                  <span v-else-if="period.status === 'rejected'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">Ditolak</span>
                  <span v-else class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-400">Expired</span>
                </td>
              </tr>
              <tr v-if="periods.length === 0">
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat.</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- Modal Form Renewal -->
    <Modal v-if="isModalOpen" @close="closeModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Pengajuan Perpanjangan Periode
          </h3>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Periode Baru</label>
              <input v-model="form.period_name" required type="text" placeholder="Misal: Kepengurusan 2027" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mulai Berlaku</label>
                <input v-model="form.start_date" required type="date" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Berakhir Pada</label>
                <input v-model="form.end_date" required type="date" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan (Opsional)</label>
              <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"></textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="closeModal" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" :disabled="isSubmitting">
                Batal
              </button>
              <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50" :disabled="isSubmitting">
                {{ isSubmitting ? 'Mengirim...' : 'Kirim Pengajuan' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>

  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import Modal from '@/components/ui/Modal.vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import type { OrganizationPeriod } from '@/types/api'

const authStore = useAuthStore()
const toastStore = useToastStore()

const periods = ref<OrganizationPeriod[]>([])
const currentPeriod = ref<OrganizationPeriod | null>(null)
const isLoading = ref(true)

const isModalOpen = ref(false)
const isSubmitting = ref(false)

const form = ref<Partial<OrganizationPeriod>>({
  period_name: '',
  start_date: '',
  end_date: '',
  notes: ''
})

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  const orgId = (authStore.user as any)?.organization_id || authStore.user?.organization?.id
  if (!orgId) return
  isLoading.value = true
  try {
    const res = await api.get(`/organizations/${orgId}/periods`)
    periods.value = res.data.data
    currentPeriod.value = res.data.current_period
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Gagal memuat periode.')
  } finally {
    isLoading.value = false
  }
}

const hasPendingRenewal = computed(() => {
    return periods.value.some(p => p.status === 'pending')
})

const daysRemaining = computed(() => {
    if (!currentPeriod.value) return null
    const end = new Date(currentPeriod.value.end_date)
    const today = new Date()
    // Reset time for accurate day calc
    end.setHours(0,0,0,0)
    today.setHours(0,0,0,0)
    
    const diffTime = end.getTime() - today.getTime()
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
})

const openModal = () => {
  form.value = { period_name: '', start_date: '', end_date: '', notes: '' }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const submitForm = async () => {
  const orgId = (authStore.user as any)?.organization_id || authStore.user?.organization?.id
  if (isSubmitting.value || !orgId) return
  isSubmitting.value = true
  
  try {
    await api.post(`/organizations/${orgId}/periods`, form.value)
    toastStore.success('Pengajuan perpanjangan berhasil dikirim.')
    closeModal()
    await loadData()
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Pengajuan gagal dikirim.')
  } finally {
    isSubmitting.value = false
  }
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
