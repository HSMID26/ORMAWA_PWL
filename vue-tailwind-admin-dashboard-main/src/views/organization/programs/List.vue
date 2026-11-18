<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Program Kerja" />

    <main class="space-y-5">
      <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Program Kerja Tahunan</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Rencanakan tujuan, PIC, sasaran peserta, dan anggaran setiap program.</p>
        </div>
        <button v-if="canCreate" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600" @click="openCreate">
          <PlusIcon class="h-4 w-4" /> Tambah Program
        </button>
      </section>

      <section class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <article v-for="item in summary" :key="item.label" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ item.label }}</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ item.value }}</p>
        </article>
      </section>

      <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div v-if="errorMessage" class="p-5 text-sm text-rose-700 dark:text-rose-300">{{ errorMessage }}</div>
        <div v-else-if="isLoading" class="p-5 text-sm text-gray-500">Memuat program kerja…</div>
        <div v-else-if="programs.length === 0" class="p-10 text-center">
          <p class="font-medium text-gray-800 dark:text-gray-200">Belum ada program kerja.</p>
          <p class="mt-1 text-sm text-gray-500">Tambahkan rencana tahunan organisasi untuk mulai mengelola target dan anggarannya.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
              <tr>
                <th class="px-4 py-3">Program</th>
                <th class="px-4 py-3">PIC</th>
                <th class="px-4 py-3">Sasaran</th>
                <th class="px-4 py-3">Anggaran</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="program in programs" :key="program.id" class="align-top">
                <td class="px-4 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ program.name }}</p>
                  <p class="mt-1 max-w-md text-xs leading-5 text-gray-600 dark:text-gray-400">{{ program.objective }}</p>
                  <p class="mt-1 text-xs text-gray-500">Periode {{ program.period_year }}</p>
                </td>
                <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ program.responsible_member?.name || 'Belum ditentukan' }}</td>
                <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ program.target_participants.toLocaleString('id-ID') }} peserta</td>
                <td class="px-4 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ formatCurrency(program.estimated_budget) }}</td>
                <td class="px-4 py-4"><span class="rounded-md px-2 py-1 text-xs font-medium" :class="statusClass(program.status)">{{ statusLabel(program.status) }}</span></td>
                <td class="px-4 py-4">
                  <div class="flex justify-end gap-2">
                    <button v-if="canUpdate" type="button" class="rounded-md border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="openEdit(program)">Ubah</button>
                    <button v-if="canDelete" type="button" class="rounded-md border border-rose-200 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600 dark:border-rose-900 dark:text-rose-300" @click="remove(program)">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <footer v-if="meta && meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm dark:border-gray-800">
          <span class="text-gray-500">Halaman {{ meta.current_page }} dari {{ meta.last_page }} · {{ meta.total }} program</span>
          <div class="flex gap-2">
            <button type="button" :disabled="meta.current_page <= 1" class="rounded-md border border-gray-300 px-3 py-1.5 disabled:opacity-40 dark:border-gray-700" @click="loadPage(meta.current_page - 1)">Sebelumnya</button>
            <button type="button" :disabled="meta.current_page >= meta.last_page" class="rounded-md border border-gray-300 px-3 py-1.5 disabled:opacity-40 dark:border-gray-700" @click="loadPage(meta.current_page + 1)">Berikutnya</button>
          </div>
        </footer>
      </section>
    </main>

    <WorkProgramFormModal
      :open="isFormOpen"
      :saving="isSaving"
      :program="selectedProgram"
      :members="members"
      @close="isFormOpen = false"
      @submit="save"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useToastStore } from '@/stores/toast'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import WorkProgramFormModal from './WorkProgramFormModal.vue'
import { memberService } from '@/services/memberService'
import { workProgramService, type WorkProgram, type WorkProgramInput, type WorkProgramStatus } from '@/services/workProgramService'
import { useAuthStore } from '@/stores/auth'
import type { Member } from '@/types/api'
import { PlusIcon } from 'lucide-vue-next'

const toast = useToastStore()
const authStore = useAuthStore()
const programs = ref<WorkProgram[]>([])
const members = ref<Pick<Member, 'id' | 'nama'>[]>([])
const meta = ref<{ current_page: number; last_page: number; per_page: number; total: number } | null>(null)
const totals = ref({ total: 0, planned: 0, in_progress: 0, completed: 0, cancelled: 0 })
const isLoading = ref(true)
const isSaving = ref(false)
const isFormOpen = ref(false)
const errorMessage = ref('')
const selectedProgram = ref<WorkProgram | null>(null)

const canCreate = computed(() => authStore.hasPermission('work_programs.create'))
const canUpdate = computed(() => authStore.hasPermission('work_programs.update'))
const canDelete = computed(() => authStore.hasPermission('work_programs.delete'))
const summary = computed(() => [
  { label: 'Total Program', value: totals.value.total },
  { label: 'Belum Terlaksana', value: totals.value.planned },
  { label: 'Berjalan', value: totals.value.in_progress },
  { label: 'Selesai', value: totals.value.completed },
  { label: 'Dibatalkan', value: totals.value.cancelled },
])

const loadPage = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await workProgramService.list(page)
    programs.value = response.data
    meta.value = response.meta
    totals.value = response.summary
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'Program kerja gagal dimuat.')
  } finally {
    isLoading.value = false
  }
}

const loadMembers = async () => {
  try {
    const response = await memberService.list({ status: 'active', per_page: 100 })
    members.value = response.data.map(({ id, nama }) => ({ id, nama }))
  } catch {
    toast.error('Daftar anggota untuk pilihan PIC gagal dimuat.')
  }
}

const openCreate = () => {
  selectedProgram.value = null
  isFormOpen.value = true
}

const openEdit = (program: WorkProgram) => {
  selectedProgram.value = program
  isFormOpen.value = true
}

const save = async (payload: WorkProgramInput) => {
  isSaving.value = true
  try {
    if (selectedProgram.value) {
      await workProgramService.update(selectedProgram.value.id, payload)
      toast.success('Program kerja berhasil diperbarui.')
    } else {
      await workProgramService.create(payload)
      toast.success('Program kerja berhasil ditambahkan.')
    }
    isFormOpen.value = false
    await loadPage(meta.value?.current_page ?? 1)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Program kerja gagal disimpan.'))
  } finally {
    isSaving.value = false
  }
}

const remove = async (program: WorkProgram) => {
  if (!window.confirm(`Hapus program "${program.name}"?`)) return
  try {
    await workProgramService.remove(program.id)
    toast.success('Program kerja berhasil dihapus.')
    await loadPage(meta.value?.current_page ?? 1)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Program kerja gagal dihapus.'))
  }
}

const getErrorMessage = (error: unknown, fallback: string) => {
  if (error && typeof error === 'object' && 'message' in error && typeof error.message === 'string') {
    return error.message
  }
  return fallback
}

const statusLabel = (status: WorkProgramStatus) => ({
  planned: 'Belum terlaksana',
  in_progress: 'Berjalan',
  completed: 'Selesai',
  cancelled: 'Dibatalkan',
}[status])

const statusClass = (status: WorkProgramStatus) => ({
  planned: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
  in_progress: 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
  completed: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
  cancelled: 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
}[status])

const formatCurrency = (amount: string | number) => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
}).format(Number(amount))

onMounted(() => {
  void Promise.all([loadPage(), loadMembers()])
})
</script>
