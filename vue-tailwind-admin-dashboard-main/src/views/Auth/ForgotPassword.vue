<template>
  <FullScreenLayout>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
        
        <!-- Kolom Kiri: Form Forgot Password -->
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
                  Lupa Kata Sandi?
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                  Masukkan email akun Anda. Jika akun terdaftar, kami akan mengirimkan petunjuk untuk mengatur ulang kata sandi.
                </p>
              </div>

              <!-- Success State -->
              <div v-if="isSubmitted" class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5 space-y-3 dark:bg-emerald-950/30 dark:border-emerald-800">
                <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                  <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span>Periksa Email Anda</span>
                </div>
                <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">
                  {{ successMessage || 'Jika alamat email tersebut terdaftar dalam sistem, tautan instruksi untuk mengatur ulang kata sandi telah dikirimkan ke kotak masuk email Anda.' }}
                </p>
                <div class="pt-3 border-t border-emerald-200/60 dark:border-emerald-800 flex items-center justify-between">
                  <button
                    type="button"
                    @click="isSubmitted = false"
                    class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 hover:underline"
                  >
                    Kirim ulang tautan
                  </button>
                  <router-link
                    to="/login"
                    class="inline-block rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                  >
                    Ke Halaman Login &rarr;
                  </router-link>
                </div>
              </div>

              <!-- Form Forgot Password -->
              <form v-else @submit.prevent="handleSubmit">
                <!-- General Error Message -->
                <div v-if="serverError" class="mb-5 rounded-lg border border-error-200 bg-error-50 p-3.5 text-xs text-error-700 dark:bg-error-950/30 dark:border-error-800">
                  {{ serverError }}
                </div>

                <div class="space-y-5">
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
                      :aria-invalid="!!fieldErrors.email"
                    />
                    <p v-if="fieldErrors.email" class="mt-1.5 text-xs text-error-500">{{ fieldErrors.email }}</p>
                  </div>
                  
                  <!-- Submit Button -->
                  <div>
                    <button 
                      type="submit" 
                      :disabled="isLoading"
                      class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ isLoading ? 'Mengirim Petunjuk...' : 'Kirim Tautan Reset' }}
                    </button>
                  </div>
                </div>
              </form>
              
              <div class="mt-6 text-center">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-400">
                  Ingat kata sandi Anda? 
                  <router-link to="/login" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">
                    Masuk Sekarang
                  </router-link>
                </p>
              </div>
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
              <h2 class="text-xl font-bold text-white mb-2">Pemulihan Akun Resmi</h2>
              <p class="text-sm text-gray-400 leading-relaxed">
                Pusat bantuan pemulihan akses pengurus organisasi kemahasiswaan Institut Teknologi Indonesia.
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import CommonGridShape from '@/components/common/CommonGridShape.vue'
import { authService } from '@/services/authService'

const email = ref('')
const isLoading = ref(false)
const isSubmitted = ref(false)
const successMessage = ref('')
const serverError = ref('')
const fieldErrors = ref<{ email?: string }>({})

const validate = (): boolean => {
  fieldErrors.value = {}
  serverError.value = ''
  
  const trimmed = email.value.trim()
  if (!trimmed) {
    fieldErrors.value.email = 'Email wajib diisi.'
    return false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(trimmed)) {
    fieldErrors.value.email = 'Masukkan alamat email yang valid.'
    return false
  }
  
  return true
}

const handleSubmit = async () => {
  if (!validate() || isLoading.value) return
  
  isLoading.value = true
  serverError.value = ''
  
  try {
    const res = await authService.forgotPassword(email.value.trim())
    successMessage.value = res.message || 'Tautan reset kata sandi telah dikirimkan.'
    isSubmitted.value = true
  } catch (err: any) {
    if (err && typeof err === 'object') {
      if (err.errors?.email?.[0]) {
        fieldErrors.value.email = err.errors.email[0]
      } else if (err.message) {
        serverError.value = err.message
      } else {
        serverError.value = 'Gagal menghubungi server. Silakan coba lagi.'
      }
    } else {
      serverError.value = 'Terjadi kesalahan pada server. Silakan coba lagi.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
