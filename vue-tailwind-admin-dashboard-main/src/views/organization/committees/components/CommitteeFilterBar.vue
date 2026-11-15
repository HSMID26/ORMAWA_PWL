<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <!-- Search -->
      <div class="relative flex-1 max-w-md">
        <SearchIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
        <input
          :value="search"
          @input="$emit('update:search', ($event.target as HTMLInputElement).value)"
          type="text"
          placeholder="Cari nama, jabatan, atau divisi..."
          class="w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white dark:placeholder-gray-500"
        />
      </div>

      <!-- Select Filters -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Filter Periode -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Periode:</label>
          <select
            :value="organizationPeriodId"
            @change="$emit('update:organizationPeriodId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : undefined)"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
          >
            <option :value="undefined">Semua Periode</option>
            <option
              v-for="period in periodsList"
              :key="period.id"
              :value="period.id"
            >
              {{ period.period_name }} ({{ period.status === 'active' ? 'Aktif' : period.status }})
            </option>
          </select>
        </div>

        <!-- Filter Status -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Status:</label>
          <select
            :value="status"
            @change="$emit('update:status', ($event.target as HTMLSelectElement).value)"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
          >
            <option value="">Semua</option>
            <option value="active">Aktif</option>
            <option value="inactive">Non-Aktif</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { SearchIcon } from 'lucide-vue-next'
import type { OrganizationPeriod } from '@/types/api'

defineProps<{
  search: string
  organizationPeriodId: number | undefined
  status: string
  periodsList: OrganizationPeriod[]
}>()

defineEmits<{
  (e: 'update:search', val: string): void
  (e: 'update:organizationPeriodId', val: number | undefined): void
  (e: 'update:status', val: string): void
}>()
</script>
