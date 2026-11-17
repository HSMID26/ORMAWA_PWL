<template>
  <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-sm font-medium text-slate-500">{{ category }}</p>
        <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ title }}</h3>
        <p class="mt-3 text-sm leading-7 text-slate-600">{{ description }}</p>
      </div>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600">{{ size }}</span>
    </div>
    <div class="mt-6 flex items-center justify-between text-sm text-slate-500">
      <span>Update {{ updatedAt }}</span>
      <a v-if="preview" :href="preview" target="_blank" rel="noreferrer" class="font-semibold text-slate-900">Preview</a>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  description: string
  category: string
  size: string
  updatedAt: string
  preview?: string
}

defineProps<Props>()
</script>
