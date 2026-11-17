<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Dokumen'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Upload Button ─────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <FileArchiveIcon class="h-5 w-5 text-blue-500" />
            Dokumen
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Kelola arsip dan dokumen publik organisasi.
          </p>
        </div>
        <button
          v-if="authStore.hasPermission('documents.create')"
          @click="openUploadModal"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition"
        >
          <PlusIcon class="h-4 w-4" />
          Upload Dokumen
        </button>
      </div>

      <!-- ─── Toolbar Filters ────────────────────────────────────────────────── -->
      <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="relative">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama dokumen..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <div>
          <select
            v-model="filterCategory"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Kategori Dokumen</option>
            <option value="SK">Surat Keputusan (SK)</option>
            <option value="Proposal">Proposal Kegiatan</option>
            <option value="LPJ">Laporan Pertanggungjawaban (LPJ)</option>
            <option value="SOP">SOP & Petunjuk Teknis</option>
            <option value="Template">Template / Formulir</option>
            <option value="Other">Lain-lain</option>
          </select>
        </div>

        <div>
          <select
            v-model="filterVisibility"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Akses</option>
            <option value="public">Publik (Dapat Diunduh Pengunjung)</option>
            <option value="internal">Internal (Khusus Pengurus)</option>
          </select>
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="space-y-3 py-6">
        <div v-for="i in 4" :key="i" class="h-14 w-full animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Table Data ─────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Nama Dokumen</th>
              <th class="px-4 py-3">Kategori</th>
              <th class="px-4 py-3">Ukuran & Format</th>
              <th class="px-4 py-3">Akses</th>
              <th class="px-4 py-3">Tanggal Upload</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
            <tr
              v-for="item in filteredDocs"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
            >
              <!-- Nama Dokumen -->
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-2.5">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <FileTextIcon class="h-4 w-4" />
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">
                      {{ item.name }}
                    </h3>
                    <span class="text-[11px] text-gray-400 font-mono">{{ item.filename }}</span>
                  </div>
                </div>
              </td>

              <!-- Kategori -->
              <td class="px-4 py-3.5">
                <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                  {{ item.category || 'Other' }}
                </span>
              </td>

              <!-- Ukuran & Format -->
              <td class="px-4 py-3.5 font-mono text-[11px] text-gray-500">
                <span class="font-semibold uppercase text-gray-700 dark:text-gray-300">{{ item.file_type }}</span> • {{ item.formatted_size || formatBytes(item.file_size) }}
              </td>

              <!-- Akses -->
              <td class="px-4 py-3.5">
                <span
                  :class="[
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold',
                    item.visibility === 'public'
                      ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                      : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                  ]"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="item.visibility === 'public' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                  {{ item.visibility === 'public' ? 'Publik' : 'Internal' }}
                </span>
              </td>

              <!-- Tanggal -->
              <td class="px-4 py-3.5 text-gray-500 text-[11px]">
                {{ formatDate(item.created_at) }}
              </td>

              <!-- Aksi -->
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <a
                    v-if="item.download_url || item.file_url || item.url"
                    :href="item.download_url || item.file_url || item.url"
                    target="_blank"
                    download
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Download Dokumen"
                  >
                    <DownloadIcon class="h-4 w-4" />
                  </a>
                  <button
                    @click="openDeleteModal(item)"
                    class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition"
                    title="Hapus Dokumen"
                  >
                    <Trash2Icon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredDocs.length === 0">
              <td colspan="6" class="px-4 py-16 text-center">
                <FileArchiveIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-700 mb-2" />
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum ada dokumen.</h3>
                <p class="text-xs text-gray-400 mt-1">Belum ada dokumen publik yang diterbitkan organisasi.</p>
                <button
                  @click="openUploadModal"
                  class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm"
                >
                  <PlusIcon class="h-3.5 w-3.5" />
                  Upload Dokumen
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Modal Upload Dokumen ─────────────────────────────────────────────── -->
    <div
      v-if="isUploadModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="isUploadModalOpen = false"
    >
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
          <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <UploadCloudIcon class="h-5 w-5 text-blue-500" />
            Upload Dokumen
          </h3>
          <button @click="isUploadModalOpen = false" class="rounded-lg p-1 text-gray-400 hover:text-gray-600">
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <form @submit.prevent="submitUpload" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Nama Dokumen <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="uploadForm.name"
              type="text"
              required
              placeholder="Contoh: SK Kepengurusan 2026 / Proposal Lomba Web"
              class="w-full rounded-xl border border-gray-300 bg-transparent px-3.5 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Kategori Dokumen
              </label>
              <select
                v-model="uploadForm.category"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              >
                <option value="SK">Surat Keputusan (SK)</option>
                <option value="Proposal">Proposal Kegiatan</option>
                <option value="LPJ">Laporan Pertanggungjawaban</option>
                <option value="SOP">SOP & Juknis</option>
                <option value="Template">Template Formulir</option>
                <option value="Other">Lain-lain</option>
              </select>
            </div>

            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Akses Visibilitas
              </label>
              <select
                v-model="uploadForm.visibility"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              >
                <option value="public">Publik (Dapat Diunduh Pengunjung)</option>
                <option value="internal">Internal (Khusus Pengurus)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Pilih Berkas File <span class="text-rose-500">*</span>
            </label>
            <input
              type="file"
              required
              accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv"
              @change="handleFileChange"
              class="w-full rounded-xl border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
            />
            <p class="text-[10px] text-gray-400 mt-1">Mendukung file PDF, DOCX, XLSX, PPTX, ZIP (Max 25MB)</p>
          </div>

          <div class="mt-6 flex justify-end gap-2.5 border-t border-gray-100 pt-4 dark:border-gray-800">
            <button
              type="button"
              @click="isUploadModalOpen = false"
              class="rounded-xl bg-gray-100 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="rounded-xl bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700 shadow-sm disabled:opacity-50"
            >
              {{ isSubmitting ? 'Mengunggah...' : 'Simpan Dokumen' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── Modal Konfirmasi Hapus ───────────────────────────────────────────── -->
    <div
      v-if="isDeleteOpen && docToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 mb-4">
          <Trash2Icon class="h-6 w-6" />
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus Dokumen?</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Apakah Anda yakin ingin menghapus arsip dokumen <strong class="text-gray-900 dark:text-white">"{{ docToDelete.name }}"</strong>? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex justify-end gap-2.5">
          <button
            @click="isDeleteOpen = false"
            class="rounded-lg px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="confirmDelete"
            :disabled="isDeleting"
            class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50"
          >
            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
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
import { documentService, type OrgDocument } from '@/services/documentService'
import { useToastStore } from '@/stores/toast'
import { useAuthStore } from '@/stores/auth'
import {
  FileArchiveIcon,
  FileTextIcon,
  PlusIcon,
  SearchIcon,
  DownloadIcon,
  Trash2Icon,
  UploadCloudIcon,
  XIcon
} from 'lucide-vue-next'

const toastStore = useToastStore()
const authStore = useAuthStore()

const documents = ref<OrgDocument[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)

const searchQuery = ref('')
const filterCategory = ref('')
const filterVisibility = ref('')

// Upload Modal
const isUploadModalOpen = ref(false)
const selectedFile = ref<File | null>(null)
const uploadForm = ref({
  name: '',
  category: 'SK' as OrgDocument['category'],
  visibility: 'public' as 'public' | 'internal'
})

// Delete Modal
const isDeleteOpen = ref(false)
const docToDelete = ref<OrgDocument | null>(null)
const isDeleting = ref(false)

const loadDocuments = async () => {
  isLoading.value = true
  try {
    documents.value = await documentService.list()
  } catch (error) {
    console.error('Failed to load documents:', error)
  } finally {
    isLoading.value = false
  }
}

const filteredDocs = computed(() => {
  return documents.value.filter(doc => {
    if (filterCategory.value && doc.category !== filterCategory.value) {
      return false
    }
    if (filterVisibility.value && doc.visibility !== filterVisibility.value) {
      return false
    }
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchName = (doc.name || '').toLowerCase().includes(q)
      const matchFilename = (doc.filename || '').toLowerCase().includes(q)
      if (!matchName && !matchFilename) return false
    }
    return true
  })
})

const formatBytes = (bytes?: number) => {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(new Date(dateStr))
}

const openUploadModal = () => {
  uploadForm.value = {
    name: '',
    category: 'SK',
    visibility: 'public'
  }
  selectedFile.value = null
  isUploadModalOpen.value = true
}

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    if (file.type.startsWith('image/')) {
      toastStore.error('File yang diunggah harus berupa dokumen (PDF, DOCX, XLSX, PPTX, ZIP, dsb). Untuk foto atau gambar, gunakan menu Galeri Foto.')
      target.value = ''
      selectedFile.value = null
      return
    }
    selectedFile.value = file
    if (!uploadForm.value.name) {
      uploadForm.value.name = file.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ')
    }
  }
}

const submitUpload = async () => {
  if (!uploadForm.value.name.trim() || !selectedFile.value) {
    toastStore.error('Nama dokumen dan berkas file wajib diisi.')
    return
  }

  isSubmitting.value = true
  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('name', uploadForm.value.name.trim())
    formData.append('category', uploadForm.value.category)
    formData.append('visibility', uploadForm.value.visibility)

    await documentService.upload(formData)

    toastStore.success('Dokumen berhasil diunggah ke server!')
    isUploadModalOpen.value = false
    await loadDocuments()
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Gagal mengunggah dokumen: ' + error.message)
  } finally {
    isSubmitting.value = false
  }
}

const openDeleteModal = (doc: OrgDocument) => {
  docToDelete.value = doc
  isDeleteOpen.value = true
}

const confirmDelete = async () => {
  if (!docToDelete.value || isDeleting.value) return
  isDeleting.value = true
  try {
    await documentService.remove(docToDelete.value.id)
    toastStore.success('Dokumen berhasil dihapus dari sistem.')
    isDeleteOpen.value = false
    docToDelete.value = null
    await loadDocuments()
  } catch (error: any) {
    toastStore.error('Gagal menghapus dokumen.')
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  loadDocuments()
})
</script>