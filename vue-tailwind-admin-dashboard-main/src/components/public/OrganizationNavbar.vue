<template>
  <header class="sticky top-0 z-40 bg-white border-b border-[#C2C6D3] h-16 transition-colors duration-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
      <div class="flex items-center justify-between h-full gap-4">
        
        <!-- Left: Org Identity -->
        <router-link
          :to="`/organizations/${organization.subdomain}`"
          class="flex items-center gap-3 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] rounded py-1 max-w-[280px] sm:max-w-none"
        >
          <div
            class="h-9 w-9 rounded border border-[#C2C6D3] bg-[#F8F9FA] overflow-hidden flex items-center justify-center p-0.5 shadow-2xs group-hover:border-[#00346F] transition duration-150 shrink-0"
          >
            <img v-if="organization.logo" :src="resolveImageUrl(organization.logo)" :alt="organization.nama" class="h-full w-full object-contain" />
            <span v-else class="font-bold text-xs text-[#00346F]">{{ organization.nama.charAt(0) }}</span>
          </div>
          <div class="leading-tight min-w-0">
            <span class="text-sm font-bold text-[#191C1D] tracking-tight block truncate">
              {{ organization.nama }}
            </span>
            <span class="text-[10px] text-[#737783] font-medium tracking-wide block truncate">
              {{ organization.jenis }} &bull; ITI
            </span>
          </div>
        </router-link>

        <!-- Center: Dynamic Module Nav Links -->
        <nav class="hidden lg:flex items-center gap-6 text-xs font-semibold text-[#424751]" aria-label="Navigasi Organisasi">
          <router-link
            :to="`/organizations/${organization.subdomain}`"
            exact
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.name === 'public-organization-home' ? 'font-bold border-b-2' : '']"
            :style="route.name === 'public-organization-home' ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            Beranda
          </router-link>

          <router-link
            v-if="hasModule('posts')"
            :to="`/organizations/${organization.subdomain}/articles`"
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.path.includes('/articles') ? 'font-bold border-b-2' : '']"
            :style="route.path.includes('/articles') ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            {{ organization.label_menu?.posts || organization.label_menu?.berita || 'Berita' }}
          </router-link>

          <router-link
            v-if="hasModule('agenda')"
            :to="`/organizations/${organization.subdomain}/agenda`"
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.path.includes('/agenda') ? 'font-bold border-b-2' : '']"
            :style="route.path.includes('/agenda') ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            {{ organization.label_menu?.agenda || organization.label_menu?.kegiatan || 'Agenda' }}
          </router-link>

          <router-link
            v-if="hasModule('announcements')"
            :to="`/organizations/${organization.subdomain}/announcements`"
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.name === 'public-organization-announcements' ? 'font-bold border-b-2' : '']"
            :style="route.name === 'public-organization-announcements' ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            {{ organization.label_menu?.announcements || organization.label_menu?.pengumuman || 'Pengumuman' }}
          </router-link>

          <router-link
            v-if="hasModule('galeri') || hasModule('gallery')"
            :to="`/organizations/${organization.subdomain}/gallery`"
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.name === 'public-organization-gallery' ? 'font-bold border-b-2' : '']"
            :style="route.name === 'public-organization-gallery' ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            {{ organization.label_menu?.galeri || organization.label_menu?.gallery || 'Galeri' }}
          </router-link>

          <router-link
            v-if="hasModule('documents')"
            :to="`/organizations/${organization.subdomain}/documents`"
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.name === 'public-organization-documents' ? 'font-bold border-b-2' : '']"
            :style="route.name === 'public-organization-documents' ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            {{ organization.label_menu?.documents || organization.label_menu?.dokumen || 'Dokumen' }}
          </router-link>

          <router-link
            v-if="hasModule('structure')"
            :to="`/organizations/${organization.subdomain}/structure`"
            class="py-2 hover:text-[#00346F] transition duration-150"
            :class="[route.name === 'public-organization-structure' ? 'font-bold border-b-2' : '']"
            :style="route.name === 'public-organization-structure' ? { color: organization.warna_tema || '#00346F', borderColor: organization.warna_tema || '#00346F' } : {}"
          >
            {{ organization.label_menu?.structure || organization.label_menu?.struktur || 'Struktur' }}
          </router-link>
        </nav>

        <!-- Right: Action, Search & Portal Link -->
        <div class="flex items-center gap-2.5">
          <button
            @click="isSearchOpen = true"
            class="inline-flex items-center gap-2 rounded border border-[#C2C6D3] bg-[#F8F9FA] px-3 py-1.5 text-xs text-[#424751] hover:bg-[#F3F4F5] hover:text-[#191C1D] transition duration-150 cursor-pointer"
            aria-label="Cari warta organisasi"
          >
            <svg class="h-3.5 w-3.5 text-[#737783]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden sm:inline">Cari...</span>
            <kbd class="hidden sm:inline-block rounded bg-white px-1.5 py-0.5 text-[9px] font-mono font-medium text-[#737783] border border-[#C2C6D3]">⌘K</kbd>
          </button>

          <router-link
            v-if="hasModule('documents')"
            :to="`/organizations/${organization.subdomain}/documents`"
            class="hidden md:inline-flex items-center gap-1.5 rounded border border-[#C2C6D3] bg-white px-3.5 py-1.5 text-xs font-semibold text-[#191C1D] hover:bg-[#F8F9FA] hover:border-[#00346F] transition duration-150 shadow-2xs"
          >
            <span>Dokumen</span>
          </router-link>

          <router-link
            to="/organizations"
            class="hidden sm:inline-block text-xs font-semibold text-[#00346F] hover:underline px-1"
          >
            &larr; Direktori
          </router-link>

          <!-- Mobile Hamburger -->
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="lg:hidden text-[#191C1D] p-2 rounded focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#00346F] cursor-pointer"
            aria-label="Menu navigasi organisasi"
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
    <div v-if="isMobileMenuOpen" class="lg:hidden bg-white border-t border-[#C2C6D3] px-5 py-4 space-y-2 text-xs font-semibold shadow-sm">
      <router-link :to="`/organizations/${organization.subdomain}`" @click="isMobileMenuOpen = false" class="block py-2 text-[#191C1D] hover:text-[#00346F]">Beranda</router-link>
      <router-link v-if="hasModule('posts')" :to="`/organizations/${organization.subdomain}/articles`" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">{{ organization.label_menu?.posts || organization.label_menu?.berita || 'Berita' }}</router-link>
      <router-link v-if="hasModule('agenda')" :to="`/organizations/${organization.subdomain}/agenda`" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">{{ organization.label_menu?.agenda || organization.label_menu?.kegiatan || 'Agenda' }}</router-link>
      <router-link v-if="hasModule('announcements')" :to="`/organizations/${organization.subdomain}/announcements`" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">{{ organization.label_menu?.announcements || organization.label_menu?.pengumuman || 'Pengumuman' }}</router-link>
      <router-link v-if="hasModule('galeri') || hasModule('gallery')" :to="`/organizations/${organization.subdomain}/gallery`" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">{{ organization.label_menu?.galeri || organization.label_menu?.gallery || 'Galeri' }}</router-link>
      <router-link v-if="hasModule('documents')" :to="`/organizations/${organization.subdomain}/documents`" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">{{ organization.label_menu?.documents || organization.label_menu?.dokumen || 'Dokumen' }}</router-link>
      <router-link v-if="hasModule('structure')" :to="`/organizations/${organization.subdomain}/structure`" @click="isMobileMenuOpen = false" class="block py-2 text-[#424751] hover:text-[#00346F]">{{ organization.label_menu?.structure || organization.label_menu?.struktur || 'Struktur' }}</router-link>
      <div class="pt-2 border-t border-[#E1E3E4]">
        <router-link to="/organizations" @click="isMobileMenuOpen = false" class="block py-2 text-[#00346F] font-bold">&larr; Kembali ke Direktori Ormawa</router-link>
      </div>
    </div>

    <!-- Global Search Modal -->
    <GlobalSearchModal v-model:isOpen="isSearchOpen" />
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import GlobalSearchModal from '@/components/public/GlobalSearchModal.vue'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { PublicOrganization } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()

const route = useRoute()
const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)

const hasModule = (modName: string) => {
  if (!props.organization.modules) return true
  const mods = props.organization.modules as Record<string, any>
  if (modName === 'announcements' || modName === 'pengumuman') {
    if (mods.announcements === false || mods.pengumuman === false) return false
    return true
  }
  if (modName === 'galeri' || modName === 'gallery') {
    if (mods.galeri === false || mods.gallery === false) return false
    return true
  }
  if (modName === 'posts' || modName === 'articles' || modName === 'berita') {
    if (mods.posts === false || mods.articles === false || mods.berita === false) return false
    return true
  }
  if (modName === 'agenda' || modName === 'kegiatan' || modName === 'activities') {
    if (mods.agenda === false || mods.kegiatan === false || mods.activities === false) return false
    return true
  }
  if (modName === 'documents' || modName === 'dokumen') {
    if (mods.documents === false || mods.dokumen === false) return false
    return true
  }
  if (modName === 'structure' || modName === 'struktur') {
    if (mods.structure === false || mods.struktur === false) return false
    return true
  }
  return mods[modName] !== false
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
