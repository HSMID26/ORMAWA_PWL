<template>
  <div
    v-if="isOpen && committee"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
  >
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
      <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
        <h3 class="font-bold text-gray-900 dark:text-white">
          Ubah Mode &amp; Status Pengurus
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <XIcon class="h-5 w-5" />
        </button>
      </div>

      <div class="text-xs space-y-3.5">
        <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-200/60 dark:border-gray-700">
          <p class="font-bold text-sm text-gray-900 dark:text-white">{{ committee.name }}</p>
          <p class="text-gray-500 mt-0.5">{{ committee.position }} &bull; {{ committee.period_name || committee.period }}</p>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Mode Evaluasi Status</label>
          <select
            v-model="form.status_mode"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          >
            <option value="auto">Mode Otomatis (Evaluasi dinamis tanggal &amp; anggota)</option>
            <option value="manual">Mode Manual (Penetapan eksplisit administrator)</option>
          </select>
        </div>

        <div v-if="form.status_mode === 'manual'" class="space-y-3 p-3 rounded-xl bg-amber-50/70 border border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/40">
          <div>
            <label class="block font-semibold text-amber-950 dark:text-amber-200 mb-1">Status Penempatan Manual *</label>
            <select
              v-model="form.status"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            >
              <option value="active">Active (Aktif)</option>
              <option value="inactive">Inactive (Nonaktif)</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-amber-950 dark:text-amber-200 mb-1">Alasan Perubahan *</label>
            <textarea
              v-model="form.status_reason"
              rows="2"
              required
              placeholder="Tuliskan alasan perubahan status..."
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            ></textarea>
          </div>
        </div>

        <div v-else class="rounded-xl bg-blue-50/70 p-3 text-xs text-blue-900 border border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/40 dark:text-blue-300">
          <span class="font-bold">Info:</span> Kembali ke mode otomatis akan mengevaluasi keaktifan pengurus berdasarkan masa berlaku periode dan status keanggotaan.
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
            type="button"
            @click="$emit('submit')"
            :disabled="isSubmitting"
            class="px-4 py-2 rounded-lg bg-brand-500 text-white font-semibold hover:bg-brand-600 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
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
  form: {
    status_mode: string
    status: string
    status_reason: string
  }
  isSubmitting: boolean
}>()

defineEmits<{
  (e: 'close'): void
  (e: 'submit'): void
}>()
</script>
