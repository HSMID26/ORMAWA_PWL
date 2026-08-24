<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <nav class="flex items-center gap-1.5 text-xs text-slate-500">
      <router-link :to="`/organizations/${organization.subdomain}`" class="hover:text-slate-900">Beranda</router-link>
      <span>/</span>
      <router-link :to="`/organizations/${organization.subdomain}/agenda`" class="hover:text-slate-900">Agenda</router-link>
      <span>/</span>
      <span class="text-slate-900 font-semibold truncate">{{ agenda?.judul || 'Rincian' }}</span>
    </nav>

    <div v-if="isLoading" class="py-20 text-center text-xs text-slate-500">Memuat rincian agenda...</div>
    <div v-else-if="!agenda" class="py-20 text-center text-xs text-slate-500 border border-dashed rounded-xl">
      Agenda tidak ditemukan atau belum dipublikasikan.
    </div>

    <div v-else class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8 space-y-5">
      <div class="space-y-1.5 border-b border-slate-200 pb-4">
        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-900">{{ organization.nama }}</span>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-950">{{ agenda.judul }}</h1>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-lg bg-slate-50 p-4 border border-slate-200 text-xs">
        <div>
          <span class="text-slate-500 block mb-0.5">Waktu Pelaksanaan:</span>
          <span class="font-bold text-slate-900">
            {{ agenda.tanggal_pelaksanaan ? new Date(agenda.tanggal_pelaksanaan).toLocaleDateString('id-ID', { dateStyle: 'full' }) : '-' }}
          </span>
        </div>
        <div>
          <span class="text-slate-500 block mb-0.5">Lokasi / Tempat:</span>
          <span class="font-bold text-slate-900">{{ agenda.tempat || 'Kampus ITI' }}</span>
        </div>
      </div>

      <div class="space-y-2 pt-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi Kegiatan</h3>
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ agenda.deskripsi }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicAgenda } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()
const route = useRoute()

const agenda = ref<PublicAgenda | null>(null)
const isLoading = ref(true)

useSeoMeta(() => ({
  title: agenda.value ? `${agenda.value.judul} | ${props.organization.nama}` : 'Agenda Kegiatan',
  description: agenda.value?.deskripsi,
  ogType: 'event',
  jsonLd: agenda.value ? {
    '@context': 'https://schema.org',
    '@type': 'Event',
    name: agenda.value.judul,
    startDate: agenda.value.tanggal_pelaksanaan,
    location: {
      '@type': 'Place',
      name: agenda.value.tempat,
    },
    organizer: {
      '@type': 'Organization',
      name: props.organization.nama,
    }
  } : undefined
}))

const loadDetail = async () => {
  const id = route.params.id as string
  isLoading.value = true
  try {
    const data = await publicService.getAgendaDetail(props.organization.subdomain, id)
    agenda.value = data
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadDetail()
})
</script>
