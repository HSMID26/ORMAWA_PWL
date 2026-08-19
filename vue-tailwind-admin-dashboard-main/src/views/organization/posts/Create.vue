<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
        Tulis Artikel Baru
      </h3>

      <form @submit.prevent="submitPost" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Artikel</label>
          <input v-model="form.judul" required type="text" placeholder="Masukkan judul artikel" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Image</label>
          <input type="file" @change="handleFileUpload" accept="image/*" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" />
          <div v-if="form.cover_image" class="mt-3">
            <img :src="form.cover_image" alt="Preview" class="h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konten Artikel</label>
          <textarea v-model="form.konten" required rows="10" placeholder="Tulis isi artikel di sini..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white resize-y"></textarea>
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
          <router-link to="/organization/posts" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            Batal
          </router-link>
          <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50" :disabled="isSubmitting">
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Artikel' }}
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
import { postService } from '@/services/postService'
import { mediaService } from '@/services/mediaService'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const toastStore = useToastStore()
const currentPageTitle = ref('Tulis Artikel')

const isSubmitting = ref(false)

const form = ref({
  judul: '',
  konten: '',
  cover_image: '',
  status: 'draft'
})

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  
  const file = target.files[0]
  
  try {
    toastStore.info('Mengunggah gambar...')
    const res = await mediaService.upload(file)
    if (res.url) {
      form.value.cover_image = res.url
      toastStore.success('Gambar berhasil diunggah')
    }
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Gagal mengunggah gambar.')
    target.value = '' // reset input
  }
}

const submitPost = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  try {
    await postService.create(form.value)
    toastStore.success('Berita berhasil dipublikasikan.')
    router.push('/organization/posts')
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Data gagal disimpan.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
