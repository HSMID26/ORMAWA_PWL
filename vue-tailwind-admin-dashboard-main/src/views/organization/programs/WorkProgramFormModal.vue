<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4"
    @click.self="emit('close')"
  >
    <section
      role="dialog"
      aria-modal="true"
      aria-labelledby="work-program-title"
      class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900"
    >
      <header class="mb-5 flex items-start justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
        <div>
          <h2 id="work-program-title" class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ program ? 'Ubah Program Kerja' : 'Program Kerja Baru' }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Catat tujuan, penanggung jawab, sasaran, dan estimasi anggaran.
          </p>
        </div>
        <button type="button" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:hover:bg-gray-800" aria-label="Tutup" @click="emit('close')">
          <XIcon class="h-5 w-5" />
        </button>
      </header>

      <form class="space-y-4" @submit.prevent="submit">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Nama program
          <input v-model.trim="form.name" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:text-white" />
        </label>

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tujuan
          <textarea v-model.trim="form.objective" required rows="3" maxlength="10000" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:text-white" />
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Periode (tahun)
            <input v-model.number="form.period_year" type="number" min="2000" max="2100" required class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            PIC
            <select v-model="form.responsible_member_id" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
              <option :value="null">Belum ditentukan</option>
              <option v-for="member in members" :key="member.id" :value="member.id">{{ member.nama }}</option>
            </select>
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Target peserta
            <input v-model.number="form.target_participants" type="number" min="0" required class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Estimasi anggaran (Rp)
            <input v-model.number="form.estimated_budget" type="number" min="0" step="1000" required class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:col-span-2">
            Status
            <select v-model="form.status" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
              <option value="planned">Belum terlaksana</option>
              <option value="in_progress">Berjalan</option>
              <option value="completed">Selesai</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
          </label>
        </div>

        <footer class="flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-800">
          <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="emit('close')">
            Batal
          </button>
          <button type="submit" :disabled="saving" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
            {{ saving ? 'Menyimpan…' : 'Simpan Program' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'
import { XIcon } from 'lucide-vue-next'
import type { Member } from '@/types/api'
import type { WorkProgram, WorkProgramInput } from '@/services/workProgramService'

const props = defineProps<{
  open: boolean
  saving: boolean
  program: WorkProgram | null
  members: Pick<Member, 'id' | 'nama'>[]
}>()

const emit = defineEmits<{
  close: []
  submit: [payload: WorkProgramInput]
}>()

const emptyForm = () => ({
  name: '',
  objective: '',
  period_year: new Date().getFullYear(),
  responsible_member_id: null as number | null,
  target_participants: 0,
  estimated_budget: 0,
  status: 'planned' as WorkProgram['status'],
})

const form = reactive(emptyForm())

watch(
  () => [props.open, props.program] as const,
  ([open, program]) => {
    if (!open) return
    Object.assign(form, program ? {
      name: program.name,
      objective: program.objective,
      period_year: program.period_year,
      responsible_member_id: program.responsible_member_id,
      target_participants: program.target_participants,
      estimated_budget: Number(program.estimated_budget),
      status: program.status,
    } : emptyForm())
  },
  { immediate: true },
)

const submit = () => {
  emit('submit', { ...form })
}
</script>
