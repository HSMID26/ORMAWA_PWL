<template>
  <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <img :src="image" :alt="title" class="h-44 w-full object-cover" />
    <div class="p-6">
      <div class="flex items-center justify-between text-sm text-slate-500">
        <span class="rounded-full bg-slate-100 px-3 py-1">{{ category }}</span>
        <span>{{ formatDate(date) }}</span>
      </div>
      <h3 class="mt-4 text-xl font-semibold text-slate-900">{{ title }}</h3>
      <p class="mt-3 text-sm leading-7 text-slate-600">{{ description }}</p>
      <p class="mt-4 text-sm font-medium text-slate-500">{{ location }}</p>
    </div>
  </article>
</template>

<script setup lang="ts">
interface Props {
  title: string
  description: string
  date: string
  location: string
  category: string
  image: string
}

const props = defineProps<Props>()

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
