<template>
  <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div v-if="errorMessage" class="p-5 text-sm text-rose-700 dark:text-rose-300">{{ errorMessage }}</div>
    <div v-else-if="loading" class="p-5 text-sm text-gray-500">Memuat arsip surat…</div>
    <div v-else-if="letters.length === 0" class="p-10 text-center">
      <p class="font-medium text-gray-800 dark:text-gray-200">Belum ada surat pada filter ini.</p>
      <p class="mt-1 text-sm text-gray-500">Catat surat masuk atau keluar untuk mulai membangun arsip organisasi.</p>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[900px] text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
          <tr>
            <th class="px-4 py-3">Nomor / Perihal</th>
            <th v-if="showOrganization" class="px-4 py-3">Organisasi</th>
            <th class="px-4 py-3">Jenis</th>
            <th class="px-4 py-3">Pengirim → Penerima</th>
            <th class="px-4 py-3">Tanggal</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          <tr v-for="letter in letters" :key="letter.id" class="align-top">
            <td class="px-4 py-4">
              <p class="font-medium text-gray-900 dark:text-white">{{ letter.reference_number }}</p>
              <p class="mt-1 max-w-xs text-xs leading-5 text-gray-600 dark:text-gray-400">{{ letter.subject }}</p>
              <p v-if="letter.review_note" class="mt-1 max-w-xs text-xs text-amber-700 dark:text-amber-300">Catatan: {{ letter.review_note }}</p>
            </td>
            <td v-if="showOrganization" class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ letter.organization?.nama || '—' }}</td>
            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ letter.type === 'incoming' ? 'Masuk' : 'Keluar' }}</td>
            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ letter.sender }} <span class="text-gray-400">→</span> {{ letter.recipient }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ formatDate(letter.letter_date) }}</td>
            <td class="px-4 py-4"><span class="rounded-md px-2 py-1 text-xs font-medium" :class="statusClass(letter.status)">{{ statusLabel(letter.status) }}</span></td>
            <td class="px-4 py-4">
              <div class="flex flex-wrap justify-end gap-2">
                <button v-if="letter.attachment_name" type="button" class="rounded-md border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="emit('download', letter)">Lampiran</button>
                <button v-if="canUpdate && editable(letter)" type="button" class="rounded-md border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="emit('edit', letter)">Ubah</button>
                <button v-if="canUpdate && editable(letter)" type="button" class="rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300" @click="emit('submit', letter)">Ajukan</button>
                <button v-if="canReview && letter.status === 'submitted'" type="button" class="rounded-md bg-blue-700 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-blue-800" @click="emit('review', letter)">Tinjau</button>
                <button v-if="canDelete && deletable(letter)" type="button" class="rounded-md border border-rose-200 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300" @click="emit('remove', letter)">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <footer v-if="meta && meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm dark:border-gray-800">
      <span class="text-gray-500">Halaman {{ meta.current_page }} dari {{ meta.last_page }} · {{ meta.total }} surat</span>
      <div class="flex gap-2">
        <button type="button" :disabled="meta.current_page <= 1" class="rounded-md border border-gray-300 px-3 py-1.5 disabled:opacity-40 dark:border-gray-700" @click="emit('page', meta.current_page - 1)">Sebelumnya</button>
        <button type="button" :disabled="meta.current_page >= meta.last_page" class="rounded-md border border-gray-300 px-3 py-1.5 disabled:opacity-40 dark:border-gray-700" @click="emit('page', meta.current_page + 1)">Berikutnya</button>
      </div>
    </footer>
  </section>
</template>

<script setup lang="ts">
import type { Correspondence } from '@/services/correspondenceService'

defineProps<{
  letters: Correspondence[]
  loading: boolean
  errorMessage: string
  meta: { current_page: number; last_page: number; per_page: number; total: number } | null
  canUpdate: boolean
  canReview: boolean
  canDelete: boolean
  showOrganization: boolean
}>()
const emit = defineEmits<{
  page: [page: number]
  edit: [letter: Correspondence]
  submit: [letter: Correspondence]
  review: [letter: Correspondence]
  remove: [letter: Correspondence]
  download: [letter: Correspondence]
}>()

const editable = (letter: Correspondence) => letter.status === 'draft' || letter.status === 'revision_requested'
const deletable = (letter: Correspondence) => ['draft', 'rejected', 'revision_requested'].includes(letter.status)
const statusLabel = (status: Correspondence['status']) => ({
  draft: 'Draf',
  submitted: 'Menunggu tinjauan',
  approved: 'Disetujui',
  rejected: 'Ditolak',
  revision_requested: 'Perlu revisi',
}[status])
const statusClass = (status: Correspondence['status']) => ({
  draft: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
  submitted: 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
  approved: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
  rejected: 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
  revision_requested: 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
}[status])
const formatDate = (value: string) => new Intl.DateTimeFormat('id-ID', {
  day: 'numeric',
  month: 'short',
  year: 'numeric',
}).format(new Date(`${value.slice(0, 10)}T00:00:00`))
</script>
