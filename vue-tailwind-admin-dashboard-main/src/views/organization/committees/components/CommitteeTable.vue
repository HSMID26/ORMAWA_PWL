<template>
  <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03]">
    <!-- Loading State -->
    <div v-if="isLoading" class="p-12 text-center">
      <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-r-transparent"></div>
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Memuat data struktur kepengurusan...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="committees.length === 0" class="p-12 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400">
        <UsersIcon class="h-7 w-7" />
      </div>
      <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">
        Belum ada data pengurus
      </h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
        {{ hasActiveFilter ? 'Tidak ada pengurus yang sesuai dengan filter pencarian.' : 'Belum ada data pengurus untuk organisasi atau periode ini.' }}
      </p>
      <div v-if="canManage && !hasActiveFilter" class="mt-5">
        <button
          @click="$emit('create')"
          class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 cursor-pointer"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Tambah Pengurus Pertama</span>
        </button>
      </div>
    </div>

    <!-- Table Data -->
    <div v-else class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/75 dark:border-gray-800 dark:bg-gray-900/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
            <th class="py-3.5 px-4 font-semibold">Pengurus</th>
            <th class="py-3.5 px-4 font-semibold">Jabatan &amp; Divisi</th>
            <th class="py-3.5 px-4 font-semibold">Periode Kepengurusan</th>
            <th class="py-3.5 px-4 font-semibold">Status</th>
            <th class="py-3.5 px-4 font-semibold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          <tr
            v-for="item in committees"
            :key="item.id"
            class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors"
          >
            <!-- Pengurus (Photo + Name) -->
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                  <img
                    v-if="item.photo_url"
                    :src="item.photo_url"
                    :alt="item.name"
                    class="h-full w-full object-cover"
                  />
                  <span v-else class="font-bold text-gray-500 dark:text-gray-400 text-sm">
                    {{ item.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <div class="font-bold text-gray-900 dark:text-white">
                    {{ item.name }}
                  </div>
                  <div class="text-[11px] text-gray-400">
                    ID: #{{ item.id }}
                  </div>
                </div>
              </div>
            </td>

            <!-- Position & Department -->
            <td class="py-3.5 px-4">
              <div class="font-semibold text-gray-800 dark:text-gray-200">
                {{ item.position }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ item.department || 'BPH / Utama' }}
              </div>
            </td>

            <!-- Period -->
            <td class="py-3.5 px-4">
              <div class="font-medium text-gray-800 dark:text-gray-200">
                {{ item.organization_period?.period_name || (item as any).period_name || item.period }}
              </div>
              <div v-if="item.organization_period" class="text-xs text-gray-400">
                {{ formatDate(item.organization_period.start_date) }} — {{ formatDate(item.organization_period.end_date) }}
              </div>
            </td>

            <!-- Status Badge -->
            <td class="py-3.5 px-4">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase',
                  item.status === 'active'
                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'
                ]"
              >
                <span
                  class="mr-1.5 h-1.5 w-1.5 rounded-full"
                  :class="item.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400'"
                ></span>
                {{ item.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
              </span>
            </td>

            <!-- Actions -->
            <td class="py-3.5 px-4 text-center">
              <div class="flex items-center justify-center gap-1.5">
                <!-- Detail -->
                <button
                  @click="$emit('detail', item)"
                  class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg dark:text-gray-400 dark:hover:text-brand-400 dark:hover:bg-brand-950/30 transition-colors cursor-pointer"
                  title="Lihat Detail Pengurus"
                >
                  <EyeIcon class="h-4 w-4" />
                </button>

                <!-- Edit -->
                <button
                  v-if="canManage"
                  @click="$emit('edit', item)"
                  class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-950/30 transition-colors cursor-pointer"
                  title="Edit Pengurus"
                >
                  <PencilIcon class="h-4 w-4" />
                </button>

                <!-- Delete -->
                <button
                  v-if="canManage"
                  @click="$emit('delete', item)"
                  class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg dark:text-gray-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/30 transition-colors cursor-pointer"
                  title="Hapus Pengurus"
                >
                  <Trash2Icon class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UsersIcon, PlusIcon, EyeIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next'
import type { Committee } from '@/types/api'

defineProps<{
  committees: Committee[]
  isLoading: boolean
  canManage: boolean
  hasActiveFilter: boolean
  formatDate: (dateStr?: string) => string
}>()

defineEmits<{
  (e: 'create'): void
  (e: 'detail', committee: Committee): void
  (e: 'edit', committee: Committee): void
  (e: 'delete', committee: Committee): void
}>()
</script>
