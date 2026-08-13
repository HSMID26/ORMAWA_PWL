<template>
  <router-link to="/" class="lg:hidden">
    <AppLogo :collapsed="true" />
  </router-link>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import AppLogo from '@/components/common/AppLogo.vue'
</script>
