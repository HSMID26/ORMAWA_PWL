<template>
  <figure class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <img :src="image" :alt="title" class="h-56 w-full object-cover transition duration-500 group-hover:scale-105" />
    <figcaption class="p-5">
      <p class="text-sm font-medium text-slate-500">{{ album }}</p>
      <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ title }}</h3>
      <p class="mt-2 text-sm leading-7 text-slate-600">{{ description }}</p>
    </figcaption>
  </figure>
</template>

<script setup lang="ts">
interface Props {
  title: string
  description: string
  image: string
  album: string
}

defineProps<Props>()
</script>
