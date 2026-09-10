<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Galeri Publik'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Actions ────────────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <EyeIcon class="h-5 w-5 text-emerald-500" />
            Galeri Publik Organisasi
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Foto yang dipublikasikan di sini akan tampil di halaman Galeri Website Publik organisasi Anda.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <!-- View Switcher -->
          <div class="flex rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-800">
            <button
              @click="viewMode = 'grid'"
              :class="['p-1.5 rounded text-xs transition', viewMode === 'grid' ? 'bg-brand-500 text-white shadow-xs' : 'text-gray-500 hover:text-gray-700']"
              title="Tampilan Grid"
            >
              <LayoutGridIcon class="h-4 w-4" />
            </button>
            <button
              @click="viewMode = 'list'"
              :class="['p-1.5 rounded text-xs transition', viewMode === 'list' ? 'bg-brand-500 text-white shadow-xs' : 'text-gray-500 hover:text-gray-700']"
              title="Tampilan Tabel"
            >
              <ListIcon class="h-4 w-4" />
            </button>
          </div>

          <!-- Add from Media Library or Upload New -->
          <button
            v-if="canCreate"
            @click="openMediaPicker('library')"
            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3.5 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300 transition"
          >
            <FolderArchiveIcon class="h-4 w-4" />
            Pilih dari Media Library
          </button>

          <button
            v-if="canCreate"
            @click="openMediaPicker('upload')"
            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm transition"
          >
            <PlusIcon class="h-4 w-4" />
            Upload Foto Baru
          </button>
        </div>
      </div>

      <!-- ─── Search Toolbar ─────────────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-md">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari foto galeri berdasarkan judul, caption, atau nama file..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <div class="text-xs text-gray-500 dark:text-gray-400">
          Foto Publik Aktif: <strong class="text-emerald-600 dark:text-emerald-400 font-bold">{{ mediaList.length }}</strong> foto
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
          class="group relative aspect-square rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 shadow-xs hover:border-emerald-400 hover:shadow-md cursor-pointer transition dark:border-gray-800 dark:bg-gray-800"
        >
          <img
            :src="resolveImageUrl(item.image_url || item.url)"
            :alt="item.alt_text || item.title || item.filename"
            class="h-full w-full object-cover group-hover:scale-105 transition duration-300"
            loading="lazy"
          />

          <!-- Overlay Info on Hover -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition p-3 flex flex-col justify-between">
            <span class="self-start rounded bg-emerald-600/90 px-1.5 py-0.5 text-[9px] font-bold text-white uppercase">
              Publik
            </span>

            <div>
              <p class="text-[11px] font-bold text-white truncate" :title="item.title || item.filename">
                {{ item.title || item.filename }}
              </p>
              <p class="text-[10px] text-gray-300">
                {{ item.formatted_size || formatBytes(item.size) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Empty Grid State -->
        <div v-if="filteredMedia.length === 0" class="col-span-full py-16 text-center">
          <ImageIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-700 mb-2" />
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum ada foto yang dipublikasikan ke galeri.</h3>
          <p class="text-xs text-gray-400 mt-1">Pilih dari Media Library atau unggah foto dokumentasi kegiatan untuk ditampilkan ke publik.</p>
          <div class="mt-4 flex justify-center gap-2">
            <button
              v-if="canCreate"
              @click="openMediaPicker('library')"
              class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 bg-brand-50 px-3.5 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition"
            >
              <FolderArchiveIcon class="h-4 w-4" /> Dari Media Library
            </button>
            <button
              v-if="canCreate"
              @click="openMediaPicker('upload')"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm transition"
            >
              <PlusIcon class="h-4 w-4" /> Upload Foto Baru
            </button>
          </div>
        </div>
      </div>

      <!-- ─── LIST VIEW ──────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-semibold">
            <tr>
              <th class="p-3 w-16">Foto</th>
              <th class="p-3">Judul</th>
              <th class="p-3">Kategori</th>
              <th class="p-3">Ukuran</th>
              <th class="p-3">Diunggah Oleh</th>
              <th class="p-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <tr
              v-for="item in filteredMedia"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition cursor-pointer"
              @click="openDetail(item)"
            >
              <td class="p-3">
                <img
                  :src="resolveImageUrl(item.image_url || item.url)"
                  class="h-10 w-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                />
              </td>
              <td class="p-3">
                <p class="font-semibold text-gray-900 dark:text-white truncate max-w-xs">{{ item.title || item.filename }}</p>
                <p class="text-[10px] text-gray-400">{{ item.caption || '-' }}</p>
              </td>
              <td class="p-3 text-gray-600 dark:text-gray-300">{{ item.category || 'Dokumentasi' }}</td>
              <td class="p-3 text-gray-600 dark:text-gray-300">{{ item.formatted_size || formatBytes(item.size) }}</td>
              <td class="p-3 text-gray-600 dark:text-gray-300">{{ item.uploader }}</td>
              <td class="p-3 text-right" @click.stop>
                <button
                  @click="handleUnpublish(item)"
                  class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300"
                  title="Tarik dari galeri publik"
                >
                  <EyeOffIcon class="h-3.5 w-3.5" /> Tarik
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Drawer Detail Media ──────────────────────────────────────────────── -->
    <div
      v-if="selectedItem"
      class="fixed inset-0 z-50 flex justify-end bg-black/50 backdrop-blur-xs transition-opacity"
      @click.self="selectedItem = null"
    >
      <div class="h-full w-full max-w-md bg-white p-6 shadow-2xl overflow-y-auto dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 flex flex-col justify-between">
        <div>
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4 mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <EyeIcon class="h-4 w-4 text-emerald-500" /> Detail Foto Galeri Publik
            </h3>
            <button @click="selectedItem = null" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
              <XIcon class="h-5 w-5" />
            </button>
          </div>

          <!-- Preview -->
          <div class="aspect-video w-full rounded-xl overflow-hidden border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-800 mb-4 flex items-center justify-center">
            <img
              :src="resolveImageUrl(selectedItem.image_url || selectedItem.url)"
              class="h-full w-full object-contain"
            />
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <span class="text-gray-400 block mb-0.5">Judul Foto:</span>
              <p class="font-semibold text-gray-800 dark:text-gray-200">{{ selectedItem.title || selectedItem.name }}</p>
            </div>
            <div v-if="selectedItem.caption">
              <span class="text-gray-400 block mb-0.5">Caption:</span>
              <p class="text-gray-700 dark:text-gray-300">{{ selectedItem.caption }}</p>
            </div>
            <div>
              <span class="text-gray-400 block mb-0.5">Kategori:</span>
              <p class="text-gray-700 dark:text-gray-300">{{ selectedItem.category || 'Dokumentasi' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
              <div>
                <span class="text-gray-400 block">Ukuran File:</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ selectedItem.formatted_size || formatBytes(selectedItem.size) }}</p>
              </div>
              <div>
                <span class="text-gray-400 block">Diunggah Oleh:</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ selectedItem.uploader }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
          <button
            v-if="canEdit"
            @click="handleUnpublish(selectedItem)"
            class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
          >
            <EyeOffIcon class="h-4 w-4" /> Tarik dari Galeri Publik
          </button>
          <div v-else></div>

          <a
            :href="resolveImageUrl(selectedItem.image_url || selectedItem.url)"
            target="_blank"
            download
            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200"
          >
            <DownloadIcon class="h-4 w-4" /> Download
          </a>
        </div>
      </div>
    </div>

    <!-- ─── Modal Media Picker & Upload untuk Galeri Publik ─────────────── -->
    <MediaPickerModal
      :isOpen="isMediaPickerOpen"
      mode="gallery"
      :initialTab="mediaPickerInitialTab"
      title="Tambahkan Foto ke Galeri Publik"
      @close="isMediaPickerOpen = false"
      @select="handleGalleryItemAdded"
      @published="handleGalleryItemAdded"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import MediaPickerModal from '@/components/organization/MediaPickerModal.vue'
import { mediaService, type MediaItem } from '@/services/mediaService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { resolveImageUrl } from '@/utils/imageUrl'
import {
  EyeIcon,
  EyeOffIcon,
  LayoutGridIcon,
  ListIcon,
  PlusIcon,
  FolderArchiveIcon,
  SearchIcon,
  AlertTriangleIcon,
  ImageIcon,
  XIcon,
  DownloadIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toast = useToastStore()

const canCreate = computed(() => authStore.role === 'Super Admin' || authStore.hasPermission('gallery.create'))
const canEdit = computed(() => authStore.role === 'Super Admin' || authStore.hasPermission('gallery.update') || authStore.hasPermission('gallery.create'))

const viewMode = ref<'grid' | 'list'>('grid')
const mediaList = ref<MediaItem[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const selectedItem = ref<MediaItem | null>(null)

// Media Picker Modal State
const isMediaPickerOpen = ref(false)
const mediaPickerInitialTab = ref<'library' | 'upload'>('library')

const openMediaPicker = (tab: 'library' | 'upload') => {
  mediaPickerInitialTab.value = tab
  isMediaPickerOpen.value = true
}

const loadMedia = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const data = await mediaService.list({ scope: 'gallery', published_only: true, type: 'images' })
    mediaList.value = data
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat galeri foto.'
  } finally {
    isLoading.value = false
  }
}

const filteredMedia = computed(() => {
  if (!searchQuery.value) return mediaList.value
  const q = searchQuery.value.toLowerCase()
  return mediaList.value.filter(item => {
    const title = (item.title || item.name || '').toLowerCase()
    const filename = (item.filename || '').toLowerCase()
    const caption = (item.caption || '').toLowerCase()
    return title.includes(q) || filename.includes(q) || caption.includes(q)
  })
})

const openDetail = (item: MediaItem) => {
  selectedItem.value = item
}

const handleUnpublish = async (item: MediaItem) => {
  try {
    await mediaService.toggleGallery(item.id, false)
    mediaList.value = mediaList.value.filter(m => m.id !== item.id)
    selectedItem.value = null
    toast.success('Foto berhasil ditarik dari galeri publik (tetap aman di Media Library).')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal menarik foto dari galeri.')
  }
}

const handleGalleryItemAdded = async () => {
  toast.success('Foto berhasil ditambahkan ke Galeri Publik!')
  isMediaPickerOpen.value = false
  await loadMedia()
}

const formatBytes = (bytes: number) => {
  if (!bytes || bytes <= 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

onMounted(() => {
  loadMedia()
})
</script>
