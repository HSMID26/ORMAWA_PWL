<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="pageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
      
      <!-- Top Bar -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Audit Log & Aktivitas Global</h2>
          <p class="text-xs text-gray-500 mt-0.5">Audit trail seluruh aksi administratif dan operasional di platform CMS ORMAWA.</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari deskripsi / aktor..."
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
        />
        <select
          v-model="selectedModule"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Semua Modul</option>
          <option value="organizations">Organisasi</option>
          <option value="organization_periods">Periode</option>
          <option value="users">Pengguna</option>
          <option value="posts">Artikel</option>
          <option value="authentication">Autentikasi</option>
        </select>
        <select
          v-model="selectedAction"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Semua Aksi</option>
          <option value="create">Create</option>
          <option value="update">Update</option>
          <option value="delete">Delete</option>
          <option value="approve">Approve</option>
          <option value="reject">Reject</option>
          <option value="activate">Activate</option>
          <option value="deactivate">Deactivate</option>
        </select>
        <button
          @click="loadLogs"
          class="h-10 rounded-xl bg-gray-100 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
        >
          Refresh Log
        </button>
      </div>

      <!-- Logs Table -->
      <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500">
        Memuat audit log...
      </div>
      <div v-else-if="filteredLogs.length === 0" class="py-12 text-center text-sm text-gray-500">
        Tidak ada catatan aktivitas yang cocok.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Waktu</th>
              <th class="px-4 py-3">Aktor</th>
              <th class="px-4 py-3">Organisasi</th>
              <th class="px-4 py-3">Modul & Aksi</th>
              <th class="px-4 py-3">Deskripsi</th>
              <th class="px-4 py-3 text-center">Metadata</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
            <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
              <td class="px-4 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                {{ new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) }}
              </td>
              <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                {{ log.user?.name || 'Sistem' }}
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                <span v-if="log.organization" class="rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                  {{ log.organization.nama }}
                </span>
                <span v-else class="text-gray-400 italic">Platform</span>
              </td>
              <td class="px-4 py-3.5">
                <span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 uppercase">
                  {{ log.action }} : {{ log.module }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-600 dark:text-gray-400">
                {{ log.description }}
              </td>
              <td class="px-4 py-3.5 text-center">
                <button
                  v-if="log.metadata"
                  @click="inspectMetadata(log)"
                  class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                >
                  Lihat JSON
                </button>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Metadata Inspector Modal -->
    <div v-if="showMetadataModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
          <h3 class="text-base font-bold text-gray-900 dark:text-white">Metadata Inspector</h3>
          <button @click="showMetadataModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="rounded-xl bg-gray-950 p-4 font-mono text-xs text-green-400 overflow-x-auto max-h-80">
          <pre>{{ JSON.stringify(activeMetadata, null, 2) }}</pre>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="showMetadataModal = false"
            class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-white"
          >
            Tutup
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
import { activityLogService } from '@/services/activityLogService'

const pageTitle = ref('Audit Log Global')
const isLoading = ref(true)
const logs = ref<any[]>([])

const searchQuery = ref('')
const selectedModule = ref('')
const selectedAction = ref('')

const showMetadataModal = ref(false)
const activeMetadata = ref<any>(null)

const filteredLogs = computed(() => {
  return logs.value.filter((l) => {
    const matchSearch = !searchQuery.value ||
      l.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (l.user?.name && l.user.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
    const matchModule = !selectedModule.value || l.module === selectedModule.value
    const matchAction = !selectedAction.value || l.action === selectedAction.value
    return matchSearch && matchModule && matchAction
  })
})

const loadLogs = async () => {
  isLoading.value = true
  try {
    const res = await activityLogService.getLogs()
    logs.value = res.data?.data || res.data || []
  } catch (err) {
    console.error('Failed to load activity logs:', err)
  } finally {
    isLoading.value = false
  }
}

const inspectMetadata = (log: any) => {
  activeMetadata.value = log.metadata
  showMetadataModal.value = true
}

onMounted(() => {
  loadLogs()
})
</script>
