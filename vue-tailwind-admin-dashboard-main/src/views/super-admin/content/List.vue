<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="pageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Monitoring Konten Global
            <span class="rounded-md bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
              Read-Only Governance
            </span>
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">Pemantauan seluruh konten lintas organisasi tanpa hak modifikasi langsung.</p>
        </div>
      </div>

      <!-- Content Type Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-700">
        <button
          v-for="tab in contentTabs"
          :key="tab.key"
          @click="setTab(tab.key)"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition cursor-pointer',
            activeTab === tab.key
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
        />
        <select
          v-model="selectedOrgId"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option :value="null">Semua Organisasi</option>
          <option v-for="org in organizations" :key="org.id" :value="org.id">
            {{ org.nama }}
          </option>
        </select>
        <select
          v-model="selectedStatus"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <!-- Loading & Empty -->
      <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500">
        Memuat data konten...
      </div>

      <!-- 1. TAB ARTIKEL -->
      <div v-else-if="activeTab === 'posts'">
        <div v-if="filteredPosts.length === 0" class="py-12 text-center text-sm text-gray-500">
          Tidak ada artikel yang cocok.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
                <th class="px-4 py-3">Judul Artikel</th>
                <th class="px-4 py-3">Organisasi</th>
                <th class="px-4 py-3">Penulis</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Tanggal Dibuat</th>
                <th class="px-4 py-3 text-center">Detail</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
              <tr v-for="post in filteredPosts" :key="post.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white max-w-sm truncate">{{ post.judul }}</td>
                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ post.organization?.nama || '-' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ post.user?.name || '-' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ post.category?.name || '-' }}</td>
                <td class="px-4 py-3">
                  <span :class="getStatusBadgeClass(post.status)">{{ post.status }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ post.created_at ? new Date(post.created_at).toLocaleDateString('id-ID') : '-' }}</td>
                <td class="px-4 py-3 text-center">
                  <button @click="inspectPost(post)" class="rounded-lg p-1 text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-900/20" title="Lihat Detail">
                    <EyeIcon class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. TAB AGENDA -->
      <div v-else-if="activeTab === 'activities'">
        <div v-if="filteredActivities.length === 0" class="py-12 text-center text-sm text-gray-500">
          Tidak ada agenda yang cocok.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
                <th class="px-4 py-3">Nama Agenda</th>
                <th class="px-4 py-3">Organisasi</th>
                <th class="px-4 py-3">Tanggal Pelaksanaan</th>
                <th class="px-4 py-3">Lokasi / Tipe</th>
                <th class="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
              <tr v-for="act in filteredActivities" :key="act.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ act.nama_kegiatan || act.judul }}</td>
                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ act.organization?.nama || '-' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ act.tanggal_pelaksanaan || act.start_date || '-' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ act.tempat || act.lokasi || '-' }}</td>
                <td class="px-4 py-3">
                  <span :class="getStatusBadgeClass(act.status)">{{ act.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3. TAB PENGUMUMAN -->
      <div v-else-if="activeTab === 'announcements'">
        <div v-if="filteredAnnouncements.length === 0" class="py-12 text-center text-sm text-gray-500">
          Tidak ada pengumuman yang cocok.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
                <th class="px-4 py-3">Judul Pengumuman</th>
                <th class="px-4 py-3">Organisasi</th>
                <th class="px-4 py-3">Prioritas</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Dibuat Pada</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
              <tr v-for="ann in filteredAnnouncements" :key="ann.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ ann.judul }}</td>
                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ ann.organization?.nama || '-' }}</td>
                <td class="px-4 py-3">
                  <span class="rounded-md bg-yellow-50 px-2 py-0.5 text-xs font-semibold text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300">{{ ann.prioritas || 'Normal' }}</span>
                </td>
                <td class="px-4 py-3">
                  <span :class="getStatusBadgeClass(ann.status)">{{ ann.status }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ ann.created_at ? new Date(ann.created_at).toLocaleDateString('id-ID') : '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4. TAB MEDIA & GALERI -->
      <div v-else-if="activeTab === 'media'">
        <div v-if="filteredMedia.length === 0" class="py-12 text-center text-sm text-gray-500">
          Tidak ada berkas media yang cocok.
        </div>
        <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          <div
            v-for="m in filteredMedia"
            :key="m.id"
            class="group rounded-xl border border-gray-200 bg-white p-2.5 shadow-2xs transition hover:shadow-md dark:border-gray-800 dark:bg-gray-800/60 flex flex-col justify-between"
          >
            <div>
              <div class="h-28 w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center relative">
                <img
                  v-if="m.mime_type && m.mime_type.startsWith('image/')"
                  :src="resolveImageUrl(m.url || (m as any).image_url || m.path)"
                  :alt="(m as any).title || (m as any).name || m.filename"
                  class="h-full w-full object-cover transition group-hover:scale-105 duration-200"
                  loading="lazy"
                />
                <FileTextIcon v-else class="h-10 w-10 text-gray-400" />
              </div>
              <div class="mt-2">
                <p
                  class="text-xs font-semibold text-gray-900 dark:text-white truncate"
                  :title="(m as any).title || (m as any).name || m.filename"
                >
                  {{ (m as any).title || (m as any).name || m.filename }}
                </p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                  {{ m.organization?.nama || 'Platform' }}
                </p>
              </div>
            </div>

            <!-- Publication Status Badge based on is_published_to_gallery -->
            <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
              <span
                v-if="(m as any).is_published_to_gallery"
                class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Tayang di Publik
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                Tidak Tayang di Publik
              </span>

              <span v-if="(m as any).category" class="text-[9px] text-gray-400 dark:text-gray-500 truncate max-w-[60px]">
                {{ (m as any).category }}
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Inspector Modal (Read-Only) -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
          <div>
            <span class="rounded-md bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-950/40 dark:text-purple-300">Inspector Detail (Read-Only)</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ activePost?.judul }}</h3>
          </div>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
          <div><span class="text-gray-500">Organisasi:</span> <b>{{ activePost?.organization?.nama }}</b></div>
          <div><span class="text-gray-500">Penulis:</span> <b>{{ activePost?.user?.name }}</b></div>
          <div><span class="text-gray-500">Status:</span> <b>{{ activePost?.status }}</b></div>
          <div><span class="text-gray-500">Dibuat:</span> <b>{{ activePost?.created_at ? new Date(activePost.created_at).toLocaleString('id-ID') : '-' }}</b></div>
        </div>

        <div class="border-t pt-3 dark:border-gray-700">
          <label class="block text-xs font-semibold text-gray-500 mb-2">Konten Lengkap:</label>
          <div class="prose prose-sm dark:prose-invert max-w-none rounded-xl bg-gray-50 p-4 dark:bg-gray-800" v-html="activePost?.konten"></div>
        </div>

        <div class="flex justify-end pt-2">
          <button @click="showDetailModal = false" class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-white">
            Tutup
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { EyeIcon, FileTextIcon } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { resolveImageUrl } from '@/utils/imageUrl'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { contentService } from '@/services/contentService'
import { organizationService } from '@/services/organizationService'
import type { Post, Activity, Announcement, Media, Organization } from '@/types/api'

const pageTitle = ref('Monitoring Konten Global')
const activeTab = ref('posts')
const isLoading = ref(true)

const posts = ref<Post[]>([])
const activities = ref<Activity[]>([])
const announcements = ref<Announcement[]>([])
const media = ref<Media[]>([])
const organizations = ref<Organization[]>([])

const searchQuery = ref('')
const selectedOrgId = ref<number | null>(null)
const selectedStatus = ref('')

const showDetailModal = ref(false)
const activePost = ref<Post | null>(null)

const contentTabs = [
  { key: 'posts', label: 'Artikel & Berita' },
  { key: 'activities', label: 'Agenda Acara' },
  { key: 'announcements', label: 'Pengumuman' },
  { key: 'media', label: 'Galeri & Berkas' },
]

// Tab switching helper with automatic status filter reset
const setTab = (tabKey: string) => {
  activeTab.value = tabKey
  selectedStatus.value = ''
}

// Watch activeTab to ensure selectedStatus is always reset when switching tabs
watch(activeTab, () => {
  selectedStatus.value = ''
})

// Dynamic search placeholder based on domain/activeTab
const searchPlaceholder = computed(() => {
  if (activeTab.value === 'media') {
    return 'Cari foto berdasarkan judul, caption, atau nama file...'
  }
  if (activeTab.value === 'activities') {
    return 'Cari agenda berdasarkan nama kegiatan...'
  }
  if (activeTab.value === 'announcements') {
    return 'Cari pengumuman berdasarkan judul...'
  }
  return 'Cari artikel berdasarkan judul...'
})

// Dynamic status dropdown options based on activeTab
const statusOptions = computed(() => {
  if (activeTab.value === 'media') {
    return [
      { value: '', label: 'Semua Status' },
      { value: 'published', label: 'Tayang di Publik' },
      { value: 'unpublished', label: 'Tidak Tayang di Publik' },
    ]
  }

  return [
    { value: '', label: 'Semua Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
  ]
})

const filteredPosts = computed(() => {
  return posts.value.filter((p) => {
    const search = searchQuery.value.toLowerCase().trim()
    const matchSearch = !search || (p.judul && p.judul.toLowerCase().includes(search))
    const orgId = p.organization_id ?? p.organization?.id
    const matchOrg = selectedOrgId.value === null || orgId === selectedOrgId.value
    const matchStatus = !selectedStatus.value || p.status === selectedStatus.value
    return matchSearch && matchOrg && matchStatus
  })
})

const filteredActivities = computed(() => {
  return activities.value.filter((a) => {
    const search = searchQuery.value.toLowerCase().trim()
    const matchSearch = !search ||
      (a.nama_kegiatan && a.nama_kegiatan.toLowerCase().includes(search)) ||
      (a.judul && a.judul.toLowerCase().includes(search))
    const orgId = a.organization_id ?? a.organization?.id
    const matchOrg = selectedOrgId.value === null || orgId === selectedOrgId.value
    const matchStatus = !selectedStatus.value || a.status === selectedStatus.value
    return matchSearch && matchOrg && matchStatus
  })
})

const filteredAnnouncements = computed(() => {
  return announcements.value.filter((an) => {
    const search = searchQuery.value.toLowerCase().trim()
    const matchSearch = !search || (an.judul && an.judul.toLowerCase().includes(search))
    const orgId = an.organization_id ?? an.organization?.id
    const matchOrg = selectedOrgId.value === null || orgId === selectedOrgId.value
    const matchStatus = !selectedStatus.value || an.status === selectedStatus.value
    return matchSearch && matchOrg && matchStatus
  })
})

const filteredMedia = computed(() => {
  return media.value.filter((m: any) => {
    // 1. Search Query
    const search = searchQuery.value.toLowerCase().trim()
    const matchSearch = !search ||
      (m.filename && m.filename.toLowerCase().includes(search)) ||
      (m.name && m.name.toLowerCase().includes(search)) ||
      (m.title && m.title.toLowerCase().includes(search)) ||
      (m.judul && m.judul.toLowerCase().includes(search)) ||
      (m.caption && m.caption.toLowerCase().includes(search)) ||
      (m.alt_text && m.alt_text.toLowerCase().includes(search))

    // 2. Organization Filter
    const orgId = m.organization_id ?? m.organization?.id
    const matchOrg = selectedOrgId.value === null || orgId === selectedOrgId.value

    // 3. Status Filter (is_published_to_gallery)
    let matchStatus = true
    if (selectedStatus.value === 'published') {
      matchStatus = Boolean(m.is_published_to_gallery)
    } else if (selectedStatus.value === 'unpublished') {
      matchStatus = !m.is_published_to_gallery
    }

    return matchSearch && matchOrg && matchStatus
  })
})

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'published':
      return 'inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-300'
    case 'review':
      return 'inline-flex rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-semibold text-yellow-800 dark:bg-yellow-950/40 dark:text-yellow-300'
    case 'rejected':
      return 'inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-300'
    default:
      return 'inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300'
  }
}

const inspectPost = (post: Post) => {
  activePost.value = post
  showDetailModal.value = true
}

const loadData = async () => {
  isLoading.value = true
  try {
    const [pList, aList, anList, mList, oList] = await Promise.all([
      contentService.getGlobalPosts(),
      contentService.getGlobalActivities(),
      contentService.getGlobalAnnouncements(),
      contentService.getGlobalMedia(),
      organizationService.list(),
    ])
    posts.value = pList || []
    activities.value = aList || []
    announcements.value = anList || []
    media.value = mList || []
    organizations.value = oList || []
  } catch (err) {
    console.error('Failed to load global content:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
