<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-slate-900">Home</router-link>
      <span>/</span>
      <router-link to="/organizations" class="hover:text-slate-900">Direktori Ormawa</router-link>
      <span>/</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-slate-900">{{ orgName }}</router-link>
      <span>/</span>
      <span class="text-slate-900 font-semibold">Dokumen</span>
    </nav>

    <div class="border-b border-slate-200 pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Arsip Publik</span>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 mt-0.5">Dokumen Resmi</h1>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="py-16 text-center text-xs text-slate-500">
      <div class="space-y-3 max-w-xl mx-auto">
        <div v-for="i in 3" :key="i" class="h-16 rounded-xl bg-slate-100 animate-pulse"></div>
      </div>
      <p class="mt-4">Memuat berkas dokumen...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="py-14 text-center text-xs text-rose-600 border border-dashed border-rose-200 rounded-xl bg-rose-50/50">
      {{ errorMessage }}
    </div>
    
    <!-- Empty State -->
    <div v-else-if="documents.length === 0" class="py-14 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
      Belum ada dokumen publik yang dibagikan.
    </div>

    <!-- Documents Archive Rows -->
    <div v-else class="divide-y divide-slate-200 border border-slate-200 rounded-xl bg-white overflow-hidden">
      <div
        v-for="doc in documents"
        :key="doc.id"
        class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-slate-50/70 transition"
      >
        <div class="flex items-center gap-3.5">
          <div class="h-9 w-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0 border border-slate-200 uppercase">
            {{ doc.filename.split('.').pop() || 'PDF' }}
          </div>
          <div>
            <h2 class="text-xs sm:text-sm font-semibold text-slate-950">{{ doc.filename }}</h2>
            <span class="text-[10px] text-slate-500">{{ doc.formatted_size }} &bull; Diperbarui {{ new Date(doc.created_at || '').toLocaleDateString('id-ID') }}</span>
          </div>
        </div>

        <a
          :href="`/api/public/organizations/${subdomain}/documents/${doc.id}/download`"
          target="_blank"
          download
          class="rounded-lg bg-slate-900 hover:bg-blue-900 text-white px-3.5 py-2 text-xs font-semibold text-center transition shrink-0 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900"
          :aria-label="`Unduh berkas ${doc.filename} (${doc.formatted_size})`"
        >
          Unduh ({{ doc.formatted_size }}) &darr;
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicDocument } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const documents = ref<PublicDocument[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Dokumen Publik — ${orgName.value} | CMS ORMAWA ITI`,
  description: `Unduh berkas, SK, pedoman, dan arsip dokumen resmi dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const loadDocs = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await publicService.getDocuments(subdomain.value, { per_page: 30 })
    documents.value = res.data || []
  } catch (err: any) {
    console.error('Failed to load documents:', err)
    errorMessage.value = (err.status === 404 || err.response?.status === 404)
      ? 'Modul dokumen dinonaktifkan oleh organisasi.'
      : 'Gagal memuat berkas dokumen.'
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newSlug) => {
  if (newSlug) loadDocs()
})

onMounted(() => {
  loadDocs()
})
</script>
