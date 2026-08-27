<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="pageTitle" />

    <section class="space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600">Kontrol akses</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Role & Hak Akses</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Atur izin yang dimiliki setiap role operasional. Perubahan berlaku untuk seluruh pengguna dengan role tersebut.</p>
          </div>
          <span class="inline-flex w-fit items-center rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/20 dark:text-green-400">Super Admin</span>
        </div>
      </div>

      <div v-if="isLoading" class="rounded-2xl border border-gray-200 bg-white py-16 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03]">Memuat konfigurasi hak akses...</div>
      <div v-else class="space-y-4">
        <div v-for="role in roles" :key="role.id" class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 class="font-semibold text-gray-900 dark:text-white">{{ role.name }}</h2>
              <p class="text-xs text-gray-500">{{ role.permissions.length }} dari {{ permissionCount }} izin aktif</p>
            </div>
            <button type="button" :disabled="savingRoleId === role.id" class="inline-flex h-9 items-center justify-center rounded-lg bg-brand-600 px-4 text-xs font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60" @click="saveRole(role)">
              {{ savingRoleId === role.id ? 'Menyimpan...' : 'Simpan perubahan' }}
            </button>
          </div>
          <div class="grid gap-5 p-5 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="(items, group) in groups" :key="group">
              <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-400">{{ group }}</h3>
              <label v-for="(label, permission) in items" :key="permission" class="flex cursor-pointer items-start gap-3 rounded-lg px-2 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/[0.04]">
                <input v-model="drafts[role.id]" :value="permission" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                <span class="text-gray-700 dark:text-gray-300">{{ label }}</span>
              </label>
            </div>
          </div>
        </div>
      </div>
      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
      <p v-if="successMessage" class="text-sm text-green-600">{{ successMessage }}</p>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { roleService, type RoleAccess } from '@/services/userService'

const pageTitle = ref('Role & Hak Akses')
const isLoading = ref(true)
const savingRoleId = ref<number | null>(null)
const roles = ref<RoleAccess[]>([])
const groups = ref<Record<string, Record<string, string>>>({})
const drafts = reactive<Record<number, string[]>>({})
const errorMessage = ref('')
const successMessage = ref('')

const permissionCount = computed(() => Object.values(groups.value).reduce((count, group) => count + Object.keys(group).length, 0))

const loadAccess = async () => {
  try {
    const data = await roleService.access()
    roles.value = data.roles
    groups.value = data.groups
    roles.value.forEach((role) => { drafts[role.id] = [...role.permissions] })
  } catch (error) {
    console.error(error)
    errorMessage.value = 'Konfigurasi hak akses tidak dapat dimuat.'
  } finally {
    isLoading.value = false
  }
}

const saveRole = async (role: RoleAccess) => {
  savingRoleId.value = role.id
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const updated = await roleService.updatePermissions(role.id, drafts[role.id] || [])
    role.permissions = updated.permissions
    drafts[role.id] = [...updated.permissions]
    successMessage.value = `Hak akses ${role.name} berhasil disimpan.`
  } catch (error) {
    console.error(error)
    errorMessage.value = `Hak akses ${role.name} gagal disimpan.`
  } finally {
    savingRoleId.value = null
  }
}

onMounted(loadAccess)
</script>
