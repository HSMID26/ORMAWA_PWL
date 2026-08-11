<template>
  <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    <img :src="image" :alt="title" class="h-48 w-full object-cover" />
    <div class="p-6">
      <div class="flex items-center gap-2 text-sm text-slate-500">
        <span class="rounded-full bg-slate-100 px-3 py-1">{{ category }}</span>
        <span>{{ publishedAt }}</span>
      </div>
      <h3 class="mt-4 text-xl font-semibold text-slate-900">{{ title }}</h3>
      <p class="mt-3 text-sm leading-7 text-slate-600">{{ excerpt }}</p>
      <RouterLink :to="href" class="mt-6 inline-flex items-center text-sm font-semibold text-slate-900">
        Baca selengkapnya
      </RouterLink>
    </div>
  </article>
</template>

<script setup lang="ts">
interface Props {
  title: string
  excerpt: string
  category: string
  publishedAt: string
  image: string
  href: string
}

defineProps<Props>()
</script>
