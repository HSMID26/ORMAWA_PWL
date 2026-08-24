<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Galeri & Media Library'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Upload Button ─────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <ImageIcon class="h-5 w-5 text-emerald-500" />
            Media Library & Galeri
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Penyimpanan aset visual terpusat dengan optimasi WebP otomatis.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <!-- View Switcher -->
          <div class="flex rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-800">
            <button
              @click="viewMode = 'grid'"
              :class="['p-1.5 rounded text-xs', viewMode === 'grid' ? 'bg-brand-500 text-white shadow-xs' : 'text-gray-500 hover:text-gray-700']"
              title="Tampilan Grid"
            >
              <LayoutGridIcon class="h-4 w-4" />
            </button>
            <button
              @click="viewMode = 'list'"
              :class="['p-1.5 rounded text-xs', viewMode === 'list' ? 'bg-brand-500 text-white shadow-xs' : 'text-gray-500 hover:text-gray-700']"
              title="Tampilan Tabel"
            >
              <ListIcon class="h-4 w-4" />
            </button>
          </div>

          <label class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm cursor-pointer transition">
            <UploadCloudIcon class="h-4 w-4" />
            {{ isUploading ? 'Mengunggah...' : 'Upload Media' }}
            <input type="file" accept="image/*" @change="handleFileUpload" :disabled="isUploading" class="hidden" />
          </label>
        </div>
      </div>

      <!-- ─── Search & Metrics Toolbar ───────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-md">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari file media berdasarkan nama..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <div class="text-xs text-gray-500 dark:text-gray-400">
          Total Media: <strong class="text-gray-900 dark:text-white">{{ mediaList.length }}</strong> file
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 py-6">
        <div v-for="i in 12" :key="i" class="aspect-square animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Error State ────────────────────────────────────────────────────── -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button @click="loadMedia" class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
          Coba Lagi
        </button>
      </div>

      <!-- ─── GRID VIEW ──────────────────────────────────────────────────────── -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <div
          v-for="item in filteredMedia"
          :key="item.id"
          @click="openDetail(item)"
          class="group relative aspect-square rounded-2xl overflow-hidden border border-gray-200 bg-gray-100 shadow-xs hover:border-brand-400 hover:shadow-md cursor-pointer transition dark:border-gray-800 dark:bg-gray-800"
        >
          <img
            :src="item.url"
            :alt="item.filename"
            class="h-full w-full object-cover group-hover:scale-105 transition duration-300"
            loading="lazy"
          />

          <!-- Overlay Info on Hover -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition p-3 flex flex-col justify-between">
            <span class="self-start rounded bg-black/60 px-1.5 py-0.5 text-[9px] font-mono uppercase text-white">
              {{ item.mime_type?.split('/')[1] || 'webp' }}
            </span>

            <div>
              <p class="text-[11px] font-bold text-white truncate" :title="item.filename">
                {{ item.filename }}
              </p>
              <p class="text-[10px] text-gray-300">
                {{ formatBytes(item.size) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Empty Grid State -->
        <div v-if="filteredMedia.length === 0" class="col-span-full py-16 text-center">
          <ImageIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-700 mb-2" />
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum Ada Media</h3>
          <p class="text-xs text-gray-400 mt-1">Unggah gambar pertama Anda ke Media Library.</p>
        </div>
      </div>

      <!-- ─── LIST VIEW ──────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[650px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Pratinjau</th>
              <th class="px-4 py-3">Nama File</th>
              <th class="px-4 py-3">Format & Ukuran</th>
              <th class="px-4 py-3">Diupload Oleh</th>
              <th class="px-4 py-3">Tanggal</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
            <tr
              v-for="item in filteredMedia"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
            >
              <td class="px-4 py-2.5">
                <div class="h-10 w-14 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 dark:border-gray-700">
                  <img :src="item.url" :alt="item.filename" class="h-full w-full object-cover" />
                </div>
              </td>

              <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white max-w-xs truncate" :title="item.filename">
                {{ item.filename }}
              </td>

              <td class="px-4 py-2.5 text-gray-500 font-mono text-[11px]">
                {{ item.mime_type }} • {{ formatBytes(item.size) }}
              </td>

              <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                {{ item.uploader || 'Admin' }}
              </td>

              <td class="px-4 py-2.5 text-gray-500 text-[11px] whitespace-nowrap">
                {{ item.created_at }}
              </td>

              <td class="px-4 py-2.5 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openDetail(item)"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Detail Media"
                  >
                    <EyeIcon class="h-4 w-4" />
                  </button>
                  <button
                    @click="copyUrl(item.url)"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Salin Tautan URL"
                  >
                    <CopyIcon class="h-4 w-4" />
                  </button>
                  <button
                    @click="openDeleteModal(item)"
                    class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition"
                    title="Hapus Media"
                  >
                    <Trash2Icon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredMedia.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-xs text-gray-400">
                Tidak ada media yang cocok dengan pencarian.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Modal Detail Media ───────────────────────────────────────────────── -->
    <div
      v-if="isDetailOpen && selectedMedia"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="isDetailOpen = false"
    >
      <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
          <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <ImageIcon class="h-5 w-5 text-emerald-500" />
            Detail Media
          </h3>
          <button @click="isDetailOpen = false" class="rounded-lg p-1 text-gray-400 hover:text-gray-600">
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <!-- Full Resolution Image Preview -->
        <div class="rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 flex items-center justify-center max-h-80 mb-4">
          <img :src="selectedMedia.url" :alt="selectedMedia.filename" class="max-h-80 w-auto object-contain" />
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 gap-3 text-xs rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60 mb-4">
          <div>
            <span class="text-[10px] uppercase font-semibold text-gray-400 block">Nama File</span>
            <p class="font-bold text-gray-900 dark:text-white mt-0.5 truncate" :title="selectedMedia.filename">
              {{ selectedMedia.filename }}
            </p>
          </div>
          <div>
            <span class="text-[10px] uppercase font-semibold text-gray-400 block">Ukuran File</span>
            <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ formatBytes(selectedMedia.size) }}</p>
          </div>
          <div>
            <span class="text-[10px] uppercase font-semibold text-gray-400 block">Diupload Oleh</span>
            <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ selectedMedia.uploader || 'Admin' }}</p>
          </div>
          <div>
            <span class="text-[10px] uppercase font-semibold text-gray-400 block">Waktu Upload</span>
            <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ selectedMedia.created_at }}</p>
          </div>
        </div>

        <!-- URL Copy Bar -->
        <div class="space-y-1 mb-4">
          <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">Tautan Langsung (Direct URL)</label>
          <div class="flex items-center gap-2">
            <input
              type="text"
              readonly
              :value="selectedMedia.url"
              class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-xs font-mono text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 select-all"
            />
            <button
              @click="copyUrl(selectedMedia.url)"
              class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-brand-700"
            >
              <CopyIcon class="h-3.5 w-3.5" />
              Salin URL
            </button>
          </div>
        </div>

        <div class="flex justify-between items-center border-t border-gray-100 pt-4 dark:border-gray-800">
          <button
            @click="openDeleteFromDetail"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700"
          >
            <Trash2Icon class="h-4 w-4" />
            Hapus File Ini
          </button>
          <button
            @click="isDetailOpen = false"
            class="rounded-xl bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- ─── Modal Konfirmasi Hapus ───────────────────────────────────────────── -->
    <div
      v-if="isDeleteOpen && mediaToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 mb-4">
          <Trash2Icon class="h-6 w-6" />
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus File Media?</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Apakah Anda yakin ingin menghapus file <strong class="text-gray-900 dark:text-white">"{{ mediaToDelete.filename }}"</strong>? File fisik dan tautan terkait akan dihapus permanen.
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
import { mediaService, type MediaItem } from '@/services/mediaService'
import { useToastStore } from '@/stores/toast'
import {
  ImageIcon,
  UploadCloudIcon,
  SearchIcon,
  LayoutGridIcon,
  ListIcon,
  EyeIcon,
  CopyIcon,
  Trash2Icon,
  AlertTriangleIcon,
  XIcon
} from 'lucide-vue-next'

const toastStore = useToastStore()

const mediaList = ref<MediaItem[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const isUploading = ref(false)

const viewMode = ref<'grid' | 'list'>('grid')
const searchQuery = ref('')

// Detail modal
const isDetailOpen = ref(false)
const selectedMedia = ref<MediaItem | null>(null)

// Delete modal
const isDeleteOpen = ref(false)
const mediaToDelete = ref<MediaItem | null>(null)
const isDeleting = ref(false)

const loadMedia = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    mediaList.value = await mediaService.list()
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat berkas media.'
  } finally {
    isLoading.value = false
  }
}

const filteredMedia = computed(() => {
  if (!searchQuery.value.trim()) return mediaList.value
  const q = searchQuery.value.toLowerCase()
  return mediaList.value.filter(m => m.filename.toLowerCase().includes(q))
})

const formatBytes = (bytes?: number) => {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files || !target.files[0]) return

  const file = target.files[0]
  isUploading.value = true
  try {
    await mediaService.upload(file)
    toastStore.success('File media berhasil diunggah dan dikompresi ke WebP!')
    await loadMedia()
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || error.message || 'Gagal mengunggah media.')
  } finally {
    isUploading.value = false
    target.value = ''
  }
}

const openDetail = (item: MediaItem) => {
  selectedMedia.value = item
  isDetailOpen.value = true
}

const copyUrl = async (url: string) => {
  try {
    await navigator.clipboard.writeText(url)
    toastStore.success('Tautan URL media disalin ke clipboard!')
  } catch {
    toastStore.error('Gagal menyalin URL.')
  }
}

const openDeleteModal = (item: MediaItem) => {
  mediaToDelete.value = item
  isDeleteOpen.value = true
}

const openDeleteFromDetail = () => {
  if (selectedMedia.value) {
    mediaToDelete.value = selectedMedia.value
    isDetailOpen.value = false
    isDeleteOpen.value = true
  }
}

const confirmDelete = async () => {
  if (!mediaToDelete.value || isDeleting.value) return
  isDeleting.value = true
  try {
    await mediaService.remove(mediaToDelete.value.id)
    toastStore.success('File media berhasil dihapus.')
    isDeleteOpen.value = false
    mediaToDelete.value = null
    await loadMedia()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menghapus file media.')
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  loadMedia()
})
</script>