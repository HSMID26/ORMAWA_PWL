<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      
      <!-- Kolom Kiri: Profil & Tampilan Visual -->
      <div class="flex flex-col gap-6 xl:col-span-1">
        
        <!-- Panel Identitas Dasar -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
          <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-700">
            Identitas Organisasi
          </h3>
          <form @submit.prevent="saveProfile" class="flex flex-col gap-5">
            
            <!-- Upload Logo -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Logo Resmi
              </label>
              <div class="flex items-center gap-4">
                <img :src="settings.logoPreview" alt="Logo" class="h-16 w-16 rounded-full border border-gray-200 object-cover dark:border-gray-700" />
                <input type="file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300" />
              </div>
            </div>

            <!-- Nama Organisasi (Read Only - Diatur oleh Super Admin) -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Nama Organisasi
              </label>
              <input
                v-model="settings.nama"
                type="text"
                disabled
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
              />
            </div>

            <!-- Tagline / Slogan -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Slogan / Tagline
              </label>
              <input
                v-model="settings.tagline"
                type="text"
                placeholder="Contoh: Inovasi Tanpa Henti"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
              />
            </div>

            <button type="submit" class="mt-2 w-full rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
              Simpan Profil
            </button>
          </form>
        </div>

        <!-- Panel Tema Warna -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Tema Website</h3>
          <p class="mb-4 text-xs text-gray-500">Warna ini akan menjadi aksen utama pada halaman publik organisasi Anda.</p>
          
          <div class="flex items-center gap-4">
            <input 
              type="color" 
              v-model="settings.primaryColor" 
              class="h-12 w-12 cursor-pointer rounded-lg border border-gray-200 bg-transparent p-1 dark:border-gray-700"
            />
            <div>
              <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warna Utama (Primary)</span>
              <span class="text-xs font-mono text-gray-500 uppercase">{{ settings.primaryColor }}</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Kolom Kanan: Manajemen Modul Konten -->
      <div class="flex flex-col gap-6 xl:col-span-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
          
          <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Konfigurasi Modul
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Aktifkan fitur yang relevan dan ubah label menu sesuai kebutuhan organisasi Anda.
              </p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 w-1/3">Nama Modul Sistem</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 w-1/3">Label Menu Kustom</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Status Aktif</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="modul in modules" 
                  :key="modul.id"
                  class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
                >
                  <td class="px-4 py-4">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                      {{ modul.name }}
                      <span v-if="modul.required" class="ml-1 text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded dark:bg-red-500/20 dark:text-red-400">Wajib</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ modul.description }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <input
                      v-model="modul.customLabel"
                      :disabled="!modul.active"
                      type="text"
                      class="h-9 w-full rounded-md border border-gray-300 bg-transparent px-3 py-1 text-sm text-gray-800 shadow-theme-xs disabled:bg-gray-100 disabled:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90 dark:disabled:bg-gray-800"
                    />
                  </td>
                  <td class="px-4 py-3 text-center">
                    <label class="relative inline-flex cursor-pointer items-center justify-center">
                      <input 
                        type="checkbox" 
                        v-model="modul.active" 
                        :disabled="modul.required"
                        class="peer sr-only" 
                      />
                      <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:peer-focus:ring-brand-800 disabled:opacity-50"></div>
                    </label>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Tombol Simpan Konfigurasi Global -->
          <div class="mt-8 flex justify-end border-t border-gray-100 pt-5 dark:border-gray-700">
            <button 
              @click="saveModuleConfig"
              class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 shadow-theme-xs transition-colors"
            >
              Simpan Konfigurasi Modul
            </button>
          </div>

        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const currentPageTitle = ref('Pengaturan Organisasi')

// State Identitas Dasar
const settings = ref({
  nama: 'HMPS Teknik Informatika', // Nilai tetap dari Super Admin
  logoPreview: 'https://ui-avatars.com/api/?name=HMIF&background=0D8ABC&color=fff',
  tagline: 'Inovasi Tanpa Henti',
  primaryColor: '#0D8ABC'
})

// State Konfigurasi Modul (Modularisasi sesuai spesifikasi)
const modules = ref([
  { id: 'berita', name: 'Berita & Artikel', description: 'Publikasi artikel dan liputan', customLabel: 'Berita', active: true, required: true },
  { id: 'agenda', name: 'Agenda / Kalender', description: 'Jadwal kegiatan organisasi', customLabel: 'Agenda Kegiatan', active: true, required: true },
  { id: 'pengumuman', name: 'Pengumuman', description: 'Informasi prioritas dan batas waktu', customLabel: 'Pengumuman', active: true, required: true },
  { id: 'galeri', name: 'Galeri Dokumentasi', description: 'Album foto dan video event', customLabel: 'Galeri', active: true, required: false },
  { id: 'proker', name: 'Program Kerja', description: 'Daftar kegiatan rutin divisi', customLabel: 'Program Kerja', active: true, required: false },
  { id: 'prestasi', name: 'Prestasi & Pencapaian', description: 'Data penghargaan anggota', customLabel: 'Prestasi', active: false, required: false },
  { id: 'dokumen', name: 'Arsip Dokumen', description: 'Pusat unduhan file publik', customLabel: 'Unduhan', active: true, required: false },
])

// Fungsi Simpan Profil (Kolom Kiri)
const saveProfile = () => {
  // Integrasi API update profil
  console.log('Menyimpan Profil:', settings.value)
}

// Fungsi Simpan Modul (Kolom Kanan)
const saveModuleConfig = () => {
  // Integrasi API update modul aktif dan label kustom
  console.log('Menyimpan Modul:', modules.value)
}
</script>