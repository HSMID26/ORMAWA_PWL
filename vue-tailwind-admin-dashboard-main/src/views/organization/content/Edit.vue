<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { postService } from '@/services/postService'
import { activityService } from '@/services/activityService'
import { announcementService } from '@/services/announcementService'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const userRole = computed(() => authStore.role)

const contentType = ref<'post' | 'activity' | 'announcement'>('post')
const contentId = ref<number>(0)

// Form Fields
const title = ref('')
const content = ref('')
const excerpt = ref('')
const cover_image = ref('')
const meta_title = ref('')
const meta_description = ref('')
const activity_date = ref('')
const activity_description = ref('')
const announcement_date = ref('')
const priority = ref<'low'|'normal'|'high'|'urgent'>('normal')
const currentStatus = ref('draft')

const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')

const canPublish = computed(() => ['Super Admin', 'Admin Organisasi'].includes(userRole.value || ''))

onMounted(async () => {
  contentType.value = route.params.type as any || 'post'
  contentId.value = Number(route.params.id)
  
  if (!contentId.value) {
    errorMessage.value = 'ID tidak valid'
    isLoading.value = false
    return
  }

  await loadData()
})

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    if (contentType.value === 'post') {
      const data = await postService.getById(contentId.value)
      title.value = data.judul
      content.value = data.konten
      excerpt.value = data.excerpt || ''
      cover_image.value = data.cover_image || ''
      meta_title.value = data.meta_title || ''
      meta_description.value = data.meta_description || ''
      currentStatus.value = data.status
    } else if (contentType.value === 'activity') {
      const data = await activityService.getById(contentId.value)
      title.value = data.judul
      activity_description.value = data.deskripsi
      activity_date.value = data.tanggal_pelaksanaan ? data.tanggal_pelaksanaan.split('T')[0] : ''
      currentStatus.value = data.status
    } else if (contentType.value === 'announcement') {
      const data = await announcementService.getById(contentId.value)
      title.value = data.title
      content.value = data.content
      announcement_date.value = data.effective_date ? data.effective_date.split('T')[0] : ''
      priority.value = data.priority
      meta_title.value = data.meta_title || ''
      meta_description.value = data.meta_description || ''
      currentStatus.value = data.status
    }
  } catch (error: any) {
    console.error('Failed to load', error)
    errorMessage.value = error.response?.data?.message || 'Gagal memuat data.'
  } finally {
    isLoading.value = false
  }
}

const saveContent = async (status: 'draft' | 'review' | 'published' | 'rejected') => {
  isSubmitting.value = true
  errorMessage.value = ''
  
  try {
    if (contentType.value === 'post') {
      await postService.update(contentId.value, {
        judul: title.value,
        konten: content.value,
        excerpt: excerpt.value,
        cover_image: cover_image.value,
        meta_title: meta_title.value,
        meta_description: meta_description.value,
        status: status as any
      })
    } else if (contentType.value === 'activity') {
      await activityService.update(contentId.value, {
        judul: title.value,
        deskripsi: activity_description.value,
        tanggal_pelaksanaan: activity_date.value,
        status: status as any
      })
    } else if (contentType.value === 'announcement') {
      await announcementService.update(contentId.value, {
        title: title.value,
        content: content.value,
        effective_date: announcement_date.value || null,
        priority: priority.value,
        meta_title: meta_title.value,
        meta_description: meta_description.value,
        status: status
      })
    }
    
    router.push('/organization/content')
  } catch (error: any) {
    console.error('Save failed', error)
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat menyimpan konten.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="`Edit ${contentType === 'post' ? 'Berita' : (contentType === 'activity' ? 'Agenda' : 'Pengumuman')}`" />

    <div class="mb-6 bg-white dark:bg-boxdark rounded-lg p-6 shadow-default">
      <div v-if="isLoading" class="py-12 text-center text-gray-500">
        Memuat data...
      </div>
      
      <div v-else>
        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 text-sm">
          {{ errorMessage }}
        </div>

        <div class="mb-4">
          <span class="inline-block px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs dark:bg-gray-700 dark:text-gray-300">
            Status saat ini: <strong>{{ currentStatus.toUpperCase() }}</strong>
          </span>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="mb-2.5 block text-black dark:text-white">Judul Konten</label>
            <input 
              type="text" 
              v-model="title" 
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            />
          </div>

          <!-- POST SPECIFIC -->
          <template v-if="contentType === 'post'">
            <div class="md:col-span-2">
              <label class="mb-2.5 block text-black dark:text-white">Ringkasan (Excerpt)</label>
              <textarea 
                v-model="excerpt" 
                rows="2"
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              ></textarea>
            </div>
            <div class="md:col-span-2">
              <label class="mb-2.5 block text-black dark:text-white">Konten Berita</label>
              <textarea 
                v-model="content" 
                rows="6"
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              ></textarea>
            </div>
            <div class="md:col-span-2">
              <label class="mb-2.5 block text-black dark:text-white">URL Cover Image</label>
              <input 
                type="text" 
                v-model="cover_image" 
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              />
            </div>
          </template>

          <!-- ACTIVITY SPECIFIC -->
          <template v-if="contentType === 'activity'">
            <div class="md:col-span-2">
              <label class="mb-2.5 block text-black dark:text-white">Deskripsi Kegiatan</label>
              <textarea 
                v-model="activity_description" 
                rows="6"
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              ></textarea>
            </div>
            <div>
              <label class="mb-2.5 block text-black dark:text-white">Tanggal Pelaksanaan</label>
              <input 
                type="date" 
                v-model="activity_date" 
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              />
            </div>
          </template>

          <!-- ANNOUNCEMENT SPECIFIC -->
          <template v-if="contentType === 'announcement'">
            <div class="md:col-span-2">
              <label class="mb-2.5 block text-black dark:text-white">Isi Pengumuman</label>
              <textarea 
                v-model="content" 
                rows="6"
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              ></textarea>
            </div>
            <div>
              <label class="mb-2.5 block text-black dark:text-white">Tanggal Efektif (Opsional)</label>
              <input 
                type="date" 
                v-model="announcement_date" 
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              />
            </div>
            <div>
              <label class="mb-2.5 block text-black dark:text-white">Prioritas</label>
              <select 
                v-model="priority"
                class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
              >
                <option value="low">Rendah (Low)</option>
                <option value="normal">Normal</option>
                <option value="high">Tinggi (High)</option>
                <option value="urgent">Mendesak (Urgent)</option>
              </select>
            </div>
          </template>

          <!-- SEO FIELDS -->
          <template v-if="contentType === 'post' || contentType === 'announcement'">
            <div class="md:col-span-2 mt-4 border-t border-stroke pt-4 dark:border-form-strokedark">
              <h4 class="mb-4 text-lg font-semibold text-black dark:text-white">Pengaturan SEO (Opsional)</h4>
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                  <label class="mb-2.5 block text-black dark:text-white">Meta Title</label>
                  <input 
                    type="text" 
                    v-model="meta_title" 
                    class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="mb-2.5 block text-black dark:text-white">Meta Description</label>
                  <textarea 
                    v-model="meta_description" 
                    rows="2"
                    class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                  ></textarea>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-wrap gap-4 pt-4 border-t border-stroke dark:border-form-strokedark justify-end">
          <button 
            @click="saveContent('draft')" 
            :disabled="isSubmitting"
            class="rounded px-6 py-2 border border-primary text-primary hover:bg-primary hover:text-white disabled:opacity-50 transition-colors"
          >
            Simpan sbg Draft
          </button>
          
          <button 
            @click="saveContent('review')" 
            :disabled="isSubmitting"
            class="rounded px-6 py-2 bg-secondary text-white hover:bg-opacity-90 disabled:opacity-50 transition-colors"
          >
            Kirim untuk Review
          </button>

          <template v-if="canPublish">
            <button 
              @click="saveContent('published')" 
              :disabled="isSubmitting"
              class="rounded px-6 py-2 bg-success text-white hover:bg-opacity-90 disabled:opacity-50 transition-colors"
            >
              Publish Sekarang
            </button>
            <button 
              @click="saveContent('rejected')" 
              :disabled="isSubmitting"
              class="rounded px-6 py-2 bg-danger text-white hover:bg-opacity-90 disabled:opacity-50 transition-colors"
            >
              Reject (Tolak)
            </button>
          </template>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
