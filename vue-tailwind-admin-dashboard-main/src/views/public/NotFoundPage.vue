<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    <PublicNavbar />

    <main class="flex-grow flex items-center justify-center px-4 py-20 pt-28">
      <div class="max-w-md w-full bg-white rounded border border-[#C2C6D3] p-8 text-center space-y-4 shadow-2xs">
        <div class="h-12 w-12 rounded bg-[#F3F4F5] text-[#00346F] mx-auto flex items-center justify-center font-bold text-lg">
          404
        </div>
        <h1 class="text-xl font-bold text-[#191C1D]">Halaman Tidak Ditemukan</h1>
        <p class="text-xs text-[#424751] leading-relaxed">
          Tautan yang Anda tuju tidak tersedia, telah dihapus, atau sedang mengalami pemeliharaan sistem.
        </p>
        <div class="pt-2">
          <router-link
            to="/"
            class="inline-block rounded bg-[#00346F] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#004A99] transition shadow-2xs"
          >
            &larr; Kembali ke Beranda
          </router-link>
        </div>
      </div>
    </main>

    <PublicFooter />
  </div>
</template>

<script setup lang="ts">
import PublicNavbar from '@/components/public/PublicNavbar.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
</script>
