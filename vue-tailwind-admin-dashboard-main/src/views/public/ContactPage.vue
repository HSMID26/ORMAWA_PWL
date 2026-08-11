<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
          <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Kontak</p>
          <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-900">Hubungi kami</h1>
          <p class="mt-4 text-base leading-8 text-slate-600">Kami terbuka untuk pertanyaan, kolaborasi, dan dukungan dari komunitas.</p>
          <div class="mt-8 space-y-4 text-sm text-slate-600">
            <p><span class="font-semibold text-slate-900">Alamat:</span> {{ config.address }}</p>
            <p><span class="font-semibold text-slate-900">Email:</span> {{ config.email }}</p>
            <p><span class="font-semibold text-slate-900">Instagram:</span> <a :href="config.instagram" target="_blank" rel="noreferrer" class="text-slate-900">{{ config.instagram }}</a></p>
            <p><span class="font-semibold text-slate-900">WhatsApp:</span> <a :href="config.whatsapp" target="_blank" rel="noreferrer" class="text-slate-900">{{ config.whatsapp }}</a></p>
          </div>
        </div>
        <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
          <form class="space-y-5">
            <div>
              <label class="text-sm font-medium text-slate-700">Nama</label>
              <input class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Nama Anda" />
            </div>
            <div>
              <label class="text-sm font-medium text-slate-700">Email</label>
              <input class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="email@domain.com" />
            </div>
            <div>
              <label class="text-sm font-medium text-slate-700">Pesan</label>
              <textarea class="mt-2 min-h-36 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Tuliskan pesan Anda..." />
            </div>
            <button class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white">Kirim Pesan</button>
          </form>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { config } = usePublicSite()
</script>
