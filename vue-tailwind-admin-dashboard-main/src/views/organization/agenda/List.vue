<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Daftar Agenda Kegiatan
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola jadwal acara organisasi yang akan tampil di halaman publik.
          </p>
        </div>
        <router-link to="/organization/agenda/create" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Tambah Agenda
        </router-link>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama kegiatan atau lokasi..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Memuat agenda...
      </div>
      <div v-else-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
        {{ errorMessage }}
      </div>
      <!-- Tabel Data Agenda -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Poster</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama Kegiatan</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal & Waktu</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Lokasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi Singkat</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="agenda in filteredAgendas" 
              :key="agenda.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-gray-200 bg-gray-100 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                  {{ agenda.judul.charAt(0) }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                {{ agenda.judul }}
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-brand-600 dark:text-brand-400">{{ new Date(agenda.tanggal_pelaksanaan).toLocaleDateString('id-ID') }}</div>
                <div class="mt-2">
                  <ToggleSwitch
                    :modelValue="agenda.status"
                    trueValue="published"
                    falseValue="draft"
                    activeLabel="Aktif"
                    inactiveLabel="Draft"
                    :disabled="togglingId === agenda.id"
                    @change="(val) => toggleStatus(agenda, val)"
                  />
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                -
              </td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-[200px] truncate">
                {{ agenda.deskripsi }}
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center gap-3">
                  <router-link :to="`/organization/agenda/edit/${agenda.id}`" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors">Edit</router-link>
                  <button @click="deleteAgenda(agenda.id)" class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors" :disabled="deletingId === agenda.id">
                    {{ deletingId === agenda.id ? 'Menghapus...' : 'Delete' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredAgendas.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data agenda kegiatan tidak ditemukan.
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
import ToggleSwitch from '@/components/forms/FormElements/ToggleSwitch.vue'
import { activityService } from '@/services/activityService'
import { useToastStore } from '@/stores/toast'
import type { Activity } from '@/types/api'

const toastStore = useToastStore()
const currentPageTitle = ref('Manajemen Agenda')
const searchQuery = ref('')
const agendas = ref<Activity[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const deletingId = ref<number | null>(null)
const togglingId = ref<number | null>(null)

onMounted(async () => {
  await loadAgendas()
})

const loadAgendas = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    agendas.value = await activityService.list()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Gagal memuat agenda.'
  } finally {
    isLoading.value = false
  }
}

const toggleStatus = async (agenda: Activity, newStatus: string) => {
  togglingId.value = agenda.id
  try {
    const payload = {
      judul: agenda.judul,
      deskripsi: agenda.deskripsi,
      tanggal_pelaksanaan: agenda.tanggal_pelaksanaan,
      status: newStatus
    }
    await activityService.update(agenda.id, payload)
    agenda.status = newStatus
    toastStore.success('Status agenda berhasil diperbarui.')
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Gagal memperbarui status.')
  } finally {
    togglingId.value = null
  }
}

const deleteAgenda = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus agenda ini?')) {
    deletingId.value = id
    try {
      await activityService.remove(id)
      toastStore.success('Data berhasil dihapus.')
      await loadAgendas()
    } catch (error: any) {
      toastStore.error(error.response?.data?.message || 'Data gagal dihapus.')
    } finally {
      deletingId.value = null
    }
  }
}

const filteredAgendas = computed(() => {
  if (!searchQuery.value) return agendas.value

  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return agendas.value.filter((agenda) =>
    agenda.judul.toLowerCase().includes(lowerCaseQuery) ||
    agenda.deskripsi.toLowerCase().includes(lowerCaseQuery) ||
    agenda.tanggal_pelaksanaan.toLowerCase().includes(lowerCaseQuery),
  )
})
</script>