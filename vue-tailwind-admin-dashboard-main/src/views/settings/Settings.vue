<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Pengaturan Akun" />

    <div class="space-y-6 max-w-4xl">
      <!-- Page Header & Subtitle -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Akun</h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
          Kelola kredensial keamanan, kata sandi, dan status sesi akun Anda.
        </p>
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

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Left / Top Column: Security Section (Ganti Kata Sandi) - 7 Cols -->
        <div class="md:col-span-7 space-y-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-7 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 space-y-6">
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-white">
                Keamanan & Kata Sandi
              </h2>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Perbarui kata sandi Anda secara berkala untuk menjaga keamanan akses akun.
              </p>
            </div>

            <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
              <!-- Kata Sandi Saat Ini -->
              <div>
                <label for="current-password" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <input
                    id="current-password"
                    v-model="passwordForm.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    required
                    placeholder="••••••••"
                    :disabled="isSubmitting"
                    class="w-full rounded-xl border border-gray-300 bg-white pr-10 pl-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                  <button
                    type="button"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    tabindex="-1"
                  >
                    <svg v-if="showCurrentPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.current_password" class="mt-1 text-xs text-rose-500">{{ errors.current_password }}</p>
              </div>

              <!-- Kata Sandi Baru -->
              <div>
                <label for="new-password" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <input
                    id="new-password"
                    v-model="passwordForm.password"
                    :type="showNewPassword ? 'text' : 'password'"
                    required
                    placeholder="Minimal 8 karakter"
                    :disabled="isSubmitting"
                    class="w-full rounded-xl border border-gray-300 bg-white pr-10 pl-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    tabindex="-1"
                  >
                    <svg v-if="showNewPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.password" class="mt-1 text-xs text-rose-500">{{ errors.password }}</p>
              </div>

              <!-- Konfirmasi Kata Sandi Baru -->
              <div>
                <label for="password-confirmation" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <input
                    id="password-confirmation"
                    v-model="passwordForm.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    placeholder="Ulangi kata sandi baru"
                    :disabled="isSubmitting"
                    class="w-full rounded-xl border border-gray-300 bg-white pr-10 pl-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    tabindex="-1"
                  >
                    <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.password_confirmation" class="mt-1 text-xs text-rose-500">{{ errors.password_confirmation }}</p>
              </div>

              <!-- Action Button -->
              <div class="pt-3 flex justify-end">
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="isSubmitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ isSubmitting ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Right / Bottom Column: Session & Account Info - 5 Cols -->
        <div class="md:col-span-5 space-y-6">
          
          <!-- Sesi Aktif Card -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <h2 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">
              Sesi & Keamanan
            </h2>

            <div class="space-y-3 text-xs">
              <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800/50">
                <span class="text-gray-500 dark:text-gray-400">Status Sesi</span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 font-semibold">
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  Aktif
                </span>
              </div>

              <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800/50">
                <span class="text-gray-500 dark:text-gray-400">Metode Autentikasi</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">Laravel Sanctum</span>
              </div>

              <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800/50">
                <span class="text-gray-500 dark:text-gray-400">Masa Berlaku Token</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">24 Jam</span>
              </div>

              <div class="flex items-center justify-between py-1">
                <span class="text-gray-500 dark:text-gray-400">Tingkat Hak Akses</span>
                <span class="font-semibold text-brand-600 dark:text-brand-400">{{ userRole }}</span>
              </div>
            </div>

            <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
              <button
                type="button"
                @click="handleLogout"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/30 dark:hover:bg-rose-900/40 dark:text-rose-400 px-4 py-2.5 text-xs font-bold transition shadow-2xs"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar dari Akun</span>
              </button>
            </div>
          </div>

          <!-- Institutional Notice Card -->
          <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
            <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
              <span>🛡️</span>
              <span>Kebijakan Keamanan Akun</span>
            </h3>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
              Akun pengurus terikat secara resmi dengan institusi Institut Teknologi Indonesia. Jangan membagikan kata sandi Anda kepada pihak lain.
            </p>
          </div>

        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { authService } from '@/services/authService'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const authStore = useAuthStore()

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const isSubmitting = ref(false)
const statusMessage = ref('')
const statusType = ref<'success' | 'error'>('success')
const errors = reactive<{ current_password?: string; password?: string; password_confirmation?: string }>({})

const userRole = computed(() => authStore.role || authStore.user?.role || 'Anggota')

const handlePasswordSubmit = async () => {
  errors.current_password = ''
  errors.password = ''
  errors.password_confirmation = ''
  statusMessage.value = ''

  if (!passwordForm.current_password) {
    errors.current_password = 'Kata sandi saat ini wajib diisi.'
    return
  }

  if (!passwordForm.password) {
    errors.password = 'Kata sandi baru wajib diisi.'
    return
  }

  if (passwordForm.password.length < 8) {
    errors.password = 'Kata sandi baru minimal 8 karakter.'
    return
  }

  if (passwordForm.password !== passwordForm.password_confirmation) {
    errors.password_confirmation = 'Konfirmasi kata sandi baru tidak cocok.'
    return
  }

  isSubmitting.value = true

  try {
    const res = await authService.updatePassword({
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    })

    statusType.value = 'success'
    statusMessage.value = res.message || 'Kata sandi berhasil diperbarui.'

    // Reset password form
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (err: any) {
    statusType.value = 'error'
    if (err?.data?.errors) {
      if (err.data.errors.current_password) errors.current_password = err.data.errors.current_password[0]
      if (err.data.errors.password) errors.password = err.data.errors.password[0]
      if (err.data.errors.password_confirmation) errors.password_confirmation = err.data.errors.password_confirmation[0]
      statusMessage.value = err.data.message || 'Gagal memperbarui kata sandi.'
    } else {
      statusMessage.value = err?.data?.message || err?.message || 'Gagal memperbarui kata sandi. Periksa kata sandi saat ini.'
    }
  } finally {
    isSubmitting.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
}
</script>
