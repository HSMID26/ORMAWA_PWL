<template>
  <FullScreenLayout>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
        <div class="flex flex-col flex-1 w-full lg:w-5/12 overflow-y-auto">
          <div class="w-full max-w-xl pt-10 mx-auto px-6 sm:px-0">
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
          <!-- Form -->
          <div class="flex flex-col justify-center flex-1 w-full max-w-xl mx-auto px-6 py-6 sm:px-0">
            <div class="mb-8">
              <h1 class="mb-2 font-bold text-gray-900 text-2xl dark:text-white">
                Daftarkan Organisasi Anda
              </h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Ajukan organisasi kemahasiswaan Anda untuk bergabung ke ORMAWA ITI.
              </p>
            </div>
            <div>
              <form @submit.prevent="handleSubmit">
                <div class="space-y-8">
                  <!-- DATA ORGANISASI -->
                  <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">
                      Data Organisasi
                    </h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                      <!-- Nama Organisasi -->
                      <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Nama Organisasi <span class="text-error-500">*</span>
                        </label>
                        <input
                          v-model="form.organization_name"
                          type="text"
                          placeholder="Contoh: Himpunan Mahasiswa Informatika"
                          :class="[
                            'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                            formErrors.organization_name ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                          ]"
                        />
                        <p v-if="formErrors.organization_name" class="mt-1.5 text-xs text-error-500">{{ formErrors.organization_name }}</p>
                      </div>

                      <!-- Jenis Organisasi -->
                      <div class="sm:col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Jenis Organisasi <span class="text-error-500">*</span>
                        </label>
                        <select
                          v-model="form.organization_type"
                          :class="[
                            'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90',
                            formErrors.organization_type ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                          ]"
                        >
                          <option value="" disabled>Pilih Jenis</option>
                          <option value="HMPS">HMPS</option>
                          <option value="UKM">UKM</option>
                          <option value="BEM">BEM</option>
                          <option value="Senat">Senat</option>
                          <option value="Lainnya">Lainnya</option>
                        </select>
                        <p v-if="formErrors.organization_type" class="mt-1.5 text-xs text-error-500">{{ formErrors.organization_type }}</p>
                      </div>

                      <!-- Subdomain -->
                      <div class="sm:col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Subdomain <span class="text-error-500">*</span>
                        </label>
                        <input
                          v-model="form.organization_subdomain"
                          @input="normalizeSubdomain"
                          type="text"
                          placeholder="Contoh: hmif"
                          :class="[
                            'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                            formErrors.organization_subdomain ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                          ]"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                          Preview: https://{{ form.organization_subdomain || '...' }}.iti.ac.id
                        </p>
                        <p v-if="formErrors.organization_subdomain" class="mt-1.5 text-xs text-error-500">{{ formErrors.organization_subdomain }}</p>
                      </div>

                      <!-- Deskripsi Organisasi -->
                      <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Deskripsi Organisasi
                        </label>
                        <textarea
                          v-model="form.organization_description"
                          rows="3"
                          placeholder="Deskripsi singkat mengenai organisasi Anda..."
                          class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        ></textarea>
                      </div>
                    </div>
                  </div>

                  <!-- DATA ADMIN ORGANISASI -->
                  <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">
                      Data Admin Organisasi
                    </h2>
                    <p class="text-sm text-brand-600 dark:text-brand-400 mb-5 bg-brand-50 dark:bg-brand-900/20 p-3 rounded-lg">
                      Akun ini akan menjadi Admin Organisasi setelah pendaftaran disetujui oleh Super Admin.
                    </p>
                    
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                      <!-- Nama Depan -->
                      <div class="sm:col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Nama Depan <span class="text-error-500">*</span>
                        </label>
                        <input
                          v-model="form.admin_first_name"
                          type="text"
                          placeholder="Nama Depan"
                          :class="[
                            'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                            formErrors.admin_first_name ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                          ]"
                        />
                        <p v-if="formErrors.admin_first_name" class="mt-1.5 text-xs text-error-500">{{ formErrors.admin_first_name }}</p>
                      </div>

                      <!-- Nama Belakang -->
                      <div class="sm:col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Nama Belakang <span class="text-error-500">*</span>
                        </label>
                        <input
                          v-model="form.admin_last_name"
                          type="text"
                          placeholder="Nama Belakang"
                          :class="[
                            'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                            formErrors.admin_last_name ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                          ]"
                        />
                        <p v-if="formErrors.admin_last_name" class="mt-1.5 text-xs text-error-500">{{ formErrors.admin_last_name }}</p>
                      </div>

                      <!-- Email -->
                      <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Email <span class="text-error-500">*</span>
                        </label>
                        <input
                          v-model="form.admin_email"
                          type="email"
                          autocomplete="email"
                          placeholder="contoh: user@hmif.iti.ac.id"
                          :class="[
                            'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                            formErrors.admin_email ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                          ]"
                        />
                        <p v-if="formErrors.admin_email" class="mt-1.5 text-xs text-error-500">{{ formErrors.admin_email }}</p>
                      </div>

                      <!-- Password -->
                      <div class="sm:col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Kata Sandi <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                          <input
                            v-model="form.admin_password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Kata sandi"
                            :class="[
                              'h-11 w-full rounded-lg border bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                              formErrors.admin_password ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                            ]"
                          />
                          <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 focus:outline-hidden"
                          >
                            <svg v-if="!showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" fill="currentColor"/></svg>
                            <svg v-else class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" fill="currentColor"/></svg>
                          </button>
                        </div>
                        <p v-if="formErrors.admin_password" class="mt-1.5 text-xs text-error-500">{{ formErrors.admin_password }}</p>
                      </div>

                      <!-- Confirm Password -->
                      <div class="sm:col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                          Konfirmasi Kata Sandi <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                          <input
                            v-model="form.admin_password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi"
                            :class="[
                              'h-11 w-full rounded-lg border bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                              formErrors.admin_password_confirmation ? 'border-error-500' : 'border-gray-300 dark:border-gray-700'
                            ]"
                          />
                          <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 focus:outline-hidden"
                          >
                            <svg v-if="!showConfirmPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" fill="currentColor"/></svg>
                            <svg v-else class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" fill="currentColor"/></svg>
                          </button>
                        </div>
                        <p v-if="formErrors.admin_password_confirmation" class="mt-1.5 text-xs text-error-500">{{ formErrors.admin_password_confirmation }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Button -->
                  <div>
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isSubmitting" class="w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ isSubmitting ? 'Mengirim Pendaftaran...' : 'Kirim Pendaftaran' }}
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
                <h2 class="text-3xl font-extrabold text-white tracking-tight">ORMAWA ITI</h2>
              </router-link>
              <h3 class="text-xl font-bold text-white mb-4">Portal Manajemen Organisasi Kemahasiswaan</h3>
              <p class="text-base text-gray-300 dark:text-white/70 leading-relaxed">
                Daftarkan organisasi Anda untuk mengelola konten, anggota, dan aktivitas dalam satu platform terpadu.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import CommonGridShape from '@/components/common/CommonGridShape.vue'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToastStore } from '@/stores/toast'
import { organizationRegistrationService } from '@/services/organizationRegistrationService'

const router = useRouter()
const toastStore = useToastStore()

const isSubmitting = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = ref({
  organization_name: '',
  organization_type: '',
  organization_subdomain: '',
  organization_description: '',
  admin_first_name: '',
  admin_last_name: '',
  admin_email: '',
  admin_password: '',
  admin_password_confirmation: ''
})

const formErrors = ref<Record<string, string>>({})

const normalizeSubdomain = () => {
  form.value.organization_subdomain = form.value.organization_subdomain
    .toLowerCase()
    .replace(/[^a-z0-9-]/g, '')
}

const validateForm = () => {
  formErrors.value = {}
  let isValid = true

  if (!form.value.organization_name.trim()) {
    formErrors.value.organization_name = 'Nama organisasi wajib diisi.'
    isValid = false
  }
  if (!form.value.organization_type) {
    formErrors.value.organization_type = 'Jenis organisasi wajib diisi.'
    isValid = false
  }
  if (!form.value.organization_subdomain.trim()) {
    formErrors.value.organization_subdomain = 'Subdomain wajib diisi.'
    isValid = false
  }
  
  if (!form.value.admin_first_name.trim()) {
    formErrors.value.admin_first_name = 'Nama depan admin wajib diisi.'
    isValid = false
  }
  if (!form.value.admin_last_name.trim()) {
    formErrors.value.admin_last_name = 'Nama belakang admin wajib diisi.'
    isValid = false
  }
  if (!form.value.admin_email.trim()) {
    formErrors.value.admin_email = 'Email wajib diisi.'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.admin_email)) {
    formErrors.value.admin_email = 'Format email tidak valid.'
    isValid = false
  }

  if (!form.value.admin_password) {
    formErrors.value.admin_password = 'Kata sandi wajib diisi.'
    isValid = false
  } else if (form.value.admin_password.length < 8) {
    formErrors.value.admin_password = 'Kata sandi minimal 8 karakter.'
    isValid = false
  }

  if (!form.value.admin_password_confirmation) {
    formErrors.value.admin_password_confirmation = 'Konfirmasi kata sandi wajib diisi.'
    isValid = false
  } else if (form.value.admin_password_confirmation !== form.value.admin_password) {
    formErrors.value.admin_password_confirmation = 'Kata sandi tidak cocok.'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return

  isSubmitting.value = true
  try {
    await organizationRegistrationService.submit(form.value)
    
    // Clear password before routing
    form.value.admin_password = ''
    form.value.admin_password_confirmation = ''
    
    toastStore.success('Pendaftaran berhasil dikirim.')
    router.push({ name: 'register-success' })
  } catch (error: any) {
    // Handling validation errors from backend
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const errors = error.response.data.errors
      for (const key in errors) {
        if (errors[key].length > 0) {
          formErrors.value[key] = errors[key][0]
        }
      }
      toastStore.error('Terdapat kesalahan pada data yang Anda masukkan.')
    } else {
      toastStore.error(error.message || 'Gagal mengirim pendaftaran. Silakan coba lagi.')
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
