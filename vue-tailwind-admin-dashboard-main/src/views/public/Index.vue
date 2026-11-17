<template>
  <OrganizationLayout v-if="tenantSlug" />
  <PortalHome v-else />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePublicStore } from '@/stores/public'
import PortalHome from '@/views/public/PortalHome.vue'
import OrganizationLayout from '@/components/public/OrganizationLayout.vue'

const publicStore = usePublicStore()
const tenantSlug = computed(() => publicStore.currentTenantSlug)
</script>
