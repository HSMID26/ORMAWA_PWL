<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#424751]" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-[#00346F]">Beranda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-[#00346F]">{{ orgName }}</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <span class="text-[#00346F] font-bold">Dokumen</span>
    </nav>

    <!-- Header -->
    <div class="border-b border-[#C2C6D3] pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Arsip Publik</span>
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#191C1D] mt-0.5">Dokumen & Arsip Resmi</h1>
      <p class="text-xs sm:text-sm text-[#424751] mt-1">
        Daftar Surat Keputusan, Proposal Kegiatan, LPJ, SOP, dan Formulir resmi dari {{ orgName }}.
      </p>
    </div>

    <!-- Error State -->
    <div v-if="hasError" class="p-6 rounded border border-rose-200 bg-rose-50 text-center space-y-3">
      <p class="text-xs sm:text-sm font-semibold text-rose-800">Gagal memuat dokumen.</p>
      <button
        @click="loadDocuments"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded bg-rose-600 text-white hover:bg-rose-700 transition"
      >
        Coba Lagi
      </button>
    </div>

    <!-- Loading Skeleton -->
    <div v-else-if="isLoading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-20 rounded bg-white border border-[#C2C6D3] animate-pulse"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="documents.length === 0" class="py-16 text-center text-xs text-[#737783] border border-dashed border-[#C2C6D3] rounded bg-white space-y-1">
      <div class="font-bold text-sm text-[#191C1D]">Belum ada dokumen publik.</div>
      <p>Organisasi ini belum menerbitkan dokumen untuk publik.</p>
    </div>

    <!-- Archive List -->
    <div v-else class="rounded border border-[#C2C6D3] bg-white divide-y divide-[#E1E3E4] shadow-2xs">
      <div
        v-for="doc in documents"
        :key="doc.id"
        class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-[#F8F9FA] transition duration-150"
      >
        <div class="flex items-start gap-3.5 min-w-0">
          <div class="h-10 w-10 rounded bg-[#F3F4F5] border border-[#C2C6D3] flex items-center justify-center text-[#00346F] shrink-0 mt-0.5">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <div class="space-y-1 min-w-0">
            <div class="flex items-center gap-2">
              <h2 class="text-xs sm:text-sm font-bold text-[#191C1D] truncate">{{ doc.name || doc.judul || doc.filename }}</h2>
              <span
                v-if="doc.category || doc.kategori"
                class="shrink-0 px-2 py-0.5 text-[10px] font-bold rounded bg-[#F3F4F5] text-[#424751] border border-[#C2C6D3]"
              >
                {{ doc.category || doc.kategori }}
              </span>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-[11px] text-[#737783]">
              <span class="font-mono">{{ doc.filename }}</span>
              <span>&bull;</span>
              <span>{{ doc.formatted_size || (doc.size ? `${(doc.size / 1024).toFixed(1)} KB` : 'Dokumen') }}</span>
              <span>&bull;</span>
              <span>{{ formatDate(doc.created_at) }}</span>
            </div>
          </div>
        </div>

        <a
          :href="doc.download_url || doc.file_url || doc.url"
          target="_blank"
          download
          class="inline-flex items-center justify-center gap-1.5 rounded border border-[#00346F] bg-white hover:bg-[#00346F] hover:text-white px-4 py-2 text-xs font-semibold text-[#00346F] transition shrink-0 self-end sm:self-center"
        >
          <span>Unduh</span>
          <span v-if="doc.formatted_size" class="text-[10px] opacity-80">&bull; {{ doc.formatted_size }}</span>
          <span>&darr;</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicDocument } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const documents = ref<PublicDocument[]>([])
const isLoading = ref(true)
const hasError = ref(false)

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Dokumen — ${orgName.value} | ORMAWA ITI`,
  description: `Dokumen dan arsip publik resmi dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const loadDocuments = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  hasError.value = false
  try {
    const res = await publicService.getDocuments(subdomain.value)
    documents.value = res.data || []
  } catch (err) {
    console.error('Failed to fetch documents:', err)
    hasError.value = true
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newVal) => {
  if (newVal) loadDocuments()
})

onMounted(() => {
  loadDocuments()
})
</script>

