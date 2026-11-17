<template>
  <FullScreenLayout>
    <div class="flex items-center justify-center h-screen p-6 bg-white dark:bg-gray-900 sm:p-0">
      <div class="w-full max-w-md mx-auto text-center">
        <!-- Icon -->
        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 bg-green-100 rounded-full dark:bg-green-900/30">
          <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>

        <h1 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">
          Pendaftaran Berhasil Dikirim
        </h1>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
          Pendaftaran Anda sedang diverifikasi oleh Super Admin. Akun Admin Organisasi akan dibuat setelah pendaftaran disetujui.
        </p>

        <div class="p-4 mb-8 bg-gray-50 border border-gray-100 rounded-xl dark:bg-gray-800 dark:border-gray-700 text-left">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">Status Pendaftaran</h3>
          <div class="flex items-center gap-2 mb-1">
            <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
            <span class="text-sm font-medium text-yellow-600 dark:text-yellow-500">Menunggu Persetujuan</span>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Anda akan bisa login ke dashboard Admin Organisasi menggunakan email yang didaftarkan setelah pendaftaran disetujui.
          </p>
        </div>

        <div class="space-y-3">
          <router-link
            to="/login"
            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
          >
            Ke Halaman Login
          </router-link>
          
          <router-link
            to="/"
            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-theme-xs hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            Kembali ke Beranda
          </router-link>
        </div>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
</script>
