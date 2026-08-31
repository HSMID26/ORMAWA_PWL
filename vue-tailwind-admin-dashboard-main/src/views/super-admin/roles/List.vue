<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Role & Hak Akses" />

    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                Otorisasi Sistem & RBAC
              </span>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Role & Hak Akses</h1>
            <p class="mt-1 max-w-2xl text-xs sm:text-sm text-gray-500 dark:text-gray-400">
              Konfigurasi matriks izin (*permissions*) yang mengontrol aksi nyata pada backend ORMAWA ITI. Setiap izin yang dicentang secara langsung menentukan hak eksekusi controller dan API.
            </p>
          </div>
          <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            Super Admin Governance
          </span>
        </div>
      </div>

      <!-- Feedback Alert -->
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div
          v-if="statusMessage"
          :class="[
            'p-4 rounded-xl border flex items-center justify-between gap-3 text-xs sm:text-sm font-medium shadow-2xs',
            statusType === 'success'
              ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300'
              : 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300'
          ]"
          role="status"
        >
          <div class="flex items-center gap-2.5">
            <svg v-if="statusType === 'success'" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ statusMessage }}</span>
          </div>
          <button @click="statusMessage = ''" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            &times;
          </button>
        </div>
      </transition>

      <!-- Loading State -->
      <div v-if="isLoading" class="rounded-2xl border border-gray-200 bg-white py-16 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 shadow-theme-xs">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-r-transparent"></div>
        <p class="mt-3 font-medium text-gray-600 dark:text-gray-400">Memuat matriks hak akses sistem...</p>
      </div>

      <!-- Role Cards List -->
      <div v-else class="space-y-6">
        <div
          v-for="role in roles"
          :key="role.id"
          class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden transition-all duration-200"
        >
          <!-- Role Header Bar -->
          <div class="flex flex-col gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4.5 bg-gray-50/60 dark:bg-gray-800/40 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
              <div class="flex items-center gap-2.5">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ role.name }}</h2>
                <span :class="[
                  'px-2.5 py-0.5 text-[11px] font-semibold rounded-full border',
                  role.name === 'Super Admin'
                    ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800'
                    : role.name === 'Admin Organisasi'
                    ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800'
                    : role.name === 'Editor'
                    ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
                    : 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'
                ]">
                  {{ getRoleBadgeLabel(role.name) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                <strong class="text-brand-600 dark:text-brand-400 font-semibold">{{ drafts[role.id]?.length || 0 }}</strong> dari {{ totalPermissionCount }} izin operasional aktif
              </p>
            </div>

            <!-- Action Button -->
            <button
              type="button"
              :disabled="savingRoleId === role.id"
              @click="openSaveConfirm(role)"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-xs transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
            >
              <svg v-if="savingRoleId === role.id" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ savingRoleId === role.id ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
            </button>
          </div>

          <!-- Super Admin Protected Note -->
          <div v-if="role.name === 'Super Admin'" class="px-6 pt-4">
            <div class="rounded-xl bg-blue-50/70 p-3.5 border border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/40 flex items-start gap-2.5 text-xs text-blue-800 dark:text-blue-300">
              <span class="text-base mt-[-2px]">🛡️</span>
              <p>
                <strong>Proteksi Tata Kelola Super Admin:</strong> Hak akses tata kelola sistem inti (pengguna, organisasi, log aktivitas, pengaturan platform) dilindungi secara otomatis oleh server untuk mencegah penguncian akun administratif secara tidak sengaja.
              </p>
            </div>
          </div>

          <!-- Permission Checkbox Grid By Groups -->
          <div class="grid gap-6 p-6 md:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="(items, groupName) in groups"
              :key="groupName"
              class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30 space-y-3"
            >
              <div class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                  {{ groupName }}
                </h3>
                <span class="text-[11px] text-gray-400 font-medium">
                  {{ getActiveGroupCount(role.id, items) }} / {{ Object.keys(items).length }}
                </span>
              </div>

              <div class="space-y-1.5">
                <label
                  v-for="(label, permissionKey) in items"
                  :key="permissionKey"
                  :class="[
                    'flex items-start gap-3 rounded-lg px-2.5 py-2 text-xs font-medium transition select-none',
                    isProtectedPermission(role.name, permissionKey as string)
                      ? 'cursor-not-allowed opacity-80 bg-gray-100/70 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300'
                      : 'cursor-pointer text-gray-700 hover:bg-white dark:text-gray-300 dark:hover:bg-gray-800'
                  ]"
                >
                  <input
                    type="checkbox"
                    v-model="drafts[role.id]"
                    :value="permissionKey"
                    :disabled="isProtectedPermission(role.name, permissionKey as string)"
                    @change="handlePermissionToggle(role.id, permissionKey as string)"
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 mt-0.5 shrink-0 disabled:opacity-60"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-1.5">
                      <span class="font-semibold">{{ permissionKey }}</span>
                      <span
                        v-if="isProtectedPermission(role.name, permissionKey as string)"
                        class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300"
                      >
                        Protected
                      </span>
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                      {{ label }}
                    </p>
                  </div>
                </label>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Confirmation Modal (No window.confirm) -->
    <div
      v-if="showConfirmModal && selectedRoleForConfirm"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-start gap-4">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
              Konfirmasi Perubahan Hak Akses
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
              Anda akan memperbarui izin untuk role <strong>{{ selectedRoleForConfirm.name }}</strong> ({{ drafts[selectedRoleForConfirm.id]?.length || 0 }} izin terpilih). Perubahan akan langsung memengaruhi seluruh akun pengguna dengan role ini secara *live*.
            </p>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="cancelConfirm"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeSaveRole"
            class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white hover:bg-brand-600 shadow-xs"
          >
            Ya, Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { roleService, type RoleAccess } from '@/services/userService'

const isLoading = ref(true)
const savingRoleId = ref<number | null>(null)
const roles = ref<RoleAccess[]>([])
const groups = ref<Record<string, Record<string, string>>>({})
const drafts = reactive<Record<number, string[]>>({})

const statusMessage = ref('')
const statusType = ref<'success' | 'error'>('success')

const showConfirmModal = ref(false)
const selectedRoleForConfirm = ref<RoleAccess | null>(null)

const totalPermissionCount = computed(() => {
  return Object.values(groups.value).reduce((acc, group) => acc + Object.keys(group).length, 0)
})

const getRoleBadgeLabel = (roleName: string) => {
  switch (roleName) {
    case 'Super Admin':
      return 'Tata Kelola Global'
    case 'Admin Organisasi':
      return 'Pengelola Ormawa'
    case 'Editor':
      return 'Redaksi Konten'
    case 'Kontributor':
      return 'Penulis Artikel'
    default:
      return 'Role Sistem'
  }
}

const isProtectedPermission = (roleName: string, permissionKey: string) => {
  if (roleName === 'Super Admin') {
    return ['users.manage', 'organizations.manage', 'roles.manage', 'activity_logs.view', 'platform_settings.manage'].includes(permissionKey)
  }
  return false
}

const getActiveGroupCount = (roleId: number, groupItems: Record<string, string>) => {
  const currentDraft = drafts[roleId] || []
  return Object.keys(groupItems).filter((key) => currentDraft.includes(key)).length
}

const handlePermissionToggle = (roleId: number, permissionKey: string) => {
  const current = drafts[roleId] || []
  const isChecked = current.includes(permissionKey)

  const dependencyMap: Record<string, string> = {
    'gallery.create': 'gallery.view',
    'gallery.update': 'gallery.view',
    'gallery.delete': 'gallery.view',
    'documents.create': 'documents.view',
    'documents.update': 'documents.view',
    'documents.delete': 'documents.view',
    'posts.create': 'posts.view',
    'posts.update': 'posts.view',
    'posts.delete': 'posts.view',
    'posts.publish': 'posts.view',
    'agenda.create': 'agenda.view',
    'agenda.update': 'agenda.view',
    'agenda.delete': 'agenda.view',
    'agenda.publish': 'agenda.view',
    'announcements.create': 'announcements.view',
    'announcements.update': 'announcements.view',
    'announcements.delete': 'announcements.view',
    'announcements.publish': 'announcements.view',
    'structure.manage': 'structure.view',
    'users.manage': 'users.view',
    'organizations.manage': 'organizations.view',
  }

  if (isChecked) {
    const requiredView = dependencyMap[permissionKey]
    if (requiredView && !current.includes(requiredView)) {
      current.push(requiredView)
    }
  } else {
    const dependentActions = Object.entries(dependencyMap)
      .filter(([_, viewKey]) => viewKey === permissionKey)
      .map(([actionKey]) => actionKey)

    if (dependentActions.length > 0) {
      drafts[roleId] = current.filter((perm) => !dependentActions.includes(perm))
    }
  }
}

const loadAccess = async () => {
  isLoading.value = true
  statusMessage.value = ''
  try {
    const data = await roleService.access()
    roles.value = data.roles
    groups.value = data.groups
    roles.value.forEach((role) => {
      drafts[role.id] = [...role.permissions]
    })
  } catch (err: any) {
    statusType.value = 'error'
    statusMessage.value = err?.data?.message || 'Konfigurasi hak akses tidak dapat dimuat dari server.'
  } finally {
    isLoading.value = false
  }
}

const openSaveConfirm = (role: RoleAccess) => {
  selectedRoleForConfirm.value = role
  showConfirmModal.value = true
}

const cancelConfirm = () => {
  showConfirmModal.value = false
  selectedRoleForConfirm.value = null
}

const executeSaveRole = async () => {
  const role = selectedRoleForConfirm.value
  if (!role) return

  showConfirmModal.value = false
  savingRoleId.value = role.id
  statusMessage.value = ''

  try {
    const updated = await roleService.updatePermissions(role.id, drafts[role.id] || [])
    role.permissions = updated.permissions
    drafts[role.id] = [...updated.permissions]
    statusType.value = 'success'
    statusMessage.value = `Hak akses role ${role.name} berhasil disimpan.`
  } catch (err: any) {
    statusType.value = 'error'
    statusMessage.value = err?.data?.message || `Hak akses role ${role.name} gagal disimpan.`
  } finally {
    savingRoleId.value = null
    selectedRoleForConfirm.value = null
  }
}

onMounted(loadAccess)
</script>
