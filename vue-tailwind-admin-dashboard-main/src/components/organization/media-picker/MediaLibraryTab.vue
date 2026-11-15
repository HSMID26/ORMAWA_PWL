<template>
  <div class="flex-1 overflow-y-auto p-6 min-h-0">
    <!-- Gallery Mode + Photo Selected: Existing Metadata READ-ONLY View -->
    <div v-if="mode === 'gallery' && selectedItem" class="max-w-2xl mx-auto space-y-4 animate-fade-in">
      <!-- Back to Grid Button & Source Badge -->
      <div class="flex items-center justify-between">
        <button
          type="button"
          @click="$emit('backToGrid')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 transition"
        >
          <ArrowLeftIcon class="h-4 w-4" />
          <span>Pilih Foto Lain dari Media Library</span>
        </button>

        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
          <FolderArchiveIcon class="h-3.5 w-3.5" /> Media Library Internal
        </span>
      </div>

      <!-- Preview Card -->
      <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl border border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
        <div class="relative h-28 w-28 shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
          <img
            :src="resolveImageUrl(selectedItem.image_url || selectedItem.url)"
            :alt="selectedItem.title || selectedItem.filename"
            class="h-full w-full object-cover"
          />
        </div>
        <div class="min-w-0 flex-1 text-xs">
          <p class="font-bold text-gray-900 dark:text-white truncate text-sm">
            {{ selectedItem.title || selectedItem.name || selectedItem.filename }}
          </p>
          <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-1">
            File: <code class="text-gray-700 dark:text-gray-300 font-mono">{{ selectedItem.filename }}</code>
          </p>
          <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">
            Ukuran: {{ selectedItem.formatted_size || (selectedItem.size ? Math.round(selectedItem.size / 1024) + ' KB' : '-') }}
          </p>
          <p class="text-emerald-600 dark:text-emerald-400 text-[11px] font-medium mt-1.5 flex items-center gap-1">
            <CheckIcon class="h-3.5 w-3.5 stroke-[2.5]" /> Metadata tersimpan akan langsung digunakan di Galeri Publik tanpa duplikasi file.
          </p>
        </div>
      </div>

      <!-- Read-Only Metadata Display -->
      <div class="space-y-3.5 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Foto</label>
          <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
            {{ selectedItem.title || selectedItem.name || selectedItem.filename }}
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
            <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
              {{ selectedItem.category || (selectedItem as any).kategori || 'Dokumentasi' }}
            </div>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alt Text (Aksesibilitas)</label>
            <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 truncate">
              {{ selectedItem.alt_text || selectedItem.title || '-' }}
            </div>
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Caption / Keterangan</label>
          <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 min-h-[50px] whitespace-pre-wrap">
            {{ selectedItem.caption || (selectedItem as any).deskripsi || '-' }}
          </div>
        </div>

        <div v-if="uploadError" class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400">
          {{ uploadError }}
        </div>
      </div>
    </div>

    <!-- Standard Grid View (Article Mode OR Gallery Mode before selection) -->
    <div v-else>
      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
        <div v-for="i in 8" :key="i" class="aspect-square animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20 my-auto">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button
          type="button"
          @click="$emit('retry')"
          class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 transition"
        >
          Coba Lagi
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="items.length === 0" class="flex flex-col items-center justify-center py-16 text-center my-auto">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 mb-3">
          <ImageIcon class="h-7 w-7" />
        </div>
        <h4 class="text-sm font-bold text-gray-800 dark:text-white">
          {{ searchQuery ? 'Foto tidak ditemukan' : 'Belum ada foto di Media Library' }}
        </h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
          {{ searchQuery ? 'Tidak ada media yang cocok dengan kata kunci pencarian.' : 'Belum ada foto yang tersimpan di Media Library organisasi ini. Anda dapat mengunggah foto baru.' }}
        </p>
        <button
          v-if="canUpload && !searchQuery"
          type="button"
          @click="$emit('switchToUpload')"
          class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-xs transition"
        >
          <UploadCloudIcon class="h-3.5 w-3.5" />
          Upload Foto Baru
        </button>
      </div>

      <!-- Grid View -->
      <div v-else class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4">
        <div
          v-for="item in items"
          :key="item.id"
          @click="$emit('selectItem', item)"
          @dblclick="$emit('doubleClick', item)"
          class="group relative aspect-square rounded-xl overflow-hidden border bg-gray-50 dark:bg-gray-800 cursor-pointer transition select-none"
          :class="[
            selectedItem?.id === item.id
              ? 'border-brand-500 ring-2 ring-brand-500/40 dark:border-brand-400'
              : 'border-gray-200 hover:border-brand-300 dark:border-gray-700'
          ]"
        >
          <!-- Thumbnail -->
          <img
            :src="resolveImageUrl(item.image_url || item.url)"
            :alt="item.alt_text || item.title || item.name || item.filename"
            class="h-full w-full object-cover transition group-hover:scale-105 duration-200"
            loading="lazy"
          />

          <!-- Selected Badge Checkmark -->
          <div
            v-if="selectedItem?.id === item.id"
            class="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white shadow-md ring-2 ring-white dark:ring-gray-900 z-10"
          >
            <CheckIcon class="h-3.5 w-3.5 stroke-[3]" />
          </div>

          <!-- Bottom Overlay Caption -->
          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2.5 pt-6 text-white">
            <p class="truncate text-[11px] font-semibold">
              {{ item.title || item.name || item.filename }}
            </p>
            <div class="flex items-center justify-between text-[9px] text-gray-300 mt-0.5">
              <span>{{ item.formatted_size || (item.size ? Math.round(item.size / 1024) + ' KB' : '') }}</span>
              <span v-if="item.category" class="rounded bg-black/40 px-1 py-0.5">{{ item.category }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  ArrowLeftIcon,
  FolderArchiveIcon,
  CheckIcon,
  ImageIcon,
  UploadCloudIcon,
  AlertTriangleIcon,
} from 'lucide-vue-next'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { MediaItem } from '@/services/mediaService'

defineProps<{
  items: MediaItem[]
  selectedItem: MediaItem | null
  mode: 'article' | 'gallery'
  isLoading: boolean
  errorMessage: string
  searchQuery: string
  canUpload: boolean
  uploadError: string
}>()

defineEmits<{
  (e: 'selectItem', item: MediaItem): void
  (e: 'doubleClick', item: MediaItem): void
  (e: 'backToGrid'): void
  (e: 'retry'): void
  (e: 'switchToUpload'): void
}>()
</script>
