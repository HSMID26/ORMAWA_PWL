<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <!-- Accessible Skip Link -->
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 rounded bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow focus:outline-none"
    >
      Menuju ke konten utama
    </a>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16 sm:h-[68px]">
        
        <!-- Institutional Brand Logo & Name -->
        <router-link to="/" class="flex items-center gap-3 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-700 rounded-md">
          <div class="h-8 w-8 bg-slate-900 text-white rounded flex items-center justify-center font-bold text-xs tracking-wider group-hover:bg-blue-900 transition">
            ITI
          </div>
          <div class="leading-tight">
            <span class="text-sm font-bold text-slate-950 tracking-tight block">
              CMS ORMAWA
            </span>
            <span class="text-[10px] text-slate-500 font-medium block">
              Institut Teknologi Indonesia
            </span>
          </div>
        </router-link>

        <!-- Center Nav Links -->
        <nav class="hidden md:flex gap-6 items-center text-xs font-semibold text-slate-600" aria-label="Navigasi Utama">
          <router-link
            to="/"
            class="hover:text-slate-950 transition py-1 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-700 rounded"
            active-class="text-blue-900 font-bold"
          >
            Beranda
          </router-link>
          <router-link
            to="/organizations"
            class="hover:text-slate-950 transition py-1 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-700 rounded"
            active-class="text-blue-900 font-bold"
          >
            Direktori Ormawa
          </router-link>
        </nav>

        <!-- Right Side: Search & Login -->
        <div class="flex items-center gap-3">
          <button
            @click="isSearchOpen = true"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-700"
            aria-label="Cari organisasi atau warta"
          >
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden sm:inline">Cari...</span>
            <kbd class="hidden sm:inline-block rounded bg-white px-1.5 py-0.5 text-[9px] font-mono font-medium text-slate-400 border border-slate-200">⌘K</kbd>
          </button>

          <div class="hidden sm:block w-px h-4 bg-slate-200"></div>

          <router-link
            to="/login"
            class="text-xs font-semibold text-slate-700 hover:text-blue-900 px-2 py-1.5 transition"
          >
            Login Pengurus
          </router-link>

          <!-- Mobile Hamburger -->
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="md:hidden text-slate-700 hover:text-slate-950 p-1.5 rounded-lg focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-700"
            aria-label="Menu navigasi"
            :aria-expanded="isMobileMenuOpen"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

      </div>
    </div>

    <!-- Mobile Drawer -->
    <div v-if="isMobileMenuOpen" class="md:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-2 text-xs font-semibold shadow-sm">
      <router-link to="/" @click="isMobileMenuOpen = false" class="block py-2 text-slate-900 hover:text-blue-900">Beranda</router-link>
      <router-link to="/organizations" @click="isMobileMenuOpen = false" class="block py-2 text-slate-700 hover:text-blue-900">Direktori Ormawa</router-link>
      <div class="pt-2 border-t border-slate-100">
        <router-link to="/login" @click="isMobileMenuOpen = false" class="block py-2 text-blue-900 font-bold">Login Pengurus Organisasi &rarr;</router-link>
      </div>
    </div>

    <!-- Global Search Modal -->
    <GlobalSearchModal v-model:isOpen="isSearchOpen" />
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import GlobalSearchModal from '@/components/public/GlobalSearchModal.vue'

const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)

const handleKeyDown = (e: KeyboardEvent) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    isSearchOpen.value = !isSearchOpen.value
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>
