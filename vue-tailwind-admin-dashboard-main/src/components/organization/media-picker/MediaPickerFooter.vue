<template>
  <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/80 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-800/80 shrink-0">
    <!-- Footer Left Status -->
    <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-sm">
      <!-- Gallery Mode Status -->
      <template v-if="mode === 'gallery'">
        <template v-if="activeTab === 'library'">
          <span v-if="selectedItem">
            Foto Terpilih: <strong class="text-gray-800 dark:text-white">{{ selectedItem.title || selectedItem.name || selectedItem.filename }}</strong>
          </span>
          <span v-else>
            Pilih salah satu foto dari Media Library untuk ditampilkan di Galeri Publik.
          </span>
        </template>
        <template v-else>
          <span>Foto yang diunggah akan disimpan dan dipublikasikan ke Galeri Publik.</span>
        </template>
      </template>

      <!-- Article Mode Status -->
      <template v-else>
        <template v-if="activeTab === 'library'">
          <span v-if="selectedItem">
            Foto Terpilih: <strong class="text-gray-800 dark:text-white">{{ selectedItem.title || selectedItem.name || selectedItem.filename }}</strong>
          </span>
          <span v-else>
            Pilih salah satu foto dari daftar untuk dimasukkan ke artikel.
          </span>
        </template>
        <template v-else>
          <span>Foto yang diunggah akan otomatis disimpan ke Media Library dan disisipkan ke artikel.</span>
        </template>
      </template>
    </div>

    <!-- Footer Right Actions -->
    <div class="flex items-center justify-end gap-2">
      <!-- ─── Gallery Mode Actions ─────────────────────────────────── -->
      <template v-if="mode === 'gallery'">
        <button
          type="button"
          @click="$emit('cancel')"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
        >
          Batal
        </button>

        <!-- Gallery Tab 1 Action (Existing Media -> Add to Gallery) -->
        <template v-if="activeTab === 'library'">
          <button
            type="button"
            @click="$emit('submitGalleryExisting')"
            :disabled="!selectedItem || isSubmitting"
            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <RefreshCwIcon v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin" />
            <CheckIcon v-else class="h-4 w-4 stroke-[2.5]" />
            <span>{{ isSubmitting ? 'Menyimpan...' : 'Tambahkan ke Galeri Publik' }}</span>
          </button>
        </template>

        <!-- Gallery Tab 2 Action (New Upload -> Upload & Publish) -->
        <template v-else>
          <button
            type="button"
            @click="$emit('submitGalleryUpload')"
            :disabled="!uploadFile || !galleryTitle.trim() || isSubmitting"
            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <RefreshCwIcon v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin" />
            <UploadCloudIcon v-else class="h-3.5 w-3.5" />
            <span>{{ isSubmitting ? 'Mengunggah...' : 'Unggah & Publikasikan' }}</span>
          </button>
        </template>
      </template>

      <!-- ─── Article Mode Actions ─────────────────────────────────── -->
      <template v-else>
        <!-- If in Library Tab -->
        <template v-if="activeTab === 'library'">
          <button
            type="button"
            @click="$emit('cancel')"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
          >
            Batal
          </button>
          <button
            type="button"
            @click="$emit('confirmSelection')"
            :disabled="!selectedItem"
            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <CheckIcon class="h-4 w-4" />
            Gunakan Foto Ini
          </button>
        </template>

        <!-- If in Upload Tab -->
        <template v-else>
          <button
            type="button"
            @click="$emit('cancel')"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
          >
            Batal
          </button>
          <button
            type="button"
            @click="$emit('submitArticleUpload')"
            :disabled="!uploadFile || isSubmitting"
            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <RefreshCwIcon v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin" />
            <UploadCloudIcon v-else class="h-3.5 w-3.5" />
            {{ isSubmitting ? 'Mengunggah...' : 'Upload & Gunakan Foto' }}
          </button>
        </template>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { RefreshCwIcon, CheckIcon, UploadCloudIcon } from 'lucide-vue-next'
import type { MediaItem } from '@/services/mediaService'

defineProps<{
  mode: 'article' | 'gallery'
  activeTab: 'library' | 'upload'
  selectedItem: MediaItem | null
  uploadFile: File | null
  galleryTitle: string
  isSubmitting: boolean
}>()

defineEmits<{
  (e: 'cancel'): void
  (e: 'submitGalleryExisting'): void
  (e: 'submitGalleryUpload'): void
  (e: 'confirmSelection'): void
  (e: 'submitArticleUpload'): void
}>()
</script>
