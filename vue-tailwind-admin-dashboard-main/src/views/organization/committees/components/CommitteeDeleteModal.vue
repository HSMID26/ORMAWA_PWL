<template>
  <div
    v-if="isOpen && committee"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
      <div class="text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400">
          <Trash2Icon class="h-6 w-6" />
        </div>

        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">
          Hapus Data Pengurus?
        </h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Apakah Anda yakin ingin menghapus data pengurus <span class="font-bold text-gray-800 dark:text-white">{{ committee.name }}</span> ({{ committee.position }})? Tindakan ini tidak dapat dibatalkan.
        </p>
      </div>

      <div class="mt-6 flex items-center justify-center gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="flex-1 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 cursor-pointer"
        >
          Batal
        </button>
        <button
          type="button"
          @click="$emit('confirm')"
          :disabled="isSubmitting"
          class="flex-1 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50 cursor-pointer"
        >
          {{ isSubmitting ? 'Menghapus...' : 'Hapus Pengurus' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Trash2Icon } from 'lucide-vue-next'
import type { Committee } from '@/types/api'

defineProps<{
  isOpen: boolean
  committee: Committee | null
  isSubmitting: boolean
}>()

defineEmits<{
  (e: 'close'): void
  (e: 'confirm'): void
}>()
</script>
