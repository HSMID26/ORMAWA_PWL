<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { postService } from '@/services/postService'
import { activityService } from '@/services/activityService'
import { announcementService } from '@/services/announcementService'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const router = useRouter()

interface UnifiedContent {
  id: number
  type: 'post' | 'activity' | 'announcement'
  title: string
  slug?: string
  status: 'draft' | 'review' | 'published' | 'rejected'
  author: string
  created_at: string
  published_at?: string | null
  // Original raw data
  raw: any
}

const contents = ref<UnifiedContent[]>([])
const loading = ref(true)
const errorMessages = ref<string[]>([])

const filterType = ref('all')
const filterStatus = ref('all')
const searchQuery = ref('')

// Modal Konfirmasi Hapus
const isDeleteModalOpen = ref(false)
const itemToDelete = ref<UnifiedContent | null>(null)
const isDeleting = ref(false)

const loadData = async () => {
  loading.value = true
  errorMessages.value = []
  
  const results: UnifiedContent[] = []

  const [postsRes, activitiesRes, announcementsRes] = await Promise.allSettled([
    postService.list(),
    activityService.list(),
    announcementService.list()
  ])

  if (postsRes.status === 'fulfilled') {
    const posts = postsRes.value.map((p: any) => ({
      id: p.id,
      type: 'post' as const,
      title: p.judul,
      slug: p.slug,
      status: p.status,
      author: p.user?.name || 'Unknown',
      created_at: p.created_at,
      published_at: p.published_at,
      raw: p
    }))
    results.push(...posts)
  } else {
    errorMessages.value.push('Gagal memuat Berita.')
  }

  if (activitiesRes.status === 'fulfilled') {
    const activities = activitiesRes.value.map((a: any) => ({
      id: a.id,
      type: 'activity' as const,
      title: a.judul,
      status: a.status,
      author: a.user?.name || 'Unknown',
      created_at: a.created_at,
      published_at: a.published_at,
      raw: a
    }))
    results.push(...activities)
  } else {
    errorMessages.value.push('Gagal memuat Agenda.')
  }

  if (announcementsRes.status === 'fulfilled') {
    const announcements = announcementsRes.value.map((a: any) => ({
      id: a.id,
      type: 'announcement' as const,
      title: a.title,
      slug: a.slug,
      status: a.status,
      author: a.user?.name || 'Unknown',
      created_at: a.created_at,
      published_at: a.published_at,
      raw: a
    }))
    results.push(...announcements)
  } else {
    errorMessages.value.push('Gagal memuat Pengumuman.')
  }

  // Urutkan terbaru berdasarkan created_at
  results.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  
  contents.value = results
  loading.value = false
}

onMounted(() => {
  loadData()
})

const filteredContents = computed(() => {
  return contents.value.filter(item => {
    const matchType = filterType.value === 'all' || item.type === filterType.value
    const matchStatus = filterStatus.value === 'all' || item.status === filterStatus.value
    const matchSearch = item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                        (item.slug && item.slug.toLowerCase().includes(searchQuery.value.toLowerCase()))
    
    return matchType && matchStatus && matchSearch
  })
})

const getTypeLabel = (type: string) => {
  switch (type) {
    case 'post': return 'Berita'
    case 'activity': return 'Agenda'
    case 'announcement': return 'Pengumuman'
    default: return type
  }
}

const getTypeClass = (type: string) => {
  switch (type) {
    case 'post': return 'bg-primary text-white'
    case 'activity': return 'bg-secondary text-white'
    case 'announcement': return 'bg-warning text-white'
    default: return 'bg-gray-500 text-white'
  }
}

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'draft': return 'Draft'
    case 'review': return 'Review'
    case 'published': return 'Published'
    case 'rejected': return 'Rejected'
    default: return status
  }
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'draft': return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    case 'review': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    case 'published': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'rejected': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

const navigateToCreate = () => {
  router.push('/organization/content/create')
}

const navigateToEdit = (item: UnifiedContent) => {
  router.push(`/organization/content/edit/${item.type}/${item.id}`)
}

const confirmDelete = (item: UnifiedContent) => {
  itemToDelete.value = item
  isDeleteModalOpen.value = true
}

const executeDelete = async () => {
  if (!itemToDelete.value) return
  isDeleting.value = true
  
  try {
    const id = itemToDelete.value.id
    const type = itemToDelete.value.type

    if (type === 'post') {
      await postService.remove(id)
    } else if (type === 'activity') {
      await activityService.remove(id)
    } else if (type === 'announcement') {
      await announcementService.remove(id)
    }
    
    // Hapus dari state
    contents.value = contents.value.filter(c => !(c.id === id && c.type === type))
    isDeleteModalOpen.value = false
    itemToDelete.value = null
    // Idealnya tambah Toast success disini, tapi saya belum inject Toast.
    console.log('Success hapus konten')
  } catch (error: any) {
    console.error('Failed to delete', error)
    alert('Gagal menghapus data. ' + (error.response?.data?.message || ''))
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <AdminLayout>
    <!-- Breadcrumb -->
    <PageBreadcrumb pageTitle="Konten Organisasi" />

    <div class="mb-6 bg-white dark:bg-boxdark rounded-lg p-6 shadow-default">
      <div class="flex flex-col md:flex-row justify-between items-center mb-4">
        <div>
          <h2 class="text-xl font-semibold text-black dark:text-white">Kelola Konten</h2>
          <p class="text-sm text-gray-500">Kelola berita, agenda, dan pengumuman organisasi dalam satu tempat.</p>
        </div>
        <button
          @click="navigateToCreate"
          class="mt-4 md:mt-0 flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90"
        >
          + Buat Konten
        </button>
      </div>

      <!-- Error Banners if any request failed -->
      <div v-if="errorMessages.length > 0" class="mb-4 space-y-2">
        <div v-for="(msg, i) in errorMessages" :key="i" class="p-4 rounded-lg bg-red-100 text-red-800 text-sm">
          {{ msg }}
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-col md:flex-row gap-4 mb-4">
        <div class="flex-1">
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Cari judul..." 
            class="w-full rounded border border-stroke bg-transparent py-2 pl-4 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
          />
        </div>
        <div class="w-full md:w-48">
          <select 
            v-model="filterType"
            class="w-full rounded border border-stroke bg-transparent py-2 px-4 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
          >
            <option value="all">Semua Jenis</option>
            <option value="post">Berita</option>
            <option value="activity">Agenda</option>
            <option value="announcement">Pengumuman</option>
          </select>
        </div>
        <div class="w-full md:w-48">
          <select 
            v-model="filterStatus"
            class="w-full rounded border border-stroke bg-transparent py-2 px-4 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
          >
            <option value="all">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="review">Review</option>
            <option value="published">Published</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="max-w-full overflow-x-auto">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-2 text-left dark:bg-meta-4">
              <th class="py-4 px-4 font-medium text-black dark:text-white">Judul</th>
              <th class="py-4 px-4 font-medium text-black dark:text-white">Jenis</th>
              <th class="py-4 px-4 font-medium text-black dark:text-white">Status</th>
              <th class="py-4 px-4 font-medium text-black dark:text-white">Penulis</th>
              <th class="py-4 px-4 font-medium text-black dark:text-white">Tanggal</th>
              <th class="py-4 px-4 font-medium text-black dark:text-white">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="text-center py-6 text-gray-500">Memuat data...</td>
            </tr>
            <tr v-else-if="filteredContents.length === 0">
              <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada konten yang ditemukan.</td>
            </tr>
            <tr v-for="item in filteredContents" :key="item.type + item.id" class="border-b border-stroke dark:border-form-strokedark">
              <td class="py-4 px-4">
                <div class="font-medium text-black dark:text-white">{{ item.title }}</div>
                <div v-if="item.slug" class="text-xs text-gray-500">{{ item.slug }}</div>
              </td>
              <td class="py-4 px-4">
                <span :class="`inline-flex rounded-full py-1 px-3 text-xs font-medium ${getTypeClass(item.type)}`">
                  {{ getTypeLabel(item.type) }}
                </span>
              </td>
              <td class="py-4 px-4">
                <span :class="`inline-flex rounded-full py-1 px-3 text-xs font-medium ${getStatusClass(item.status)}`">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td class="py-4 px-4 text-sm">{{ item.author }}</td>
              <td class="py-4 px-4 text-sm">{{ formatDate(item.created_at) }}</td>
              <td class="py-4 px-4 flex gap-2">
                <button @click="navigateToEdit(item)" class="text-primary hover:underline text-sm font-medium">Edit</button>
                <button @click="confirmDelete(item)" class="text-danger hover:underline text-sm font-medium">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-9999 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-default dark:bg-boxdark">
        <h3 class="mb-4 text-xl font-bold text-black dark:text-white">Hapus Konten?</h3>
        <p class="mb-6 text-gray-500">
          Apakah Anda yakin ingin menghapus konten <strong>{{ itemToDelete?.title }}</strong>? Data yang dihapus tidak dapat dikembalikan.
        </p>
        <div class="flex justify-end gap-4">
          <button @click="isDeleteModalOpen = false" class="rounded px-4 py-2 bg-gray-200 text-black dark:bg-meta-4 dark:text-white hover:bg-opacity-90">Batal</button>
          <button @click="executeDelete" :disabled="isDeleting" class="rounded px-4 py-2 bg-danger text-white hover:bg-opacity-90 disabled:opacity-50">
            {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
