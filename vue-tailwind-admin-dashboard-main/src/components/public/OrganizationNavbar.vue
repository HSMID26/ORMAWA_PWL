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
      <div class="flex justify-between items-center h-16">
        
        <!-- Organization Brand -->
        <router-link
          :to="`/organizations/${organization.subdomain}`"
          class="flex items-center gap-3 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 rounded-md"
        >
          <div class="h-9 w-9 shrink-0 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
            <img v-if="organization.logo" :src="organization.logo" :alt="organization.nama" class="h-full w-full object-cover" />
            <span v-else class="font-bold text-xs text-slate-900">
              {{ organization.nama.substring(0, 2).toUpperCase() }}
            </span>
          </div>
          <div>
            <span class="text-sm font-bold text-slate-950 group-hover:text-blue-900 transition block leading-tight">
              {{ organization.nama }}
            </span>
            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">
              {{ organization.jenis }} &bull; ITI
            </span>
          </div>
        </router-link>

        <!-- Desktop Navigation Tabs with Underline Accent -->
        <nav class="hidden lg:flex gap-5 items-center text-xs font-medium text-slate-600" aria-label="Navigasi Organisasi">
          <router-link
            :to="`/organizations/${organization.subdomain}`"
            exact
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: $route.name === 'public-organization-home' ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Beranda
          </router-link>

          <router-link
            :to="`/organizations/${organization.subdomain}/profil`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: $route.name === 'public-organization-profile' ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Profil
          </router-link>

          <router-link
            v-if="modules.posts !== false && modules.berita !== false"
            :to="`/organizations/${organization.subdomain}/articles`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: ($route.name === 'public-organization-articles-list' || $route.name === 'public-organization-article-detail') ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Berita
          </router-link>

          <router-link
            v-if="modules.agenda !== false && modules.kegiatan !== false"
            :to="`/organizations/${organization.subdomain}/agenda`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: ($route.name === 'public-organization-agenda-list' || $route.name === 'public-organization-agenda-detail') ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Agenda
          </router-link>

          <router-link
            v-if="modules.announcements !== false && modules.pengumuman !== false"
            :to="`/organizations/${organization.subdomain}/announcements`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: $route.name === 'public-organization-announcements' ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Pengumuman
          </router-link>

          <router-link
            v-if="modules.galeri !== false && modules.gallery !== false"
            :to="`/organizations/${organization.subdomain}/gallery`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: $route.name === 'public-organization-gallery' ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Galeri
          </router-link>

          <router-link
            v-if="modules.documents !== false && modules.dokumen !== false"
            :to="`/organizations/${organization.subdomain}/documents`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: $route.name === 'public-organization-documents' ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Dokumen
          </router-link>

          <router-link
            v-if="modules.structure !== false && modules.struktur !== false"
            :to="`/organizations/${organization.subdomain}/structure`"
            class="hover:text-slate-950 py-1 transition border-b-2 border-transparent"
            active-class="!border-current !text-slate-950 font-bold"
            :style="{ color: $route.name === 'public-organization-structure' ? (organization.warna_tema || '#0f172a') : undefined }"
          >
            Struktur
          </router-link>
          
          <div class="w-px h-3.5 bg-slate-200 mx-1"></div>
          <router-link to="/organizations" class="text-slate-400 hover:text-slate-600 transition text-[11px]">
            &larr; Direktori ITI
          </router-link>
        </nav>

        <!-- Mobile Menu Button -->
        <div class="lg:hidden flex items-center">
          <button
            @click="isOpen = !isOpen"
            class="text-slate-700 hover:text-slate-950 p-1.5 rounded-lg"
            aria-label="Menu ormawa"
            :aria-expanded="isOpen"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

      </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div v-if="isOpen" class="lg:hidden bg-white border-t border-slate-200 px-4 py-3 space-y-1.5 text-xs font-semibold shadow-sm">
      <router-link :to="`/organizations/${organization.subdomain}`" @click="isOpen = false" class="block py-1.5 text-slate-900">Beranda</router-link>
      <router-link :to="`/organizations/${organization.subdomain}/profil`" @click="isOpen = false" class="block py-1.5 text-slate-700">Profil</router-link>
      <router-link v-if="modules.posts !== false && modules.berita !== false" :to="`/organizations/${organization.subdomain}/articles`" @click="isOpen = false" class="block py-1.5 text-slate-700">Berita</router-link>
      <router-link v-if="modules.agenda !== false && modules.kegiatan !== false" :to="`/organizations/${organization.subdomain}/agenda`" @click="isOpen = false" class="block py-1.5 text-slate-700">Agenda</router-link>
      <router-link v-if="modules.announcements !== false && modules.pengumuman !== false" :to="`/organizations/${organization.subdomain}/announcements`" @click="isOpen = false" class="block py-1.5 text-slate-700">Pengumuman</router-link>
      <router-link v-if="modules.galeri !== false && modules.gallery !== false" :to="`/organizations/${organization.subdomain}/gallery`" @click="isOpen = false" class="block py-1.5 text-slate-700">Galeri</router-link>
      <router-link v-if="modules.documents !== false && modules.dokumen !== false" :to="`/organizations/${organization.subdomain}/documents`" @click="isOpen = false" class="block py-1.5 text-slate-700">Dokumen</router-link>
      <router-link v-if="modules.structure !== false && modules.struktur !== false" :to="`/organizations/${organization.subdomain}/structure`" @click="isOpen = false" class="block py-1.5 text-slate-700">Struktur</router-link>
      <div class="pt-2 border-t border-slate-100">
        <router-link to="/organizations" @click="isOpen = false" class="block py-1 text-slate-400 text-[11px]">&larr; Direktori ITI</router-link>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { PublicOrganization } from '@/types/public'

const props = defineProps<{
  organization: PublicOrganization
}>()

const isOpen = ref(false)

const modules = computed(() => {
  return props.organization?.modul_aktif || {}
})
</script>
