<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <h1 class="text-4xl font-semibold tracking-tight text-slate-900">Pencarian</h1>
        <p class="mt-3 text-base leading-8 text-slate-600">Cari berita, agenda, dan dokumen organisasi.</p>
        <input class="mt-8 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Ketik kata kunci..." />
        <div class="mt-8 space-y-4">
          <div v-for="item in [...news, ...events, ...documents]" :key="item.slug" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-medium text-slate-500">{{ item.title }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ 'excerpt' in item ? item.excerpt : item.description }}</p>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { news, events, documents } = usePublicSite()
</script>
