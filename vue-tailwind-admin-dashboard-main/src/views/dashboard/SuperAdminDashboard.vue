<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <!-- Error State Global (Hanya jika Dashboard API gagal) -->
    <div v-if="!isLoading && isError" class="flex h-64 flex-col items-center justify-center gap-4 rounded-2xl border border-error-200 bg-error-50 p-6 dark:border-error-800 dark:bg-error-900/20">
      <AlertCircleIcon class="h-10 w-10 text-error-500" />
      <h3 class="text-lg font-bold text-error-700 dark:text-error-400">Gagal memuat data utama dashboard.</h3>
      <button @click="fetchDashboardData" class="rounded-lg bg-error-600 px-4 py-2 text-sm font-medium text-white hover:bg-error-700">Refresh</button>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Bagian 1: Kartu Metrik Utama (Global) -->
      <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
        
        <!-- Loading State untuk Kartu -->
        <template v-if="isLoading">
          <div v-for="i in 4" :key="i" class="animate-pulse rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="h-11 w-11 rounded-full bg-gray-200 dark:bg-gray-700"></div>
            <div class="mt-4 flex flex-col gap-2">
              <div class="h-6 w-1/2 rounded bg-gray-200 dark:bg-gray-700"></div>
              <div class="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
        </template>
        
        <template v-else>
          <!-- Total Organisasi -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/20 dark:text-brand-400">
              <BuildingIcon class="h-6 w-6" />
            </div>
            <div class="mt-4 flex items-end justify-between">
              <div>
                <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">{{ stats.organizations.active }}</h4>
                <span class="text-sm font-medium text-gray-500">Total Organisasi Aktif</span>
              </div>
            </div>
          </div>

          <!-- Total Pengguna Global -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 text-blue-500 dark:bg-blue-500/20 dark:text-blue-400">
              <UsersIcon class="h-6 w-6" />
            </div>
            <div class="mt-4 flex items-end justify-between">
              <div>
                <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">{{ stats.users.total }}</h4>
                <span class="text-sm font-medium text-gray-500">Total Pengguna Sistem</span>
              </div>
            </div>
          </div>

          <!-- Kapasitas Penyimpanan Global -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-warning-50 text-warning-500 dark:bg-warning-500/20 dark:text-warning-400">
              <HardDriveIcon class="h-6 w-6" />
            </div>
            <div class="mt-4 flex items-end justify-between">
              <div v-if="stats.storage.available">
                <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">{{ stats.storage.used }} GB</h4>
                <span class="text-sm font-medium text-gray-500">Storage Terpakai (Kapasitas {{ stats.storage.capacity }}GB)</span>
              </div>
              <div v-else>
                <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">-</h4>
                <span class="text-sm font-medium text-gray-500">Data storage belum tersedia</span>
              </div>
            </div>
          </div>

          <!-- Total Konten -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-purple-50 text-purple-500 dark:bg-purple-500/20 dark:text-purple-400">
              <FileTextIcon class="h-6 w-6" />
            </div>
            <div class="mt-4 flex items-end justify-between">
              <div>
                <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">{{ stats.content.published }}</h4>
                <span class="text-sm font-medium text-gray-500">Total Konten Terpublikasi</span>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Bagian 2: Area Konten Utama -->
      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        
        <!-- Panel Kiri: Antrean Pendaftaran Organisasi Baru -->
        <div class="flex flex-col gap-6 xl:col-span-2">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <div class="mb-5 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                  Permintaan Pendaftaran Organisasi
                  <span v-if="!isLoading && stats.registrations.pending > 0" class="rounded-full bg-warning-100 px-2.5 py-0.5 text-xs font-medium text-warning-700 dark:bg-warning-900/30 dark:text-warning-400">
                    {{ stats.registrations.pending }} Menunggu
                  </span>
                </h3>
                <p class="text-sm text-gray-500">Menunggu persetujuan pendaftaran.</p>
              </div>
              <router-link to="/super-admin/organization-registrations" class="text-sm font-medium text-brand-500 hover:text-brand-600">Lihat Semua</router-link>
            </div>

            <div class="overflow-x-auto">
              <div v-if="isLoadingRegistrations" class="animate-pulse flex flex-col gap-3 py-4">
                <div v-for="i in 3" :key="i" class="h-12 w-full rounded bg-gray-100 dark:bg-gray-800"></div>
              </div>
              <div v-else-if="isErrorRegistrations" class="py-6 text-center text-sm text-error-500">
                Gagal memuat pendaftaran organisasi.
              </div>
              <table v-else-if="pendingRegistrations.length > 0" class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama Organisasi</th>
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Jenis</th>
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Pemohon</th>
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="req in pendingRegistrations" :key="req.id" class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ req.organization_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ req.organization_type }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ req.admin_first_name }} {{ req.admin_last_name }}</td>
                    <td class="px-4 py-3 text-center">
                      <router-link :to="`/super-admin/organization-registrations/${req.id}`" class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition">
                        Review
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-else class="py-8 text-center text-sm text-gray-500">
                Belum ada pendaftaran organisasi yang menunggu persetujuan.
              </div>
            </div>
          </div>

          <!-- Statistik Konten -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
              Ringkasan Data Konten Global
            </h3>
            
            <div v-if="isLoading" class="animate-pulse grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="i in 4" :key="i" class="h-24 rounded-lg bg-gray-100 dark:bg-gray-800/50"></div>
            </div>
            
            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
               <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                 <p class="text-sm text-gray-500">Total Berita</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.content.posts }}</p>
               </div>
               <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                 <p class="text-sm text-gray-500">Total Agenda</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.content.activities }}</p>
               </div>
               <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                 <p class="text-sm text-gray-500">Total Pengumuman</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.content.announcements }}</p>
               </div>
               <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                 <p class="text-sm text-gray-500">Draft (Belum Terbit)</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.content.draft }}</p>
               </div>
            </div>
          </div>

          <!-- Statistik Periode Organisasi -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:p-6 mt-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
              Periode Organisasi
            </h3>
            
            <div v-if="isLoading" class="animate-pulse grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="i in 4" :key="i" class="h-24 rounded-lg bg-gray-100 dark:bg-gray-800/50"></div>
            </div>
            
            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
               <div class="rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-900/30 dark:bg-green-900/10">
                 <p class="text-sm text-green-600 dark:text-green-400 font-medium">Active</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.periods?.active || 0 }}</p>
               </div>
               <div class="rounded-lg border border-orange-100 bg-orange-50 p-4 dark:border-orange-900/30 dark:bg-orange-900/10">
                 <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Expiring Soon</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.periods?.expiring_soon || 0 }}</p>
               </div>
               <div class="rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-900/30 dark:bg-red-900/10">
                 <p class="text-sm text-red-600 dark:text-red-400 font-medium">Expired</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.periods?.expired || 0 }}</p>
               </div>
               <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
                 <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Renewal Pending</p>
                 <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.periods?.pending || 0 }}</p>
               </div>
            </div>
          </div>
        </div>

        <!-- Panel Kanan: Status Sistem & Quick Actions -->
        <div class="flex flex-col gap-6 xl:col-span-1">
          
          <!-- Quick Actions Super Admin -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Aksi Cepat Pusat</h3>
            <div class="grid grid-cols-2 gap-3">
              <router-link to="/super-admin/organization-registrations" class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition text-brand-500 dark:text-brand-400 group">
                <ClipboardCheckIcon class="h-8 w-8 mb-2 group-hover:scale-110 transition-transform" />
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Pendaftaran</span>
              </router-link>
              <router-link to="/super-admin/organizations" class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition text-brand-500 dark:text-brand-400 group">
                <BuildingIcon class="h-8 w-8 mb-2 group-hover:scale-110 transition-transform" />
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Organisasi</span>
              </router-link>
              <router-link to="/super-admin/users" class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition text-brand-500 dark:text-brand-400 group">
                <UsersIcon class="h-8 w-8 mb-2 group-hover:scale-110 transition-transform" />
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Pengguna</span>
              </router-link>
              <router-link to="/super-admin/activity-logs" class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition text-brand-500 dark:text-brand-400 group">
                <ActivityIcon class="h-8 w-8 mb-2 group-hover:scale-110 transition-transform" />
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Activity Log</span>
              </router-link>
              <button disabled title="Fitur belum tersedia" class="col-span-2 flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 opacity-50 cursor-not-allowed dark:border-gray-700 transition text-gray-500">
                <SettingsIcon class="h-6 w-6 mb-2" />
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Pengaturan Platform (Segera Tersedia)</span>
              </button>
            </div>
          </div>

          <!-- Log Aktivitas Lintas Instance Terbaru -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Aktivitas Sistem Terbaru</h3>
            
            <div v-if="isLoadingLogs" class="animate-pulse flex flex-col gap-4 py-2">
              <div v-for="i in 3" :key="i" class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 shrink-0"></div>
                <div class="flex flex-col gap-2 w-full mt-1">
                  <div class="h-3 w-3/4 rounded bg-gray-200 dark:bg-gray-700"></div>
                  <div class="h-2 w-1/2 rounded bg-gray-200 dark:bg-gray-700"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="isErrorLogs" class="py-4 text-center text-sm text-error-500">
              Gagal memuat aktivitas.
            </div>

            <ul v-else-if="recentGlobalLogs.length > 0" class="flex flex-col gap-4">
              <li v-for="log in recentGlobalLogs" :key="log.id" class="flex items-start gap-3">
                <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300 shrink-0">
                  {{ log.user ? log.user.name.charAt(0) : 'S' }}
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-800 dark:text-white/90 leading-tight">
                    {{ log.user ? log.user.name : 'Sistem' }} 
                    <span v-if="log.organization" class="font-normal text-gray-500">dari</span> 
                    {{ log.organization ? log.organization.nama : '' }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">{{ log.description }}</p>
                  <span class="text-[10px] text-gray-400 mt-1 block">{{ new Date(log.created_at).toLocaleString('id-ID') }}</span>
                </div>
              </li>
            </ul>
            <div v-else class="py-4 text-center text-sm text-gray-500">
              Belum ada aktivitas sistem.
            </div>

            <router-link to="/super-admin/activity-logs" class="mt-5 flex w-full justify-center rounded-lg border border-gray-300 bg-white py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
              Lihat Semua Log
            </router-link>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { BuildingIcon, UsersIcon, HardDriveIcon, FileTextIcon, ClipboardCheckIcon, ActivityIcon, SettingsIcon, AlertCircleIcon } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { getSuperAdminDashboard } from '@/services/dashboardService'
import { organizationRegistrationService } from '@/services/organizationRegistrationService'
import { activityLogService } from '@/services/activityLogService'

const currentPageTitle = ref('Dashboard Super Admin — CMS ORMAWA')

const isLoading = ref(true)
const isError = ref(false)

const isLoadingRegistrations = ref(true)
const isErrorRegistrations = ref(false)

const isLoadingLogs = ref(true)
const isErrorLogs = ref(false)

const stats = ref({
  organizations: { total: 0, active: 0 },
  periods: { active: 0, pending: 0, expired: 0, expiring_soon: 0 },
  users: { total: 0, active: 0 },
  registrations: { pending: 0 },
  content: { posts: 0, activities: 0, announcements: 0, published: 0, draft: 0 },
  storage: { available: false, used: null, capacity: null, percentage: null }
})

const pendingRegistrations = ref([])
const recentGlobalLogs = ref([])

const fetchDashboardData = async () => {
  isLoading.value = true
  isError.value = false
  isLoadingRegistrations.value = true
  isErrorRegistrations.value = false
  isLoadingLogs.value = true
  isErrorLogs.value = false

  try {
    const [summaryRes, regRes, logsRes] = await Promise.allSettled([
      getSuperAdminDashboard(),
      organizationRegistrationService.list({ status: 'pending', per_page: 5 }),
      activityLogService.getLogs({ per_page: 5 })
    ])

    // Handle Dashboard Summary Data
    if (summaryRes.status === 'fulfilled') {
      stats.value = summaryRes.value
    } else {
      console.error('Failed to load dashboard summary', summaryRes.reason)
      isError.value = true
    }
    isLoading.value = false

    // Handle Registrations Data
    if (regRes.status === 'fulfilled') {
      pendingRegistrations.value = regRes.value.data || []
    } else {
      console.error('Failed to load pending registrations', regRes.reason)
      isErrorRegistrations.value = true
    }
    isLoadingRegistrations.value = false
    
    // Handle Activity Logs Data
    if (logsRes.status === 'fulfilled') {
      recentGlobalLogs.value = logsRes.value.data?.data || []
    } else {
      console.error('Failed to load recent activity logs', logsRes.reason)
      isErrorLogs.value = true
    }
    isLoadingLogs.value = false

  } catch (err) {
    // Top-level fallback, though allSettled shouldn't throw here typically
    console.error('Unexpected error in fetchDashboardData:', err)
    isError.value = true
    isLoading.value = false
    isLoadingRegistrations.value = false
    isLoadingLogs.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
