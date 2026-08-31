<template>
  <header class="fixed top-0 left-0 w-full z-50 bg-white border-b border-[#C2C6D3] h-16 transition-colors duration-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
      <div class="flex items-center justify-between h-full gap-4">
        
        <!-- Left: Brand Identity -->
        <router-link
          to="/"
          class="flex items-center gap-3 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded py-1"
        >
          <div class="h-9 w-9 rounded border border-[#C2C6D3] bg-white overflow-hidden flex items-center justify-center p-0.5 shadow-2xs group-hover:border-[#00346F] transition duration-150 shrink-0">
            <img src="/images/logo/iti-logo.png" alt="Institut Teknologi Indonesia" class="h-full w-full object-contain" />
          </div>
          <div class="leading-tight">
            <span class="text-sm sm:text-base font-bold text-[#191C1D] tracking-tight block">
              ORMAWA ITI
            </span>
            <span class="text-[10px] text-[#737783] font-medium tracking-wide block">
              Institut Teknologi Indonesia
            </span>
          </div>
        </router-link>

        <!-- Center Nav Links -->
        <nav class="hidden md:flex gap-7 items-center text-xs font-semibold text-[#424751]" aria-label="Navigasi Utama">
          <router-link
            to="/"
            class="hover:text-[#00346F] transition duration-150 py-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded"
            active-class="text-[#00346F] font-bold border-b-2 border-[#00346F]"
          >
            Beranda
          </router-link>
          <router-link
            to="/organizations"
            class="hover:text-[#00346F] transition duration-150 py-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded"
            active-class="text-[#00346F] font-bold border-b-2 border-[#00346F]"
          >
            Direktori Ormawa
          </router-link>
          <router-link
            to="/berita"
            class="hover:text-[#00346F] transition duration-150 py-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded"
            active-class="text-[#00346F] font-bold border-b-2 border-[#00346F]"
          >
            Berita
          </router-link>
          <router-link
            to="/agenda"
            class="hover:text-[#00346F] transition duration-150 py-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded"
            active-class="text-[#00346F] font-bold border-b-2 border-[#00346F]"
          >
            Agenda
          </router-link>
          <router-link
            to="/pengumuman"
            class="hover:text-[#00346F] transition duration-150 py-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded"
            active-class="text-[#00346F] font-bold border-b-2 border-[#00346F]"
          >
            Pengumuman
          </router-link>
        </nav>

        <!-- Right Side: Search & Login -->
        <div class="flex items-center gap-2.5 sm:gap-3">
          <button
            @click="isSearchOpen = true"
            class="inline-flex items-center gap-2 rounded border border-[#C2C6D3] bg-[#F8F9FA] px-2.5 sm:px-3 py-1.5 text-xs text-[#424751] hover:bg-[#F3F4F5] hover:text-[#191C1D] transition duration-150 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] cursor-pointer"
            aria-label="Cari organisasi atau warta"
          >
            <svg class="h-4 w-4 text-[#737783]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden sm:inline">Cari...</span>
            <kbd class="hidden sm:inline-block rounded bg-white px-1.5 py-0.5 text-[9px] font-mono font-medium text-[#737783] border border-[#C2C6D3]">⌘K</kbd>
          </button>

          <div class="hidden md:block w-px h-4 bg-[#C2C6D3]"></div>

          <!-- Desktop Login Button -->
          <router-link
            to="/login"
            class="hidden md:inline-flex items-center gap-1.5 rounded bg-[#00346F] hover:bg-[#004A99] px-3.5 py-2 text-xs font-semibold text-white transition duration-150 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] shadow-2xs"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Login Pengurus</span>
          </router-link>

          <!-- Mobile Hamburger -->
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="md:hidden text-[#191C1D] p-2 rounded hover:bg-[#F3F4F5] focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] cursor-pointer transition"
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
    <div v-if="isMobileMenuOpen" class="md:hidden bg-white border-t border-[#C2C6D3] px-5 py-4 space-y-2.5 text-xs font-semibold shadow-md">
      <router-link to="/" @click="isMobileMenuOpen = false" class="block py-2 text-[#191C1D] hover:text-[#00346F]">Beranda</router-link>
      <router-link to="/organizations" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">Direktori Ormawa</router-link>
      <router-link to="/berita" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">Berita</router-link>
      <router-link to="/agenda" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">Agenda</router-link>
      <router-link to="/pengumuman" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">Pengumuman</router-link>
      
      <div class="pt-3 border-t border-[#E1E3E4]">
        <router-link
          to="/login"
          @click="isMobileMenuOpen = false"
          class="w-full flex items-center justify-center gap-2 rounded bg-[#00346F] hover:bg-[#004A99] py-2.5 px-4 text-xs font-bold text-white transition shadow-2xs"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
          <span>Login Pengurus Organisasi</span>
        </router-link>
      </div>
    </div>

    <!-- Global Search Modal -->
    <GlobalSearchModal v-model:isOpen="isSearchOpen" />
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import GlobalSearchModal from '@/components/public/GlobalSearchModal.vue'

const route = useRoute()
const router = useRouter()

const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)

const scrollToSection = (hash: string) => {
  isMobileMenuOpen.value = false
  if (route.path === '/' || route.name === 'public-home') {
    const el = document.querySelector(hash)
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' })
      return
    }
  }
  router.push({ path: '/', hash })
}

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
