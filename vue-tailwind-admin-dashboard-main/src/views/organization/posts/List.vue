<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Daftar Berita & Artikel
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola publikasi tulisan, pengumuman, dan liputan kegiatan organisasi.
          </p>
        </div>
        <router-link to="/organization/posts/create" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Tulis Artikel Baru
        </router-link>
      </div>

      <!-- Search & Filter Bar -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari judul artikel atau penulis..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          />
        </div>
        <div class="sm:w-48">
          <select
            v-model="filterStatus"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Status</option>
            <option value="Published">Published</option>
            <option value="Review">Menunggu Review</option>
            <option value="Draft">Draft</option>
          </select>
        </div>
      </div>

      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Memuat artikel...
      </div>
      <div v-else-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
        {{ errorMessage }}
      </div>
      <!-- Tabel Data Artikel -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Judul Artikel</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Kategori</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Penulis</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="post in filteredPosts" 
              :key="post.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-4">
                <p class="text-sm font-semibold text-gray-800 dark:text-white/90 line-clamp-1 max-w-[250px]" :title="post.judul">
                  {{ post.judul }}
                </p>
                <div class="mt-1 flex gap-1">
                  <span class="text-[10px] text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">
                    {{ post.status === 'published' ? 'Published' : 'Draft' }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                -
              </td>
              <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                {{ post.user?.name ?? '-' }}
              </td>
              <td class="px-4 py-4">
                <ToggleSwitch
                  :modelValue="post.status"
                  trueValue="published"
                  falseValue="draft"
                  activeLabel="Published"
                  inactiveLabel="Draft"
                  :disabled="togglingId === post.id"
                  @change="(val) => toggleStatus(post, val)"
                />
              </td>
              <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                {{ post.created_at ? new Date(post.created_at).toLocaleDateString('id-ID') : '-' }}
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex justify-center items-center gap-3">
                  <router-link :to="`/organization/posts/edit/${post.id}`" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors">Edit</router-link>
                  <button @click="deletePost(post.id)" class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors" :disabled="deletingId === post.id">
                    {{ deletingId === post.id ? 'Menghapus...' : 'Hapus' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredPosts.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Artikel tidak ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import ToggleSwitch from '@/components/forms/FormElements/ToggleSwitch.vue'
import { postService } from '@/services/postService'
import { useToastStore } from '@/stores/toast'
import type { Post } from '@/types/api'

const toastStore = useToastStore()
const currentPageTitle = ref('Berita & Artikel')
const searchQuery = ref('')
const filterStatus = ref('')
const posts = ref<Post[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const deletingId = ref<number | null>(null)
const togglingId = ref<number | null>(null)

onMounted(async () => {
  await loadPosts()
})

const loadPosts = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    posts.value = await postService.list()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Gagal memuat artikel.'
  } finally {
    isLoading.value = false
  }
}

const toggleStatus = async (post: Post, newStatus: string) => {
  togglingId.value = post.id
  try {
    await postService.update(post.id, { status: newStatus })
    post.status = newStatus
    toastStore.success('Status artikel berhasil diperbarui.')
  } catch (error: any) {
    toastStore.error(error.response?.data?.message || 'Gagal memperbarui status.')
    // Rollback visual is handled automatically because the model is bound to `post.status`
    // but the `@change` event might update a local ref if we were using v-model directly.
    // However, since we emit `change` and pass `val`, we ONLY update `post.status` if the API succeeds.
    // Wait, the toggle emits `update:modelValue` before `change`, so `ToggleSwitch` doesn't mutate `post.status` internally. It just emits.
    // Ah, wait, if I don't use `v-model` but `:modelValue`, it won't update `post.status` unless I manually update it! Which is perfect!
  } finally {
    togglingId.value = null
  }
}

const deletePost = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus berita ini?')) {
    deletingId.value = id
    try {
      await postService.remove(id)
      toastStore.success('Data berhasil dihapus.')
      await loadPosts()
    } catch (error: any) {
      toastStore.error(error.response?.data?.message || 'Data gagal dihapus.')
    } finally {
      deletingId.value = null
    }
  }
}

const filteredPosts = computed(() => {
  return posts.value.filter((post) => {
    const matchSearch = post.judul.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (post.user?.name ?? '').toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchStatus = filterStatus.value === '' || post.status === filterStatus.value

    return matchSearch && matchStatus
  })
})
</script>