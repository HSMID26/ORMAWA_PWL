<template>
  <div class="flex items-center gap-2">
    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-white font-bold text-lg">
      <!-- Placeholder for ITI Logo -->
      ITI
    </div>
    <span class="text-xl font-bold text-gray-800 dark:text-white/90" v-if="!collapsed">
      CMS ORMAWA
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
