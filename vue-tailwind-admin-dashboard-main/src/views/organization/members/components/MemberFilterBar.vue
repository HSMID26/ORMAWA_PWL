<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
      <div class="relative">
        <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
        <input
          :value="search"
          @input="$emit('update:search', ($event.target as HTMLInputElement).value); $emit('filterChange')"
          type="text"
          placeholder="Cari NIM atau nama..."
          class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
      </div>

      <div>
        <select
          :value="status"
          @change="$emit('update:status', ($event.target as HTMLSelectElement).value); $emit('filterChange')"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
          <option value="">Semua Status Keanggotaan</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
          <option value="alumni">Alumni</option>
        </select>
      </div>

      <div>
        <input
          :value="jurusan"
          @input="$emit('update:jurusan', ($event.target as HTMLInputElement).value); $emit('filterChange')"
          type="text"
          placeholder="Filter jurusan..."
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
      </div>

      <div>
        <input
          :value="angkatan"
          @input="$emit('update:angkatan', ($event.target as HTMLInputElement).value); $emit('filterChange')"
          type="text"
          placeholder="Filter angkatan (misal: 2024)..."
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { SearchIcon } from 'lucide-vue-next'

defineProps<{
  search?: string
  status?: string
  jurusan?: string
  angkatan?: string
}>()

defineEmits<{
  (e: 'update:search', val: string): void
  (e: 'update:status', val: string): void
  (e: 'update:jurusan', val: string): void
  (e: 'update:angkatan', val: string): void
  (e: 'filterChange'): void
}>()
</script>
