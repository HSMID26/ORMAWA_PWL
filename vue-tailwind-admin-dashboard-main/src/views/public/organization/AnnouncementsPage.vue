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
      <span class="text-slate-900 font-semibold">Pengumuman</span>
    </nav>

    <div class="border-b border-slate-200 pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Papan Warta Resmi</span>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 mt-0.5">Pengumuman Organisasi</h1>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="py-16 text-center text-xs text-slate-500 space-y-3">
      <div class="h-20 max-w-xl mx-auto rounded-xl bg-slate-100 animate-pulse"></div>
      <p>Memuat pengumuman...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="py-14 text-center text-xs text-rose-600 border border-dashed border-rose-200 rounded-xl bg-rose-50/50">
      {{ errorMessage }}
    </div>
    
    <!-- Empty State -->
    <div v-else-if="announcements.length === 0" class="py-14 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
      Belum ada pengumuman aktif saat ini.
    </div>

    <!-- Announcements List -->
    <div v-else class="space-y-3.5">
      <article
        v-for="ann in announcements"
        :key="ann.id"
        :class="[
          'rounded-xl border p-5 transition space-y-2 bg-white',
          ann.priority === 'urgent' ? 'border-l-4 border-l-rose-600 border-slate-200 shadow-2xs' :
          ann.priority === 'high' ? 'border-l-4 border-l-amber-500 border-slate-200' :
          ann.priority === 'low' ? 'border-l-4 border-l-slate-400 border-slate-200' :
          'border-l-4 border-l-blue-700 border-slate-200'
        ]"
      >
        <div class="flex items-center justify-between gap-3 text-xs">
          <div class="flex items-center gap-2">
            <span
              :class="[
                'rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider',
                ann.priority === 'urgent' ? 'bg-rose-100 text-rose-800' :
                ann.priority === 'high' ? 'bg-amber-100 text-amber-900' :
                ann.priority === 'low' ? 'bg-slate-100 text-slate-600' :
                'bg-blue-100 text-blue-800'
              ]"
            >
              {{ ann.priority }}
            </span>
            <span class="text-slate-400 text-[11px]">
              Diterbitkan {{ new Date(ann.published_at || '').toLocaleDateString('id-ID', { dateStyle: 'long' }) }}
            </span>
          </div>
        </div>

        <h2 class="text-sm sm:text-base font-bold text-slate-950">{{ ann.title }}</h2>
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ ann.content }}</p>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicAnnouncement } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const announcements = ref<PublicAnnouncement[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Pengumuman — ${orgName.value} | CMS ORMAWA ITI`,
  description: `Pengumuman resmi dan maklumat penting dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const loadAnnouncements = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  errorMessage.value = ''
  try {
    const data = await publicService.getAnnouncements(subdomain.value)
    announcements.value = data || []
  } catch (err: any) {
    console.error('Failed to load announcements:', err)
    errorMessage.value = (err.status === 404 || err.response?.status === 404)
      ? 'Modul pengumuman dinonaktifkan oleh organisasi.'
      : 'Gagal memuat pengumuman.'
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newSlug) => {
  if (newSlug) loadAnnouncements()
})

onMounted(() => {
  loadAnnouncements()
})
</script>
