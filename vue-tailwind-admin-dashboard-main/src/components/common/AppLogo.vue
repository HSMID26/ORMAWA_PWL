<template>
  <div class="flex items-center gap-2.5">
    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white overflow-hidden p-0.5 shadow-2xs border border-slate-200 shrink-0">
      <img src="/images/logo/iti-logo.png" alt="Institut Teknologi Indonesia" class="h-full w-full object-contain" />
    </div>
    <span class="text-base font-bold text-gray-900 dark:text-white/90 tracking-tight" v-if="!collapsed">
      ORMAWA ITI
    </span>
  </div>
</template>

<script setup>
defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})
</script>
