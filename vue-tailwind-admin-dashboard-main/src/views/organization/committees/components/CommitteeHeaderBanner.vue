<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl flex items-center gap-2">
          <UsersIcon class="h-6 w-6 text-brand-500" />
          Struktur Organisasi
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Kelola struktur kepengurusan organisasi berdasarkan periode.
        </p>
      </div>

      <div v-if="canManage" class="flex items-center gap-3">
        <button
          @click="$emit('create')"
          class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-xs hover:bg-brand-600 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 disabled:opacity-50 transition cursor-pointer"
          :disabled="isLoading"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Tambah Pengurus</span>
        </button>
      </div>
    </div>

    <!-- Info Periode Aktif Card -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 border-t border-gray-100 dark:border-gray-800 pt-5">
      <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Organisasi</span>
        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
          {{ currentOrgName }}
        </p>
      </div>

      <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Periode Aktif</span>
        <p class="mt-1 text-base font-semibold" :class="activePeriod ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500'">
          {{ activePeriod ? activePeriod.period_name : 'Tidak Ada' }}
        </p>
      </div>

      <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Masa Berlaku</span>
        <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
          <template v-if="activePeriod">
            {{ formatDate(activePeriod.start_date) }} — {{ formatDate(activePeriod.end_date) }}
          </template>
          <template v-else>-</template>
        </p>
      </div>

      <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Pengurus</span>
        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
          {{ committeesCount }} Pengurus
        </p>
      </div>
    </div>

    <!-- Warning jika tidak ada periode aktif -->
    <div
      v-if="!isLoading && !activePeriod && userRole === 'Admin Organisasi'"
      class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/30"
    >
      <div class="flex items-start gap-3">
        <AlertTriangleIcon class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
        <div>
          <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300">
            Belum ada periode kepengurusan aktif
          </h4>
          <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
            Organisasi Anda saat ini belum memiliki periode kepengurusan dengan status aktif. Anda dapat menambahkan pengurus baru setelah memilih periode kepengurusan.
          </p>
          <div class="mt-3">
            <router-link
              to="/organization/period"
              class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800 hover:text-amber-900 dark:text-amber-300 underline"
            >
              Buka Manajemen Periode
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UsersIcon, PlusIcon, AlertTriangleIcon } from 'lucide-vue-next'
import type { OrganizationPeriod } from '@/types/api'

defineProps<{
  canManage: boolean
  isLoading: boolean
  currentOrgName: string
  activePeriod: OrganizationPeriod | null
  committeesCount: number
  userRole?: string | null
  formatDate: (dateStr?: string) => string
}>()

defineEmits<{
  (e: 'create'): void
}>()
</script>
