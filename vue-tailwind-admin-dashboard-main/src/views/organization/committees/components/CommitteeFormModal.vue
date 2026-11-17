<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8">
      <!-- Close Button -->
      <button
        @click="$emit('close')"
        class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer"
      >
        <XIcon class="h-5 w-5" />
      </button>

      <h3 class="text-lg font-bold text-gray-900 dark:text-white">
        {{ isEditing ? 'Edit Data Pengurus' : 'Tambah Pengurus Baru' }}
      </h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Lengkapi formulir informasi kepengurusan organisasi di bawah ini.
      </p>

      <form @submit.prevent="$emit('submit')" class="mt-5 space-y-4">
        <!-- Nama Lengkap Pengurus -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
            Nama Pengurus <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="formData.name"
            type="text"
            required
            placeholder="Contoh: Fahkrie"
            class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
          />
        </div>

        <!-- Jabatan -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
            Jabatan <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="formData.position"
            type="text"
            required
            placeholder="Contoh: Ketua, Wakil Ketua, Sekretaris, Bendahara"
            class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
          />
        </div>

        <!-- Divisi / Departemen -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
            Divisi / Departemen
          </label>
          <input
            v-model="formData.department"
            type="text"
            placeholder="Contoh: Ketua, Hubungan Masyarakat, Pengembangan SDM"
            class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
          />
        </div>

        <!-- Periode Kepengurusan -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
            Periode Kepengurusan <span class="text-rose-500">*</span>
          </label>
          <select
            v-model="formData.organization_period_id"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option :value="undefined" disabled>-- Pilih Periode Kepengurusan --</option>
            <option
              v-for="period in periodsList"
              :key="period.id"
              :value="period.id"
            >
              {{ period.period_name }} ({{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}) - [{{ period.status === 'active' ? 'Aktif' : period.status }}]
            </option>
          </select>
        </div>

        <!-- Status Kepengurusan -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
            Status Kepengurusan
          </label>
          <select
            v-model="formData.status"
            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="active">Aktif</option>
            <option value="inactive">Non-Aktif</option>
          </select>
        </div>

        <!-- Upload Foto Profil -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
            Foto Profil
          </label>
          <input
            type="file"
            accept="image/*"
            @change="$emit('photoChange', $event)"
            class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300 cursor-pointer"
          />
          <!-- Preview Foto -->
          <div v-if="photoPreview || formData.photo_url" class="mt-2 flex items-center gap-3">
            <img
              :src="photoPreview || formData.photo_url"
              alt="Preview"
              class="h-16 w-16 rounded-full object-cover border border-gray-200 dark:border-gray-700"
            />
            <span class="text-xs text-gray-400">Preview foto profil</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
          <button
            type="button"
            @click="$emit('close')"
            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 cursor-pointer"
          >
            {{ isSubmitting ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Pengurus') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { XIcon } from 'lucide-vue-next'
import type { OrganizationPeriod } from '@/types/api'
import type { CommitteeFormData } from './types'

defineProps<{
  isOpen: boolean
  isEditing: boolean
  isSubmitting: boolean
  formData: CommitteeFormData
  photoPreview: string | null
  periodsList: OrganizationPeriod[]
  formatDate: (dateStr?: string) => string
}>()

defineEmits<{
  (e: 'close'): void
  (e: 'submit'): void
  (e: 'photoChange', event: Event): void
}>()
</script>
