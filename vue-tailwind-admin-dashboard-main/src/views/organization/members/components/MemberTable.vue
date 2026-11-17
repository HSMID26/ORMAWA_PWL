<template>
  <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead class="bg-gray-50/80 border-b border-gray-200 dark:bg-gray-900/50 dark:border-gray-800">
          <tr>
            <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">NIM &amp; Mahasiswa</th>
            <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Program Studi</th>
            <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Kontak</th>
            <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Status Anggota</th>
            <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Akun Sistem</th>
            <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
          <tr v-if="isLoading">
            <td colspan="6" class="py-8 text-center text-gray-500">Memuat data anggota...</td>
          </tr>
          <tr v-else-if="members.length === 0">
            <td colspan="6" class="py-12 text-center text-gray-500">
              <UsersIcon class="h-8 w-8 mx-auto text-gray-300 mb-2" />
              Belum ada data anggota yang cocok dengan filter.
            </td>
          </tr>
          <tr v-for="m in members" :key="m.id" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition">
            <td class="py-3 px-4">
              <div class="font-bold text-gray-900 dark:text-white">{{ m.nama }}</div>
              <div class="text-[11px] font-mono text-gray-500">NIM: {{ m.nim }}</div>
            </td>
            <td class="py-3 px-4">
              <div class="text-gray-800 dark:text-gray-200">{{ m.jurusan || '-' }}</div>
              <div class="text-[11px] text-gray-400">Angkatan: {{ m.angkatan || '-' }}</div>
            </td>
            <td class="py-3 px-4">
              <div class="text-gray-700 dark:text-gray-300">{{ m.email || '-' }}</div>
              <div class="text-[11px] text-gray-400">{{ m.telepon || '-' }}</div>
            </td>
            <td class="py-3 px-4">
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                :class="[
                  m.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' :
                  m.status === 'alumni' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20' :
                  'bg-gray-100 text-gray-700 ring-1 ring-gray-400/20'
                ]"
              >
                {{ m.status === 'active' ? 'Aktif' : (m.status === 'alumni' ? 'Alumni' : 'Nonaktif') }}
              </span>
            </td>
            <td class="py-3 px-4">
              <div v-if="m.user" class="inline-flex items-center gap-1.5 text-xs text-brand-600 dark:text-brand-400">
                <UserCircleIcon class="h-4 w-4" />
                <span class="truncate max-w-[120px]">{{ m.user.name }}</span>
              </div>
              <span v-else class="text-[11px] text-gray-400 italic">Tidak ditautkan</span>
            </td>
            <td class="py-3 px-4 text-right space-x-1">
              <button
                v-if="canManage"
                @click="$emit('edit', m)"
                class="p-1 text-gray-500 hover:text-brand-600 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                title="Edit Anggota"
              >
                <PencilIcon class="h-4 w-4" />
              </button>
              <button
                v-if="canManage"
                @click="$emit('delete', m)"
                class="p-1 text-gray-500 hover:text-rose-600 rounded hover:bg-rose-50 dark:hover:bg-rose-950/30 transition"
                title="Hapus Anggota"
              >
                <Trash2Icon class="h-4 w-4" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer Pagination -->
    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
      <span>Total: {{ pagination.total }} anggota</span>
      <div class="flex items-center gap-2">
        <button
          @click="$emit('changePage', pagination.page - 1)"
          :disabled="pagination.page <= 1"
          class="px-2.5 py-1 rounded border border-gray-200 disabled:opacity-40"
        >
          Sebelumnya
        </button>
        <span>Hal. {{ pagination.page }} dari {{ pagination.last_page }}</span>
        <button
          @click="$emit('changePage', pagination.page + 1)"
          :disabled="pagination.page >= pagination.last_page"
          class="px-2.5 py-1 rounded border border-gray-200 disabled:opacity-40"
        >
          Selanjutnya
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UsersIcon, UserCircleIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next'
import type { Member } from '@/types/api'

defineProps<{
  members: Member[]
  isLoading: boolean
  pagination: {
    page: number
    last_page: number
    total: number
  }
  canManage: boolean
}>()

defineEmits<{
  (e: 'edit', member: Member): void
  (e: 'delete', member: Member): void
  (e: 'changePage', newPage: number): void
}>()
</script>
