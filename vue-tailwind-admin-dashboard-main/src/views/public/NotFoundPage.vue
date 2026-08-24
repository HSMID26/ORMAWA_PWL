<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans selection:bg-slate-900 selection:text-white">
    <PublicNavbar />
    
    <main id="main-content" class="flex-grow flex flex-col items-center justify-center py-32 px-4 text-center">
      <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-900 flex items-center justify-center mb-4 text-2xl font-black">
        404
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-slate-950 mb-2">Halaman Tidak Ditemukan</h1>
      <p class="text-xs sm:text-sm text-slate-500 max-w-md mb-8 leading-relaxed">
        Halaman atau berkas publik yang Anda tuju tidak tersedia, telah dipindahkan, atau status publikasi belum aktif.
      </p>
      <div class="flex flex-wrap gap-3 justify-center">
        <router-link
          to="/"
          class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white hover:bg-slate-800 shadow-sm transition"
        >
          Kembali ke Beranda
        </router-link>
        <router-link
          to="/organizations"
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition"
        >
          Direktori Ormawa
        </router-link>
      </div>
    </main>

    <PublicFooter />
  </div>
</template>

<script setup lang="ts">
import PublicNavbar from '@/components/public/PublicNavbar.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
</script>
