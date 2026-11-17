<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Manajemen Berita & Artikel'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Action Button ─────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <FileTextIcon class="h-5 w-5 text-brand-500" />
            Daftar Berita & Artikel
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Kelola publikasi berita, opini, dan liputan kegiatan organisasi.
          </p>
        </div>
        <router-link
          to="/organization/posts/create"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition"
        >
          <PlusIcon class="h-4 w-4" />
          Tulis Artikel Baru
        </router-link>
      </div>

      <!-- ─── Toolbar Filters ────────────────────────────────────────────────── -->
      <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
        <!-- Search Input -->
        <div class="sm:col-span-2 relative">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari judul artikel, penulis, atau konten..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <!-- Filter Status -->
        <div>
          <select
            v-model="filterStatus"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Status</option>
            <option value="published">Published</option>
            <option value="review">Menunggu Review</option>
            <option value="draft">Draft</option>
            <option value="rejected">Ditolak (Rejected)</option>
          </select>
        </div>

        <!-- Filter Kategori -->
        <div>
          <select
            v-model="filterCategory"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="space-y-3 py-6">
        <div v-for="i in 5" :key="i" class="h-14 w-full animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Error State ────────────────────────────────────────────────────── -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button @click="loadData" class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
          Coba Lagi
        </button>
      </div>

      <!-- ─── Table Data ─────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[750px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Artikel</th>
              <th class="px-4 py-3">Kategori & Tags</th>
              <th class="px-4 py-3">Penulis</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Tanggal</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
            <tr
              v-for="post in filteredPosts"
              :key="post.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
            >
              <!-- Artikel & Thumbnail -->
              <td class="px-4 py-3.5">
                <div class="flex items-start gap-3">
                  <div class="h-12 w-16 shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <img
                      v-if="post.cover_image"
                      :src="post.cover_image"
                      :alt="post.judul"
                      class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-gray-400">
                      <ImageIcon class="h-5 w-5" />
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-1" :title="post.judul">
                      {{ post.judul }}
                    </h3>
                    <p class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">
                      {{ post.excerpt || stripHtml(post.konten).substring(0, 70) + '...' }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Kategori & Tags -->
              <td class="px-4 py-3.5">
                <div>
                  <span
                    v-if="post.category"
                    class="inline-block rounded-md bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400"
                  >
                    {{ post.category.name }}
                  </span>
                  <span v-else class="text-gray-400 italic text-[11px]">Tanpa Kategori</span>
                </div>
                <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-1 mt-1">
                  <span
                    v-for="tag in post.tags.slice(0, 2)"
                    :key="tag.id"
                    class="text-[10px] text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded"
                  >
                    #{{ tag.name }}
                  </span>
                  <span v-if="post.tags.length > 2" class="text-[10px] text-gray-400">
                    +{{ post.tags.length - 2 }}
                  </span>
                </div>
              </td>

              <!-- Penulis -->
              <td class="px-4 py-3.5 font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                {{ post.user?.name || '-' }}
              </td>

              <!-- Status -->
              <td class="px-4 py-3.5">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize"
                  :class="{
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400': post.status === 'published',
                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400': post.status === 'review',
                    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300': post.status === 'draft',
                    'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400': post.status === 'rejected',
                  }"
                >
                  {{ getStatusLabel(post.status) }}
                </span>
              </td>

              <!-- Tanggal -->
              <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 whitespace-nowrap text-[11px]">
                {{ formatDate(post.published_at || post.created_at) }}
              </td>

              <!-- Aksi -->
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Preview Button -->
                  <button
                    @click="openPreview(post)"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Live Preview"
                  >
                    <EyeIcon class="h-4 w-4" />
                  </button>

                  <!-- Edit Button -->
                  <router-link
                    :to="`/organization/posts/edit/${post.id}`"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Edit Artikel"
                  >
                    <PencilIcon class="h-4 w-4" />
                  </router-link>

                  <!-- Quick Approve (For Admin Organisasi if status is review) -->
                  <button
                    v-if="canApprove && post.status === 'review'"
                    @click="quickPublish(post)"
                    class="rounded-lg p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition"
                    title="Setujui & Publikasikan"
                  >
                    <CheckCircleIcon class="h-4 w-4" />
                  </button>

                  <!-- Delete Button -->
                  <button
                    @click="openDeleteModal(post)"
                    class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition"
                    title="Hapus Artikel"
                  >
                    <Trash2Icon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredPosts.length === 0">
              <td colspan="6" class="px-4 py-12 text-center">
                <FileTextIcon class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-700 mb-2" />
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum Ada Artikel</h3>
                <p class="text-xs text-gray-400 mt-1">Organisasi Anda belum memiliki artikel yang cocok dengan filter ini.</p>
                <router-link
                  to="/organization/posts/create"
                  class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"
                >
                  <PlusIcon class="h-3.5 w-3.5" />
                  Tulis Artikel Pertama
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Modal Live Preview Post ──────────────────────────────────────────── -->
    <div
      v-if="isPreviewOpen && previewPost"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="isPreviewOpen = false"
    >
      <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8 overflow-hidden">
        <!-- Preview Header Bar -->
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/60">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Live Preview Artikel</span>
            <span class="rounded bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-600">{{ previewPost.status }}</span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Switch Device -->
            <div class="flex rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-800">
              <button
                @click="previewDevice = 'desktop'"
                :class="['p-1 rounded text-xs', previewDevice === 'desktop' ? 'bg-brand-500 text-white' : 'text-gray-500 hover:text-gray-700']"
                title="Desktop View"
              >
                <MonitorIcon class="h-3.5 w-3.5" />
              </button>
              <button
                @click="previewDevice = 'mobile'"
                :class="['p-1 rounded text-xs', previewDevice === 'mobile' ? 'bg-brand-500 text-white' : 'text-gray-500 hover:text-gray-700']"
                title="Mobile View"
              >
                <SmartphoneIcon class="h-3.5 w-3.5" />
              </button>
            </div>

            <button
              @click="isPreviewOpen = false"
              class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <XIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Preview Body Content -->
        <div class="p-6 max-h-[75vh] overflow-y-auto flex justify-center bg-gray-100 dark:bg-gray-950">
          <div
            :class="[
              'bg-white dark:bg-gray-900 rounded-xl p-6 shadow-sm transition-all',
              previewDevice === 'mobile' ? 'max-w-sm border-4 border-gray-800 rounded-3xl' : 'w-full max-w-2xl'
            ]"
          >
            <!-- Featured Image -->
            <div v-if="previewPost.cover_image" class="mb-4 rounded-xl overflow-hidden aspect-video bg-gray-100">
              <img :src="previewPost.cover_image" :alt="previewPost.judul" class="h-full w-full object-cover" />
            </div>

            <!-- Category & Date -->
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
              <span v-if="previewPost.category" class="font-bold text-brand-600">
                {{ previewPost.category.name }}
              </span>
              <span>•</span>
              <span>{{ formatDate(previewPost.published_at || previewPost.created_at) }}</span>
              <span>•</span>
              <span>Oleh {{ previewPost.user?.name || 'Admin' }}</span>
            </div>

            <!-- Title -->
            <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight mb-3">
              {{ previewPost.judul }}
            </h1>

            <!-- Excerpt -->
            <p v-if="previewPost.excerpt" class="text-xs font-medium text-gray-600 dark:text-gray-300 italic mb-4 border-l-2 border-brand-500 pl-3">
              {{ previewPost.excerpt }}
            </p>

            <!-- HTML Content -->
            <div
              class="prose prose-sm dark:prose-invert max-w-none text-xs text-gray-800 dark:text-gray-200 leading-relaxed"
              v-html="previewPost.konten"
            ></div>

            <!-- Tags -->
            <div v-if="previewPost.tags && previewPost.tags.length > 0" class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-1.5">
              <span
                v-for="t in previewPost.tags"
                :key="t.id"
                class="rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-medium text-gray-600 dark:text-gray-300"
              >
                #{{ t.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Modal Konfirmasi Hapus ───────────────────────────────────────────── -->
    <div
      v-if="isDeleteModalOpen && postToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 mb-4">
          <Trash2Icon class="h-6 w-6" />
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus Artikel?</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Apakah Anda yakin ingin menghapus artikel <strong class="text-gray-900 dark:text-white">"{{ postToDelete.judul }}"</strong>? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex justify-end gap-2.5">
          <button
            @click="isDeleteModalOpen = false"
            class="rounded-lg px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="confirmDelete"
            :disabled="isDeleting"
            class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50"
          >
            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { postService } from '@/services/postService'
import { taxonomyService } from '@/services/taxonomyService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import type { Post, Category } from '@/types/api'
import {
  FileTextIcon,
  PlusIcon,
  SearchIcon,
  EyeIcon,
  PencilIcon,
  Trash2Icon,
  AlertTriangleIcon,
  ImageIcon,
  CheckCircleIcon,
  XIcon,
  MonitorIcon,
  SmartphoneIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toastStore = useToastStore()

const canApprove = computed(() => authStore.role === 'Admin Organisasi' || authStore.role === 'Super Admin')

const posts = ref<Post[]>([])
const categories = ref<Category[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

const searchQuery = ref('')
const filterStatus = ref('')
const filterCategory = ref<number | string>('')

// Modal preview
const isPreviewOpen = ref(false)
const previewPost = ref<Post | null>(null)
const previewDevice = ref<'desktop' | 'mobile'>('desktop')

// Modal delete
const isDeleteModalOpen = ref(false)
const postToDelete = ref<Post | null>(null)
const isDeleting = ref(false)

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const [postsData, categoriesData] = await Promise.all([
      postService.list(),
      taxonomyService.getCategories()
    ])
    posts.value = postsData
    categories.value = categoriesData
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat artikel.'
  } finally {
    isLoading.value = false
  }
}

const filteredPosts = computed(() => {
  return posts.value.filter(post => {
    // Status filter
    if (filterStatus.value && post.status !== filterStatus.value) {
      return false
    }

    // Category filter
    if (filterCategory.value && post.category_id !== Number(filterCategory.value)) {
      return false
    }

    // Search filter
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchTitle = post.judul.toLowerCase().includes(q)
      const matchAuthor = post.user?.name?.toLowerCase().includes(q) || false
      const matchExcerpt = post.excerpt?.toLowerCase().includes(q) || false
      if (!matchTitle && !matchAuthor && !matchExcerpt) {
        return false
      }
    }

    return true
  })
})

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'published': return 'Published'
    case 'review': return 'Menunggu Review'
    case 'draft': return 'Draft'
    case 'rejected': return 'Ditolak'
    default: return status
  }
}

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(new Date(dateStr))
}

const stripHtml = (html: string) => {
  const tmp = document.createElement('DIV')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}

const openPreview = (post: Post) => {
  previewPost.value = post
  isPreviewOpen.value = true
}

const openDeleteModal = (post: Post) => {
  postToDelete.value = post
  isDeleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (!postToDelete.value || isDeleting.value) return
  isDeleting.value = true
  try {
    await postService.delete(postToDelete.value.id)
    toastStore.success('Artikel berhasil dihapus.')
    isDeleteModalOpen.value = false
    postToDelete.value = null
    await loadData()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menghapus artikel.')
  } finally {
    isDeleting.value = false
  }
}

const quickPublish = async (post: Post) => {
  try {
    await postService.update(post.id, { status: 'published' })
    toastStore.success(`Artikel "${post.judul}" berhasil dipublikasikan!`)
    await loadData()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal mempublikasikan artikel.')
  }
}

onMounted(() => {
  loadData()
})
</script>