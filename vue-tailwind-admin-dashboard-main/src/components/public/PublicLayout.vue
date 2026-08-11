<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <RouterLink :to="resolvePath('/')" class="flex items-center gap-3">
          <img :src="config.logo" alt="Organization logo" class="h-10 w-10 rounded-full object-cover" />
          <div>
            <p class="text-lg font-semibold tracking-tight">{{ config.organizationName }}</p>
            <p class="text-sm text-slate-500">{{ config.tagline }}</p>
          </div>
        </RouterLink>

        <nav class="hidden items-center gap-6 md:flex">
          <RouterLink
            v-for="item in navigation"
            :key="item.to"
            :to="resolvePath(item.to)"
            class="text-sm font-medium text-slate-600 transition hover:text-slate-900"
          >
            {{ item.label }}
          </RouterLink>
        </nav>

        <a
          :href="`mailto:${config.email}`"
          class="hidden rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 md:inline-flex"
        >
          Hubungi Kami
        </a>
      </div>
    </header>

    <main>
      <slot />
    </main>

    <footer class="border-t border-slate-200 bg-white">
      <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div>
          <p class="text-lg font-semibold">{{ config.organizationName }}</p>
          <p class="mt-3 text-sm leading-7 text-slate-600">{{ config.intro }}</p>
        </div>
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Quick Links</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li v-for="item in navigation" :key="item.to">
              <RouterLink :to="resolvePath(item.to)" class="transition hover:text-slate-900">{{ item.label }}</RouterLink>
            </li>
          </ul>
        </div>
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Kontak</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li>{{ config.address }}</li>
            <li>{{ config.email }}</li>
            <li><a :href="config.instagram" target="_blank" rel="noreferrer">Instagram</a></li>
          </ul>
        </div>
      </div>
      <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-4 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
        © {{ new Date().getFullYear() }} {{ config.organizationName }} · Powered by CMS Ormawa ITI
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { config, navigation, resolvePath } = usePublicSite()

const accent = computed(() => config.value.accentColor)
</script>
