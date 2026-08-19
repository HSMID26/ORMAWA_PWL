<template>
  <PublicLayout>
    <section class="mx-auto flex min-h-[70vh] max-w-7xl items-center px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="w-full rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-10">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">404</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Halaman tidak ditemukan</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-8 text-slate-600">Halaman yang Anda cari mungkin telah dipindahkan atau tidak tersedia saat ini.</p>
        <RouterLink to="/" class="mt-8 inline-flex rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white">
          Kembali ke Beranda
        </RouterLink>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
</script>
