<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="pageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
      
      <!-- Top Bar -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Monitoring Pengguna Global</h2>
          <p class="text-xs text-gray-500 mt-0.5">Pemantauan seluruh pengguna operasional (Admin Organisasi, Editor, Kontributor).</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama atau email..."
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
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
          v-model="selectedRole"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Semua Peran</option>
          <option value="Admin Organisasi">Admin Organisasi</option>
          <option value="Editor">Editor</option>
          <option value="Kontributor">Kontributor</option>
        </select>
        <select
          v-model="selectedStatus"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </div>

      <!-- Data Table -->
      <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500">
        Memuat data pengguna global...
      </div>
      <div v-else-if="filteredUsers.length === 0" class="py-12 text-center text-sm text-gray-500">
        Tidak ada data pengguna yang cocok.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Nama & Email</th>
              <th class="px-4 py-3">Organisasi</th>
              <th class="px-4 py-3">Peran (Role)</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Terdaftar</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
              <td class="px-4 py-3.5">
                <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                <div class="text-xs text-gray-500">{{ user.email }}</div>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                <span v-if="user.organization" class="font-medium">{{ user.organization.nama }}</span>
                <span v-else class="text-gray-400 italic">-</span>
              </td>
              <td class="px-4 py-3.5">
                <span :class="getRoleBadgeClass(user.role)">{{ user.role || 'Member' }}</span>
              </td>
              <td class="px-4 py-3.5">
                <span v-if="user.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                  Aktif
                </span>
                <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                  Nonaktif
                </span>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-500">
                {{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { userService } from '@/services/userService'
import { organizationService } from '@/services/organizationService'
import type { UserProfile, Organization } from '@/types/api'

const pageTitle = ref('Pengguna Global')
const isLoading = ref(true)
const users = ref<UserProfile[]>([])
const organizations = ref<Organization[]>([])

const searchQuery = ref('')
const selectedOrgId = ref<number | null>(null)
const selectedRole = ref('')
const selectedStatus = ref('')

const filteredUsers = computed(() => {
  return users.value.filter((u) => {
    if (u.role === 'Super Admin') return false
    const matchSearch = !searchQuery.value ||
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchOrg = selectedOrgId.value === null || u.organization_id === selectedOrgId.value
    const matchRole = !selectedRole.value || u.role === selectedRole.value
    const matchStatus = !selectedStatus.value || u.status === selectedStatus.value
    return matchSearch && matchOrg && matchRole && matchStatus
  })
})

const getRoleBadgeClass = (role?: string) => {
  switch (role) {
    case 'Admin Organisasi':
      return 'inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700'
    case 'Editor':
      return 'inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700'
    case 'Kontributor':
      return 'inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700'
    default:
      return 'inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700'
  }
}

const loadData = async () => {
  isLoading.value = true
  try {
    const [userList, orgList] = await Promise.all([
      userService.list({ exclude_super_admin: true }),
      organizationService.list(),
    ])
    users.value = userList || []
    organizations.value = orgList || []
  } catch (err) {
    console.error('Failed to load global users:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
