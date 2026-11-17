<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="pageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
      
      <!-- Top Action Bar -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Admin Organisasi</h2>
          <p class="text-xs text-gray-500 mt-0.5">Kelola akun administrator operasional untuk setiap organisasi aktif.</p>
        </div>
        <button
          @click="openCreateModal"
          class="flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-brand-700 transition"
        >
          <UserPlusIcon class="h-4 w-4" />
          <span>Tambah Admin Organisasi</span>
        </button>
      </div>

      <!-- Filters & Search -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama atau email admin..."
            class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
          />
        </div>
        <div>
          <select
            v-model="selectedOrgId"
            class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
          >
            <option :value="null">Semua Organisasi</option>
            <option v-for="org in organizations" :key="org.id" :value="org.id">
              {{ org.nama }} ({{ org.jenis }})
            </option>
          </select>
        </div>
        <div>
          <select
            v-model="selectedStatus"
            class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
          >
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>
      </div>

      <!-- Data Table -->
      <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500">
        Memuat data admin organisasi...
      </div>
      <div v-else-if="filteredAdmins.length === 0" class="py-12 text-center text-sm text-gray-500">
        Tidak ada data Admin Organisasi yang cocok.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Nama & Email</th>
              <th class="px-4 py-3">Organisasi Terhubung</th>
              <th class="px-4 py-3">Peran (Role)</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Terdaftar</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
            <tr v-for="admin in filteredAdmins" :key="admin.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
              <td class="px-4 py-3.5">
                <div class="font-medium text-gray-900 dark:text-white">{{ admin.name }}</div>
                <div class="text-xs text-gray-500">{{ admin.email }}</div>
              </td>
              <td class="px-4 py-3.5">
                <span v-if="admin.organization" class="font-medium text-gray-800 dark:text-gray-200">
                  {{ admin.organization.nama }}
                </span>
                <span v-else class="text-xs text-red-500 italic">Belum terhubung</span>
              </td>
              <td class="px-4 py-3.5">
                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                  Admin Organisasi
                </span>
              </td>
              <td class="px-4 py-3.5">
                <span v-if="admin.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                  Aktif
                </span>
                <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                  Nonaktif
                </span>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-500">
                {{ admin.created_at ? new Date(admin.created_at).toLocaleDateString('id-ID') : '-' }}
              </td>
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button
                    @click="openEditModal(admin)"
                    class="rounded-lg p-1.5 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                    title="Edit Profil Admin"
                  >
                    <EditIcon class="h-4 w-4" />
                  </button>
                  <button
                    @click="openResetPasswordModal(admin)"
                    class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                    title="Reset Password"
                  >
                    <KeyIcon class="h-4 w-4" />
                  </button>
                  <button
                    v-if="admin.status === 'active'"
                    @click="toggleStatus(admin)"
                    class="rounded-lg p-1.5 text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-900/20"
                    title="Nonaktifkan Akun"
                  >
                    <UserXIcon class="h-4 w-4" />
                  </button>
                  <button
                    v-else
                    @click="toggleStatus(admin)"
                    class="rounded-lg p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20"
                    title="Aktifkan Akun"
                  >
                    <UserCheckIcon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Admin Organisasi' : 'Tambah Admin Organisasi Baru' }}
        </h3>
        
        <form @submit.prevent="saveAdmin" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>
          <div v-if="!isEditing">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Password</label>
            <input
              v-model="form.password"
              type="password"
              minlength="8"
              required
              placeholder="Minimal 8 karakter"
              class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Organisasi yang Ditugaskan</label>
            <select
              v-model="form.organization_id"
              required
              class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option :value="null" disabled>Pilih Organisasi</option>
              <option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.nama }} ({{ org.jenis }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Akun</label>
            <select
              v-model="form.status"
              class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>

          <div v-if="formError" class="text-xs text-red-600 bg-red-50 dark:bg-red-950/30 p-2.5 rounded-lg">
            {{ formError }}
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="showFormModal = false"
              class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Reset Password -->
    <div v-if="showPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reset Password Admin</h3>
        <p class="text-xs text-gray-500">Masukkan kata sandi baru untuk akun <b>{{ activeAdmin?.name }}</b>.</p>
        
        <form @submit.prevent="submitPasswordReset" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
            <input
              v-model="newPassword"
              type="password"
              minlength="8"
              required
              placeholder="Minimal 8 karakter"
              class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="showPasswordModal = false"
              class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {{ isSubmitting ? 'Mereset...' : 'Reset Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { UserPlusIcon, EditIcon, KeyIcon, UserXIcon, UserCheckIcon } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { userService } from '@/services/userService'
import { organizationService } from '@/services/organizationService'
import type { UserProfile, Organization } from '@/types/api'

const pageTitle = ref('Admin Organisasi')
const isLoading = ref(true)
const admins = ref<UserProfile[]>([])
const organizations = ref<Organization[]>([])

const searchQuery = ref('')
const selectedOrgId = ref<number | null>(null)
const selectedStatus = ref('')

const showFormModal = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const formError = ref<string | null>(null)

const showPasswordModal = ref(false)
const activeAdmin = ref<UserProfile | null>(null)
const newPassword = ref('')

const form = ref({
  id: null as number | null,
  name: '',
  email: '',
  password: '',
  organization_id: null as number | null,
  role: 'Admin Organisasi',
  status: 'active',
})

const filteredAdmins = computed(() => {
  return admins.value.filter((admin) => {
    const matchSearch = !searchQuery.value ||
      admin.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      admin.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchOrg = selectedOrgId.value === null || admin.organization_id === selectedOrgId.value
    const matchStatus = !selectedStatus.value || admin.status === selectedStatus.value
    return matchSearch && matchOrg && matchStatus
  })
})

const loadData = async () => {
  isLoading.value = true
  try {
    const [adminList, orgList] = await Promise.all([
      userService.list({ role: 'Admin Organisasi' }),
      organizationService.list(),
    ])
    admins.value = adminList || []
    organizations.value = orgList || []
  } catch (err) {
    console.error('Failed to load organization admins:', err)
  } finally {
    isLoading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  formError.value = null
  form.value = {
    id: null,
    name: '',
    email: '',
    password: '',
    organization_id: organizations.value[0]?.id ?? null,
    role: 'Admin Organisasi',
    status: 'active',
  }
  showFormModal.value = true
}

const openEditModal = (admin: UserProfile) => {
  isEditing.value = true
  formError.value = null
  form.value = {
    id: admin.id,
    name: admin.name,
    email: admin.email,
    password: '',
    organization_id: admin.organization_id ?? null,
    role: 'Admin Organisasi',
    status: admin.status || 'active',
  }
  showFormModal.value = true
}

const saveAdmin = async () => {
  isSubmitting.value = true
  formError.value = null

  try {
    if (isEditing.value && form.value.id) {
      await userService.update(form.value.id, {
        name: form.value.name,
        email: form.value.email,
        organization_id: form.value.organization_id,
        status: form.value.status,
      })
    } else {
      await userService.create({
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        organization_id: form.value.organization_id,
        role: 'Admin Organisasi',
        status: form.value.status,
      })
    }
    showFormModal.value = false
    await loadData()
  } catch (err: any) {
    formError.value = err?.message || 'Gagal menyimpan data admin organisasi.'
  } finally {
    isSubmitting.value = false
  }
}

const toggleStatus = async (admin: UserProfile) => {
  try {
    if (admin.status === 'active') {
      await userService.deactivate(admin.id)
    } else {
      await userService.activate(admin.id)
    }
    await loadData()
  } catch (err) {
    console.error('Failed to change status:', err)
  }
}

const openResetPasswordModal = (admin: UserProfile) => {
  activeAdmin.value = admin
  newPassword.value = ''
  showPasswordModal.value = true
}

const submitPasswordReset = async () => {
  if (!activeAdmin.value) return
  isSubmitting.value = true
  try {
    await userService.resetPassword(activeAdmin.value.id, newPassword.value)
    showPasswordModal.value = false
  } catch (err: any) {
    alert(err?.message || 'Gagal mereset password.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
