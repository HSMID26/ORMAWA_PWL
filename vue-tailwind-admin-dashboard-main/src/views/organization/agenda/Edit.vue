<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Edit Agenda'" />

    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <CalendarIcon class="h-5 w-5 text-indigo-500" />
          Edit Agenda Kegiatan
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
          Perbarui rincian, waktu, atau lokasi pelaksanaan agenda.
        </p>
      </div>

      <div v-if="isLoading" class="py-12 text-center text-xs text-gray-400">
        Memuat data agenda...
      </div>

      <form v-else @submit.prevent="submitForm" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
          <!-- Judul Kegiatan -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
              Nama Kegiatan / Agenda <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.judul"
              type="text"
              required
              placeholder="Contoh: Rapat Kerja Pengurus 2026"
              class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <!-- Tanggal Pelaksanaan -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
              Tanggal Pelaksanaan <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.tanggal_pelaksanaan"
              type="date"
              required
              class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <!-- Deskripsi & Rundown -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
              Deskripsi & Rincian Kegiatan <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.deskripsi"
              required
              rows="6"
              placeholder="Tuliskan detail agenda..."
              class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-xs leading-relaxed text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            ></textarea>
          </div>

          <!-- Status Publikasi -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
              Status Agenda
            </label>
            <select
              v-model="form.status"
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option v-if="canPublish" value="published">Published (Tampil di Website)</option>
              <option value="draft">Draft (Arsip Internal)</option>
            </select>
            <p v-if="!canPublish" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">
              *Sebagai Editor, perubahan status ke Published memerlukan persetujuan Admin Organisasi.
            </p>
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3">
          <router-link
            to="/organization/agenda"
            class="rounded-xl bg-gray-100 px-5 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Batal
          </router-link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="rounded-xl bg-brand-600 px-6 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Perbarui Agenda' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { activityService } from '@/services/activityService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { CalendarIcon } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toastStore = useToastStore()

const canPublish = computed(() => authStore.hasPermission('agenda.publish') || authStore.role === 'Super Admin')

const agendaId = Number(route.params.id)
const isLoading = ref(true)
const isSubmitting = ref(false)

const form = ref({
  judul: '',
  tanggal_pelaksanaan: '',
  deskripsi: '',
  status: 'draft' as 'draft' | 'published'
})

const loadAgenda = async () => {
  isLoading.value = true
  try {
    const list = await activityService.list()
    const item = list.find((a: any) => a.id === agendaId)
    if (!item) {
      toastStore.error('Agenda kegiatan tidak ditemukan.')
      router.push('/organization/agenda')
      return
    }

    form.value = {
      judul: item.judul,
      tanggal_pelaksanaan: item.tanggal_pelaksanaan.split('T')[0],
      deskripsi: item.deskripsi,
      status: item.status === 'draft' ? 'draft' : 'published'
    }
  } catch (error: any) {
    toastStore.error('Gagal memuat agenda: ' + error.message)
    router.push('/organization/agenda')
  } finally {
    isLoading.value = false
  }
}

const submitForm = async () => {
  if (!form.value.judul.trim() || !form.value.tanggal_pelaksanaan || !form.value.deskripsi.trim()) {
    toastStore.error('Harap lengkapi semua kolom bertanda bintang.')
    return
  }

  isSubmitting.value = true
  try {
    await activityService.update(agendaId, {
      judul: form.value.judul,
      tanggal_pelaksanaan: form.value.tanggal_pelaksanaan,
      deskripsi: form.value.deskripsi,
      status: form.value.status
    })

    toastStore.success('Agenda kegiatan berhasil diperbarui!')
    router.push('/organization/agenda')
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal memperbarui agenda.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadAgenda()
})
</script>
