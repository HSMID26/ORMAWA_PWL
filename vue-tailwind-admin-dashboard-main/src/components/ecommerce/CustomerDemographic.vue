<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <!-- Bagian 1: Kartu Metrik Utama (Global) -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
      <!-- Total Organisasi -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/20">
          <span class="text-brand-500 dark:text-brand-400 font-bold text-xl">🏢</span>
        </div>
        <div class="mt-4 flex items-end justify-between">
          <div>
            <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">24</h4>
            <span class="text-sm font-medium text-gray-500">Total Organisasi Aktif</span>
          </div>
          <span class="flex items-center gap-1 text-sm font-medium text-success-500">
            +2 <span class="text-gray-500">bulan ini</span>
          </span>
        </div>
      </div>

      <!-- Total Pengguna Global -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-500/20">
          <span class="text-blue-500 dark:text-blue-400 font-bold text-xl">👥</span>
        </div>
        <div class="mt-4 flex items-end justify-between">
          <div>
            <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">842</h4>
            <span class="text-sm font-medium text-gray-500">Total Pengguna Sistem</span>
          </div>
          <span class="flex items-center gap-1 text-sm font-medium text-success-500">
            +15%
          </span>
        </div>
      </div>

      <!-- Kapasitas Penyimpanan Global -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/20">
          <span class="text-warning-500 dark:text-warning-400 font-bold text-xl">💾</span>
        </div>
        <div class="mt-4 flex items-end justify-between">
          <div>
            <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">145 GB</h4>
            <span class="text-sm font-medium text-gray-500">Storage Terpakai (Kapasitas 500GB)</span>
          </div>
          <span class="flex items-center gap-1 text-sm font-medium text-warning-500">
            29%
          </span>
        </div>
      </div>

      <!-- Total Traffic Global -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-500/20">
          <span class="text-purple-500 dark:text-purple-400 font-bold text-xl">📈</span>
        </div>
        <div class="mt-4 flex items-end justify-between">
          <div>
            <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">125.4k</h4>
            <span class="text-sm font-medium text-gray-500">Visitors Lintas Ormawa</span>
          </div>
          <span class="flex items-center gap-1 text-sm font-medium text-success-500">
            +12.5%
          </span>
        </div>
      </div>
    </div>

    <!-- Bagian 2: Area Konten Utama -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      
      <!-- Panel Kiri: Antrean Pendaftaran Organisasi Baru (Alur Onboarding) -->
      <div class="flex flex-col gap-6 xl:col-span-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
          <div class="mb-5 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Permintaan Pendaftaran Organisasi Baru
              </h3>
              <p class="text-sm text-gray-500">Menunggu penetapan Admin Organisasi dan Subdomain.</p>
            </div>
            <button class="text-sm font-medium text-brand-500 hover:text-brand-600">Lihat Semua</button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Nama Organisasi</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Jenis</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Pemohon</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="req in pendingRegistrations" :key="req.id" class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                  <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ req.nama }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ req.jenis }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ req.pemohon }}</td>
                  <td class="px-4 py-3 text-center">
                    <button class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                      Proses (Set Subdomain)
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Panel Grafik Pertumbuhan Sistem -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
            Pertumbuhan Data Konten Lintas Instance
          </h3>
          <!-- Placeholder untuk Chart.js / ApexCharts yang merender grafik multiline -->
          <div class="h-64 w-full rounded-lg bg-gray-50 flex items-center justify-center border border-dashed border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <span class="text-sm text-gray-500 font-medium">[ Area Visualisasi Grafik Traffic Global ]</span>
          </div>
        </div>
      </div>

      <!-- Panel Kanan: Status Sistem & Quick Actions -->
      <div class="flex flex-col gap-6 xl:col-span-1">
        
        <!-- Quick Actions Super Admin -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Aksi Cepat Pusat</h3>
          <div class="grid grid-cols-2 gap-3">
            <button class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-2xl mb-2">🏢</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Buat Instance Ormawa</span>
            </button>
            <button class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-2xl mb-2">⚙️</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Konfigurasi Global</span>
            </button>
            <button class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-2xl mb-2">📢</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Broadcast Pengumuman</span>
            </button>
            <button class="flex flex-col items-center justify-center rounded-xl border border-gray-200 p-4 hover:bg-error-50 dark:border-gray-700 dark:hover:bg-error-500/10 transition">
              <span class="text-2xl mb-2">🛑</span>
              <span class="text-xs font-medium text-error-600 dark:text-error-400 text-center">Mode Maintenance</span>
            </button>
          </div>
        </div>

        <!-- Log Aktivitas Lintas Instance Terbaru -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Aktivitas Lintas Sistem</h3>
          <ul class="flex flex-col gap-4">
            <li v-for="log in recentGlobalLogs" :key="log.id" class="flex items-start gap-3">
              <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ log.initial }}
              </div>
              <div>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                  {{ log.user }} <span class="font-normal text-gray-500">dari</span> {{ log.org }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">{{ log.action }}</p>
                <span class="text-[10px] text-gray-400 mt-1 block">{{ log.time }}</span>
              </div>
            </li>
          </ul>
          <button class="mt-5 w-full rounded-lg border border-gray-300 bg-white py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Lihat Semua Log
          </button>
        </div>

      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const currentPageTitle = ref('Global Dashboard (Super Admin)')

// Data Mockup Pendaftaran Organisasi Baru (Berdasarkan Dokumen Alur Onboarding)
const pendingRegistrations = ref([
  { id: 1, nama: 'UKM Paduan Suara', jenis: 'UKM', pemohon: 'Andi Saputra' },
  { id: 2, nama: 'HMPS Teknik Elektro', jenis: 'HMPS', pemohon: 'Budi Santoso' }
])

// Data Mockup Aktivitas Lintas Sistem
const recentGlobalLogs = ref([
  { id: 1, initial: 'NP', user: 'Nadia Prameswari', org: 'HMPS TI', action: 'Mempublikasikan artikel baru', time: '10 menit yang lalu' },
  { id: 2, initial: 'BW', user: 'Bagas Wicaksono', org: 'UKM Basket', action: 'Mengubah struktur organisasi', time: '1 jam yang lalu' },
  { id: 3, initial: 'SA', user: 'Super Admin', org: 'Pusat', action: 'Mendaftarkan UKM Kesenian', time: '3 jam yang lalu' }
])
</script>

