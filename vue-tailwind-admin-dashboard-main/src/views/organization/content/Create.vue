<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { postService } from '@/services/postService'
import { activityService } from '@/services/activityService'
import { announcementService } from '@/services/announcementService'
import { taxonomyService } from '@/services/taxonomyService'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import type { Category, Tag } from '@/types/api'

const router = useRouter()
const authStore = useAuthStore()
const userRole = computed(() => authStore.role)

const contentType = ref<'post' | 'activity' | 'announcement'>('post')

// Form Fields
const title = ref('')
const content = ref('')
const excerpt = ref('')
const cover_image = ref('')
const meta_title = ref('')
const meta_description = ref('')
const category_id = ref<number | undefined>(undefined)
const selectedTags = ref<number[]>([])
const activity_date = ref('')
const activity_description = ref('')
const announcement_date = ref('')
const announcement_expires_at = ref('')
const priority = ref<'low'|'normal'|'high'|'urgent'>('normal')

const categoriesList = ref<Category[]>([])
const tagsList = ref<Tag[]>([])

onMounted(async () => {
  try {
    const [cats, tags] = await Promise.all([
      taxonomyService.getCategories(),
      taxonomyService.getTags(),
    ])
    categoriesList.value = cats
    tagsList.value = tags
  } catch (err) {
    console.error('Failed to load categories or tags', err)
  }
})

const isSubmitting = ref(false)
const errorMessage = ref('')

const canPublish = computed(() => ['Super Admin', 'Admin Organisasi'].includes(userRole.value || ''))

const saveContent = async (status: 'draft' | 'review' | 'published' | 'rejected') => {
  isSubmitting.value = true
  errorMessage.value = ''
  
  try {
    if (contentType.value === 'post') {
      await postService.create({
        judul: title.value,
        konten: content.value,
        excerpt: excerpt.value,
        cover_image: cover_image.value,
        meta_title: meta_title.value,
        meta_description: meta_description.value,
        category_id: category_id.value,
        tags: selectedTags.value as any,
        status: status as any
      })
    } else if (contentType.value === 'activity') {
      await activityService.create({
        judul: title.value,
        deskripsi: activity_description.value,
        tanggal_pelaksanaan: activity_date.value,
        status: status as any
      })
    } else if (contentType.value === 'announcement') {
      await announcementService.create({
        title: title.value,
        content: content.value,
        effective_date: announcement_date.value || null,
        expires_at: announcement_expires_at.value || null,
        priority: priority.value,
        meta_title: meta_title.value,
        meta_description: meta_description.value,
        status: status
      })
    }
    
    // Idealnya tambah Toast success disini
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
    <PageBreadcrumb pageTitle="Buat Konten" />

    <div class="mb-6 bg-white dark:bg-boxdark rounded-lg p-6 shadow-default">
      <!-- Error Message -->
      <div v-if="errorMessage" class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 text-sm">
        {{ errorMessage }}
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <label class="mb-2.5 block text-black dark:text-white">Tipe Konten</label>
          <select 
            v-model="contentType" 
            class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
          >
            <option value="post">Berita / Artikel</option>
            <option value="activity">Agenda Kegiatan</option>
            <option value="announcement">Pengumuman</option>
          </select>
        </div>

        <div>
          <label class="mb-2.5 block text-black dark:text-white">Judul Konten</label>
          <input 
            type="text" 
            v-model="title" 
            placeholder="Masukkan judul konten" 
            class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
          />
        </div>

        <!-- POST SPECIFIC -->
        <template v-if="contentType === 'post'">
          <div>
            <label class="mb-2.5 block text-black dark:text-white">Kategori</label>
            <select 
              v-model="category_id" 
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            >
              <option :value="null">Pilih Kategori</option>
              <option v-for="c in categoriesList" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <label class="mb-2.5 block text-black dark:text-white">Cover Image URL</label>
            <input 
              type="text" 
              v-model="cover_image" 
              placeholder="https://example.com/image.jpg" 
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            />
          </div>

          <div class="md:col-span-2">
            <label class="mb-2.5 block text-black dark:text-white">Tags</label>
            <div class="flex flex-wrap gap-2">
              <button 
                type="button"
                v-for="t in tagsList" 
                :key="t.id"
                @click="selectedTags.includes(t.id) ? selectedTags.splice(selectedTags.indexOf(t.id), 1) : selectedTags.push(t.id)"
                :class="[
                  'px-3 py-1 rounded-full text-xs font-medium transition-colors',
                  selectedTags.includes(t.id) 
                    ? 'bg-primary text-white' 
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-meta-4 dark:text-gray-300'
                ]"
              >
                #{{ t.name }}
              </button>
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="mb-2.5 block text-black dark:text-white">Ringkasan (Excerpt)</label>
            <textarea 
              v-model="excerpt" 
              rows="2"
              placeholder="Ringkasan singkat untuk tampilan kartu..."
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            ></textarea>
          </div>
          <div class="md:col-span-2">
            <label class="mb-2.5 block text-black dark:text-white">Konten Berita</label>
            <textarea 
              v-model="content" 
              rows="6"
              placeholder="Tulis lengkap isi artikel atau berita..."
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            ></textarea>
          </div>
        </template>

        <!-- ACTIVITY SPECIFIC -->
        <template v-if="contentType === 'activity'">
          <div class="md:col-span-2">
            <label class="mb-2.5 block text-black dark:text-white">Deskripsi Kegiatan</label>
            <textarea 
              v-model="activity_description" 
              rows="6"
              placeholder="Jelaskan detail tujuan dan agenda kegiatan..."
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
            <label class="mb-2.5 block text-black dark:text-white">Tanggal Efektif (Mulai Berlaku)</label>
            <input 
              type="date" 
              v-model="announcement_date" 
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            />
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika langsung tayang saat diterbitkan.</p>
          </div>
          <div>
            <label class="mb-2.5 block text-black dark:text-white">Berlaku Hingga (Opsional)</label>
            <input 
              type="date" 
              v-model="announcement_expires_at" 
              :min="announcement_date || ''"
              class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            />
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika berlaku tanpa batas kedaluwarsa.</p>
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

        <!-- SEO FIELDS (POST & ANNOUNCEMENT) -->
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
        </template>
      </div>
    </div>
  </AdminLayout>
</template>
