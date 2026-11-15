<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
  >
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
        <h3 class="font-bold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Data Pengurus' : 'Tambah Pengurus Baru' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <XIcon class="h-5 w-5" />
        </button>
      </div>

      <form @submit.prevent="$emit('submit')" class="space-y-3.5 text-xs">
        <!-- Pilih Anggota Terdaftar -->
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Anggota Organisasi *</label>
          <select
            v-model="form.member_id"
            @change="$emit('memberSelected')"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          >
            <option :value="null">-- Input Nama Manual / Bukan Anggota Tetap --</option>
            <option v-for="m in linkableMembers" :key="m.id" :value="m.id">
              {{ m.nama }} (NIM: {{ m.nim }}) - {{ m.status === 'active' ? 'Aktif' : 'Nonaktif' }}
            </option>
          </select>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Pengurus *</label>
          <input
            v-model="form.name"
            required
            type="text"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Jabatan *</label>
            <input
              v-model="form.position"
              required
              type="text"
              placeholder="misal: Ketua, Sekretaris"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Departemen / Bidang</label>
            <input
              v-model="form.department"
              type="text"
              placeholder="misal: BPH, Humas"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Periode Kepengurusan *</label>
          <select
            v-model="form.organization_period_id"
            required
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          >
            <option v-for="p in periods" :key="p.id" :value="p.id">
              {{ p.period_name }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai Jabatan</label>
            <input
              v-model="form.start_date"
              type="date"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Berakhir Jabatan</label>
            <input
              v-model="form.end_date"
              type="date"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Mode Evaluasi Status</label>
          <select
            v-model="form.status_mode"
            class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          >
            <option value="auto">Mode Otomatis (Evaluasi dinamis tanggal periode &amp; keaktifan anggota)</option>
            <option value="manual">Mode Manual (Ditetapkan eksplisit oleh pengurus / administrator)</option>
          </select>
        </div>

        <div v-if="form.status_mode === 'manual'" class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-amber-50/70 border border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/40">
          <div>
            <label class="block font-semibold text-amber-950 dark:text-amber-200 mb-1">Status Penempatan *</label>
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
            <input
              v-model="form.status_reason"
              :required="form.status_mode === 'manual'"
              type="text"
              placeholder="misal: Cuti akademik, penugasan khusus"
              class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:outline-hidden dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div v-else class="rounded-xl bg-blue-50/70 p-3 text-xs text-blue-900 border border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/40 dark:text-blue-300">
          <span class="font-bold">Info Mode Otomatis:</span> Keaktifan pengurus akan aktif secara otomatis selama tanggal periode kepengurusan sedang berjalan dan status keanggotaan aktif.
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tautkan Akun Sistem (User Login)</label>
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

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Foto Pengurus</label>
          <input
            type="file"
            accept="image/*"
            @change="$emit('photoSelected', $event)"
            class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300"
          />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan / Keterangan</label>
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
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengurus' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { XIcon } from 'lucide-vue-next'
import type { OrganizationPeriod } from '@/types/api'
import type { LinkableMember, LinkableUser } from '@/services/committeeService'

defineProps<{
  isOpen: boolean
  isEditing: boolean
  form: {
    member_id: number | null
    name: string
    position: string
    department: string
    organization_period_id: number | null
    start_date: string
    end_date: string
    status_mode: string
    status: string
    status_reason: string
    user_id: number | null
    notes: string
  }
  isSubmitting: boolean
  periods: OrganizationPeriod[]
  linkableMembers: LinkableMember[]
  linkableUsers: LinkableUser[]
}>()

defineEmits<{
  (e: 'close'): void
  (e: 'submit'): void
  (e: 'memberSelected'): void
  (e: 'photoSelected', event: Event): void
}>()
</script>
