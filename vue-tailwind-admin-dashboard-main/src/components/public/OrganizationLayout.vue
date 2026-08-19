<template>
  <div class="min-h-screen bg-white flex flex-col font-sans">
    <!-- Navbar -->
    <header class="bg-white sticky top-0 z-50 border-b border-gray-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <router-link :to="`/org/${currentTenantSlug}`" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center font-bold text-brand-600">
              {{ currentTenantSlug?.charAt(0).toUpperCase() || 'O' }}
            </div>
            <span class="text-lg font-bold text-gray-900 uppercase">{{ currentTenantSlug }}</span>
          </router-link>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex gap-8">
            <router-link 
              :to="`/org/${currentTenantSlug}`" 
              class="text-sm font-medium pt-5 pb-5 transition-colors border-b-2"
              :class="isActive(`/org/${currentTenantSlug}`, true) ? 'text-gray-900 border-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900 border-transparent'"
            >
              Beranda
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/profil`" 
              class="text-sm font-medium pt-5 pb-5 transition-colors border-b-2"
              :class="isActive(`/org/${currentTenantSlug}/profil`) ? 'text-gray-900 border-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900 border-transparent'"
            >
              Profil
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/struktur`" 
              class="text-sm font-medium pt-5 pb-5 transition-colors border-b-2"
              :class="isActive(`/org/${currentTenantSlug}/struktur`) ? 'text-gray-900 border-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900 border-transparent'"
            >
              Struktur
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/berita`" 
              class="text-sm font-medium pt-5 pb-5 transition-colors border-b-2"
              :class="isActive(`/org/${currentTenantSlug}/berita`) ? 'text-gray-900 border-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900 border-transparent'"
            >
              Berita
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/kegiatan`" 
              class="text-sm font-medium pt-5 pb-5 transition-colors border-b-2"
              :class="isActive(`/org/${currentTenantSlug}/kegiatan`) ? 'text-gray-900 border-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900 border-transparent'"
            >
              Kegiatan
            </router-link>
          </nav>
          
          <div class="flex items-center gap-4">
            <router-link to="/login" class="hidden md:block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
              Login Pengurus
            </router-link>
            <!-- Mobile Menu Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-500 hover:text-gray-900">
              <span class="sr-only">Buka menu utama</span>
              <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
              <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-100 shadow-sm absolute w-full">
          <div class="px-4 pt-2 pb-6 space-y-1">
            <router-link 
              :to="`/org/${currentTenantSlug}`" 
              class="block px-3 py-2 rounded-md text-base font-medium"
              :class="isActive(`/org/${currentTenantSlug}`, true) ? 'text-brand-600 bg-brand-50' : 'text-gray-700 hover:text-brand-600 hover:bg-gray-50'"
              @click="mobileMenuOpen = false"
            >
              Beranda
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/profil`" 
              class="block px-3 py-2 rounded-md text-base font-medium"
              :class="isActive(`/org/${currentTenantSlug}/profil`) ? 'text-brand-600 bg-brand-50' : 'text-gray-700 hover:text-brand-600 hover:bg-gray-50'"
              @click="mobileMenuOpen = false"
            >
              Profil
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/struktur`" 
              class="block px-3 py-2 rounded-md text-base font-medium"
              :class="isActive(`/org/${currentTenantSlug}/struktur`) ? 'text-brand-600 bg-brand-50' : 'text-gray-700 hover:text-brand-600 hover:bg-gray-50'"
              @click="mobileMenuOpen = false"
            >
              Struktur
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/berita`" 
              class="block px-3 py-2 rounded-md text-base font-medium"
              :class="isActive(`/org/${currentTenantSlug}/berita`) ? 'text-brand-600 bg-brand-50' : 'text-gray-700 hover:text-brand-600 hover:bg-gray-50'"
              @click="mobileMenuOpen = false"
            >
              Berita
            </router-link>
            <router-link 
              :to="`/org/${currentTenantSlug}/kegiatan`" 
              class="block px-3 py-2 rounded-md text-base font-medium"
              :class="isActive(`/org/${currentTenantSlug}/kegiatan`) ? 'text-brand-600 bg-brand-50' : 'text-gray-700 hover:text-brand-600 hover:bg-gray-50'"
              @click="mobileMenuOpen = false"
            >
              Kegiatan
            </router-link>
            <div class="border-t border-gray-100 mt-4 pt-4">
              <router-link 
                to="/login" 
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50"
                @click="mobileMenuOpen = false"
              >
                Login Pengurus
              </router-link>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow flex flex-col">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
          <h3 class="text-lg font-bold uppercase tracking-wider">{{ currentTenantSlug }}</h3>
          <p class="text-sm text-gray-400 mt-2">&copy; {{ new Date().getFullYear() }} All rights reserved.</p>
        </div>
        <div class="flex gap-6">
          <router-link to="/" class="text-sm text-gray-400 hover:text-white transition-colors">Kembali ke Portal Utama</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { usePublicStore } from '@/stores/public'

const route = useRoute()
const publicStore = usePublicStore()
const { currentTenantSlug } = storeToRefs(publicStore)
const mobileMenuOpen = ref(false)

const isActive = (path: string, exact = false) => {
  if (exact) {
    return route.path === path
  }
  return route.path.startsWith(path)
}

onMounted(async () => {
  const slug = route.params.slug as string
  if (slug && slug !== currentTenantSlug.value) {
    await publicStore.setTenant(slug)
  }
})

// React to route changes if navigating between different organizations
watch(() => route.params.slug, async (newSlug) => {
  if (newSlug && newSlug !== currentTenantSlug.value && typeof newSlug === 'string') {
    await publicStore.setTenant(newSlug)
  }
})
</script>
