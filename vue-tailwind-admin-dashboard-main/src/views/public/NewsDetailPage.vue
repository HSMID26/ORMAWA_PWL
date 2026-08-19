<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <div class="text-sm font-medium text-slate-500">Berita / {{ selectedNews?.category }}</div>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">{{ selectedNews?.title }}</h1>
        <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-slate-500">
          <span>Oleh {{ selectedNews?.author }}</span>
          <span>•</span>
          <span>{{ selectedNews?.publishedAt }}</span>
          <span>•</span>
          <span>{{ selectedNews?.readingTime }}</span>
        </div>
        <img :src="selectedNews?.image" :alt="selectedNews?.title" class="mt-8 h-[360px] w-full rounded-[2rem] object-cover" />
        <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
          <div class="prose max-w-none text-slate-600">
            <p>{{ selectedNews?.content }}</p>
          </div>
          <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <h3 class="text-lg font-semibold text-slate-900">Artikel terkait</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-600">
              <li v-for="item in news.filter((article) => article.slug !== selectedSlug)" :key="item.slug" class="rounded-2xl bg-white p-3">
                <RouterLink :to="`/news/${item.slug}`" class="font-medium text-slate-900">{{ item.title }}</RouterLink>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import PublicLayout from '@/components/public/PublicLayout.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const route = useRoute()
const { news } = usePublicSite()
const selectedSlug = computed(() => route.params.slug as string)
const selectedNews = computed(() => news.find((item) => item.slug === selectedSlug.value))
</script>
