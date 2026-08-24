<template>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-slate-900">Home</router-link>
      <span>/</span>
      <router-link to="/organizations" class="hover:text-slate-900">Direktori Ormawa</router-link>
      <span>/</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-slate-900">{{ orgName }}</router-link>
      <span>/</span>
      <span class="text-slate-900 font-semibold">Galeri</span>
    </nav>

    <div class="border-b border-slate-200 pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Dokumentasi Visual</span>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 mt-0.5">Galeri Foto Kegiatan</h1>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="py-16 text-center text-xs text-slate-500">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="aspect-4/3 rounded-lg bg-slate-100 animate-pulse"></div>
      </div>
      <p class="mt-4">Memuat galeri foto...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="py-14 text-center text-xs text-rose-600 border border-dashed border-rose-200 rounded-xl bg-rose-50/50">
      {{ errorMessage }}
    </div>
    
    <!-- Empty State -->
    <div v-else-if="mediaItems.length === 0" class="py-14 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
      Belum ada foto yang dipublikasikan.
    </div>

    <!-- Gallery Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <button
        v-for="(item, idx) in mediaItems"
        :key="item.id"
        @click="openLightbox(idx)"
        class="group relative aspect-4/3 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-700 block text-left"
        :aria-label="`Pratinjau foto ${item.filename}`"
      >
        <img
          :src="item.url"
          :alt="item.filename"
          loading="lazy"
          class="h-full w-full object-cover group-hover:scale-102 transition duration-200"
        />
        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition p-2 flex items-end">
          <span class="text-[10px] font-semibold text-white truncate">{{ item.filename }}</span>
        </div>
      </button>
    </div>

    <!-- Lightbox Modal -->
    <Teleport to="body">
      <div
        v-if="activePhotoIndex !== null"
        @keydown.escape="closeLightbox"
        @keydown.left="prevPhoto"
        @keydown.right="nextPhoto"
        class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-xs flex items-center justify-center p-4 cursor-pointer"
        @click="closeLightbox"
        role="dialog"
        aria-modal="true"
        aria-label="Pratinjau Foto"
      >
        <div class="relative max-w-4xl max-h-full flex flex-col items-center cursor-default" @click.stop>
          <div class="w-full flex justify-between items-center pb-2 text-white text-xs">
            <span class="font-medium">{{ mediaItems[activePhotoIndex].filename }} ({{ activePhotoIndex + 1 }} / {{ mediaItems.length }})</span>
            <button
              @click="closeLightbox"
              class="rounded bg-white/20 hover:bg-white/30 px-2.5 py-1 text-xs font-semibold"
              aria-label="Tutup"
            >
              TUTUP (ESC)
            </button>
          </div>

          <div class="relative flex items-center justify-center">
            <!-- Prev Button -->
            <button
              v-if="activePhotoIndex > 0"
              @click="prevPhoto"
              class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white p-2"
              aria-label="Foto sebelumnya"
            >
              &larr;
            </button>

            <img
              :src="mediaItems[activePhotoIndex].url"
              :alt="mediaItems[activePhotoIndex].filename"
              class="max-h-[75vh] w-auto rounded-lg object-contain"
            />

            <!-- Next Button -->
            <button
              v-if="activePhotoIndex < mediaItems.length - 1"
              @click="nextPhoto"
              class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white p-2"
              aria-label="Foto selanjutnya"
            >
              &rarr;
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicMedia } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const mediaItems = ref<PublicMedia[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const activePhotoIndex = ref<number | null>(null)

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Galeri — ${orgName.value} | CMS ORMAWA ITI`,
  description: `Dokumentasi foto kegiatan dan agenda resmi ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const openLightbox = (index: number) => {
  activePhotoIndex.value = index
}

const closeLightbox = () => {
  activePhotoIndex.value = null
}

const nextPhoto = () => {
  if (activePhotoIndex.value !== null && activePhotoIndex.value < mediaItems.value.length - 1) {
    activePhotoIndex.value++
  }
}

const prevPhoto = () => {
  if (activePhotoIndex.value !== null && activePhotoIndex.value > 0) {
    activePhotoIndex.value--
  }
}

const loadGallery = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await publicService.getGallery(subdomain.value, { per_page: 40 })
    mediaItems.value = res.data || []
  } catch (err: any) {
    console.error('Failed to load gallery:', err)
    errorMessage.value = (err.status === 404 || err.response?.status === 404)
      ? 'Modul galeri dinonaktifkan oleh organisasi.'
      : 'Gagal memuat galeri foto.'
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newSlug) => {
  if (newSlug) loadGallery()
})

onMounted(() => {
  loadGallery()
})
</script>
