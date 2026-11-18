<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Surat-Menyurat" />

    <main class="space-y-5">
      <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Surat-Menyurat</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Arsipkan surat masuk dan keluar, lalu ajukan untuk ditinjau.</p>
        </div>
        <button v-if="canCreate" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600" @click="openCreate">
          <PlusIcon class="h-4 w-4" /> Catat Surat
        </button>
      </header>

      <section class="grid gap-3 sm:grid-cols-2">
        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
          Jenis surat
          <select v-model="filters.type" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Semua jenis</option>
            <option value="incoming">Surat masuk</option>
            <option value="outgoing">Surat keluar</option>
          </select>
        </label>
        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
          Status
          <select v-model="filters.status" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Semua status</option>
            <option value="draft">Draf</option>
            <option value="submitted">Menunggu tinjauan</option>
            <option value="revision_requested">Perlu revisi</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
        </label>
      </section>

      <CorrespondenceTable
        :letters="letters"
        :loading="isLoading"
        :error-message="errorMessage"
        :meta="meta"
        :can-update="canUpdate"
        :can-review="canReview"
        :can-delete="canDelete"
        :show-organization="isSuperAdmin"
        @page="loadPage"
        @edit="openEdit"
        @submit="submitLetter"
        @review="openReview"
        @remove="remove"
        @download="download"
      />
    </main>

    <CorrespondenceFormModal
      :open="isFormOpen"
      :saving="isSaving"
      :correspondence="selectedLetter"
      :is-super-admin="isSuperAdmin"
      :organizations="organizations"
      @close="isFormOpen = false"
      @submit="save"
    />
    <CorrespondenceReviewModal
      :open="isReviewOpen"
      :saving="isSaving"
      :correspondence="reviewTarget"
      @close="isReviewOpen = false"
      @submit="review"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import { PlusIcon } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import {
  correspondenceService,
  type Correspondence,
  type CorrespondenceOrganization,
  type CorrespondenceInput,
  type CorrespondenceReviewInput,
} from '@/services/correspondenceService'
import { organizationService } from '@/services/organizationService'
import CorrespondenceTable from './CorrespondenceTable.vue'
import CorrespondenceFormModal from './CorrespondenceFormModal.vue'
import CorrespondenceReviewModal from './CorrespondenceReviewModal.vue'

const authStore = useAuthStore()
const toast = useToastStore()
const letters = ref<Correspondence[]>([])
const organizations = ref<CorrespondenceOrganization[]>([])
const meta = ref<{ current_page: number; last_page: number; per_page: number; total: number } | null>(null)
const filters = reactive({ type: '', status: '' })
const isLoading = ref(true)
const isSaving = ref(false)
const isFormOpen = ref(false)
const isReviewOpen = ref(false)
const errorMessage = ref('')
const selectedLetter = ref<Correspondence | null>(null)
const reviewTarget = ref<Correspondence | null>(null)

const isSuperAdmin = authStore.role === 'Super Admin'
const canCreate = authStore.hasPermission('correspondence.create')
const canUpdate = authStore.hasPermission('correspondence.update')
const canReview = authStore.hasPermission('correspondence.review')
const canDelete = authStore.hasPermission('correspondence.delete')

const loadPage = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await correspondenceService.list(page, filters)
    letters.value = response.data
    meta.value = response.meta
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'Arsip surat gagal dimuat.')
  } finally {
    isLoading.value = false
  }
}

const loadOrganizations = async () => {
  try {
    organizations.value = (await organizationService.list())
      .map(({ id, nama }) => ({ id, nama }))
  } catch (error) {
    toast.error(getErrorMessage(error, 'Daftar organisasi gagal dimuat.'))
  }
}

watch(() => [filters.type, filters.status], () => loadPage(1))

const openCreate = () => {
  selectedLetter.value = null
  isFormOpen.value = true
}
const openEdit = (letter: Correspondence) => {
  selectedLetter.value = letter
  isFormOpen.value = true
}
const openReview = (letter: Correspondence) => {
  reviewTarget.value = letter
  isReviewOpen.value = true
}

const save = async (input: CorrespondenceInput) => {
  isSaving.value = true
  try {
    if (selectedLetter.value) {
      await correspondenceService.update(selectedLetter.value.id, input)
      toast.success('Surat berhasil diperbarui.')
    } else {
      await correspondenceService.create(input)
      toast.success('Surat disimpan sebagai draf.')
    }
    isFormOpen.value = false
    await loadPage(meta.value?.current_page ?? 1)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Surat gagal disimpan.'))
  } finally {
    isSaving.value = false
  }
}

const submitLetter = async (letter: Correspondence) => {
  if (!window.confirm(`Ajukan surat ${letter.reference_number} untuk ditinjau?`)) return
  try {
    await correspondenceService.submit(letter.id)
    toast.success('Surat diajukan untuk ditinjau.')
    await loadPage(meta.value?.current_page ?? 1)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Surat gagal diajukan.'))
  }
}

const review = async (input: CorrespondenceReviewInput) => {
  if (!reviewTarget.value) return
  isSaving.value = true
  try {
    await correspondenceService.review(reviewTarget.value.id, input)
    toast.success('Keputusan tinjauan berhasil disimpan.')
    isReviewOpen.value = false
    await loadPage(meta.value?.current_page ?? 1)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Tinjauan surat gagal disimpan.'))
  } finally {
    isSaving.value = false
  }
}

const remove = async (letter: Correspondence) => {
  if (!window.confirm(`Hapus surat ${letter.reference_number}?`)) return
  try {
    await correspondenceService.remove(letter.id)
    toast.success('Surat berhasil dihapus.')
    await loadPage(meta.value?.current_page ?? 1)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Surat gagal dihapus.'))
  }
}

const download = async (letter: Correspondence) => {
  if (!letter.attachment_name) return
  try {
    await correspondenceService.download(letter.id, letter.attachment_name)
  } catch (error) {
    toast.error(getErrorMessage(error, 'Lampiran gagal diunduh.'))
  }
}

const getErrorMessage = (error: unknown, fallback: string) => {
  if (error && typeof error === 'object' && 'message' in error && typeof error.message === 'string') {
    return error.message
  }
  return fallback
}

onMounted(() => {
  void loadPage()
  if (isSuperAdmin) void loadOrganizations()
})
</script>
