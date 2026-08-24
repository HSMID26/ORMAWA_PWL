<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Pengguna Organisasi" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header ─────────────────────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <UserCircleIcon class="h-6 w-6 text-brand-500" />
            Pengguna Organisasi
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Kelola akun internal organisasi yang memiliki akses ke CMS.
          </p>
        </div>

        <div v-if="canManageUsers">
          <button
            @click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition"
          >
            <PlusIcon class="h-4 w-4" />
            Tambah Pengguna Baru
          </button>
        </div>
      </div>

      <!-- ─── Toolbar Filters ────────────────────────────────────────────────── -->
      <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="relative">
          <SearchIcon class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama atau email pengguna..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
        </div>

        <div>
          <select
            v-model="filterRole"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Peran (Role)</option>
            <option value="Admin Organisasi">Admin Organisasi</option>
            <option value="Editor">Editor</option>
            <option value="Kontributor">Kontributor</option>
          </select>
        </div>

        <div>
          <select
            v-model="filterStatus"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          >
            <option value="">Semua Status Akun</option>
            <option value="active">Aktif</option>
            <option value="inactive">Non-Aktif</option>
          </select>
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="space-y-3 py-6">
        <div v-for="i in 5" :key="i" class="h-14 w-full animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Error State ────────────────────────────────────────────────────── -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button @click="loadUsers" class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
          Coba Lagi
        </button>
      </div>

      <!-- ─── Table Data ─────────────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Pengguna</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Peran (Role)</th>
              <th class="px-4 py-3">Status Akun</th>
              <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
            <tr
              v-for="user in filteredUsers"
              :key="user.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
            >
              <!-- Avatar & Nama -->
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-bold text-xs border border-brand-100 dark:border-brand-900/30">
                    {{ user.name.substring(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                      {{ user.name }}
                      <span v-if="user.id === authStore.user?.id" class="rounded bg-brand-100 dark:bg-brand-900/40 text-[9px] font-bold text-brand-700 dark:text-brand-300 px-1.5 py-0.2">
                        Anda
                      </span>
                    </h3>
                    <span class="text-[10px] text-gray-400">ID: #{{ user.id }}</span>
                  </div>
                </div>
              </td>

              <!-- Email -->
              <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 font-mono text-[11px]">
                {{ user.email }}
              </td>

              <!-- Role -->
              <td class="px-4 py-3.5">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                  :class="{
                    'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400': user.role === 'Admin Organisasi',
                    'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': user.role === 'Editor',
                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400': user.role === 'Kontributor',
                    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400': !user.role || user.role === 'User'
                  }"
                >
                  {{ user.role || 'Anggota' }}
                </span>
              </td>

              <!-- Status Akun -->
              <td class="px-4 py-3.5">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase"
                  :class="user.status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800'"
                >
                  {{ user.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3.5 text-right">
                <div class="inline-flex items-center gap-1">
                  <!-- Edit Button -->
                  <button
                    v-if="canEditUser(user)"
                    @click="openEditModal(user)"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-gray-800 dark:hover:text-brand-400 transition"
                    title="Edit Pengguna"
                  >
                    <PencilIcon class="h-4 w-4" />
                  </button>

                  <!-- Delete Button -->
                  <button
                    v-if="canDeleteUser(user)"
                    @click="openDeleteModal(user)"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30 dark:hover:text-rose-400 transition"
                    title="Hapus Pengguna"
                  >
                    <Trash2Icon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" class="px-4 py-12 text-center text-xs text-gray-400">
                Tidak ada pengguna yang cocok dengan kriteria pencarian.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Form Modal (Tambah / Edit Pengguna) ─────────────────────────────── -->
    <div
      v-if="isFormModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
          <h3 class="text-base font-bold text-gray-900 dark:text-white">
            {{ isEditing ? 'Edit Akun Pengguna' : 'Tambah Pengguna Organisasi' }}
          </h3>
          <button @click="isFormModalOpen = false" class="text-gray-400 hover:text-gray-500">
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Ahmad Fauzi"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="fauzi@kampus.ac.id"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
              Password {{ isEditing ? '(Kosongkan jika tidak diubah)' : '' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!isEditing"
              minlength="8"
              placeholder="Minimal 8 karakter"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Peran (Role)</label>
            <div v-if="isEditing && selectedUser?.role === 'Admin Organisasi'" class="rounded-lg border border-purple-200 bg-purple-50/50 p-2.5 dark:border-purple-900/30 dark:bg-purple-950/20">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-purple-700 dark:text-purple-300">Admin Organisasi</span>
                <span class="text-[10px] uppercase font-semibold text-purple-500">Terkunci / Read-Only</span>
              </div>
              <p class="text-[11px] text-purple-600/80 dark:text-purple-400/80 mt-0.5">
                Peran Admin Organisasi adalah hak akses operasional utama dan tidak dapat diubah.
              </p>
            </div>
            <select
              v-else
              v-model="form.role"
              required
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="Editor">Editor (Pengelola Konten: Artikel, Agenda, Pengumuman, Galeri, Dokumen)</option>
              <option value="Kontributor">Kontributor (Pembuat Artikel / Berita)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Akun</label>
            <select
              v-model="form.status"
              required
              :disabled="isEditing && selectedUser?.id === authStore.user?.id"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800/50"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Non-Aktif</option>
            </select>
          </div>

          <div class="mt-6 flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="isFormModalOpen = false"
              class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600 disabled:opacity-50 flex items-center gap-1.5"
            >
              <span v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-r-transparent"></span>
              {{ isEditing ? 'Simpan Perubahan' : 'Buat Pengguna' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── Delete Confirmation Modal ──────────────────────────────────────── -->
    <div
      v-if="isDeleteModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-center animate-in fade-in zoom-in-95 duration-150">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 mb-3">
          <Trash2Icon class="h-6 w-6" />
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white">
          Hapus Akun Pengguna?
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
          Apakah Anda yakin ingin menghapus akun <span class="font-bold text-gray-800 dark:text-gray-200">{{ selectedUser?.name }}</span>? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex justify-center gap-2">
          <button
            type="button"
            @click="isDeleteModalOpen = false"
            class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
          >
            Batal
          </button>
          <button
            type="button"
            @click="confirmDelete"
            :disabled="isDeleting"
            class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50 flex items-center gap-1.5"
          >
            <span v-if="isDeleting" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-r-transparent"></span>
            Hapus Pengguna
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
import { userService } from '@/services/userService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import type { UserProfile } from '@/types/api'
import {
  UserCircleIcon,
  SearchIcon,
  PlusIcon,
  PencilIcon,
  Trash2Icon,
  AlertTriangleIcon,
  XIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toastStore = useToastStore()

const users = ref<UserProfile[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

const searchQuery = ref('')
const filterRole = ref('')
const filterStatus = ref('')

const isFormModalOpen = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const selectedUser = ref<UserProfile | null>(null)

const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)

const form = ref({
  id: 0,
  name: '',
  email: '',
  password: '',
  role: 'Editor',
  status: 'active'
})

const canManageUsers = computed(() => {
  return authStore.role === 'Super Admin' || authStore.role === 'Admin Organisasi'
})

const canEditUser = (user: UserProfile) => {
  if (!canManageUsers.value) return false
  if (user.role === 'Super Admin') return false
  if (user.role === 'Admin Organisasi' && user.id !== authStore.user?.id) return false
  return true
}

const canDeleteUser = (user: UserProfile) => {
  if (!canManageUsers.value) return false
  if (user.role === 'Super Admin' || user.role === 'Admin Organisasi') return false
  if (user.id === authStore.user?.id) return false
  return true
}

const loadUsers = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const list = await userService.list({
      role: filterRole.value || undefined,
      status: filterStatus.value || undefined,
      search: searchQuery.value || undefined,
    })
    users.value = list
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat daftar pengguna organisasi.'
  } finally {
    isLoading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  selectedUser.value = null
  form.value = {
    id: 0,
    name: '',
    email: '',
    password: '',
    role: 'Editor',
    status: 'active'
  }
  isFormModalOpen.value = true
}

const openEditModal = (user: UserProfile) => {
  isEditing.value = true
  selectedUser.value = user
  form.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '',
    role: user.role || 'Editor',
    status: user.status || 'active'
  }
  isFormModalOpen.value = true
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (isEditing.value) {
      const payload: any = {
        name: form.value.name,
        email: form.value.email,
        status: form.value.status
      }

      // Only send role if editing Editor or Kontributor (NEVER mutate Admin Organisasi role)
      if (selectedUser.value?.role !== 'Admin Organisasi') {
        payload.role = form.value.role
      }

      if (form.value.password.trim()) {
        payload.password = form.value.password
      }

      const updated = await userService.update(form.value.id, payload)

      // If user updated their own profile, sync the authStore state
      if (form.value.id === authStore.user?.id && authStore.user) {
        authStore.user.name = updated.name
        authStore.user.email = updated.email
      }

      toastStore.success('Data pengguna berhasil diperbarui!')
    } else {
      await userService.create({
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        role: form.value.role,
        status: form.value.status
      })
      toastStore.success(`Akun ${form.value.role} berhasil dibuat!`)
    }
    isFormModalOpen.value = false
    await loadUsers()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menyimpan data pengguna.')
  } finally {
    isSubmitting.value = false
  }
}

const openDeleteModal = (user: UserProfile) => {
  selectedUser.value = user
  isDeleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (!selectedUser.value) return
  isDeleting.value = true
  try {
    await userService.remove(selectedUser.value.id)
    toastStore.success('Pengguna berhasil dihapus!')
    isDeleteModalOpen.value = false
    await loadUsers()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menghapus pengguna.')
  } finally {
    isDeleting.value = false
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    // Role filter
    if (filterRole.value && u.role !== filterRole.value) {
      return false
    }

    // Status filter
    if (filterStatus.value && u.status !== filterStatus.value) {
      return false
    }

    // Search filter
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchName = u.name.toLowerCase().includes(q)
      const matchEmail = u.email.toLowerCase().includes(q)
      if (!matchName && !matchEmail) return false
    }

    return true
  })
})

onMounted(() => {
  loadUsers()
})
</script>
