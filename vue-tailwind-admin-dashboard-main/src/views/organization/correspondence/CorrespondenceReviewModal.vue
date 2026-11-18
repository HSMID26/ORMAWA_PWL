<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4" @click.self="emit('close')">
    <section role="dialog" aria-modal="true" aria-labelledby="correspondence-review-title" class="w-full max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
      <h2 id="correspondence-review-title" class="text-lg font-semibold text-gray-900 dark:text-white">Tinjau Surat</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ correspondence?.reference_number }} · {{ correspondence?.subject }}</p>
      <form class="mt-5 space-y-4" @submit.prevent="submit">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Keputusan
          <select v-model="action" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="approve">Setujui</option>
            <option value="request_revision">Minta revisi</option>
            <option value="reject">Tolak</option>
          </select>
        </label>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Catatan {{ action === 'approve' ? '(opsional)' : '(wajib)' }}
          <textarea v-model.trim="reviewNote" :required="action !== 'approve'" rows="3" maxlength="2000" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-white" />
        </label>
        <footer class="flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-800">
          <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="saving" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-60">{{ saving ? 'Memproses…' : 'Simpan Keputusan' }}</button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Correspondence, CorrespondenceReviewInput } from '@/services/correspondenceService'

const props = defineProps<{
  open: boolean
  saving: boolean
  correspondence: Correspondence | null
}>()
const emit = defineEmits<{
  close: []
  submit: [payload: CorrespondenceReviewInput]
}>()
const action = ref<CorrespondenceReviewInput['action']>('approve')
const reviewNote = ref('')

watch(() => props.open, (open) => {
  if (!open) return
  action.value = 'approve'
  reviewNote.value = ''
})

const submit = () => emit('submit', { action: action.value, review_note: reviewNote.value })
</script>
