<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Kalender Kegiatan" />

    <main class="space-y-5">
      <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Kalender Kegiatan</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Jadwal kegiatan organisasi dalam tampilan bulanan atau daftar.
          </p>
        </div>
        <router-link
          v-if="authStore.hasPermission('agenda.view')"
          to="/organization/agenda"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        >
          Kelola agenda
        </router-link>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
        <p v-if="errorMessage" role="alert" class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-300">
          {{ errorMessage }}
        </p>
        <p v-else-if="isLoading" class="mb-4 text-sm text-gray-500" aria-live="polite">
          Memuat jadwal kegiatan…
        </p>
        <FullCalendar :options="calendarOptions" />
      </section>

      <section
        v-if="selectedEvent"
        class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"
        aria-live="polite"
      >
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Detail kegiatan</p>
            <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ selectedEvent.title }}</h2>
          </div>
          <span class="w-fit rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
            {{ statusLabel }}
          </span>
        </div>
        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">
          {{ selectedDescription || 'Tidak ada deskripsi kegiatan.' }}
        </p>
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
          {{ selectedDate }}<span v-if="selectedOrganization"> · {{ selectedOrganization }}</span>
        </p>
      </section>
    </main>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import listPlugin from '@fullcalendar/list'
import idLocale from '@fullcalendar/core/locales/id'
import type { CalendarOptions, EventApi } from '@fullcalendar/core'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { calendarService } from '@/services/calendarService'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const isLoading = ref(false)
const errorMessage = ref('')
const selectedEvent = ref<EventApi | null>(null)
let requestSequence = 0

const loadEvents = async (start: string, end: string) => {
  const sequence = ++requestSequence
  isLoading.value = true
  errorMessage.value = ''
  try {
    const events = await calendarService.list(start, end)
    if (sequence === requestSequence) calendarOptions.events = events
  } catch (error) {
    if (sequence === requestSequence) {
      errorMessage.value = error && typeof error === 'object' && 'message' in error && typeof error.message === 'string'
        ? error.message
        : 'Jadwal kegiatan gagal dimuat.'
    }
  } finally {
    if (sequence === requestSequence) isLoading.value = false
  }
}

const calendarOptions = reactive<CalendarOptions>({
  plugins: [dayGridPlugin, listPlugin],
  locale: idLocale,
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,listMonth',
  },
  buttonText: {
    today: 'Hari ini',
    month: 'Bulan',
    list: 'Daftar',
  },
  height: 'auto',
  events: [],
  datesSet: ({ startStr, endStr }) => {
    void loadEvents(startStr.slice(0, 10), endStr.slice(0, 10))
  },
  eventClick: ({ event }) => {
    selectedEvent.value = event
  },
})

const selectedDate = computed(() => {
  const start = selectedEvent.value?.start
  return start
    ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeZone: 'Asia/Jakarta' }).format(start)
    : ''
})
const selectedDescription = computed(() => {
  const value = selectedEvent.value?.extendedProps.description
  return typeof value === 'string' ? value : ''
})
const selectedOrganization = computed(() => {
  const value = selectedEvent.value?.extendedProps.organization
  return typeof value === 'string' ? value : ''
})
const statusLabel = computed(() => {
  const status = selectedEvent.value?.extendedProps.status
  const labels: Record<string, string> = {
    draft: 'Draf',
    review: 'Menunggu tinjauan',
    revision_requested: 'Perlu revisi',
    published: 'Dipublikasikan',
    rejected: 'Ditolak',
  }
  return typeof status === 'string' ? labels[status] ?? status : 'Kegiatan'
})
</script>
