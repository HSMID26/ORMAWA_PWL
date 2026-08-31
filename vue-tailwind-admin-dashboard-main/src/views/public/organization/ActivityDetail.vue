<template>
  <div class="max-w-[760px] mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#424751]" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-[#00346F]">Beranda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${organization.subdomain}`" class="hover:text-[#00346F]">{{ organization.nama }}</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${organization.subdomain}/agenda`" class="hover:text-[#00346F]">Agenda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <span class="text-[#00346F] font-bold truncate max-w-[200px]">{{ agenda?.judul || 'Rincian' }}</span>
    </nav>

    <div v-if="isLoading" class="py-20 text-center text-xs text-[#737783]">Memuat rincian agenda...</div>
    
    <div v-else-if="!agenda" class="py-20 text-center text-xs text-[#737783] border border-dashed border-[#C2C6D3] rounded bg-white">
      Agenda tidak ditemukan atau belum dipublikasikan.
    </div>

    <div v-else class="rounded border border-[#C2C6D3] bg-white p-6 sm:p-8 space-y-6 shadow-2xs">
      <div class="space-y-1.5 border-b border-[#E1E3E4] pb-4">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#00346F]">{{ organization.nama }}</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#191C1D]">{{ agenda.judul }}</h1>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded bg-[#F8F9FA] p-4 border border-[#C2C6D3] text-xs">
        <div>
          <span class="text-[#737783] block mb-0.5">Waktu Pelaksanaan:</span>
          <span class="font-bold text-[#191C1D]">
            {{ agenda.tanggal_pelaksanaan ? new Date(agenda.tanggal_pelaksanaan).toLocaleDateString('id-ID', { dateStyle: 'full' }) : '-' }}
          </span>
        </div>
        <div>
          <span class="text-[#737783] block mb-0.5">Lokasi / Tempat:</span>
          <span class="font-bold text-[#191C1D]">{{ agenda.tempat || 'Kampus ITI' }}</span>
        </div>
      </div>

      <div class="space-y-2 pt-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-[#00346F]">Deskripsi Kegiatan</h2>
        <p class="text-xs sm:text-sm text-[#424751] leading-relaxed whitespace-pre-line">{{ agenda.deskripsi }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicAgenda } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()
const route = useRoute()

const agenda = ref<PublicAgenda | null>(null)
const isLoading = ref(true)

useSeoMeta(() => ({
  title: agenda.value ? `${agenda.value.judul} — ${props.organization.nama}` : 'Agenda Kegiatan',
  description: agenda.value?.deskripsi,
  ogType: 'event',
}))

const loadDetail = async () => {
  const id = route.params.id as string
  if (!id || !props.organization?.subdomain) return
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

watch(() => [route.params.id, props.organization?.subdomain], () => {
  loadDetail()
})

onMounted(() => {
  loadDetail()
})
</script>
