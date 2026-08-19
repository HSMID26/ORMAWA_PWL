<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Profil Pengguna" />

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:gap-8">
      <!-- Profile Display Card -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3">Informasi Akun</h3>
        <div class="flex items-center gap-4 mb-6">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600 font-bold text-2xl dark:bg-brand-500/20 dark:text-brand-400">
            {{ userInitial }}
          </div>
          <div>
            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ userName }}</h4>
            <span class="text-sm font-medium text-gray-500">{{ userRole }}</span>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <span class="block text-sm font-medium text-gray-500 mb-1">Email Address</span>
            <p class="text-gray-900 dark:text-white font-medium">{{ userEmail }}</p>
          </div>
          <div v-if="userOrganization">
            <span class="block text-sm font-medium text-gray-500 mb-1">Organisasi</span>
            <p class="text-gray-900 dark:text-white font-medium">{{ userOrganization }}</p>
          </div>
          <div>
            <span class="block text-sm font-medium text-gray-500 mb-1">Status</span>
            <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-sm font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
              Aktif
            </span>
          </div>
        </div>
      </div>

      <!-- Profile Edit Form (Unsupported) -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3">Edit Profil</h3>
        
        <div class="mb-6 rounded-lg bg-warning-50 p-4 border border-warning-200 dark:bg-warning-500/10 dark:border-warning-500/20">
          <div class="flex items-start gap-3">
            <span class="text-warning-600 text-lg mt-0.5">⚠️</span>
            <div>
              <h4 class="text-sm font-medium text-warning-800 dark:text-warning-400">Perubahan profil belum tersedia.</h4>
              <p class="text-xs text-warning-700 mt-1 dark:text-warning-500">
                Fitur ini membutuhkan implementasi backend <code>PATCH /api/profile</code>. Saat ini, profil hanya bersifat read-only.
              </p>
            </div>
          </div>
        </div>

        <form @submit.prevent>
          <div class="space-y-5">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
              <input
                type="text"
                :value="userName"
                disabled
                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-500 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 cursor-not-allowed"
              />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
              <input
                type="email"
                :value="userEmail"
                disabled
                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-500 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 cursor-not-allowed"
              />
            </div>
            <div class="pt-2 flex justify-end gap-3">
              <button disabled class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-400">
                Batal
              </button>
              <button disabled class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white opacity-50 cursor-not-allowed">
                Simpan Perubahan
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const authStore = useAuthStore()

const userName = computed(() => authStore.user?.name ?? 'Pengguna')
const userEmail = computed(() => authStore.user?.email ?? '-')
const userRole = computed(() => authStore.role ?? 'Anggota')
const userOrganization = computed(() => authStore.user?.organization?.nama ?? null)
const userInitial = computed(() => userName.value.charAt(0).toUpperCase())
</script>
