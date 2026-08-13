<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    

    <main>
      <slot />
    </main>

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { config, navigation, resolvePath } = usePublicSite()

const accent = computed(() => config.value.accentColor)
</script>
