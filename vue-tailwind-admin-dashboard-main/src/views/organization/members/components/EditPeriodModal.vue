<template>
  <div
    v-if="isOpen && period"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
  >
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
      <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
        <h3 class="font-bold text-gray-900 dark:text-white">
          Edit Periode Kepengurusan
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <XIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- Warning Banner Sinkronisasi Otomatis -->
      <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-3.5 dark:border-amber-900/40 dark:bg-amber-950/20 text-xs text-amber-900 dark:text-amber-300 mb-4">
        <div class="flex items-start gap-2.5">
          <AlertTriangleIcon class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" />
          <div>
            <span class="font-bold">PERINGATAN SINKRONISASI OTOMATIS:</span>
            <p class="mt-0.5 text-[11px] leading-relaxed">
              Mengubah tanggal mulai atau tanggal berakhir periode akan secara otomatis memperbarui status efektivitas seluruh pengurus yang terhubung pada periode ini. Pengurus dalam periode yang berakhir di masa lalu langsung berstatus kedaluwarsa tanpa perlu penonaktifan manual.
            </p>
          </div>
        </div>
      </div>

      <form @submit.prevent="$emit('submit')" class="space-y-3.5 text-xs">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama / Label Periode *</label>
          <input
            v-model="form.period_name"
            required
            type="text"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai *</label>
            <input
              v-model="form.start_date"
              required
              type="date"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Berakhir *</label>
            <input
              v-model="form.end_date"
              required
              type="date"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
          <textarea
            v-model="form.notes"
            rows="2"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200 dark:border-gray-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-3.5 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 rounded-lg bg-brand-500 text-white font-semibold hover:bg-brand-600 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan Periode' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { XIcon, AlertTriangleIcon } from 'lucide-vue-next'
import type { OrganizationPeriod } from '@/types/api'

defineProps<{
  isOpen: boolean
  period: OrganizationPeriod | null
  form: {
    period_name: string
    start_date: string
    end_date: string
    notes: string
  }
  isSubmitting: boolean
}>()

defineEmits<{
  (e: 'close'): void
  (e: 'submit'): void
}>()
</script>
