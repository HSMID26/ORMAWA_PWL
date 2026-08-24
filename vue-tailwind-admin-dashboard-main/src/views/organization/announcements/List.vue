<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Manajemen Pengumuman'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Action ────────────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <MegaphoneIcon class="h-5 w-5 text-amber-500" />
            Daftar Pengumuman Organisasi
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Siarkan maklumat resmi, pemberitahuan penting, dan instruksi kepengurusan.
          </p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition"
        >
          <PlusIcon class="h-4 w-4" />
          Buat Pengumuman Baru
        </button>
      </div>

      <!-- ─── Toolbar Filters ────────────────────────────────────────────────── -->
      <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="sm:col-span-2 relative">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari judul pengumuman atau isi maklumat..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <div>
          <select
            v-model="filterPriority"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Prioritas</option>
            <option value="urgent">Urgent (Sangat Mendesak)</option>
            <option value="high">Tinggi (High)</option>
            <option value="normal">Normal</option>
            <option value="low">Rendah (Low)</option>
          </select>
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="space-y-3 py-6">
        <div v-for="i in 4" :key="i" class="h-14 w-full animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Error State ────────────────────────────────────────────────────── -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button @click="loadAnnouncements" class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
          Coba Lagi
        </button>
      </div>

      <!-- ─── Table Data ─────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Pengumuman</th>
              <th class="px-4 py-3">Prioritas</th>
              <th class="px-4 py-3">Berlaku Sampai</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
            <tr
              v-for="item in filteredAnnouncements"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
            >
              <!-- Judul & Isi -->
              <td class="px-4 py-3.5">
                <div class="flex items-start gap-2.5">
                  <div
                    class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                    :class="getPriorityIconClass(item.priority)"
                  >
                    <MegaphoneIcon class="h-3.5 w-3.5" />
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-900 dark:text-white line-clamp-1">
                      {{ item.title }}
                    </h3>
                    <p class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">
                      {{ item.content }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Prioritas -->
              <td class="px-4 py-3.5">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                  :class="getPriorityBadgeClass(item.priority)"
                >
                  {{ item.priority }}
                </span>
              </td>

              <!-- Tanggal Berlaku -->
              <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 text-[11px] whitespace-nowrap">
                {{ formatDate(item.effective_date) }}
              </td>

              <!-- Status -->
              <td class="px-4 py-3.5">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                  :class="item.status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'"
                >
                  {{ item.status === 'published' ? 'Aktif' : 'Draft' }}
                </span>
              </td>

              <!-- Aksi -->
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openEditModal(item)"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Edit Pengumuman"
                  >
                    <PencilIcon class="h-4 w-4" />
                  </button>
                  <button
                    @click="openDeleteModal(item)"
                    class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition"
                    title="Hapus Pengumuman"
                  >
                    <Trash2Icon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredAnnouncements.length === 0">
              <td colspan="5" class="px-4 py-12 text-center">
                <MegaphoneIcon class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-700 mb-2" />
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum Ada Pengumuman</h3>
                <p class="text-xs text-gray-400 mt-1">Organisasi belum mempublikasikan maklumat pengumuman.</p>
                <button
                  @click="openCreateModal"
                  class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"
                >
                  <PlusIcon class="h-3.5 w-3.5" />
                  Buat Pengumuman Pertama
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Modal Create / Edit Pengumuman ───────────────────────────────────── -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="isModalOpen = false"
    >
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
          <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <MegaphoneIcon class="h-5 w-5 text-amber-500" />
            {{ editingId ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }}
          </h3>
          <button @click="isModalOpen = false" class="rounded-lg p-1 text-gray-400 hover:text-gray-600">
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <form @submit.prevent="submitModalForm" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Judul Pengumuman <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="modalForm.title"
              type="text"
              required
              placeholder="Contoh: Pemberitahuan Libur Sekretariat / Rapat Pleno"
              class="w-full rounded-xl border border-gray-300 bg-transparent px-3.5 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Tingkat Prioritas
              </label>
              <select
                v-model="modalForm.priority"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              >
                <option value="urgent">Urgent (Sangat Mendesak)</option>
                <option value="high">High (Tinggi)</option>
                <option value="normal">Normal (Biasa)</option>
                <option value="low">Low (Informasi Tambahan)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Berlaku Hingga
              </label>
              <input
                v-model="modalForm.effective_date"
                type="date"
                class="w-full rounded-xl border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Isi Maklumat / Pesan Pengumuman <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="modalForm.content"
              required
              rows="5"
              placeholder="Tuliskan isi pengumuman secara jelas dan ringkas..."
              class="w-full rounded-xl border border-gray-300 bg-transparent px-3.5 py-2 text-xs leading-relaxed text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            ></textarea>
          </div>

          <div>
            <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Status Publikasi
            </label>
            <select
              v-model="modalForm.status"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="published">Published (Siarkan Sekarang)</option>
              <option value="draft">Draft (Simpan Sementara)</option>
            </select>
          </div>

          <div class="mt-6 flex justify-end gap-2.5 border-t border-gray-100 pt-4 dark:border-gray-800">
            <button
              type="button"
              @click="isModalOpen = false"
              class="rounded-xl bg-gray-100 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="rounded-xl bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700 shadow-sm disabled:opacity-50"
            >
              {{ isSubmitting ? 'Menyimpan...' : (editingId ? 'Perbarui Pengumuman' : 'Publikasikan') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── Modal Konfirmasi Hapus ───────────────────────────────────────────── -->
    <div
      v-if="isDeleteOpen && announcementToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 mb-4">
          <Trash2Icon class="h-6 w-6" />
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus Pengumuman?</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Apakah Anda yakin ingin menghapus pengumuman <strong class="text-gray-900 dark:text-white">"{{ announcementToDelete.title }}"</strong>? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex justify-end gap-2.5">
          <button
            @click="isDeleteOpen = false"
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
import { announcementService, type Announcement } from '@/services/announcementService'
import { useToastStore } from '@/stores/toast'
import {
  MegaphoneIcon,
  PlusIcon,
  SearchIcon,
  PencilIcon,
  Trash2Icon,
  AlertTriangleIcon,
  XIcon
} from 'lucide-vue-next'

const toastStore = useToastStore()

const announcements = ref<Announcement[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const isSubmitting = ref(false)

const searchQuery = ref('')
const filterPriority = ref('')

// Create/Edit modal state
const isModalOpen = ref(false)
const editingId = ref<number | null>(null)
const modalForm = ref({
  title: '',
  content: '',
  priority: 'normal' as 'low' | 'normal' | 'high' | 'urgent',
  effective_date: '',
  status: 'published' as 'draft' | 'published'
})

// Delete modal state
const isDeleteOpen = ref(false)
const announcementToDelete = ref<Announcement | null>(null)
const isDeleting = ref(false)

const loadAnnouncements = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    announcements.value = await announcementService.list()
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat pengumuman.'
  } finally {
    isLoading.value = false
  }
}

const filteredAnnouncements = computed(() => {
  return announcements.value.filter(item => {
    if (filterPriority.value && item.priority !== filterPriority.value) {
      return false
    }

    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchTitle = item.title.toLowerCase().includes(q)
      const matchContent = item.content.toLowerCase().includes(q)
      if (!matchTitle && !matchContent) return false
    }

    return true
  })
})

const formatDate = (dateStr?: string | null) => {
  if (!dateStr) return 'Tidak Terbatas'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(new Date(dateStr))
}

const getPriorityBadgeClass = (priority: string) => {
  switch (priority) {
    case 'urgent': return 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-400'
    case 'high': return 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400'
    case 'normal': return 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400'
    default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
  }
}

const getPriorityIconClass = (priority: string) => {
  switch (priority) {
    case 'urgent': return 'bg-rose-100 text-rose-600'
    case 'high': return 'bg-amber-100 text-amber-600'
    case 'normal': return 'bg-blue-100 text-blue-600'
    default: return 'bg-gray-100 text-gray-600'
  }
}

const openCreateModal = () => {
  editingId.value = null
  modalForm.value = {
    title: '',
    content: '',
    priority: 'normal',
    effective_date: '',
    status: 'published'
  }
  isModalOpen.value = true
}

const openEditModal = (item: Announcement) => {
  editingId.value = item.id
  modalForm.value = {
    title: item.title,
    content: item.content,
    priority: item.priority,
    effective_date: item.effective_date ? item.effective_date.split('T')[0] : '',
    status: item.status === 'published' ? 'published' : 'draft'
  }
  isModalOpen.value = true
}

const submitModalForm = async () => {
  if (!modalForm.value.title.trim() || !modalForm.value.content.trim()) {
    toastStore.error('Judul dan isi pengumuman wajib diisi.')
    return
  }

  isSubmitting.value = true
  try {
    if (editingId.value) {
      await announcementService.update(editingId.value, {
        title: modalForm.value.title,
        content: modalForm.value.content,
        priority: modalForm.value.priority,
        effective_date: modalForm.value.effective_date || null,
        status: modalForm.value.status
      })
      toastStore.success('Pengumuman berhasil diperbarui!')
    } else {
      await announcementService.create({
        title: modalForm.value.title,
        content: modalForm.value.content,
        priority: modalForm.value.priority,
        effective_date: modalForm.value.effective_date || null,
        status: modalForm.value.status
      })
      toastStore.success('Pengumuman baru berhasil diterbitkan!')
    }

    isModalOpen.value = false
    await loadAnnouncements()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menyimpan pengumuman.')
  } finally {
    isSubmitting.value = false
  }
}

const openDeleteModal = (item: Announcement) => {
  announcementToDelete.value = item
  isDeleteOpen.value = true
}

const confirmDelete = async () => {
  if (!announcementToDelete.value || isDeleting.value) return
  isDeleting.value = true
  try {
    await announcementService.remove(announcementToDelete.value.id)
    toastStore.success('Pengumuman berhasil dihapus.')
    isDeleteOpen.value = false
    announcementToDelete.value = null
    await loadAnnouncements()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menghapus pengumuman.')
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  loadAnnouncements()
})
</script>