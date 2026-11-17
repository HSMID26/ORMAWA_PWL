<template>
  <div class="flex items-end justify-between gap-4">
    <div>
      <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">{{ eyebrow }}</p>
      <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ title }}</h2>
    </div>
    <RouterLink
      v-if="actionLabel && actionHref && actionHref !== '#'"
      :to="actionHref"
      class="text-sm font-medium text-slate-600 transition hover:text-slate-900"
    >
      {{ actionLabel }}
    </RouterLink>
  </div>
</template>

<script setup lang="ts">
interface Props {
  eyebrow: string
  title: string
  actionLabel?: string
  actionHref?: string
}

withDefaults(defineProps<Props>(), {
  actionLabel: '',
  actionHref: '#',
})
</script>
