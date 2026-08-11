<template>
  <article class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
    <div class="p-6">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
          <img :src="organization.logo" :alt="organization.name" class="h-12 w-12 rounded-full object-cover" />
          <div>
            <h3 class="text-lg font-semibold text-slate-900">{{ organization.name }}</h3>
            <p class="mt-1 text-sm font-medium text-slate-500">{{ organization.category }}</p>
          </div>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-slate-600">Portal</span>
      </div>
      <p class="mt-5 text-sm leading-7 text-slate-600">{{ organization.shortDescription }}</p>
      <div class="mt-6 flex items-center justify-between">
        <RouterLink :to="organization.website" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
          Visit Website
        </RouterLink>
        <span class="text-sm text-slate-500">{{ organization.slug }}</span>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import type { OrganizationDirectoryItem } from '@/types/portal'

interface Props {
  organization: OrganizationDirectoryItem
}

defineProps<Props>()
</script>
