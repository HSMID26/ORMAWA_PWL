<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Manajemen Agenda & Acara'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Action Button ─────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <CalendarIcon class="h-5 w-5 text-indigo-500" />
            Daftar Agenda Kegiatan
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Kelola jadwal workshop, rapat kerja, seminar, dan acara organisasi lainnya.
          </p>
        </div>
        <router-link
          to="/organization/agenda/create"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition"
        >
          <PlusIcon class="h-4 w-4" />
          Tambah Agenda Baru
        </router-link>
      </div>

      <!-- ─── Tabs Filter (Upcoming, Today, Past, All) ────────────────────────── -->
      <div class="mb-5 flex border-b border-gray-200 dark:border-gray-800 text-xs font-semibold">
        <button
          @click="activeTab = 'upcoming'"
          :class="[
            'pb-3 px-4 border-b-2 transition flex items-center gap-1.5',
            activeTab === 'upcoming'
              ? 'border-brand-600 text-brand-600 dark:text-brand-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
          ]"
        >
          <span>Mendatang (Upcoming)</span>
          <span class="rounded-full bg-brand-50 px-1.5 py-0.5 text-[10px] text-brand-600 dark:bg-brand-500/10">
            {{ countUpcoming }}
          </span>
        </button>

        <button
          @click="activeTab = 'today'"
          :class="[
            'pb-3 px-4 border-b-2 transition flex items-center gap-1.5',
            activeTab === 'today'
              ? 'border-brand-600 text-brand-600 dark:text-brand-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
          ]"
        >
          <span>Hari Ini (Today)</span>
          <span v-if="countToday > 0" class="rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] text-emerald-600">
            {{ countToday }}
          </span>
        </button>

        <button
          @click="activeTab = 'past'"
          :class="[
            'pb-3 px-4 border-b-2 transition flex items-center gap-1.5',
            activeTab === 'past'
              ? 'border-brand-600 text-brand-600 dark:text-brand-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
          ]"
        >
          <span>Riwayat (Past)</span>
          <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-600 dark:bg-gray-800">
            {{ countPast }}
          </span>
        </button>

        <button
          @click="activeTab = 'all'"
          :class="[
            'pb-3 px-4 border-b-2 transition',
            activeTab === 'all'
              ? 'border-brand-600 text-brand-600 dark:text-brand-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
          ]"
        >
          Semua ({{ agendas.length }})
        </button>
      </div>

      <!-- ─── Search & Status Toolbar ────────────────────────────────────────── -->
      <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="sm:col-span-2 relative">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama agenda kegiatan atau lokasi..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <div>
          <select
            v-model="filterStatus"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Status Publikasi</option>
            <option value="published">Published (Terbit)</option>
            <option value="draft">Draft</option>
          </select>
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="space-y-3 py-6">
        <div v-for="i in 4" :key="i" class="h-16 w-full animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Error State ────────────────────────────────────────────────────── -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button @click="loadAgendas" class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
          Coba Lagi
        </button>
      </div>

      <!-- ─── Table Data ─────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Tanggal</th>
              <th class="px-4 py-3">Nama Kegiatan</th>
              <th class="px-4 py-3">Deskripsi & Lokasi</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
            <tr
              v-for="item in filteredAgendas"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
            >
              <!-- Tanggal Box -->
              <td class="px-4 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-2.5">
                  <div class="flex h-11 w-11 flex-col items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
                    <span class="text-sm font-bold leading-none">{{ getDay(item.tanggal_pelaksanaan) }}</span>
                    <span class="text-[10px] uppercase font-semibold mt-0.5 leading-none">{{ getMonth(item.tanggal_pelaksanaan) }}</span>
                  </div>
                  <div class="text-[11px] text-gray-500">
                    <div>{{ getYear(item.tanggal_pelaksanaan) }}</div>
                    <span
                      class="inline-block rounded px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wider"
                      :class="getTimingBadgeClass(item.tanggal_pelaksanaan)"
                    >
                      {{ getTimingLabel(item.tanggal_pelaksanaan) }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Nama Kegiatan -->
              <td class="px-4 py-3.5">
                <h3 class="font-bold text-gray-900 dark:text-white line-clamp-1">
                  {{ item.judul }}
                </h3>
                <span class="text-[11px] text-gray-400">Dibuat oleh {{ item.user?.name || 'Admin' }}</span>
              </td>

              <!-- Deskripsi & Lokasi -->
              <td class="px-4 py-3.5">
                <p class="text-gray-600 dark:text-gray-300 line-clamp-2 max-w-sm">
                  {{ item.deskripsi }}
                </p>
              </td>

              <!-- Status -->
              <td class="px-4 py-3.5">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                  :class="item.status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'"
                >
                  {{ item.status === 'published' ? 'Published' : 'Draft' }}
                </span>
              </td>

              <!-- Aksi -->
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Detail Modal -->
                  <button
                    @click="openDetail(item)"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Lihat Detail"
                  >
                    <EyeIcon class="h-4 w-4" />
                  </button>

                  <!-- Edit -->
                  <router-link
                    :to="`/organization/agenda/edit/${item.id}`"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 transition"
                    title="Edit Agenda"
                  >
                    <PencilIcon class="h-4 w-4" />
                  </router-link>

                  <!-- Delete -->
                  <button
                    @click="openDeleteModal(item)"
                    class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition"
                    title="Hapus Agenda"
                  >
                    <Trash2Icon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredAgendas.length === 0">
              <td colspan="5" class="px-4 py-12 text-center">
                <CalendarIcon class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-700 mb-2" />
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum Ada Agenda</h3>
                <p class="text-xs text-gray-400 mt-1">Tidak ada kegiatan yang terdaftar pada tab ini.</p>
                <router-link
                  to="/organization/agenda/create"
                  class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"
                >
                  <PlusIcon class="h-3.5 w-3.5" />
                  Tambah Agenda Baru
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Modal Detail Agenda ──────────────────────────────────────────────── -->
    <div
      v-if="isDetailOpen && selectedAgenda"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
      @click.self="isDetailOpen = false"
    >
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
          <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <CalendarIcon class="h-5 w-5 text-indigo-500" />
            Detail Agenda Kegiatan
          </h3>
          <button @click="isDetailOpen = false" class="rounded-lg p-1 text-gray-400 hover:text-gray-600">
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Nama Kegiatan</span>
            <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ selectedAgenda.judul }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4 rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
            <div>
              <span class="text-[10px] font-semibold text-gray-400 uppercase block">Tanggal Pelaksanaan</span>
              <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ formatDate(selectedAgenda.tanggal_pelaksanaan) }}</p>
            </div>
            <div>
              <span class="text-[10px] font-semibold text-gray-400 uppercase block">Status</span>
              <span class="inline-block mt-0.5 rounded px-2 py-0.5 text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700">
                {{ selectedAgenda.status }}
              </span>
            </div>
          </div>

          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Deskripsi & Informasi</span>
            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed mt-1 whitespace-pre-line">
              {{ selectedAgenda.deskripsi }}
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
          <button
            @click="isDetailOpen = false"
            class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Tutup
          </button>
          <router-link
            :to="`/organization/agenda/edit/${selectedAgenda.id}`"
            class="rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700"
          >
            Edit Kegiatan
          </router-link>
        </div>
      </div>
    </div>

    <!-- ─── Modal Konfirmasi Hapus ───────────────────────────────────────────── -->
    <div
      v-if="isDeleteOpen && agendaToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 mb-4">
          <Trash2Icon class="h-6 w-6" />
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus Agenda Acara?</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Apakah Anda yakin ingin menghapus agenda kegiatan <strong class="text-gray-900 dark:text-white">"{{ agendaToDelete.judul }}"</strong>? Tindakan ini tidak dapat dibatalkan.
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
import { activityService } from '@/services/activityService'
import { useToastStore } from '@/stores/toast'
import type { Activity } from '@/types/api'
import {
  CalendarIcon,
  PlusIcon,
  SearchIcon,
  EyeIcon,
  PencilIcon,
  Trash2Icon,
  AlertTriangleIcon,
  XIcon
} from 'lucide-vue-next'

const toastStore = useToastStore()

const agendas = ref<Activity[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

const activeTab = ref<'all' | 'upcoming' | 'today' | 'past'>('upcoming')
const searchQuery = ref('')
const filterStatus = ref('')

// Detail modal
const isDetailOpen = ref(false)
const selectedAgenda = ref<Activity | null>(null)

// Delete modal
const isDeleteOpen = ref(false)
const agendaToDelete = ref<Activity | null>(null)
const isDeleting = ref(false)

const loadAgendas = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    agendas.value = await activityService.list()
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat agenda kegiatan.'
  } finally {
    isLoading.value = false
  }
}

const todayStr = computed(() => {
  const d = new Date()
  return d.toISOString().split('T')[0]
})

const countUpcoming = computed(() => {
  return agendas.value.filter(a => a.tanggal_pelaksanaan > todayStr.value).length
})

const countToday = computed(() => {
  return agendas.value.filter(a => a.tanggal_pelaksanaan.startsWith(todayStr.value)).length
})

const countPast = computed(() => {
  return agendas.value.filter(a => a.tanggal_pelaksanaan < todayStr.value).length
})

const filteredAgendas = computed(() => {
  return agendas.value.filter(item => {
    // Tab Filter
    if (activeTab.value === 'upcoming' && item.tanggal_pelaksanaan <= todayStr.value) {
      return false
    }
    if (activeTab.value === 'today' && !item.tanggal_pelaksanaan.startsWith(todayStr.value)) {
      return false
    }
    if (activeTab.value === 'past' && item.tanggal_pelaksanaan >= todayStr.value) {
      return false
    }

    // Status Filter
    if (filterStatus.value && item.status !== filterStatus.value) {
      return false
    }

    // Search Filter
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchTitle = item.judul.toLowerCase().includes(q)
      const matchDesc = item.deskripsi.toLowerCase().includes(q)
      if (!matchTitle && !matchDesc) {
        return false
      }
    }

    return true
  })
})

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(new Date(dateStr))
}

const getDay = (dateStr: string) => {
  if (!dateStr) return '01'
  return new Date(dateStr).getDate().toString().padStart(2, '0')
}

const getMonth = (dateStr: string) => {
  if (!dateStr) return 'BLN'
  return new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(new Date(dateStr))
}

const getYear = (dateStr: string) => {
  if (!dateStr) return ''
  return new Date(dateStr).getFullYear()
}

const getTimingLabel = (dateStr: string) => {
  const d = dateStr.split('T')[0]
  if (d === todayStr.value) return 'Hari Ini'
  if (d > todayStr.value) return 'Mendatang'
  return 'Selesai'
}

const getTimingBadgeClass = (dateStr: string) => {
  const d = dateStr.split('T')[0]
  if (d === todayStr.value) return 'bg-emerald-100 text-emerald-700'
  if (d > todayStr.value) return 'bg-indigo-100 text-indigo-700'
  return 'bg-gray-100 text-gray-500'
}

const openDetail = (item: Activity) => {
  selectedAgenda.value = item
  isDetailOpen.value = true
}

const openDeleteModal = (item: Activity) => {
  agendaToDelete.value = item
  isDeleteOpen.value = true
}

const confirmDelete = async () => {
  if (!agendaToDelete.value || isDeleting.value) return
  isDeleting.value = true
  try {
    await activityService.remove(agendaToDelete.value.id)
    toastStore.success('Agenda kegiatan berhasil dihapus.')
    isDeleteOpen.value = false
    agendaToDelete.value = null
    await loadAgendas()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menghapus agenda.')
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  loadAgendas()
})
</script>