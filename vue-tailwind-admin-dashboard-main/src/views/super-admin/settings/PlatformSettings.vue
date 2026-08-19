<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
      
      <!-- Panel 1: Konfigurasi Institusi & Domain -->
      <div class="flex flex-col gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="mb-5 border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
              Identitas Institusi & Jaringan
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Pengaturan dasar identitas kampus dan konfigurasi subdomain wildcard.
            </p>
          </div>

          <form @submit.prevent="saveGlobalSettings" class="flex flex-col gap-5">
            <!-- Nama Institusi -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Nama Institusi / Kampus
              </label>
              <input
                v-model="globalSettings.campusName"
                type="text"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
              />
            </div>

            <!-- Domain Utama -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Domain Utama (Root Domain)
              </label>
              <div class="flex items-center">
                <span class="inline-flex h-11 items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800">
                  https://*.
                </span>
                <input
                  v-model="globalSettings.mainDomain"
                  type="text"
                  placeholder="contoh: kampus.ac.id"
                  class="dark:bg-dark-900 h-11 w-full rounded-r-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                />
              </div>
              <p class="mt-1.5 text-xs text-gray-500">Subdomain tiap organisasi akan merujuk ke domain ini.</p>
            </div>
          </form>
        </div>
      </div>

      <!-- Panel 2: Batasan Sistem & Keamanan -->
      <div class="flex flex-col gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="mb-5 border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
              Alokasi & Pembatasan Sistem
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Manajemen kuota sumber daya (multi-tenant) dan status operasional.
            </p>
          </div>

          <div class="flex flex-col gap-5">
            <!-- Limit Storage -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Default Batas Penyimpanan (per Organisasi)
              </label>
              <div class="relative">
                <input
                  v-model="globalSettings.defaultStorageLimit"
                  type="number"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-12 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                />
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500">
                  GB
                </span>
              </div>
            </div>

            <!-- Toggles -->
            <div class="mt-2 flex flex-col gap-4">
              <!-- Approval Ormawa Baru -->
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-800 dark:text-white/90">Otomatisasi Pendaftaran</h4>
                  <p class="text-xs text-gray-500">HMPS/UKM baru langsung aktif tanpa review Super Admin.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                  <input type="checkbox" v-model="globalSettings.autoApproveNewOrg" class="peer sr-only" />
                  <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:peer-focus:ring-brand-800"></div>
                </label>
              </div>

              <!-- Maintenance Mode -->
              <div class="flex items-center justify-between rounded-lg bg-error-50 p-3 dark:bg-error-500/10">
                <div>
                  <h4 class="text-sm font-medium text-error-800 dark:text-error-400">Mode Pemeliharaan (Maintenance)</h4>
                  <p class="text-xs text-error-600 dark:text-error-500">Nonaktifkan seluruh akses web publik sementara waktu.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                  <input type="checkbox" v-model="globalSettings.maintenanceMode" class="peer sr-only" />
                  <div class="h-6 w-11 rounded-full bg-error-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-white after:bg-white after:transition-all after:content-[''] peer-checked:bg-error-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700"></div>
                </label>
              </div>
            </div>
            
          </div>
        </div>
      </div>

      <!-- Action Button (Full Width) -->
      <div class="xl:col-span-2 flex justify-end">
        <button 
          @click="saveGlobalSettings"
          class="flex items-center justify-center rounded-lg bg-brand-500 px-8 py-3 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-theme-xs"
        >
          Simpan Konfigurasi Global
        </button>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const currentPageTitle = ref('Pengaturan Sistem Pusat')

// State Global Setting (Level Super Admin)
const globalSettings = ref({
  campusName: 'Institut Teknologi Indonesia',
  mainDomain: 'iti.ac.id',
  defaultStorageLimit: 20, // 20 GB merujuk pada indikator antarmuka mock-up
  autoApproveNewOrg: false, // Default false agar pendaftaran butuh approval
  maintenanceMode: false
})

const saveGlobalSettings = () => {
  console.log('Menyimpan konfigurasi global:', globalSettings.value)
  // Eksekusi API Update Global Settings Laravel
  alert('Konfigurasi sistem pusat berhasil diperbarui.')
}
</script>

