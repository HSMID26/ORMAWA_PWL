<template>
  <component :is="tenantSlug ? OrganizationHome : PortalHome" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePublicStore } from '@/stores/public'
import PortalHome from '@/views/public/PortalHome.vue'
import OrganizationHome from '@/views/public/OrganizationHome.vue'

const publicStore = usePublicStore()
const tenantSlug = computed(() => publicStore.currentTenantSlug)
</script>
