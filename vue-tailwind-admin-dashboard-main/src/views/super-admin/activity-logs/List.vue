<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Activity Log'" />
    
    <div class="p-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      
      <!-- Header -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Activity Log</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Riwayat aktivitas pengguna dan perubahan data sistem.</p>
        </div>
        <button @click="() => fetchLogs()" class="flex w-full justify-center rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 sm:w-auto">
          Refresh
        </button>
      </div>

      <!-- Toolbar / Filters -->
      <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6">
        <div class="lg:col-span-2">
          <input
            v-model="filters.search"
            @keyup.enter="applyFilters"
            type="text"
            placeholder="Search aktivitas..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          />
        </div>
        <div>
          <select v-model="filters.module" @change="applyFilters" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            <option value="">Semua Modul</option>
            <option value="authentication">Authentication</option>
            <option value="users">Users</option>
            <option value="organizations">Organizations</option>
            <option value="posts">Posts</option>
            <option value="activities">Activities</option>
            <option value="announcements">Announcements</option>
            <option value="organization_registrations">Registrations</option>
          </select>
        </div>
        <div>
          <select v-model="filters.action" @change="applyFilters" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            <option value="">Semua Action</option>
            <option value="create">Create</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="publish">Publish</option>
            <option value="reject">Reject</option>
            <option value="approve">Approve</option>
            <option value="register">Register</option>
            <option value="login_failed">Login Failed</option>
          </select>
        </div>
        <div>
          <input
            v-model="filters.date_from"
            @change="applyFilters"
            type="date"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          />
        </div>
        <div class="flex gap-2">
          <button @click="resetFilters" class="flex h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            Reset
          </button>
        </div>
      </div>

      <!-- Tabel Data -->
      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Memuat activity log...
      </div>
      <div v-else-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
        {{ errorMessage }}
        <button @click="() => fetchLogs()" class="ml-4 font-semibold hover:underline">Coba Lagi</button>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Waktu</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Pengguna</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Organisasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Aktivitas</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Modul</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Detail</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="log in logs" 
              :key="log.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90 whitespace-nowrap">
                {{ formatDate(log.created_at) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">
                {{ log.user ? log.user.name : 'Guest/System' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">
                {{ log.organization ? log.organization.nama : '-' }}
              </td>
              <td class="px-4 py-3">
                <span :class="getActionBadgeClass(log.action)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium">
                  {{ log.action }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                {{ log.module }}
              </td>
              <td class="px-4 py-3 text-center">
                <button @click="viewDetail(log)" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Lihat</button>
              </td>
            </tr>
            <tr v-if="logs.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada aktivitas.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="!isLoading && meta && meta.last_page > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-800">
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Halaman {{ meta.current_page }} dari {{ meta.last_page }} (Total: {{ meta.total }} aktivitas)
        </div>
        <div class="flex gap-2">
          <button 
            @click="changePage(meta.current_page - 1)" 
            :disabled="meta.current_page === 1"
            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
          >
            Prev
          </button>
          <button 
            @click="changePage(meta.current_page + 1)" 
            :disabled="meta.current_page === meta.last_page"
            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
          >
            Next
          </button>
        </div>
      </div>

    </div>

    <!-- Modal Detail Log -->
    <Modal v-if="selectedLog" @close="selectedLog = null" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Detail Aktivitas</h3>
            <button @click="selectedLog = null" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="space-y-4">
            <div>
              <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Deskripsi</span>
              <p class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ selectedLog.description }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Waktu</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedLog.created_at) }}</p>
              </div>
              <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Aksi & Modul</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.action }} / {{ selectedLog.module }}</p>
              </div>
              <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengguna</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.user ? selectedLog.user.name : 'Guest/System' }}</p>
              </div>
              <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Organisasi</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.organization ? selectedLog.organization.nama : '-' }}</p>
              </div>
            </div>

            <div v-if="selectedLog.subject_type">
              <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Data Terkait</span>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.subject_type }} (ID: {{ selectedLog.subject_id }})</p>
            </div>

            <div v-if="selectedLog.metadata" class="pt-2 border-t border-gray-100 dark:border-gray-800">
              <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400 mb-2">Metadata</span>
              <pre class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg text-xs overflow-x-auto text-gray-700 dark:text-gray-300 font-mono">{{ formatJSON(selectedLog.metadata) }}</pre>
            </div>
          </div>
        </div>
      </template>
    </Modal>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import Modal from '@/components/ui/Modal.vue';
import { activityLogService, type ActivityLogParams } from '@/services/activityLogService';

const logs = ref<any[]>([]);
const meta = ref<any>(null);
const isLoading = ref(true);
const errorMessage = ref('');
const selectedLog = ref<any>(null);

const filters = ref({
  search: '',
  module: '',
  action: '',
  date_from: ''
});

const fetchLogs = async (page = 1) => {
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    const params: ActivityLogParams = {
      page,
      per_page: 20,
      ...(filters.value.search && { search: filters.value.search }),
      ...(filters.value.module && { module: filters.value.module }),
      ...(filters.value.action && { action: filters.value.action }),
      ...(filters.value.date_from && { date_from: filters.value.date_from }),
    };
    
    const response = await activityLogService.getLogs(params);
    logs.value = response.data.data;
    meta.value = response.data.meta;
  } catch (error: any) {
    errorMessage.value = 'Gagal memuat activity log.';
    console.error('Error fetching logs:', error);
  } finally {
    isLoading.value = false;
  }
};

const applyFilters = () => {
  fetchLogs(1);
};

const resetFilters = () => {
  filters.value = {
    search: '',
    module: '',
    action: '',
    date_from: ''
  };
  fetchLogs(1);
};

const changePage = (page: number) => {
  if (page >= 1 && page <= (meta.value?.last_page || 1)) {
    fetchLogs(page);
  }
};

const viewDetail = (log: any) => {
  selectedLog.value = log;
};

const formatDate = (dateString: string) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

const formatJSON = (obj: any) => {
  return JSON.stringify(obj, null, 2);
};

const getActionBadgeClass = (action: string) => {
  const classes: Record<string, string> = {
    create: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400',
    update: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400',
    delete: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400',
    publish: 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400',
    approve: 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400',
    reject: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400',
    login: 'bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-400',
    logout: 'bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-400',
    login_failed: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400',
    register: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-400',
  };
  return classes[action] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

onMounted(() => {
  fetchLogs();
});
</script>
