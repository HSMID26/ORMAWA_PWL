<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
        Edit Artikel
      </h3>
      
      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500">
        Memuat artikel...
      </div>

      <form v-else @submit.prevent="submitPost" class="space-y-6">
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
import { postService } from '@/services/postService'
import { mediaService } from '@/services/mediaService'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const route = useRoute()
const toastStore = useToastStore()
const currentPageTitle = ref('Edit Artikel')

const isLoading = ref(true)
const isSubmitting = ref(false)

const form = ref({
  judul: '',
  konten: '',
  cover_image: '',
  status: 'draft'
})

onMounted(async () => {
  const id = Number(route.params.id)
  if (isNaN(id)) {
    toastStore.error('ID artikel tidak valid.')
    router.push('/org/posts')
    return
  }
  
  try {
    const post = await postService.getById(id)
    form.value = {
      judul: post.judul,
      konten: post.konten,
      cover_image: post.cover_image || '',
      status: post.status,
    }
  } catch (error: any) {
    toastStore.error('Gagal memuat artikel.')
    router.push('/org/posts')
  } finally {
    isLoading.value = false
  }
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
    target.value = ''
  }
}

const submitPost = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  const id = Number(route.params.id)
  try {
    await postService.update(id, form.value)
    toastStore.success('Data berhasil diperbarui.')
    router.push('/organization/posts')
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Data gagal diperbarui.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
