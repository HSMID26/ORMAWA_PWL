<template>
  <div
    v-if="isOpen && committee"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
      <button
        @click="$emit('close')"
        class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer"
      >
        <XIcon class="h-5 w-5" />
      </button>

      <div class="text-center">
        <div class="mx-auto h-20 w-20 overflow-hidden rounded-full border-2 border-brand-500 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
          <img
            v-if="committee.photo_url"
            :src="committee.photo_url"
            :alt="committee.name"
            class="h-full w-full object-cover"
          />
          <span v-else class="text-2xl font-bold text-gray-600 dark:text-gray-300">
            {{ committee.name.charAt(0).toUpperCase() }}
          </span>
        </div>

        <h3 class="mt-3 text-lg font-bold text-gray-900 dark:text-white">
          {{ committee.name }}
        </h3>
        <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">
          {{ committee.position }}
        </p>
        <p class="text-xs text-gray-500">
          {{ committee.department || 'BPH / Utama' }}
        </p>
      </div>

      <div class="mt-6 space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-400">Periode:</span>
          <span class="font-medium text-gray-800 dark:text-gray-200">
            {{ committee.organization_period?.period_name || (committee as any).period_name || committee.period }}
          </span>
        </div>

        <div v-if="committee.organization_period" class="flex justify-between">
          <span class="text-gray-400">Masa Bakti:</span>
          <span class="font-medium text-gray-800 dark:text-gray-200">
            {{ formatDate(committee.organization_period.start_date) }} s/d {{ formatDate(committee.organization_period.end_date) }}
          </span>
        </div>

        <div class="flex justify-between">
          <span class="text-gray-400">Status Kepengurusan:</span>
          <span
            :class="committee.status === 'active' ? 'text-emerald-600 font-semibold' : 'text-gray-500 font-semibold'"
          >
            {{ committee.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
          </span>
        </div>
      </div>

      <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
        <button
          @click="$emit('close')"
          class="w-full rounded-lg bg-gray-100 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { XIcon } from 'lucide-vue-next'
import type { Committee } from '@/types/api'

defineProps<{
  isOpen: boolean
  committee: Committee | null
  formatDate: (dateStr?: string) => string
}>()

defineEmits<{
  (e: 'close'): void
}>()
</script>
