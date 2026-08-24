<template>
  <div class="min-h-screen bg-white flex flex-col font-sans selection:bg-slate-900 selection:text-white">
    
    <!-- Loading State Skeleton -->
    <div v-if="isLoading" class="flex-grow flex flex-col items-center justify-center py-28 space-y-3">
      <div class="w-7 h-7 border-2 border-slate-200 border-t-slate-900 rounded-full animate-spin"></div>
      <p class="text-xs text-slate-500">Memuat profil organisasi...</p>
    </div>

    <!-- Error / Inactive State -->
    <div v-else-if="error || !org" class="flex-grow flex flex-col items-center justify-center py-28 px-4 text-center">
      <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center mb-3 text-lg font-bold">
        404
      </div>
      <h1 class="text-xl font-bold text-slate-950 mb-1.5">Organisasi Tidak Ditemukan</h1>
      <p class="text-xs text-slate-500 max-w-sm mb-5 leading-relaxed">
        Organisasi yang Anda tuju belum terdaftar, berstatus nonaktif, atau alamat yang dimasukkan salah.
      </p>
      <router-link
        to="/organizations"
        class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition"
      >
        Kembali ke Direktori Ormawa
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
