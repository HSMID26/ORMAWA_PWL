<template>
  <div class="space-y-4">
    <!-- Period Selector & Status -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Pilih Periode Struktur:</label>
        <select
          :value="selectedPeriodId ?? ''"
          @change="$emit('update:selectedPeriodId', Number(($event.target as HTMLSelectElement).value)); $emit('periodChange')"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
          <option v-for="p in periods" :key="p.id" :value="p.id">
            {{ p.period_name }} — {{ p.calculated_status === 'ongoing' ? 'Sedang Berjalan' : (p.calculated_status === 'expired' ? 'Kedaluwarsa' : 'Mendatang') }}
          </option>
        </select>
      </div>

      <div v-if="selectedPeriodObj" class="flex items-center gap-2">
        <span
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
          :class="[
            selectedPeriodObj.calculated_status === 'ongoing' ? 'bg-emerald-100 text-emerald-800' :
            selectedPeriodObj.calculated_status === 'expired' ? 'bg-amber-100 text-amber-800' :
            'bg-blue-100 text-blue-800'
          ]"
        >
          {{ selectedPeriodObj.calculated_status === 'ongoing' ? 'Sedang Berjalan (Publik Aktif)' : (selectedPeriodObj.calculated_status === 'expired' ? 'Periode Telah Berakhir (Arsip)' : 'Periode Mendatang') }}
        </span>
      </div>
    </div>

    <!-- Warning if Expired Period -->
    <div
      v-if="selectedPeriodObj && selectedPeriodObj.calculated_status === 'expired'"
      class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20 text-xs text-amber-900 dark:text-amber-300"
    >
      <div class="flex items-center gap-2 font-bold mb-1">
        <AlertTriangleIcon class="h-4 w-4 text-amber-600" />
        Periode ini telah kedaluwarsa berdasarkan tanggal berakhirnya ({{ formatDate(selectedPeriodObj.end_date) }}).
      </div>
      Pengurus dalam periode ini tersimpan sebagai riwayat arsip dan tidak ditampilkan sebagai pengurus aktif di struktur website publik.
    </div>

    <!-- Committees Table -->
    <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead class="bg-gray-50/80 border-b border-gray-200 dark:bg-gray-900/50 dark:border-gray-800">
            <tr>
              <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Pengurus</th>
              <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Jabatan &amp; Departemen</th>
              <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Anggota Terkait</th>
              <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Mode &amp; Penempatan</th>
              <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Status Efektif</th>
              <th class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
            <tr v-if="isLoading">
              <td colspan="6" class="py-8 text-center text-gray-500">Memuat susunan pengurus...</td>
            </tr>
            <tr v-else-if="committees.length === 0">
              <td colspan="6" class="py-12 text-center text-gray-500">
                Belum ada pengurus yang ditambahkan pada periode ini.
              </td>
            </tr>
            <tr v-for="c in committees" :key="c.id" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="h-9 w-9 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                    <img v-if="c.photo_url || c.photo" :src="c.photo_url || c.photo || undefined" class="h-full w-full object-cover" />
                    <span v-else class="font-bold text-gray-500">{{ c.name.charAt(0) }}</span>
                  </div>
                  <div>
                    <div class="font-bold text-gray-900 dark:text-white">{{ c.name }}</div>
                    <div class="text-[11px] text-gray-400">{{ c.period_name || c.period }}</div>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="font-semibold text-brand-600 dark:text-brand-400">{{ c.position }}</div>
                <div class="text-[11px] text-gray-500">{{ c.department || 'Pengurus Inti' }}</div>
              </td>
              <td class="py-3 px-4">
                <div v-if="c.member" class="text-gray-800 dark:text-gray-200">
                  <div class="font-medium">{{ c.member.nama }}</div>
                  <div class="text-[11px] font-mono text-gray-400 flex items-center gap-1.5 mt-0.5">
                    <span>NIM: {{ c.member.nim }}</span>
                    <span
                      class="px-1.5 py-0.2 rounded-full text-[9px] font-semibold"
                      :class="c.member.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                    >
                      {{ c.member.status === 'active' ? 'Anggota Aktif' : 'Anggota Nonaktif' }}
                    </span>
                  </div>
                </div>
                <span v-else class="text-[11px] text-gray-400 italic">Bukan anggota tetap</span>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="c.status_mode === 'manual' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20 dark:bg-indigo-950/40 dark:text-indigo-300' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-400/20 dark:bg-slate-800 dark:text-slate-300'"
                  >
                    {{ c.status_mode === 'manual' ? 'Mode Manual' : 'Mode Otomatis' }}
                  </span>
                  <span
                    v-if="c.status_mode === 'manual'"
                    class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold uppercase"
                    :class="c.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                  >
                    {{ c.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <div v-if="c.status_mode === 'manual' && c.status_reason" class="text-[10px] text-gray-500 italic mt-1 truncate max-w-[160px]" :title="c.status_reason">
                  "{{ c.status_reason }}"
                </div>
                <div v-if="c.status_changed_by_user" class="text-[10px] text-gray-400 mt-0.5">
                  Oleh: {{ c.status_changed_by_user.name }}
                </div>
              </td>
              <td class="py-3 px-4">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                  :class="c.is_active_effective ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-1 ring-gray-400/20'"
                >
                  {{ c.is_active_effective ? 'Aktif Efektif' : 'Nonaktif / Kedaluwarsa' }}
                </span>
              </td>
              <td class="py-3 px-4 text-right space-x-1">
                <button
                  v-if="canManage"
                  @click="$emit('quickStatus', c)"
                  class="p-1 text-gray-500 hover:text-indigo-600 rounded hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition"
                  title="Ubah Mode / Status Penempatan"
                >
                  <SlidersHorizontalIcon class="h-4 w-4" />
                </button>
                <button
                  v-if="canManage"
                  @click="$emit('edit', c)"
                  class="p-1 text-gray-500 hover:text-brand-600 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                  title="Edit Pengurus"
                >
                  <PencilIcon class="h-4 w-4" />
                </button>
                <button
                  v-if="canManage"
                  @click="$emit('delete', c)"
                  class="p-1 text-gray-500 hover:text-rose-600 rounded hover:bg-rose-50 dark:hover:bg-rose-950/30 transition"
                  title="Hapus Pengurus"
                >
                  <Trash2Icon class="h-4 w-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { AlertTriangleIcon, SlidersHorizontalIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next'
import type { Committee, OrganizationPeriod } from '@/types/api'

defineProps<{
  committees: Committee[]
  periods: OrganizationPeriod[]
  selectedPeriodId: number | null
  selectedPeriodObj?: OrganizationPeriod
  isLoading: boolean
  canManage: boolean
  formatDate: (dateStr?: string) => string
}>()

defineEmits<{
  (e: 'update:selectedPeriodId', id: number): void
  (e: 'periodChange'): void
  (e: 'quickStatus', committee: Committee): void
  (e: 'edit', committee: Committee): void
  (e: 'delete', committee: Committee): void
}>()
</script>
