<template>
  <router-link
    :to="publicHomeLink"
    class="lg:hidden focus:outline-hidden transition-opacity hover:opacity-90"
    :title="logoTitle"
  >
    <AppLogo :collapsed="true" />
  </router-link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLogo from '@/components/common/AppLogo.vue'

const authStore = useAuthStore()

const publicHomeLink = computed(() => {
  const subdomain = authStore.user?.organization?.subdomain || authStore.organizationSubdomain
  if (subdomain) {
    return `/organizations/${subdomain.toLowerCase()}`
  }
  return '/'
})

const logoTitle = computed(() => {
  const orgName = authStore.user?.organization?.nama
  return orgName ? `Buka Halaman Publik ${orgName}` : 'Buka Portal Publik ORMAWA ITI'
})
</script>
