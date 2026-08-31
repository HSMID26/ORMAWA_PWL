<template>
  <FullScreenLayout>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
        
        <!-- Kolom Kiri: Form Reset Password -->
        <div class="flex flex-col flex-1 w-full lg:w-5/12">
          <div class="w-full max-w-md pt-10 mx-auto px-6 sm:px-0">
            <router-link
              to="/login"
              class="inline-flex items-center text-sm font-medium text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
              <svg class="stroke-current mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              Kembali ke Login
            </router-link>
          </div>
          
          <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto px-6 sm:px-0">
            <div>
              <div class="mb-8">
                <h1 class="mb-2 font-bold text-gray-900 text-2xl dark:text-white">
                  Atur Ulang Kata Sandi
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                  Masukkan kata sandi baru untuk akun Anda.
                </p>
              </div>

              <!-- Invalid Token Alert -->
              <div v-if="!token && !isSuccess" class="rounded-xl border border-amber-200 bg-amber-50 p-5 space-y-3 dark:bg-amber-950/30 dark:border-amber-800">
                <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-bold text-sm">
                  <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <span>Tautan Tidak Lengkap</span>
                </div>
                <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                  Tautan pemulihan kata sandi tidak memiliki token valid. Silakan ajukan permintaan lupa kata sandi baru.
                </p>
                <div class="pt-2">
                  <router-link
                    to="/forgot-password"
                    class="inline-block rounded-lg bg-amber-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition"
                  >
                    Minta Tautan Baru &rarr;
                  </router-link>
                </div>
              </div>

              <!-- Success State -->
              <div v-else-if="isSuccess" class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5 space-y-3 dark:bg-emerald-950/30 dark:border-emerald-800">
                <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                  <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Kata Sandi Berhasil Diperbarui</span>
                </div>
                <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">
                  Kata sandi Anda telah berhasil diubah. Silakan masuk kembali menggunakan kata sandi baru Anda.
                </p>
                <div class="pt-3 border-t border-emerald-200/60 dark:border-emerald-800">
                  <router-link
                    to="/login"
                    class="inline-block w-full text-center rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white hover:bg-brand-600 transition"
                  >
                    Masuk ke Akun
                  </router-link>
                </div>
              </div>

              <!-- Form Reset Password -->
              <form v-else @submit.prevent="handleSubmit">
                <!-- General Error Message -->
                <div v-if="serverError" class="mb-5 rounded-lg border border-error-200 bg-error-50 p-3.5 text-xs text-error-700 dark:bg-error-950/30 dark:border-error-800">
                  {{ serverError }}
                </div>

                <div class="space-y-4">
                  <!-- Email -->
                  <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Email Akun <span class="text-error-500">*</span>
                    </label>
                    <input
                      v-model="email"
                      type="email"
                      id="email"
                      name="email"
                      autocomplete="email"
                      placeholder="contoh: user@iti.ac.id"
                      :class="[
                        'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                        fieldErrors.email 
                          ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' 
                          : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800'
                      ]"
                    />
                    <p v-if="fieldErrors.email" class="mt-1.5 text-xs text-error-500">{{ fieldErrors.email }}</p>
                  </div>

                  <!-- Password Baru -->
                  <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Kata Sandi Baru <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        :class="[
                          'h-11 w-full rounded-lg border bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                          fieldErrors.password 
                            ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' 
                            : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800'
                        ]"
                      />
                      <button 
                        type="button"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        @click="showPassword = !showPassword" 
                        class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-hidden"
                      >
                        <svg v-if="!showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" fill="currentColor" />
                        </svg>
                        <svg v-else class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" fill="currentColor" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="fieldErrors.password" class="mt-1.5 text-xs text-error-500">{{ fieldErrors.password }}</p>
                  </div>

                  <!-- Konfirmasi Password Baru -->
                  <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Konfirmasi Kata Sandi Baru <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model="passwordConfirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                        :class="[
                          'h-11 w-full rounded-lg border bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                          fieldErrors.password_confirmation 
                            ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' 
                            : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800'
                        ]"
                      />
                      <button 
                        type="button"
                        :aria-label="showConfirmPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        @click="showConfirmPassword = !showConfirmPassword" 
                        class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-hidden"
                      >
                        <svg v-if="!showConfirmPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" fill="currentColor" />
                        </svg>
                        <svg v-else class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" fill="currentColor" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="fieldErrors.password_confirmation" class="mt-1.5 text-xs text-error-500">{{ fieldErrors.password_confirmation }}</p>
                  </div>
                  
                  <!-- Submit Button -->
                  <div class="pt-2">
                    <button 
                      type="submit" 
                      :disabled="isLoading"
                      class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ isLoading ? 'Menyimpan...' : 'Simpan Kata Sandi Baru' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      
        <!-- Kolom Kanan: Identitas Kampus (Branding) -->
        <div class="relative items-center hidden w-full h-full lg:w-7/12 bg-brand-950 dark:bg-gray-950 lg:grid overflow-hidden">
          <div class="flex items-center justify-center z-1">
            <common-grid-shape />
            <div class="flex flex-col items-center max-w-lg text-center px-6">
              <router-link to="/" class="block mb-8">
                <div class="flex items-center gap-3">
                  <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/10 backdrop-blur-xs text-white font-bold text-xl border border-white/20">
                    ITI
                  </div>
                  <span class="text-2xl font-bold tracking-tight text-white">ORMAWA ITI</span>
                </div>
              </router-link>
              <h2 class="text-xl font-bold text-white mb-2">Keamanan Kredensial</h2>
              <p class="text-sm text-gray-400 leading-relaxed">
                Pastikan Anda menggunakan kata sandi unik dan kuat untuk melindungi akun organisasi Anda.
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import CommonGridShape from '@/components/common/CommonGridShape.vue'
import { authService } from '@/services/authService'

const route = useRoute()

const token = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isLoading = ref(false)
const isSuccess = ref(false)
const serverError = ref('')
const fieldErrors = ref<{ email?: string; password?: string; password_confirmation?: string }>({})

onMounted(() => {
  token.value = (route.query.token as string) || ''
  email.value = (route.query.email as string) || ''
})

const validate = (): boolean => {
  fieldErrors.value = {}
  serverError.value = ''
  
  if (!email.value.trim()) {
    fieldErrors.value.email = 'Email wajib diisi.'
  }
  
  if (!password.value) {
    fieldErrors.value.password = 'Kata sandi baru wajib diisi.'
  } else if (password.value.length < 8) {
    fieldErrors.value.password = 'Kata sandi minimal 8 karakter.'
  }
  
  if (!passwordConfirmation.value) {
    fieldErrors.value.password_confirmation = 'Konfirmasi kata sandi wajib diisi.'
  } else if (password.value !== passwordConfirmation.value) {
    fieldErrors.value.password_confirmation = 'Konfirmasi kata sandi tidak cocok.'
  }
  
  return Object.keys(fieldErrors.value).length === 0
}

const handleSubmit = async () => {
  if (!validate() || isLoading.value || !token.value) return
  
  isLoading.value = true
  serverError.value = ''
  
  try {
    await authService.resetPassword({
      token: token.value,
      email: email.value.trim(),
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    isSuccess.value = true
  } catch (err: any) {
    if (err && typeof err === 'object') {
      if (err.errors) {
        if (err.errors.email?.[0]) fieldErrors.value.email = err.errors.email[0]
        if (err.errors.password?.[0]) fieldErrors.value.password = err.errors.password[0]
        if (err.errors.token?.[0]) serverError.value = err.errors.token[0]
      } else if (err.message) {
        serverError.value = err.message
      } else {
        serverError.value = 'Gagal mengatur ulang kata sandi. Silakan ajukan permohonan baru.'
      }
    } else {
      serverError.value = 'Terjadi kesalahan pada server. Silakan coba lagi.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
