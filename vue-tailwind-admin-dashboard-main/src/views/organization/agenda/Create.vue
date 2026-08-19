<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
        Tambah Agenda Kegiatan
      </h3>

      <form @submit.prevent="submitAgenda" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kegiatan</label>
          <input v-model="form.judul" required type="text" placeholder="Masukkan nama kegiatan" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal & Waktu Pelaksanaan</label>
          <input v-model="form.tanggal_pelaksanaan" required type="datetime-local" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat</label>
          <textarea v-model="form.deskripsi" required rows="5" placeholder="Tulis deskripsi kegiatan..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white resize-y"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
          <ToggleSwitch
            v-model="form.status"
            trueValue="published"
            falseValue="draft"
            activeLabel="Published"
            inactiveLabel="Draft"
          />
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
          <router-link to="/organization/agenda" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            Batal
          </router-link>
          <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50" :disabled="isSubmitting">
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Agenda' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import ToggleSwitch from '@/components/forms/FormElements/ToggleSwitch.vue'
import { activityService } from '@/services/activityService'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const toastStore = useToastStore()
const currentPageTitle = ref('Tambah Agenda')

const isSubmitting = ref(false)

const form = ref({
  judul: '',
  deskripsi: '',
  tanggal_pelaksanaan: '',
  status: 'draft'
})

const submitAgenda = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  try {
    // Convert datetime-local format to appropriate backend string (often acceptable as YYYY-MM-DD HH:mm:ss)
    const payload = {
      ...form.value,
      tanggal_pelaksanaan: form.value.tanggal_pelaksanaan.replace('T', ' ')
    }

    await activityService.create(payload)
    toastStore.success('Agenda berhasil ditambahkan.')
    router.push('/organization/agenda')
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Data gagal disimpan.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
