<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
  >
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
      <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
        <h3 class="font-bold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Data Anggota' : 'Tambah Anggota Baru' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <XIcon class="h-5 w-5" />
        </button>
      </div>

      <form @submit.prevent="$emit('submit')" class="space-y-3.5 text-xs">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">NIM (Nomor Induk Mahasiswa) *</label>
          <input
            v-model="form.nim"
            required
            type="text"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap *</label>
          <input
            v-model="form.nama"
            required
            type="text"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Program Studi / Jurusan</label>
            <input
              v-model="form.jurusan"
              type="text"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Angkatan</label>
            <input
              v-model="form.angkatan"
              type="text"
              placeholder="misal: 2024"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon</label>
            <input
              v-model="form.telepon"
              type="text"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Keanggotaan</label>
            <select
              v-model="form.status"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
              <option value="alumni">Alumni</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tautkan Akun Sistem (User)</label>
            <select
              v-model="form.user_id"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            >
              <option :value="null">-- Tidak Ditautkan --</option>
              <option v-for="u in linkableUsers" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
          <textarea
            v-model="form.catatan"
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
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Anggota' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { XIcon } from 'lucide-vue-next'
import type { LinkableUser } from '@/services/committeeService'

defineProps<{
  isOpen: boolean
  isEditing: boolean
  form: {
    nim: string
    nama: string
    jurusan: string
    angkatan: string
    email: string
    telepon: string
    status: string
    user_id: number | null
    catatan: string
  }
  isSubmitting: boolean
  linkableUsers: LinkableUser[]
}>()

defineEmits<{
  (e: 'close'): void
  (e: 'submit'): void
}>()
</script>
