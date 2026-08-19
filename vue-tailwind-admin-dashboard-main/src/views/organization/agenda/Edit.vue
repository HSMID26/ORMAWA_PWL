<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
        Edit Agenda Kegiatan
      </h3>
      
      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500">
        Memuat agenda...
      </div>

      <form v-else @submit.prevent="submitAgenda" class="space-y-6">
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
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import ToggleSwitch from '@/components/forms/FormElements/ToggleSwitch.vue'
import { activityService } from '@/services/activityService'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const route = useRoute()
const toastStore = useToastStore()
const currentPageTitle = ref('Edit Agenda')

const isLoading = ref(true)
const isSubmitting = ref(false)

const form = ref({
  judul: '',
  deskripsi: '',
  tanggal_pelaksanaan: '',
  status: 'draft'
})

onMounted(async () => {
  const id = Number(route.params.id)
  if (isNaN(id)) {
    toastStore.error('ID agenda tidak valid.')
    router.push('/org/agenda')
    return
  }
  
  try {
    const agenda = await activityService.getById(id)
    
    // Convert YYYY-MM-DD HH:mm:ss to YYYY-MM-DDTHH:mm for datetime-local
    let datetimeValue = agenda.tanggal_pelaksanaan
    if (datetimeValue && datetimeValue.includes(' ')) {
      datetimeValue = datetimeValue.replace(' ', 'T')
      // slice to remove seconds if any
      if (datetimeValue.length > 16) {
        datetimeValue = datetimeValue.substring(0, 16)
      }
    }

    form.value = {
      judul: agenda.judul,
      deskripsi: agenda.deskripsi,
      tanggal_pelaksanaan: datetimeValue,
      status: agenda.status,
    }
  } catch (error: any) {
    toastStore.error('Gagal memuat agenda.')
    router.push('/org/agenda')
  } finally {
    isLoading.value = false
  }
})

const submitAgenda = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  const id = Number(route.params.id)
  try {
    const payload = {
      ...form.value,
      tanggal_pelaksanaan: form.value.tanggal_pelaksanaan.replace('T', ' ')
    }

    await activityService.update(id, payload)
    toastStore.success('Data berhasil diperbarui.')
    router.push('/organization/agenda')
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Data gagal diperbarui.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
