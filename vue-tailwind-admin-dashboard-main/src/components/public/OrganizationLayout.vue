<template>
  <div
    class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white"
    :style="{ '--org-primary': org?.warna_tema || '#00346F' }"
  >
    <!-- Loading State Skeleton -->
    <div v-if="isLoading" class="flex-grow flex flex-col items-center justify-center py-28 space-y-3">
      <div class="w-8 h-8 border-2 border-slate-200 border-t-[#00346F] rounded-full animate-spin"></div>
      <p class="text-xs text-slate-500 font-medium">Memuat profil organisasi resmi...</p>
    </div>

    <!-- Error / Inactive State (404) -->
    <div v-else-if="error || !org" class="flex-grow flex flex-col items-center justify-center py-28 px-4 text-center">
      <div class="w-14 h-14 rounded-2xl bg-white border border-[#C2C6D3] text-[#00346F] flex items-center justify-center mb-4 text-xl font-extrabold shadow-2xs">
        404
      </div>
      <h1 class="text-2xl font-extrabold text-[#191C1D] mb-2 tracking-tight">Organisasi Tidak Ditemukan</h1>
      <p class="text-xs sm:text-sm text-[#424751] max-w-md mb-6 leading-relaxed">
        Organisasi yang Anda tuju belum terdaftar, berstatus nonaktif, atau alamat subdomain yang dimasukkan tidak valid.
      </p>
      <router-link
        to="/organizations"
        class="rounded-lg bg-[#00346F] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#002855] shadow-xs transition"
      >
        &larr; Kembali ke Direktori Ormawa
      </router-link>
    </div>

    <!-- Public Website Disabled by Admin -->
    <div v-else-if="org.modules?.public_website_enabled === false" class="flex-grow flex flex-col items-center justify-center py-28 px-4 text-center">
      <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mb-4 text-xl font-extrabold shadow-2xs">
        🔒
      </div>
      <h1 class="text-2xl font-extrabold text-[#191C1D] mb-2 tracking-tight">Portal Publik Sedang Dinonaktifkan</h1>
      <p class="text-xs sm:text-sm text-[#424751] max-w-md mb-6 leading-relaxed">
        Portal website publik resmi <strong class="text-[#191C1D]">{{ org.nama }}</strong> sedang dinonaktifkan sementara oleh pihak pengurus.
      </p>
      <router-link
        to="/organizations"
        class="rounded-lg bg-[#00346F] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#002855] shadow-xs transition"
      >
        &larr; Kembali ke Direktori Ormawa
      </router-link>
    </div>

    <!-- Active Organization Content -->
    <template v-else>
      <OrganizationNavbar :organization="org" />
      <main id="main-content" class="flex-grow">
        <router-view :organization="org" />
      </main>
      <PublicFooter />
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import OrganizationNavbar from '@/components/public/OrganizationNavbar.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
import type { PublicOrganization } from '@/types/public'

const route = useRoute()
const org = ref<PublicOrganization | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

const loadOrg = async () => {
  const slug = (route.params.slug as string) || ''
  if (!slug) {
    isLoading.value = false
    error.value = 'Organisasi tidak ditentukan.'
    return
  }
  isLoading.value = true
  error.value = null
  try {
    const data = await publicService.getOrganizationBySlug(slug)
    org.value = data
  } catch (err: any) {
    error.value = err.message || 'Gagal memuat profil organisasi.'
  } finally {
    isLoading.value = false
  }
}

watch(() => route.params.slug, () => {
  loadOrg()
})

onMounted(() => {
  loadOrg()
})
</script>
