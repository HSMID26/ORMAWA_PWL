<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <!-- Error State Global -->
    <div v-if="!isLoading && isError" class="flex h-64 flex-col items-center justify-center gap-4 rounded-2xl border border-error-200 bg-error-50 p-6 dark:border-error-800 dark:bg-error-900/20">
      <AlertCircleIcon class="h-10 w-10 text-error-500" />
      <h3 class="text-lg font-bold text-error-700 dark:text-error-400">Gagal memuat data utama dashboard.</h3>
      <button @click="fetchDashboardData" class="rounded-lg bg-error-600 px-4 py-2 text-sm font-medium text-white hover:bg-error-700">Refresh</button>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      
      <!-- 1. GOVERNANCE ALERTS SECTION -->
      <div v-if="hasAlerts" class="space-y-3">
        <!-- Alert Pending Registrasi -->
        <div v-if="stats.governance_alerts.pending_registrations > 0" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 rounded-2xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-800/50 dark:bg-warning-950/20">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-warning-500 text-white">
              <ClipboardCheckIcon class="h-5 w-5" />
            </div>
            <div>
              <h4 class="text-sm font-bold text-warning-900 dark:text-warning-300">Pendaftaran Organisasi Menunggu Review</h4>
              <p class="text-xs text-warning-700 dark:text-warning-400">Terdapat {{ stats.governance_alerts.pending_registrations }} permintaan pendaftaran ormawa baru yang perlu ditinjau.</p>
            </div>
          </div>
          <router-link to="/super-admin/organization-registrations" class="shrink-0 rounded-lg bg-warning-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-warning-700 transition">
            Review Pendaftaran
          </router-link>
        </div>

        <!-- Alert Pending Renewal Periode -->
        <div v-if="stats.governance_alerts.pending_renewals > 0" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 rounded-2xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-800/50 dark:bg-brand-950/20">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white">
              <CalendarClockIcon class="h-5 w-5" />
            </div>
            <div>
              <h4 class="text-sm font-bold text-brand-900 dark:text-brand-300">Pengajuan Perpanjangan Periode Menunggu Persetujuan</h4>
              <p class="text-xs text-brand-700 dark:text-brand-400">Terdapat {{ stats.governance_alerts.pending_renewals }} pengajuan perpanjangan kepengurusan yang memerlukan persetujuan.</p>
            </div>
          </div>
          <router-link to="/super-admin/periods" class="shrink-0 rounded-lg bg-brand-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-brand-700 transition">
            Review Periode
          </router-link>
        </div>

        <!-- Alert Periode Akan Berakhir -->
        <div v-if="stats.governance_alerts.expiring_periods > 0" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-800/50 dark:bg-orange-950/20">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-500 text-white">
              <AlertCircleIcon class="h-5 w-5" />
            </div>
            <div>
              <h4 class="text-sm font-bold text-orange-900 dark:text-orange-300">Periode Kepengurusan Segera Berakhir</h4>
              <p class="text-xs text-orange-700 dark:text-orange-400">Terdapat {{ stats.governance_alerts.expiring_periods }} organisasi yang masa kepengurusannya akan habis dalam <= 30 hari.</p>
            </div>
          </div>
          <router-link to="/super-admin/periods" class="shrink-0 rounded-lg bg-orange-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-orange-700 transition">
            Pantau Organisasi
          </router-link>
        </div>
      </div>

      <!-- 2. METRIC CARDS UTAMA PLATFORM -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 2xl:gap-6">
        <!-- Total Organisasi Aktif -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
              <BuildingIcon class="h-6 w-6" />
            </div>
            <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
              {{ stats.organizations.active }} Aktif
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.organizations.total }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">Total Organisasi Terdaftar</p>
          </div>
        </div>

        <!-- Total Pengguna Platform -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
              <UsersIcon class="h-6 w-6" />
            </div>
            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
              {{ stats.users.active }} Aktif
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.users.total }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">Pengguna Operasional (Ormawa)</p>
          </div>
        </div>

        <!-- Konten Terpublikasi -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
              <FileTextIcon class="h-6 w-6" />
            </div>
            <span class="rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
              {{ stats.content.posts.published }} Terbit
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.content.posts.total }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">Total Berita & Artikel</p>
          </div>
        </div>

        <!-- Media & Dokumen -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
              <LayersIcon class="h-6 w-6" />
            </div>
            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
              {{ stats.content.media.images }} Galeri
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.content.media.total }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">Total Berkas & Media Global</p>
          </div>
        </div>
      </div>

      <!-- 3. BREAKDOWN STATISTIK KONTEN, PENGGUNA & PERIODE -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Panel 1: Breakdown Konten Global -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
              <FileTextIcon class="h-4 w-4 text-brand-500" />
              Monitoring Konten Global
            </h3>
            <router-link to="/super-admin/content" class="text-xs font-medium text-brand-500 hover:text-brand-600">Lihat Semua</router-link>
          </div>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Artikel Published</span>
              <span class="text-sm font-bold text-green-600">{{ stats.content.posts.published }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Artikel In-Review</span>
              <span class="text-sm font-bold text-yellow-600">{{ stats.content.posts.review }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Artikel Draft / Rejected</span>
              <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ stats.content.posts.draft + stats.content.posts.rejected }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Agenda Acara Mendatang</span>
              <span class="text-sm font-bold text-blue-600">{{ stats.content.agendas.upcoming }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Pengumuman Aktif</span>
              <span class="text-sm font-bold text-purple-600">{{ stats.content.announcements.published }}</span>
            </div>
          </div>
        </div>

        <!-- Panel 2: Breakdown Pengguna Ormawa -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
              <UsersIcon class="h-4 w-4 text-blue-500" />
              Distribusi Pengguna Organisasi
            </h3>
            <router-link to="/super-admin/users" class="text-xs font-medium text-brand-500 hover:text-brand-600">Lihat Semua</router-link>
          </div>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Admin Organisasi</span>
              <span class="text-sm font-bold text-brand-600">{{ stats.users.org_admins }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Editor Konten</span>
              <span class="text-sm font-bold text-blue-600">{{ stats.users.editors }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Kontributor Berita</span>
              <span class="text-sm font-bold text-indigo-600">{{ stats.users.contributors }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
              <span class="text-xs text-gray-600 dark:text-gray-400">Akun Nonaktif / Ditangguhkan</span>
              <span class="text-sm font-bold text-red-600">{{ stats.users.inactive }}</span>
            </div>
          </div>
        </div>

        <!-- Panel 3: Tata Kelola Periode Ormawa -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
              <CalendarClockIcon class="h-4 w-4 text-purple-500" />
              Tata Kelola Periode Kepengurusan
            </h3>
            <router-link to="/super-admin/periods" class="text-xs font-medium text-brand-500 hover:text-brand-600">Lihat Semua</router-link>
          </div>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-green-50/60 dark:bg-green-950/20">
              <span class="text-xs text-green-800 dark:text-green-300">Periode Aktif Normal</span>
              <span class="text-sm font-bold text-green-700 dark:text-green-400">{{ stats.periods.active }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-brand-50/60 dark:bg-brand-950/20">
              <span class="text-xs text-brand-800 dark:text-brand-300">Menunggu Persetujuan Renewal</span>
              <span class="text-sm font-bold text-brand-700 dark:text-brand-400">{{ stats.periods.pending }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-orange-50/60 dark:bg-orange-950/20">
              <span class="text-xs text-orange-800 dark:text-orange-300">Segera Habis (<= 30 hari)</span>
              <span class="text-sm font-bold text-orange-700 dark:text-orange-400">{{ stats.periods.expiring_soon }}</span>
            </div>
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-red-50/60 dark:bg-red-950/20">
              <span class="text-xs text-red-800 dark:text-red-300">Periode Kedaluwarsa</span>
              <span class="text-sm font-bold text-red-700 dark:text-red-400">{{ stats.periods.expired }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. RECENT GLOBAL ACTIVITY LOG -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <ActivityIcon class="h-5 w-5 text-brand-500" />
              Aktivitas Platform Terbaru
            </h3>
            <p class="text-xs text-gray-500">Audit trail aktivitas seluruh organisasi dan administrator.</p>
          </div>
          <router-link to="/super-admin/activity-logs" class="text-xs font-semibold text-brand-500 hover:text-brand-600">
            Buka Audit Log Lengkap →
          </router-link>
        </div>

        <div v-if="stats.recent_activities.length === 0" class="py-8 text-center text-sm text-gray-500">
          Belum ada rekaman aktivitas sistem.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400">Waktu</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400">Aktor</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400">Organisasi</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400">Aksi & Modul</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400">Deskripsi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in stats.recent_activities" :key="log.id" class="border-b border-gray-100 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-white/[0.02] text-xs">
                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                  {{ new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) }}
                </td>
                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                  {{ log.actor }}
                </td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                  <span class="rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ log.organization }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="rounded-md bg-brand-50 px-2 py-0.5 font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 uppercase tracking-wide text-[10px]">
                    {{ log.action }} : {{ log.module }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                  {{ log.description }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  BuildingIcon,
  UsersIcon,
  FileTextIcon,
  LayersIcon,
  ClipboardCheckIcon,
  CalendarClockIcon,
  ActivityIcon,
  AlertCircleIcon,
} from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { getSuperAdminDashboard, type SuperAdminDashboardSummary } from '@/services/dashboardService'

const currentPageTitle = ref('Dashboard Super Admin')
const isLoading = ref(true)
const isError = ref(false)

const stats = ref<SuperAdminDashboardSummary>({
  organizations: { total: 0, active: 0, inactive: 0, pending_registrations: 0 },
  periods: { active: 0, pending: 0, expired: 0, expiring_soon: 0 },
  users: { total: 0, active: 0, inactive: 0, org_admins: 0, editors: 0, contributors: 0 },
  content: {
    posts: { total: 0, published: 0, review: 0, draft: 0, rejected: 0 },
    agendas: { total: 0, upcoming: 0, today: 0, past: 0 },
    announcements: { total: 0, published: 0, draft: 0, archived: 0 },
    media: { total: 0, images: 0, documents: 0 },
  },
  governance_alerts: {
    pending_registrations: 0,
    pending_renewals: 0,
    expiring_periods: 0,
    expired_periods: 0,
    inactive_organizations: 0,
  },
  recent_activities: [],
})

const hasAlerts = computed(() => {
  const alerts = stats.value.governance_alerts
  return alerts.pending_registrations > 0 || alerts.pending_renewals > 0 || alerts.expiring_periods > 0
})

const fetchDashboardData = async () => {
  isLoading.value = true
  isError.value = false

  try {
    const data = await getSuperAdminDashboard()
    stats.value = data
  } catch (err) {
    console.error('Error loading super admin dashboard:', err)
    isError.value = true
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
