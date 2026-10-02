<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="pageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Tata Kelola Periode Kepengurusan Ormawa
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">
            Kontrol status aktif/nonaktif periode organisasi. Dalam satu waktu hanya ada 1 periode berstatus Aktif per ormawa.
          </p>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-700">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="currentTab = tab.value"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition cursor-pointer',
            currentTab === tab.value
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
          <span class="ml-1.5 rounded-full bg-black/10 dark:bg-white/20 px-2 py-0.5 text-[10px]">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Filters Row -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama periode atau nama organisasi..."
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
        />
        <select
          v-model="selectedOrgId"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option :value="null">Semua Organisasi</option>
          <option v-for="org in organizations" :key="org.id" :value="org.id">
            {{ org.nama }}
          </option>
        </select>
      </div>

      <!-- Table Content -->
      <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500">
        Memuat data periode...
      </div>

      <div v-else-if="filteredPeriods.length === 0" class="py-12 text-center text-sm text-gray-500">
        Tidak ada data periode yang cocok dengan filter saat ini.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Nama Periode</th>
              <th class="px-4 py-3">Organisasi</th>
              <th class="px-4 py-3">Rentang Tanggal</th>
              <th class="px-4 py-3 text-center">Status Periode</th>
              <th class="px-4 py-3">Keterangan / Catatan</th>
              <th class="px-4 py-3 text-center">Aksi / Kontrol</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
            <tr v-for="period in filteredPeriods" :key="period.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
              <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                {{ period.period_name }}
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                <span class="font-semibold">{{ period.organization?.nama || 'ID: ' + period.organization_id }}</span>
                <span class="block text-[10px] text-gray-400">@{{ period.organization?.subdomain || '-' }}</span>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-600 dark:text-gray-400">
                {{ formatDate(period.start_date) }} &ndash; {{ formatDate(period.end_date) }}
              </td>
              <td class="px-4 py-3.5 text-center">
                <span
                  v-if="period.status === 'active'"
                  class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  Aktif
                </span>
                <span
                  v-else-if="period.status === 'pending'"
                  class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                  Menunggu Persetujuan
                </span>
                <span
                  v-else-if="period.status === 'rejected'"
                  class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                  Ditolak
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                  Nonaktif / Kedaluwarsa
                </span>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-500 max-w-xs truncate">
                {{ period.rejection_reason || period.notes || '-' }}
              </td>
              <td class="px-4 py-3.5 text-center">
                <!-- Pending Actions -->
                <div v-if="period.status === 'pending'" class="flex items-center justify-center gap-2">
                  <button
                    @click="openApproveModal(period)"
                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition shadow-2xs"
                  >
                    Setujui & Aktifkan
                  </button>
                  <button
                    @click="openRejectModal(period)"
                    class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 transition"
                  >
                    Tolak
                  </button>
                </div>

                <!-- Active Toggle Actions -->
                <div v-else-if="period.status === 'active'" class="flex items-center justify-center gap-2">
                  <button
                    @click="openDeactivateModal(period)"
                    class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300 transition"
                  >
                    Nonaktifkan
                  </button>
                </div>

                <!-- Inactive/Expired/Rejected Toggle Actions -->
                <div v-else class="flex items-center justify-center gap-2">
                  <button
                    @click="openActivateModal(period)"
                    class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300 transition"
                  >
                    Jadikan Aktif
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Modal Konfirmasi Approve / Setujui -->
    <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Persetujuan & Aktivasi Periode</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Apakah Anda yakin ingin menyetujui dan mengaktifkan periode <b>{{ selectedPeriod?.period_name }}</b> untuk organisasi <b>{{ selectedPeriod?.organization?.nama }}</b>?
        </p>
        <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 p-2.5 rounded-lg">
          ⚠️ Periode aktif sebelumnya (jika ada) akan otomatis dinonaktifkan agar hanya 1 periode aktif dalam satu waktu.
        </p>
        <div class="flex justify-end gap-3 pt-2">
          <button
            @click="showApproveModal = false"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="confirmApprove"
            :disabled="isSubmitting"
            class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Memproses...' : 'Ya, Setujui & Aktifkan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Jadikan Aktif -->
    <div v-if="showActivateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Aktivasi Periode</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Aktifkan periode <b>{{ selectedPeriod?.period_name }}</b> untuk organisasi <b>{{ selectedPeriod?.organization?.nama }}</b>?
        </p>
        <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 p-2.5 rounded-lg">
          ⚠️ Periode aktif sebelumnya akan otomatis dinonaktifkan. Data kepengurusan dan arsip lama tetap aman tersimpan.
        </p>
        <div class="flex justify-end gap-3 pt-2">
          <button
            @click="showActivateModal = false"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="confirmActivate"
            :disabled="isSubmitting"
            class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Memproses...' : 'Ya, Aktifkan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Nonaktifkan -->
    <div v-if="showDeactivateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nonaktifkan Periode</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Apakah Anda yakin ingin menonaktifkan periode <b>{{ selectedPeriod?.period_name }}</b> untuk organisasi <b>{{ selectedPeriod?.organization?.nama }}</b>?
        </p>
        <div class="flex justify-end gap-3 pt-2">
          <button
            @click="showDeactivateModal = false"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="confirmDeactivate"
            :disabled="isSubmitting"
            class="rounded-xl bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Memproses...' : 'Ya, Nonaktifkan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Rejection with Reason -->
    <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tolak Pengajuan Periode</h3>
        <p class="text-xs text-gray-500">
          Berikan alasan penolakan untuk organisasi <b>{{ selectedPeriod?.organization?.nama }}</b>.
        </p>
        <textarea
          v-model="rejectionReason"
          rows="3"
          required
          placeholder="Contoh: Dokumen LPJ dan SK kepengurusan belum lengkap..."
          class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-rose-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        ></textarea>
        <div class="flex justify-end gap-3 pt-2">
          <button
            @click="showRejectModal = false"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="confirmReject"
            :disabled="isSubmitting || !rejectionReason.trim()"
            class="rounded-xl bg-rose-600 px-5 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menolak...' : 'Tolak Pengajuan' }}
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { organizationPeriodService } from '@/services/organizationPeriodService'
import { organizationService } from '@/services/organizationService'
import { useToastStore } from '@/stores/toast'
import type { OrganizationPeriod, Organization } from '@/types/api'

const pageTitle = ref('Tata Kelola Periode Ormawa')
const isLoading = ref(true)
const isSubmitting = ref(false)
const toast = useToastStore()

const periods = ref<OrganizationPeriod[]>([])
const organizations = ref<Organization[]>([])

const currentTab = ref('all')
const searchQuery = ref('')
const selectedOrgId = ref<number | null>(null)

const showApproveModal = ref(false)
const showActivateModal = ref(false)
const showDeactivateModal = ref(false)
const showRejectModal = ref(false)
const selectedPeriod = ref<OrganizationPeriod | null>(null)
const rejectionReason = ref('')

const statusTabs = computed(() => [
  { label: 'Semua Periode', value: 'all', count: periods.value.length },
  { label: 'Aktif', value: 'active', count: periods.value.filter(p => p.status === 'active').length },
  { label: 'Nonaktif / Kedaluwarsa', value: 'expired', count: periods.value.filter(p => p.status === 'expired').length },
  { label: 'Menunggu Persetujuan', value: 'pending', count: periods.value.filter(p => p.status === 'pending').length },
  { label: 'Ditolak', value: 'rejected', count: periods.value.filter(p => p.status === 'rejected').length },
])

const filteredPeriods = computed(() => {
  return periods.value.filter((p) => {
    const matchTab = currentTab.value === 'all' || p.status === currentTab.value
    const matchOrg = selectedOrgId.value === null || p.organization_id === selectedOrgId.value
    const search = searchQuery.value.toLowerCase().trim()
    const matchSearch = !search ||
      p.period_name.toLowerCase().includes(search) ||
      (p.organization?.nama && p.organization.nama.toLowerCase().includes(search)) ||
      (p.organization?.subdomain && p.organization.subdomain.toLowerCase().includes(search))
    return matchTab && matchOrg && matchSearch
  })
})

const loadData = async () => {
  isLoading.value = true
  try {
    const [periodList, orgList] = await Promise.all([
      organizationPeriodService.getAllPeriods(),
      organizationService.list(),
    ])
    periods.value = periodList || []
    organizations.value = orgList || []
  } catch (err) {
    console.error('Failed to load global periods:', err)
  } finally {
    isLoading.value = false
  }
}

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

const openApproveModal = (period: OrganizationPeriod) => {
  selectedPeriod.value = period
  showApproveModal.value = true
}

const confirmApprove = async () => {
  if (!selectedPeriod.value) return
  isSubmitting.value = true
  try {
    await organizationPeriodService.approvePeriod(selectedPeriod.value.id)
    showApproveModal.value = false
    toast.success('Periode berhasil disetujui dan diaktifkan.')
    await loadData()
  } catch (err: any) {
    toast.error(err?.response?.data?.message || err?.message || 'Gagal menyetujui periode.')
  } finally {
    isSubmitting.value = false
  }
}

const openActivateModal = (period: OrganizationPeriod) => {
  selectedPeriod.value = period
  showActivateModal.value = true
}

const confirmActivate = async () => {
  if (!selectedPeriod.value) return
  isSubmitting.value = true
  try {
    await organizationPeriodService.activatePeriod(selectedPeriod.value.id)
    showActivateModal.value = false
    toast.success('Periode berhasil diaktifkan. Periode aktif sebelumnya otomatis dinonaktifkan.')
    await loadData()
  } catch (err: any) {
    toast.error(err?.response?.data?.message || err?.message || 'Gagal mengaktifkan periode.')
  } finally {
    isSubmitting.value = false
  }
}

const openDeactivateModal = (period: OrganizationPeriod) => {
  selectedPeriod.value = period
  showDeactivateModal.value = true
}

const confirmDeactivate = async () => {
  if (!selectedPeriod.value) return
  isSubmitting.value = true
  try {
    await organizationPeriodService.deactivatePeriod(selectedPeriod.value.id)
    showDeactivateModal.value = false
    toast.success('Periode berhasil dinonaktifkan.')
    await loadData()
  } catch (err: any) {
    toast.error(err?.response?.data?.message || err?.message || 'Gagal menonaktifkan periode.')
  } finally {
    isSubmitting.value = false
  }
}

const openRejectModal = (period: OrganizationPeriod) => {
  selectedPeriod.value = period
  rejectionReason.value = ''
  showRejectModal.value = true
}

const confirmReject = async () => {
  if (!selectedPeriod.value || !rejectionReason.value.trim()) return
  isSubmitting.value = true
  try {
    await organizationPeriodService.rejectPeriod(selectedPeriod.value.id, rejectionReason.value.trim())
    showRejectModal.value = false
    toast.success('Pengajuan periode berhasil ditolak.')
    await loadData()
  } catch (err: any) {
    toast.error(err?.response?.data?.message || err?.message || 'Gagal menolak periode.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
