<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#424751]" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-[#00346F]">Beranda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-[#00346F]">{{ orgName }}</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <span class="text-[#00346F] font-bold">Galeri</span>
    </nav>

    <!-- Header Section -->
    <div class="border-b border-[#C2C6D3] pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">GALERI</span>
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#191C1D] mt-0.5">Dokumentasi Kegiatan</h1>
      <p class="text-xs sm:text-sm text-[#424751] mt-1 max-w-2xl">
        Dokumentasi visual kegiatan dan aktivitas resmi {{ orgName }}.
      </p>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden animate-pulse">
        <div class="h-56 bg-[#F0F2F5]"></div>
        <div class="p-4 space-y-2">
          <div class="h-4 bg-[#E1E3E4] rounded w-3/4"></div>
          <div class="h-3 bg-[#E1E3E4] rounded w-1/2"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="py-16 text-center text-xs text-rose-600 border border-dashed border-rose-300 rounded-xl bg-rose-50/50 p-6">
      <p class="font-bold mb-2">{{ errorMessage }}</p>
      <button @click="loadGallery" class="px-4 py-1.5 bg-[#00346F] text-white rounded text-xs font-semibold hover:bg-[#002855]">
        Coba Lagi
      </button>
    </div>

    <!-- Empty State -->
    <div v-else-if="gallery.length === 0" class="py-16 text-center border border-dashed border-[#C2C6D3] rounded-xl bg-white p-8">
      <div class="h-12 w-12 rounded-full bg-[#F3F4F5] flex items-center justify-center mx-auto text-[#737783] mb-3 text-lg font-bold">
        📷
      </div>
      <h3 class="text-sm font-bold text-[#191C1D]">Belum ada dokumentasi publik.</h3>
      <p class="text-xs text-[#737783] mt-1">Organisasi ini belum menerbitkan foto kegiatan.</p>
    </div>

    <!-- Gallery Grid (3 cols desktop, 2 cols tablet, 1-2 cols mobile) -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="(item, index) in gallery"
        :key="item.id"
        class="rounded-xl border border-[#C2C6D3] bg-white overflow-hidden group hover:border-[#00346F] transition duration-200 shadow-2xs cursor-pointer flex flex-col"
        @click="openLightbox(index)"
      >
        <div class="relative h-56 bg-[#F3F4F5] overflow-hidden">
          <img
            :src="resolveImageUrl(item.image_url || item.url)"
            :alt="item.alt_text || item.title || item.judul || 'Dokumentasi'"
            class="h-full w-full object-cover group-hover:scale-105 transition duration-300"
            loading="lazy"
          />
          <div class="absolute top-3 left-3">
            <span class="rounded bg-black/60 backdrop-blur-xs px-2 py-0.5 text-[10px] font-semibold text-white uppercase tracking-wider">
              {{ item.category || item.kategori || 'Dokumentasi' }}
            </span>
          </div>
          <div v-if="item.taken_at || item.tanggal" class="absolute bottom-3 right-3">
            <span class="rounded bg-black/60 backdrop-blur-xs px-2 py-0.5 text-[10px] font-mono text-white">
              {{ item.taken_at || item.tanggal }}
            </span>
          </div>
        </div>

        <div class="p-4 space-y-1.5 flex-1 flex flex-col justify-between">
          <div>
            <h2 class="text-sm font-bold text-[#191C1D] group-hover:text-[#00346F] transition line-clamp-1">
              {{ item.title || item.judul || item.name || item.filename }}
            </h2>
            <p v-if="item.caption || item.deskripsi" class="text-xs text-[#424751] line-clamp-2 mt-1 leading-relaxed">
              {{ item.caption || item.deskripsi }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Interactive Lightbox Modal -->
    <div
      v-if="currentIndex !== null && activePhoto"
      class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
      @click.self="closeLightbox"
    >
      <!-- Close Button -->
      <button
        @click="closeLightbox"
        class="absolute top-4 right-4 text-white/70 hover:text-white text-2xl font-bold p-2 z-10 transition"
        aria-label="Tutup Galeri"
      >
        &times;
      </button>

      <!-- Previous Arrow -->
      <button
        v-if="gallery.length > 1"
        @click="prevPhoto"
        class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white bg-black/40 hover:bg-black/80 rounded-full h-11 w-11 flex items-center justify-center text-xl font-bold transition z-10"
        aria-label="Foto Sebelumnya"
      >
        &lsaquo;
      </button>

      <!-- Next Arrow -->
      <button
        v-if="gallery.length > 1"
        @click="nextPhoto"
        class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white bg-black/40 hover:bg-black/80 rounded-full h-11 w-11 flex items-center justify-center text-xl font-bold transition z-10"
        aria-label="Foto Selanjutnya"
      >
        &rsaquo;
      </button>

      <!-- Lightbox Content Card -->
      <div class="max-w-4xl w-full bg-[#191C1D] text-white rounded-2xl overflow-hidden shadow-2xl border border-white/10 flex flex-col max-h-[90vh]">
        <!-- Full Image Area -->
        <div class="flex-1 bg-black/60 flex items-center justify-center p-2 min-h-[300px] max-h-[65vh] overflow-hidden">
          <img
            :src="resolveImageUrl(activePhoto.image_url || activePhoto.url)"
            :alt="activePhoto.alt_text || activePhoto.title || activePhoto.judul"
            class="max-h-[62vh] w-auto max-w-full object-contain rounded"
          />
        </div>

        <!-- Info Footer Bar -->
        <div class="p-4 sm:p-5 bg-[#1F2328] border-t border-white/10 space-y-2">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#90CAF9]">
                {{ activePhoto.category || activePhoto.kategori || 'Dokumentasi' }}
              </span>
              <h3 class="text-base font-bold text-white mt-0.5">
                {{ activePhoto.title || activePhoto.judul || activePhoto.name || activePhoto.filename }}
              </h3>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-400 font-mono">
              <span v-if="activePhoto.taken_at || activePhoto.tanggal">{{ activePhoto.taken_at || activePhoto.tanggal }}</span>
              <span>•</span>
              <span>Foto {{ currentIndex + 1 }} dari {{ gallery.length }}</span>
            </div>
          </div>
          <p v-if="activePhoto.caption || activePhoto.deskripsi" class="text-xs text-gray-300 leading-relaxed">
            {{ activePhoto.caption || activePhoto.deskripsi }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { PublicOrganization, PublicMedia } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const gallery = ref<PublicMedia[]>([])
const currentIndex = ref<number | null>(null)
const isLoading = ref(true)
const errorMessage = ref('')

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Galeri Foto — ${orgName.value} | ORMAWA ITI`,
  description: `Dokumentasi foto kegiatan dan aktivitas resmi dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const activePhoto = computed(() => {
  if (currentIndex.value === null || !gallery.value[currentIndex.value]) return null
  return gallery.value[currentIndex.value]
})

const loadGallery = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await publicService.getGallery(subdomain.value)
    gallery.value = res.data || []
  } catch (err: any) {
    errorMessage.value = err.message || 'Gagal memuat galeri foto.'
  } finally {
    isLoading.value = false
  }
}

const openLightbox = (index: number) => {
  currentIndex.value = index
}

const closeLightbox = () => {
  currentIndex.value = null
}

const nextPhoto = () => {
  if (currentIndex.value === null || gallery.value.length === 0) return
  currentIndex.value = (currentIndex.value + 1) % gallery.value.length
}

const prevPhoto = () => {
  if (currentIndex.value === null || gallery.value.length === 0) return
  currentIndex.value = (currentIndex.value - 1 + gallery.value.length) % gallery.value.length
}

const handleKeyDown = (e: KeyboardEvent) => {
  if (currentIndex.value === null) return
  if (e.key === 'Escape') closeLightbox()
  if (e.key === 'ArrowRight') nextPhoto()
  if (e.key === 'ArrowLeft') prevPhoto()
}

watch(() => subdomain.value, (newVal) => {
  if (newVal) loadGallery()
})

onMounted(() => {
  loadGallery()
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>
