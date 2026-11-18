<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4" @click.self="emit('close')">
    <section role="dialog" aria-modal="true" aria-labelledby="correspondence-form-title" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
      <header class="mb-5 flex items-start justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
        <div>
          <h2 id="correspondence-form-title" class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ correspondence ? 'Ubah Surat' : 'Catat Surat Baru' }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Simpan metadata surat dan lampirkan salinan berkasnya.</p>
        </div>
        <button type="button" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:hover:bg-gray-800" aria-label="Tutup" @click="emit('close')">
          <XIcon class="h-5 w-5" />
        </button>
      </header>

      <form class="space-y-4" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
          <label v-if="isSuperAdmin && !correspondence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:col-span-2">
            Organisasi
            <select v-model="organizationId" required class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
              <option :value="null" disabled>Pilih organisasi</option>
              <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{ organization.nama }}</option>
            </select>
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Jenis surat
            <select v-model="form.type" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
              <option value="incoming">Surat masuk</option>
              <option value="outgoing">Surat keluar</option>
            </select>
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nomor surat
            <input v-model.trim="form.reference_number" required maxlength="80" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pengirim
            <input v-model.trim="form.sender" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Penerima
            <input v-model.trim="form.recipient" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Tanggal surat
            <input v-model="form.letter_date" type="date" required class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
          </label>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Lampiran (PDF/DOC/DOCX)
            <input type="file" accept=".pdf,.doc,.docx" class="mt-1 w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-blue-700 dark:text-gray-300" @change="onFileChange" />
            <span v-if="correspondence?.attachment_name" class="mt-1 block text-xs text-gray-500">Lampiran saat ini: {{ correspondence.attachment_name }}</span>
          </label>
        </div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Perihal
          <input v-model.trim="form.subject" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
        </label>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Catatan
          <textarea v-model.trim="form.notes" rows="3" maxlength="10000" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
        </label>

        <footer class="flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-800">
          <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="saving" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 disabled:opacity-60">
            {{ saving ? 'Menyimpan…' : 'Simpan Draf' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { XIcon } from 'lucide-vue-next'
import type { Correspondence, CorrespondenceInput, CorrespondenceOrganization } from '@/services/correspondenceService'

const props = defineProps<{
  open: boolean
  saving: boolean
  correspondence: Correspondence | null
  isSuperAdmin: boolean
  organizations: CorrespondenceOrganization[]
}>()
const emit = defineEmits<{
  close: []
  submit: [payload: CorrespondenceInput]
}>()

const attachment = ref<File | null>(null)
const organizationId = ref<number | null>(null)
const emptyForm = () => ({
  reference_number: '',
  type: 'incoming' as CorrespondenceInput['type'],
  sender: '',
  recipient: '',
  subject: '',
  letter_date: new Date().toISOString().slice(0, 10),
  notes: '',
})
const form = reactive(emptyForm())

watch(
  () => [props.open, props.correspondence] as const,
  ([open, correspondence]) => {
    if (!open) return
    attachment.value = null
    organizationId.value = correspondence?.organization?.id ?? null
    Object.assign(form, correspondence ? {
      reference_number: correspondence.reference_number,
      type: correspondence.type,
      sender: correspondence.sender,
      recipient: correspondence.recipient,
      subject: correspondence.subject,
      letter_date: correspondence.letter_date.slice(0, 10),
      notes: correspondence.notes ?? '',
    } : emptyForm())
  },
  { immediate: true },
)

const onFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  attachment.value = input.files?.[0] ?? null
}

const submit = () => emit('submit', {
  ...form,
  ...(props.isSuperAdmin && !props.correspondence && organizationId.value
    ? { organization_id: organizationId.value }
    : {}),
  attachment: attachment.value,
})
</script>
