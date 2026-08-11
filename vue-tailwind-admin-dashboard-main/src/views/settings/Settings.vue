<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Pengaturan Akun" />

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 lg:gap-8">
      
      <!-- Quick Info Panel -->
      <div class="md:col-span-1 space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3">Sesi Saat Ini</h3>
          <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">
            Anda login sebagai <strong class="text-gray-800 dark:text-white">{{ authStore.role ?? 'Anggota' }}</strong>. 
            Pastikan untuk keluar jika Anda menggunakan perangkat publik.
          </p>
          <button @click="handleLogout" class="w-full rounded-lg bg-error-50 px-4 py-2 text-sm font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20 transition-colors">
            Keluar dari Akun
          </button>
        </div>
      </div>

      <!-- Main Settings Form -->
      <div class="md:col-span-2 space-y-6">
        
        <!-- Password Section (Unsupported) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3">Keamanan & Sandi</h3>
          
          <div class="mb-6 rounded-lg bg-warning-50 p-4 border border-warning-200 dark:bg-warning-500/10 dark:border-warning-500/20">
            <div class="flex items-start gap-3">
              <span class="text-warning-600 text-lg mt-0.5">⚠️</span>
              <div>
                <h4 class="text-sm font-medium text-warning-800 dark:text-warning-400">Perubahan password belum tersedia.</h4>
                <p class="text-xs text-warning-700 mt-1 dark:text-warning-500">
                  Fitur ini membutuhkan implementasi backend <code>PATCH /api/password</code>. Saat ini fitur ubah sandi dinonaktifkan.
                </p>
              </div>
            </div>
          </div>

          <form @submit.prevent>
            <div class="space-y-5">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Lama</label>
                <input
                  type="password"
                  disabled
                  placeholder="••••••••"
                  class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-500 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 cursor-not-allowed"
                />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                <input
                  type="password"
                  disabled
                  placeholder="••••••••"
                  class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-500 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 cursor-not-allowed"
                />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                <input
                  type="password"
                  disabled
                  placeholder="••••••••"
                  class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-500 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 cursor-not-allowed"
                />
              </div>
              <div class="pt-2 flex justify-end gap-3">
                <button disabled class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-400">
                  Batal
                </button>
                <button disabled class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white opacity-50 cursor-not-allowed">
                  Perbarui Password
                </button>
              </div>
            </div>
          </form>
        </div>
        
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
}
</script>
