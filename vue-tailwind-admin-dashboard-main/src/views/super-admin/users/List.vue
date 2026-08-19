<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div
      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6"
    >
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
          Data Pengguna
        </h3>
        <button @click="openModal()" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
          + Tambah User
        </button>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, atau role..."
          class="dark:bg-dark-900 h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
      </div>

      <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Memuat pengguna...
      </div>
      <div v-else-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
        {{ errorMessage }}
      </div>
      <!-- Tabel Data -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Email</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Role</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Organisasi</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Status</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="user in filteredUsers" 
              :key="user.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ user.name }}</td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span :class="[
                  'inline-flex rounded-md px-2 py-1 text-xs font-medium',
                  user.role === 'Super Admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-400' :
                  user.role === 'Admin Organisasi' ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400' :
                  user.role === 'Editor' ? 'bg-orange-100 text-orange-800 dark:bg-orange-500/20 dark:text-orange-400' :
                  user.role === 'Kontributor' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400' :
                  'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'
                ]">
                  {{ user.role || '-' }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ user.organization?.nama ?? '-' }}</td>
              <td class="px-4 py-3">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" :checked="user.status === 'active'" @change="toggleUserStatus(user)" :disabled="togglingId === user.id" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500 opacity-50 peer-disabled:opacity-50"></div>
                  <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                    {{ user.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </label>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button @click="openModal(user)" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Edit</button>
                  <button @click="deleteUser(user.id)" class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400" :disabled="deletingId === user.id">
                    {{ deletingId === user.id ? 'Menghapus...' : 'Delete' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data pengguna tidak ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Modal Form User -->
    <Modal v-if="isModalOpen" @close="closeModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            {{ isEditMode ? 'Edit User' : 'Tambah User' }}
          </h3>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
              <input v-model="form.name" required type="text" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
              <input v-model="form.email" required type="email" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Password <span v-if="isEditMode" class="text-xs text-gray-500 font-normal">(Kosongkan jika tidak ingin mengubah)</span>
              </label>
              <input v-model="form.password" :required="!isEditMode" type="password" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
              <select v-model="form.role" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:bg-gray-900">
                <option value="Super Admin">Super Admin</option>
                <option value="Admin Organisasi">Admin Organisasi</option>
              </select>
            </div>

            <div v-if="form.role === 'Admin Organisasi'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organisasi</label>
              <select v-model="form.organization_id" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:bg-gray-900">
                <option value="" disabled>Pilih Organisasi...</option>
                <option v-for="org in organizations" :key="org.id" :value="org.id">
                  {{ org.nama }}
                </option>
              </select>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="closeModal" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" :disabled="isSubmitting">
                Batal
              </button>
              <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50" :disabled="isSubmitting">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import Modal from '@/components/ui/Modal.vue'
import { userService } from '@/services/userService'
import { organizationService } from '@/services/organizationService'
import { useToastStore } from '@/stores/toast'
import type { UserProfile, Organization } from '@/types/api'

const toastStore = useToastStore()
const currentPageTitle = ref('Manajemen Pengguna')
const searchQuery = ref('')
const users = ref<UserProfile[]>([])
const organizations = ref<Organization[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

// Form State
const isModalOpen = ref(false)
const isEditMode = ref(false)
const isSubmitting = ref(false)
const deletingId = ref<number | null>(null)
const togglingId = ref<number | null>(null)

const form = ref<{
  name: string
  email: string
  password?: string
  role: string
  organization_id: number | ''
}>({
  name: '',
  email: '',
  password: '',
  role: 'Admin Organisasi',
  organization_id: ''
})
let editId: number | null = null

onMounted(async () => {
  await Promise.all([loadUsers(), loadOrganizations()])
})

const loadUsers = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    users.value = await userService.list()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Gagal memuat pengguna.'
  } finally {
    isLoading.value = false
  }
}

const loadOrganizations = async () => {
  try {
    organizations.value = await organizationService.list()
  } catch (error) {
    console.error('Failed to load organizations', error)
  }
}

const openModal = (user?: UserProfile) => {
  if (user) {
    isEditMode.value = true
    editId = user.id
    form.value = { 
      name: user.name, 
      email: user.email, 
      password: '', // Kosongkan saat edit
      role: user.role || 'Admin Organisasi',
      organization_id: user.organization?.id || ''
    }
  } else {
    isEditMode.value = false
    editId = null
    form.value = { 
      name: '', 
      email: '', 
      password: '', 
      role: 'Admin Organisasi', 
      organization_id: '' 
    }
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  // Siapkan payload, hanya kirim password jika diisi
  const payload: Record<string, any> = {
    name: form.value.name,
    email: form.value.email,
    role: form.value.role,
    organization_id: form.value.role === 'Admin Organisasi' ? form.value.organization_id : null
  }
  if (form.value.password) {
    payload.password = form.value.password
  }
  
  try {
    if (isEditMode.value && editId !== null) {
      await userService.update(editId, payload)
      toastStore.success('Data berhasil diperbarui.')
    } else {
      await userService.create(payload)
      toastStore.success('Pengguna berhasil ditambahkan.')
    }
    closeModal()
    await loadUsers()
  } catch (error: any) {
    if (error.status === 422) {
      const errors = error.errors
      if (errors) {
        const firstError = Object.values(errors)[0] as string[]
        toastStore.error(firstError[0])
      } else {
        toastStore.error(error.message || 'Data tidak valid.')
      }
    } else {
      toastStore.error(error.message || 'Data gagal disimpan.')
    }
  } finally {
    isSubmitting.value = false
  }
}

const deleteUser = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
    deletingId.value = id
    try {
      await userService.remove(id)
      toastStore.success('Data berhasil dihapus.')
      await loadUsers()
    } catch (error: any) {
      toastStore.error(error.message || 'Data gagal dihapus.')
    } finally {
      deletingId.value = null
    }
  }
}

const toggleUserStatus = async (user: UserProfile) => {
  const newStatus = user.status === 'active' ? 'inactive' : 'active'
  const oldStatus = user.status
  
  togglingId.value = user.id
  // Optimistic update
  user.status = newStatus
  
  try {
    await userService.update(user.id, {
      name: user.name,
      email: user.email,
      role: user.role,
      status: newStatus
    })
    toastStore.success(`Status berhasil diubah menjadi ${newStatus === 'active' ? 'Aktif' : 'Nonaktif'}.`)
  } catch (error: any) {
    // Rollback visual state
    user.status = oldStatus
    toastStore.error(error.message || 'Gagal mengubah status.')
  } finally {
    togglingId.value = null
  }
}

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value

  const lowerCaseQuery = searchQuery.value.toLowerCase()
  return users.value.filter((user) =>
    user.name.toLowerCase().includes(lowerCaseQuery) ||
    user.email.toLowerCase().includes(lowerCaseQuery) ||
    (user.role ?? '').toLowerCase().includes(lowerCaseQuery) ||
    (user.organization?.nama ?? '').toLowerCase().includes(lowerCaseQuery),
  )
})
</script>

