<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Edit Profile" />

    <div class="space-y-6 max-w-4xl">
      <!-- Page Header & Subtitle -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
          Perbarui informasi profil akun Anda.
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

      <!-- Main Profile Card -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 space-y-8">
        
        <!-- Profile Identity Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 pb-6 border-b border-gray-100 dark:border-gray-800">
          <div class="relative group shrink-0">
            <div class="h-20 w-20 rounded-full overflow-hidden border-2 border-brand-500/20 bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center text-brand-600 dark:text-brand-400 font-extrabold text-2xl shadow-xs">
              <img
                v-if="avatarPreview || authStore.user?.avatar"
                :src="avatarPreview || authStore.user?.avatar || undefined"
                :alt="form.name || 'Foto Profil'"
                class="h-full w-full object-cover"
              />
              <span v-else>{{ userInitial }}</span>
            </div>
          </div>

          <div class="space-y-1.5 flex-grow">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
              {{ form.name || authStore.user?.name || 'Pengguna' }}
            </h2>
            <div class="flex flex-wrap items-center gap-2 text-xs">
              <!-- Role Badge (Read-only) -->
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300 font-semibold border border-brand-200 dark:border-brand-800">
                {{ userRole }}
              </span>

              <!-- Organization Badge (Read-only) -->
              <span v-if="userOrganization" class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-medium">
                {{ userOrganization }} · ITI
              </span>
            </div>
          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          
          <!-- Section 1: Informasi Profil -->
          <div class="space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2">
              Informasi Profil
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Nama Lengkap (Editable) -->
              <div class="sm:col-span-2">
                <label for="profile-name" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input
                  id="profile-name"
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="Masukkan nama lengkap"
                  :disabled="isSubmitting"
                  class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <p v-if="errors.name" class="mt-1 text-xs text-rose-500">{{ errors.name }}</p>
              </div>

              <!-- Email (Read-Only) -->
              <div class="sm:col-span-2">
                <label for="profile-email" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Alamat Email
                </label>
                <input
                  id="profile-email"
                  :value="authStore.user?.email || '-'"
                  type="email"
                  disabled
                  readonly
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs sm:text-sm text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400 select-all"
                />
                <p class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                  Email digunakan sebagai identitas login akun resmi Anda.
                </p>
              </div>

              <!-- Peran / Role (Read-Only) -->
              <div>
                <label class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Peran Akun
                </label>
                <input
                  :value="userRole"
                  type="text"
                  disabled
                  readonly
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs sm:text-sm text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400"
                />
              </div>

              <!-- Organisasi Terkait (Read-Only) -->
              <div>
                <label class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                  Organisasi Naungan
                </label>
                <input
                  :value="userOrganization ? `${userOrganization} · Institut Teknologi Indonesia` : 'Pusat Administrator Global'"
                  type="text"
                  disabled
                  readonly
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs sm:text-sm text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400 truncate"
                />
              </div>
            </div>
          </div>

          <!-- Section 2: Foto Profil / Avatar -->
          <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2">
              Foto Profil
            </h3>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
              <!-- Thumbnail Preview -->
              <div class="h-16 w-16 rounded-full overflow-hidden border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center shrink-0">
                <img
                  v-if="avatarPreview || authStore.user?.avatar"
                  :src="avatarPreview || authStore.user?.avatar || undefined"
                  alt="Preview"
                  class="h-full w-full object-cover"
                />
                <span v-else class="text-xs font-bold text-gray-400">Belum ada</span>
              </div>

              <div class="space-y-2 flex-grow">
                <div class="flex flex-wrap items-center gap-3">
                  <label
                    for="avatar-upload"
                    class="cursor-pointer inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition shadow-2xs"
                  >
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Pilih Foto Baru</span>
                  </label>
                  <input
                    id="avatar-upload"
                    type="file"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="hidden"
                    @change="handleFileSelect"
                  />

                  <button
                    v-if="avatarFile || avatarPreview || authStore.user?.avatar"
                    type="button"
                    @click="clearAvatar"
                    class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400 font-semibold px-2 py-1"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus Foto</span>
                  </button>
                </div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                  Format yang didukung: JPG, JPEG, PNG, atau WEBP. Maksimal ukuran 5 MB.
                </p>
                <p v-if="errors.avatar" class="text-xs text-rose-500">{{ errors.avatar }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 px-6 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isSubmitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { authService } from '@/services/authService'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const authStore = useAuthStore()

const form = reactive({
  name: '',
})

const avatarFile = ref<File | null>(null)
const avatarPreview = ref<string | null>(null)
const shouldRemoveAvatar = ref(false)

const isSubmitting = ref(false)
const statusMessage = ref('')
const statusType = ref<'success' | 'error'>('success')
const errors = reactive<{ name?: string; avatar?: string }>({})

const userRole = computed(() => authStore.role || authStore.user?.role || 'Anggota')
const userOrganization = computed(() => authStore.user?.organization?.nama || null)
const userInitial = computed(() => (form.name || authStore.user?.name || 'P').charAt(0).toUpperCase())

onMounted(() => {
  if (authStore.user) {
    form.name = authStore.user.name || ''
  }
})

const handleFileSelect = (event: Event) => {
  errors.avatar = ''
  const target = event.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return

  const file = target.files[0]
  if (file.size > 5 * 1024 * 1024) {
    errors.avatar = 'Ukuran berkas tidak boleh melebihi 5 MB.'
    target.value = ''
    return
  }

  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
  if (!validTypes.includes(file.type)) {
    errors.avatar = 'Format berkas harus berupa JPG, PNG, atau WEBP.'
    target.value = ''
    return
  }

  avatarFile.value = file
  shouldRemoveAvatar.value = false
  avatarPreview.value = URL.createObjectURL(file)
}

const clearAvatar = () => {
  avatarFile.value = null
  avatarPreview.value = null
  shouldRemoveAvatar.value = true
  const input = document.getElementById('avatar-upload') as HTMLInputElement
  if (input) input.value = ''
}

const handleSubmit = async () => {
  errors.name = ''
  errors.avatar = ''
  statusMessage.value = ''

  const trimmedName = form.name.trim()
  if (!trimmedName) {
    errors.name = 'Nama lengkap wajib diisi.'
    return
  }

  isSubmitting.value = true

  try {
    let payload: FormData | { name: string; avatar?: string | null }

    if (avatarFile.value) {
      const fd = new FormData()
      fd.append('name', trimmedName)
      fd.append('avatar', avatarFile.value)
      payload = fd
    } else if (shouldRemoveAvatar.value) {
      payload = {
        name: trimmedName,
        avatar: '',
      }
    } else {
      payload = {
        name: trimmedName,
      }
    }

    const res = await authService.updateProfile(payload)

    // Sync Auth Store in real-time
    if (res?.user) {
      authStore.setUser(res.user)
      form.name = res.user.name
    }

    statusType.value = 'success'
    statusMessage.value = res.message || 'Profil berhasil diperbarui.'
    avatarFile.value = null
    shouldRemoveAvatar.value = false
  } catch (err: any) {
    statusType.value = 'error'
    if (err?.data?.errors) {
      if (err.data.errors.name) errors.name = err.data.errors.name[0]
      if (err.data.errors.avatar) errors.avatar = err.data.errors.avatar[0]
      statusMessage.value = err.data.message || 'Terdapat kesalahan pada isian form.'
    } else {
      statusMessage.value = err?.data?.message || err?.message || 'Gagal memperbarui profil. Silakan coba lagi.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
