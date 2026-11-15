<template>
  <div class="flex-1 overflow-y-auto p-6 min-h-0">
    <!-- Gallery Mode Upload Workflow (Full Editable Metadata Form) -->
    <div v-if="mode === 'gallery'" class="max-w-2xl mx-auto space-y-4 animate-fade-in">
      <!-- Dropzone Area -->
      <div
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onDrop"
        :class="[
          'relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition cursor-pointer',
          isDragging
            ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20'
            : 'border-gray-300 hover:border-emerald-400 dark:border-gray-700 dark:hover:border-emerald-500 bg-gray-50/50 dark:bg-gray-800/30'
        ]"
      >
        <input
          ref="fileInputRef"
          type="file"
          accept="image/*"
          @change="onFileChange"
          class="hidden"
        />

        <div v-if="uploadPreviewUrl" class="relative w-full aspect-video rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 mb-3 border border-gray-200 dark:border-gray-700">
          <img :src="uploadPreviewUrl" alt="Upload Preview" class="h-full w-full object-contain" />
          <button
            type="button"
            @click.stop="$emit('clearFile')"
            class="absolute top-2 right-2 rounded-lg bg-black/60 p-1 text-white hover:bg-rose-600 transition"
            title="Hapus foto terpilih"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <div v-else @click="fileInputRef?.click()" class="py-4">
          <UploadCloudIcon class="mx-auto h-10 w-10 text-emerald-500 mb-2" />
          <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
            Klik untuk memilih file atau seret foto ke sini
          </p>
          <p class="text-[11px] text-gray-400 mt-1">
            Format didukung: JPG, PNG, WebP, AVIF (Maks. 5MB)
          </p>
        </div>
      </div>

      <!-- Full Editable Form for Gallery Mode -->
      <div class="space-y-3.5 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
            Judul Foto <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="galleryForm.title"
            type="text"
            placeholder="Misal: Dokumentasi Kegiatan Seminar Nasional 2026..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            required
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
            <select
              v-model="galleryForm.category"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
              <option value="Dokumentasi">Dokumentasi</option>
              <option value="Kegiatan">Kegiatan</option>
              <option value="Prestasi">Prestasi</option>
              <option value="Banner / Sampul">Banner / Sampul</option>
              <option value="Fasilitas">Fasilitas</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alt Text (Aksesibilitas)</label>
            <input
              v-model="galleryForm.alt_text"
              type="text"
              placeholder="Deskripsi foto untuk tuna netra & SEO..."
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Caption / Keterangan</label>
          <textarea
            v-model="galleryForm.caption"
            rows="3"
            placeholder="Keterangan singkat mengenai foto ini..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
          ></textarea>
        </div>

        <div v-if="uploadError" class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400">
          {{ uploadError }}
        </div>
      </div>
    </div>

    <!-- Article Mode Upload Workflow (Clean & Lightweight) -->
    <div v-else class="max-w-xl mx-auto space-y-4">
      <!-- Dropzone Area -->
      <div
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onDrop"
        :class="[
          'relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition cursor-pointer',
          isDragging
            ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/20'
            : 'border-gray-300 hover:border-brand-400 dark:border-gray-700 dark:hover:border-brand-500 bg-gray-50/50 dark:bg-gray-800/30'
        ]"
      >
        <input
          ref="fileInputRef"
          type="file"
          accept="image/*"
          @change="onFileChange"
          class="hidden"
        />

        <div v-if="uploadPreviewUrl" class="relative w-full aspect-video rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 mb-3 border border-gray-200 dark:border-gray-700">
          <img :src="uploadPreviewUrl" alt="Upload Preview" class="h-full w-full object-contain" />
          <button
            type="button"
            @click.stop="$emit('clearFile')"
            class="absolute top-2 right-2 rounded-lg bg-black/60 p-1 text-white hover:bg-rose-600 transition"
            title="Hapus foto terpilih"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <div v-else @click="fileInputRef?.click()" class="py-4">
          <UploadCloudIcon class="mx-auto h-10 w-10 text-gray-400 mb-2" />
          <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
            Klik untuk memilih file atau seret foto ke sini
          </p>
          <p class="text-[11px] text-gray-400 mt-1">
            Format didukung: JPG, PNG, WebP, AVIF (Maks. 5MB)
          </p>
        </div>
      </div>

      <!-- Upload Form Fields for Article Mode (Clean & Fast) -->
      <div class="space-y-3 text-xs">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
            Judul / Nama Foto
          </label>
          <input
            v-model="articleForm.title"
            type="text"
            placeholder="Misal: Dokumentasi Kegiatan Seminar..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
          />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
            Alt Text (Aksesibilitas / SEO)
          </label>
          <input
            v-model="articleForm.alt_text"
            type="text"
            placeholder="Deskripsi singkat gambar untuk aksesibilitas..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
          />
        </div>

        <div v-if="uploadError" class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400">
          {{ uploadError }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { UploadCloudIcon, XIcon } from 'lucide-vue-next'
import type { GalleryUploadForm, ArticleUploadForm } from './types'

defineProps<{
  mode: 'article' | 'gallery'
  uploadPreviewUrl: string | null
  galleryForm: GalleryUploadForm
  articleForm: ArticleUploadForm
  uploadError: string
}>()

const emit = defineEmits<{
  (e: 'fileSelected', file: File): void
  (e: 'clearFile'): void
}>()

const fileInputRef = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)

const onFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    emit('fileSelected', target.files[0])
  }
}

const onDrop = (e: DragEvent) => {
  isDragging.value = false
  if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
    emit('fileSelected', e.dataTransfer.files[0])
  }
}

defineExpose({
  clearInput: () => {
    if (fileInputRef.value) {
      fileInputRef.value.value = ''
    }
  }
})
</script>
