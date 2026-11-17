<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-6 animate-fade-in"
      @click.self="close"
      @keydown.esc="close"
    >
      <div
        class="relative flex flex-col w-full max-w-4xl h-[88vh] max-h-[760px] rounded-2xl bg-white shadow-2xl border border-gray-100 dark:border-gray-800 dark:bg-gray-900 overflow-hidden transition-all"
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800 shrink-0">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'flex h-10 w-10 items-center justify-center rounded-xl shrink-0',
                mode === 'gallery'
                  ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                  : 'bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400'
              ]"
            >
              <EyeIcon v-if="mode === 'gallery'" class="h-5 w-5" />
              <ImageIcon v-else class="h-5 w-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ modalTitle }}
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ modalSubtitle }}
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="close"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition cursor-pointer"
            title="Tutup Modal"
          >
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <!-- Navigation Tabs & Search Toolbar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-6 py-2.5 dark:border-gray-800 dark:bg-gray-800/50 shrink-0">
          <!-- Mode Tabs -->
          <div class="flex items-center gap-1.5 bg-gray-200/70 dark:bg-gray-800 p-1 rounded-xl self-start">
            <button
              type="button"
              @click="switchToTab('library')"
              :class="[
                'flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                activeTab === 'library'
                  ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-900 dark:text-white'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]"
            >
              <LayoutGridIcon class="h-3.5 w-3.5" />
              {{ libraryTabLabel }}
            </button>

            <button
              v-if="canUpload"
              type="button"
              @click="switchToTab('upload')"
              :class="[
                'flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                activeTab === 'upload'
                  ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-900 dark:text-white'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]"
            >
              <UploadCloudIcon class="h-3.5 w-3.5" />
              Upload Foto Baru
            </button>
          </div>

          <!-- Search Bar (Library Tab - only when in grid selection) -->
          <div v-if="activeTab === 'library' && !(mode === 'gallery' && selectedItem)" class="flex items-center gap-2">
            <div class="relative w-full sm:w-64">
              <SearchIcon class="absolute left-3 top-2.5 h-3.5 w-3.5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari foto..."
                class="h-8 w-full rounded-lg border border-gray-300 bg-white pl-8 pr-3 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
              />
            </div>

            <button
              type="button"
              @click="loadMedia"
              class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-medium transition cursor-pointer"
              title="Segarkan Media"
            >
              <RefreshCwIcon class="h-3.5 w-3.5" :class="{ 'animate-spin': isLoading }" />
            </button>
          </div>
        </div>

        <!-- Tab 1: Media Library -->
        <MediaLibraryTab
          v-if="activeTab === 'library'"
          :items="filteredItems"
          :selected-item="selectedItem"
          :mode="mode"
          :is-loading="isLoading"
          :error-message="errorMessage"
          :search-query="searchQuery"
          :can-upload="canUpload"
          :upload-error="uploadError"
          @select-item="selectItem"
          @double-click="handleDoubleClick"
          @back-to-grid="selectedItem = null"
          @retry="loadMedia"
          @switch-to-upload="switchToTab('upload')"
        />

        <!-- Tab 2: Upload Foto Baru -->
        <MediaUploadTab
          v-else-if="activeTab === 'upload'"
          ref="uploadTabRef"
          :mode="mode"
          :upload-preview-url="uploadPreviewUrl"
          :gallery-form="galleryUploadForm"
          :article-form="articleUploadForm"
          :upload-error="uploadError"
          @file-selected="setUploadFile"
          @clear-file="clearUploadFile"
        />

        <!-- Unified Footer -->
        <MediaPickerFooter
          :mode="mode"
          :active-tab="activeTab"
          :selected-item="selectedItem"
          :upload-file="uploadFile"
          :gallery-title="galleryUploadForm.title"
          :is-submitting="isSubmitting"
          @cancel="close"
          @submit-gallery-existing="submitGalleryExisting"
          @submit-gallery-upload="submitGalleryUpload"
          @confirm-selection="confirmSelection"
          @submit-article-upload="submitArticleUpload"
        />
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { mediaService, type MediaItem } from '@/services/mediaService'
import { useAuthStore } from '@/stores/auth'
import {
  ImageIcon,
  EyeIcon,
  SearchIcon,
  XIcon,
  RefreshCwIcon,
  LayoutGridIcon,
  UploadCloudIcon,
} from 'lucide-vue-next'

import type { GalleryUploadForm, ArticleUploadForm } from './media-picker/types'
import MediaLibraryTab from './media-picker/MediaLibraryTab.vue'
import MediaUploadTab from './media-picker/MediaUploadTab.vue'
import MediaPickerFooter from './media-picker/MediaPickerFooter.vue'

const props = withDefaults(
  defineProps<{
    isOpen: boolean
    title?: string
    mode?: 'article' | 'gallery'
    initialTab?: 'library' | 'upload'
    initialSelectedUrl?: string | null
  }>(),
  {
    isOpen: false,
    mode: 'article',
    initialTab: 'library',
    initialSelectedUrl: null
  }
)

const emit = defineEmits<{
  close: []
  select: [item: MediaItem]
  published: [item: MediaItem]
}>()

const authStore = useAuthStore()

// User permissions
const canUpload = computed(() => {
  return (
    authStore.role === 'Super Admin' ||
    authStore.hasPermission('gallery.create') ||
    authStore.role === 'Admin Organisasi' ||
    authStore.role === 'Editor'
  )
})

const activeTab = ref<'library' | 'upload'>('library')
const mediaList = ref<MediaItem[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const selectedItem = ref<MediaItem | null>(null)
const isSubmitting = ref(false)

// Titles and Labels based on Mode
const modalTitle = computed(() => {
  if (props.title) return props.title
  return props.mode === 'gallery' ? 'Kelola Foto Galeri Publik' : 'Pilih Foto'
})

const modalSubtitle = computed(() => {
  if (props.mode === 'gallery') {
    return 'Pilih foto dari Media Library atau unggah foto baru untuk ditampilkan di Galeri Publik organisasi.'
  }
  return 'Pilih foto dari Media Library atau unggah foto baru untuk digunakan di artikel.'
})

const libraryTabLabel = computed(() => {
  if (props.mode === 'gallery') {
    return `Pilih dari Media Library (${mediaList.value.length})`
  }
  return `Media Library (${mediaList.value.length})`
})

// Form for Gallery Upload
const galleryUploadForm = ref<GalleryUploadForm>({
  title: '',
  category: 'Dokumentasi',
  alt_text: '',
  caption: ''
})

const resetGalleryUploadForm = () => {
  galleryUploadForm.value = {
    title: '',
    category: 'Dokumentasi',
    alt_text: '',
    caption: ''
  }
}

// Upload state
const uploadTabRef = ref<{ clearInput: () => void } | null>(null)
const uploadFile = ref<File | null>(null)
const uploadPreviewUrl = ref<string | null>(null)
const uploadError = ref('')

// Form for Article Upload
const articleUploadForm = ref<ArticleUploadForm>({
  title: '',
  alt_text: ''
})

const loadMedia = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    mediaList.value = await mediaService.list({ type: 'images' })
    if (props.initialSelectedUrl) {
      const match = mediaList.value.find(
        (m) => m.url === props.initialSelectedUrl || m.image_url === props.initialSelectedUrl
      )
      if (match) {
        selectItem(match)
      }
    }
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat daftar media.'
  } finally {
    isLoading.value = false
  }
}

const switchToTab = (tab: 'library' | 'upload') => {
  activeTab.value = tab
  uploadError.value = ''
}

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      activeTab.value = props.initialTab || 'library'
      searchQuery.value = ''
      selectedItem.value = null
      clearUploadFile()
      resetGalleryUploadForm()
      loadMedia()
    }
  }
)

const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) {
    return mediaList.value
  }
  const q = searchQuery.value.toLowerCase().trim()
  return mediaList.value.filter(
    (item) =>
      (item.title && item.title.toLowerCase().includes(q)) ||
      (item.name && item.name.toLowerCase().includes(q)) ||
      (item.filename && item.filename.toLowerCase().includes(q)) ||
      (item.caption && item.caption.toLowerCase().includes(q)) ||
      (item.alt_text && item.alt_text.toLowerCase().includes(q))
  )
})

const selectItem = (item: MediaItem) => {
  if (props.mode === 'gallery') {
    selectedItem.value = item
    uploadError.value = ''
  } else {
    if (selectedItem.value?.id === item.id) {
      selectedItem.value = null
    } else {
      selectedItem.value = item
    }
  }
}

const handleDoubleClick = (item: MediaItem) => {
  selectItem(item)
  if (props.mode === 'article') {
    confirmSelection()
  }
}

const confirmSelection = () => {
  if (selectedItem.value) {
    emit('select', selectedItem.value)
    emit('close')
  }
}

const close = () => {
  emit('close')
}

// Upload Handling
const MAX_UPLOAD_SIZE = 5 * 1024 * 1024 // 5 MB

const setUploadFile = (file: File) => {
  uploadError.value = ''

  if (!file.type.startsWith('image/')) {
    uploadError.value = 'Format file tidak didukung. Harap pilih gambar dengan format JPG, PNG, WebP, atau AVIF.'
    if (uploadTabRef.value) uploadTabRef.value.clearInput()
    return
  }

  if (file.size > MAX_UPLOAD_SIZE) {
    uploadError.value = 'Ukuran file melebihi batas 5MB. Silakan pilih foto dengan ukuran lebih kecil.'
    if (uploadTabRef.value) uploadTabRef.value.clearInput()
    return
  }

  uploadFile.value = file
  uploadPreviewUrl.value = URL.createObjectURL(file)
  const cleanTitle = file.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ')

  if (props.mode === 'gallery') {
    galleryUploadForm.value.title = cleanTitle
    galleryUploadForm.value.alt_text = cleanTitle
  } else {
    articleUploadForm.value.title = cleanTitle
    articleUploadForm.value.alt_text = cleanTitle
  }
}

const clearUploadFile = () => {
  uploadFile.value = null
  uploadPreviewUrl.value = null
  uploadError.value = ''
  resetGalleryUploadForm()
  articleUploadForm.value = {
    title: '',
    alt_text: ''
  }
  if (uploadTabRef.value) {
    uploadTabRef.value.clearInput()
  }
}

// ─── Gallery Mode Submissions ────────────────────────────────────────────────
// Mode 1: Existing Media -> publish to gallery without re-uploading
const submitGalleryExisting = async () => {
  if (!selectedItem.value) return

  isSubmitting.value = true
  uploadError.value = ''

  try {
    const updatedMedia = await mediaService.toggleGallery(selectedItem.value.id, true)

    emit('select', updatedMedia)
    emit('published', updatedMedia)
    emit('close')
    selectedItem.value = null
  } catch (err: any) {
    uploadError.value = err.response?.data?.message || err.message || 'Gagal menambahkan foto ke galeri.'
  } finally {
    isSubmitting.value = false
  }
}

// Mode 2: New Upload -> upload with user-input metadata & publish to gallery
const submitGalleryUpload = async () => {
  if (!uploadFile.value) return
  if (!galleryUploadForm.value.title.trim()) {
    uploadError.value = 'Silakan masukkan judul foto.'
    return
  }

  isSubmitting.value = true
  uploadError.value = ''

  try {
    const formData = new FormData()
    formData.append('image', uploadFile.value)
    formData.append('title', galleryUploadForm.value.title.trim())
    formData.append('category', galleryUploadForm.value.category)
    formData.append('alt_text', galleryUploadForm.value.alt_text.trim() || galleryUploadForm.value.title.trim())
    formData.append('caption', galleryUploadForm.value.caption.trim())
    formData.append('publish_to_gallery', '1')
    formData.append('is_published_to_gallery', '1')

    const res = await mediaService.upload(formData)
    await loadMedia()

    const newMedia = res.data || res || (mediaList.value.length > 0 ? mediaList.value[0] : null)
    if (newMedia) {
      emit('select', newMedia)
      emit('published', newMedia)
    }
    emit('close')
    clearUploadFile()
    resetGalleryUploadForm()
  } catch (err: any) {
    uploadError.value = err.response?.data?.message || err.message || 'Gagal mengunggah foto ke galeri.'
  } finally {
    isSubmitting.value = false
  }
}

// ─── Article Mode Submission ─────────────────────────────────────────────────
const submitArticleUpload = async () => {
  if (!uploadFile.value) return
  const effectiveTitle = articleUploadForm.value.title.trim() || uploadFile.value.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ')

  isSubmitting.value = true
  uploadError.value = ''

  try {
    const formData = new FormData()
    formData.append('image', uploadFile.value)
    formData.append('title', effectiveTitle)
    formData.append('alt_text', articleUploadForm.value.alt_text || effectiveTitle)
    formData.append('category', 'Dokumentasi')
    formData.append('is_published_to_gallery', '0')
    formData.append('publish_to_gallery', '0')

    const res = await mediaService.upload(formData)
    await loadMedia()

    const newMedia = res.data || res || (mediaList.value.length > 0 ? mediaList.value[0] : null)
    if (newMedia) {
      selectedItem.value = newMedia
      emit('select', newMedia)
      emit('close')
      clearUploadFile()
      return
    }

    activeTab.value = 'library'
    clearUploadFile()
  } catch (err: any) {
    uploadError.value = err.response?.data?.message || err.message || 'Gagal mengunggah foto.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
