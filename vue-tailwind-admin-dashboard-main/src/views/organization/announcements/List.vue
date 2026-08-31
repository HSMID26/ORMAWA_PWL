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
              <th class="px-4 py-3">Tanggal Efektif</th>
              <th class="px-4 py-3">Berlaku Hingga</th>
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

              <!-- Tanggal Efektif -->
              <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 text-[11px] whitespace-nowrap">
                <div class="font-medium">{{ formatDate(item.effective_date) }}</div>
                <span v-if="!item.effective_date" class="text-[10px] text-gray-400 block">Langsung Aktif</span>
              </td>

              <!-- Berlaku Hingga -->
              <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 text-[11px] whitespace-nowrap">
                <div class="font-medium">{{ item.expires_at ? formatDate(item.expires_at) : 'Tanpa Batas' }}</div>
                <span v-if="item.expires_at && isPastDate(item.expires_at)" class="text-[10px] text-rose-500 font-semibold block">Kedaluwarsa</span>
                <span v-else-if="!item.expires_at" class="text-[10px] text-gray-400 block">Seterusnya</span>
              </td>

              <!-- Status -->
              <td class="px-4 py-3.5">
                <div class="flex flex-col items-start gap-1">
                  <span
                    class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                    :class="getItemStatusBadge(item).class"
                    :title="getItemStatusBadge(item).description"
                  >
                    {{ getItemStatusBadge(item).label }}
                  </span>
                  <span v-if="getItemStatusBadge(item).subtext" class="text-[10px] font-medium" :class="getItemStatusBadge(item).subtextClass">
                    {{ getItemStatusBadge(item).subtext }}
                  </span>
                </div>
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
              <td colspan="6" class="px-4 py-12 text-center">
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
          <div class="flex items-center gap-2">
            <MegaphoneIcon class="h-5 w-5 text-amber-500" />
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
              {{ editingId ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }}
            </h3>
            <span
              v-if="editingId"
              class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ml-1"
              :class="modalCurrentStatusBadge.class"
            >
              {{ modalCurrentStatusBadge.label }}
            </span>
          </div>
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
              placeholder="Contoh: Pendaftaran Anggota Baru / Rapat Pleno"
              class="w-full rounded-xl border border-gray-300 bg-transparent px-3.5 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Prioritas
              </label>
              <select
                v-model="modalForm.priority"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              >
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
                <option value="low">Low</option>
              </select>
            </div>

            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Tanggal Aktif
              </label>
              <input
                v-model="modalForm.effective_date"
                type="date"
                class="w-full rounded-xl border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              />
              <p class="text-[10px] text-gray-500 mt-1">Mulai tampil (kosong = langsung tampil).</p>
            </div>

            <div>
              <label class="block font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                Berlaku Hingga
              </label>
              <input
                v-model="modalForm.expires_at"
                type="date"
                :min="modalForm.effective_date || ''"
                class="w-full rounded-xl border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              />
              <p class="text-[10px] text-gray-500 mt-1">Batas akhir (opsional).</p>
            </div>
          </div>

          <!-- Alert Khusus Hanya Saat Terjadwal atau Sudah Kedaluwarsa -->
          <div v-if="modalForm.status === 'published' && isPastDate(modalForm.expires_at)" class="rounded-xl border border-rose-200 bg-rose-50/70 p-3 text-xs text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200 flex items-start gap-2">
            <AlertTriangleIcon class="h-4 w-4 shrink-0 text-rose-600 mt-0.5" />
            <div>
              <span class="font-bold">Batas Waktu Berakhir:</span>
              <p class="text-[11px] text-rose-700 dark:text-rose-300 mt-0.5">
                Tanggal 'Berlaku Hingga' sudah lewat ({{ formatDate(modalForm.expires_at) }}). Pengumuman ini tidak akan tampil di portal publik.
              </p>
            </div>
          </div>

          <div v-else-if="modalForm.status === 'published' && isFutureDate(modalForm.effective_date)" class="rounded-xl border border-blue-200 bg-blue-50/70 p-3 text-xs text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-200 flex items-start gap-2">
            <svg class="h-4 w-4 shrink-0 text-blue-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div>
              <span class="font-bold">Publikasi Terjadwal:</span>
              <p class="text-[11px] text-blue-700 dark:text-blue-300 mt-0.5">
                Pengumuman dijadwalkan otomatis mulai tayang di publik pada <strong>{{ formatDate(modalForm.effective_date) }}</strong><span v-if="modalForm.expires_at"> hingga <strong>{{ formatDate(modalForm.expires_at) }}</strong></span>.
              </p>
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
              <option value="published">Published (Siarkan / Jadwalkan)</option>
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
  expires_at: '',
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

const isFutureDate = (dateStr?: string | null) => {
  if (!dateStr) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const target = new Date(dateStr)
  target.setHours(0, 0, 0, 0)
  return target.getTime() > today.getTime()
}

const isPastDate = (dateStr?: string | null) => {
  if (!dateStr) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const target = new Date(dateStr)
  target.setHours(0, 0, 0, 0)
  return target.getTime() < today.getTime()
}

const getItemStatusBadge = (item: Announcement) => {
  if (item.status === 'draft') {
    return {
      label: 'Draft',
      class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
      subtext: 'Draf Internal',
      subtextClass: 'text-gray-400 dark:text-gray-500',
      description: 'Disimpan sebagai draf dan belum dipublikasikan.'
    }
  }
  if (item.status === 'review') {
    return {
      label: 'Review',
      class: 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300',
      subtext: 'Menunggu Review',
      subtextClass: 'text-amber-600 dark:text-amber-400',
      description: 'Menunggu persetujuan admin organisasi.'
    }
  }
  if (item.status === 'rejected') {
    return {
      label: 'Ditolak',
      class: 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
      subtext: 'Tidak Diterbitkan',
      subtextClass: 'text-rose-600 dark:text-rose-400',
      description: 'Pengumuman ditolak.'
    }
  }

  // Published: Check Expired First
  if (item.expires_at && isPastDate(item.expires_at)) {
    return {
      label: 'Kedaluwarsa',
      class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
      subtext: `Berakhir ${formatDate(item.expires_at)}`,
      subtextClass: 'text-slate-500 dark:text-slate-400',
      description: `Masa berlaku pengumuman sudah berakhir pada ${formatDate(item.expires_at)} dan tidak lagi tampil di portal publik.`
    }
  }

  // Published: Check Future / Scheduled
  if (isFutureDate(item.effective_date)) {
    return {
      label: 'Terjadwal',
      class: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300',
      subtext: `Mulai ${formatDate(item.effective_date)}`,
      subtextClass: 'text-blue-600 dark:text-blue-400 font-semibold',
      description: `Pengumuman dijadwalkan dan akan otomatis tampil di portal publik pada ${formatDate(item.effective_date)}.`
    }
  }

  return {
    label: 'Aktif',
    class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
    subtext: item.expires_at ? `s/d ${formatDate(item.expires_at)}` : 'Tayang di Publik',
    subtextClass: 'text-emerald-600 dark:text-emerald-400',
    description: 'Pengumuman aktif dan tampil di portal publik.'
  }
}

const modalCurrentStatusBadge = computed(() => {
  if (modalForm.value.status === 'draft') {
    return { label: 'Draft', class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }
  }
  if (modalForm.value.expires_at && isPastDate(modalForm.value.expires_at)) {
    return { label: 'Kedaluwarsa', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400' }
  }
  if (isFutureDate(modalForm.value.effective_date)) {
    return { label: 'Terjadwal', class: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300' }
  }
  return { label: 'Aktif', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300' }
})

const formatDate = (dateStr?: string | null) => {
  if (!dateStr) return 'Langsung Berlaku'
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
    expires_at: '',
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
    expires_at: item.expires_at ? item.expires_at.split('T')[0] : '',
    status: item.status === 'published' ? 'published' : 'draft'
  }
  isModalOpen.value = true
}

const submitModalForm = async () => {
  if (!modalForm.value.title.trim() || !modalForm.value.content.trim()) {
    toastStore.error('Judul dan isi pengumuman wajib diisi.')
    return
  }

  if (modalForm.value.effective_date && modalForm.value.expires_at) {
    if (modalForm.value.expires_at < modalForm.value.effective_date) {
      toastStore.error('Tanggal berlaku hingga tidak boleh lebih awal dari tanggal efektif.')
      return
    }
  }

  isSubmitting.value = true
  try {
    if (editingId.value) {
      await announcementService.update(editingId.value, {
        title: modalForm.value.title,
        content: modalForm.value.content,
        priority: modalForm.value.priority,
        effective_date: modalForm.value.effective_date || null,
        expires_at: modalForm.value.expires_at || null,
        status: modalForm.value.status
      })
      
      if (modalForm.value.status === 'published') {
        if (isPastDate(modalForm.value.expires_at)) {
          toastStore.success('Pengumuman disimpan (Status: Kedaluwarsa karena melewati tanggal batas).')
        } else if (isFutureDate(modalForm.value.effective_date)) {
          toastStore.success(`Pengumuman berhasil dijadwalkan untuk tayang pada ${formatDate(modalForm.value.effective_date)}.`)
        } else {
          toastStore.success('Pengumuman berhasil diperbarui dan kini aktif di portal publik!')
        }
      } else {
        toastStore.success('Pengumuman berhasil disimpan sebagai draft.')
      }
    } else {
      await announcementService.create({
        title: modalForm.value.title,
        content: modalForm.value.content,
        priority: modalForm.value.priority,
        effective_date: modalForm.value.effective_date || null,
        expires_at: modalForm.value.expires_at || null,
        status: modalForm.value.status
      })
      
      if (modalForm.value.status === 'published') {
        if (isPastDate(modalForm.value.expires_at)) {
          toastStore.success('Pengumuman disimpan (Status: Kedaluwarsa karena melewati tanggal batas).')
        } else if (isFutureDate(modalForm.value.effective_date)) {
          toastStore.success(`Pengumuman berhasil dijadwalkan untuk tayang pada ${formatDate(modalForm.value.effective_date)}.`)
        } else {
          toastStore.success('Pengumuman baru berhasil diterbitkan dan kini aktif di portal publik!')
        }
      } else {
        toastStore.success('Pengumuman baru berhasil disimpan sebagai draft.')
      }
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