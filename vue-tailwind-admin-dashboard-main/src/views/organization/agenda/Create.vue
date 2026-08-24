<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Tambah Agenda Baru'" />

    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <CalendarPlusIcon class="h-5 w-5 text-indigo-500" />
          Formulir Agenda Baru
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
          Jadwalkan kegiatan organisasi, rapat, pelantikan, atau seminar.
        </p>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
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
              placeholder="Contoh: Rapat Kerja Pengurus 2026 / Workshop UI/UX"
              class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <!-- Tanggal & Waktu -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                Waktu / Jam Acara
              </label>
              <input
                v-model="form.waktu"
                type="text"
                placeholder="Contoh: 09:00 - 15:00 WIB"
                class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Jenis Lokasi -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
              Jenis Lokasi Acara
            </label>
            <div class="flex items-center gap-4 mb-2">
              <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                <input type="radio" value="offline" v-model="locationType" class="text-brand-600 focus:ring-brand-500" />
                <span>Tatap Muka (Offline)</span>
              </label>
              <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                <input type="radio" value="online" v-model="locationType" class="text-brand-600 focus:ring-brand-500" />
                <span>Daring / Online (Zoom / Meet)</span>
              </label>
            </div>

            <input
              v-model="form.lokasi"
              type="text"
              :placeholder="locationType === 'offline' ? 'Contoh: Aula Gedung B Lt. 3 Kampus' : 'Contoh: https://zoom.us/j/123456789 (Passcode: 1234)'"
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
              placeholder="Tuliskan tujuan agenda, target peserta, susunan acara/rundown singkat, serta instruksi kehadiran..."
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
              <option value="published">Published (Tampil di Website & Kalender)</option>
              <option value="draft">Draft (Arsip Internal)</option>
            </select>
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
import { activityService } from '@/services/activityService'
import { useToastStore } from '@/stores/toast'
import { CalendarPlusIcon } from 'lucide-vue-next'

const router = useRouter()
const toastStore = useToastStore()

const isSubmitting = ref(false)
const locationType = ref<'offline' | 'online'>('offline')

const form = ref({
  judul: '',
  tanggal_pelaksanaan: '',
  waktu: '',
  lokasi: '',
  deskripsi: '',
  status: 'published' as 'draft' | 'published'
})

const submitForm = async () => {
  if (!form.value.judul.trim() || !form.value.tanggal_pelaksanaan || !form.value.deskripsi.trim()) {
    toastStore.error('Harap lengkapi semua kolom bertanda bintang.')
    return
  }

  isSubmitting.value = true
  try {
    let fullDescription = form.value.deskripsi
    if (form.value.waktu || form.value.lokasi) {
      fullDescription += `\n\n📌 Waktu: ${form.value.waktu || 'Sesuai Jadwal'}\n📍 Lokasi: ${form.value.lokasi || 'Kampus'}`
    }

    await activityService.create({
      judul: form.value.judul,
      tanggal_pelaksanaan: form.value.tanggal_pelaksanaan,
      deskripsi: fullDescription,
      status: form.value.status
    })

    toastStore.success('Agenda kegiatan berhasil ditambahkan!')
    router.push('/organization/agenda')
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menambahkan agenda.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
