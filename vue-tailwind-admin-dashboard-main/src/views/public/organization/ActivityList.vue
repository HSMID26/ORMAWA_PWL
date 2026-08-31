<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#424751]" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-[#00346F]">Beranda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-[#00346F]">{{ orgName }}</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <span class="text-[#00346F] font-bold">Agenda</span>
    </nav>

    <!-- Page Header -->
    <div class="border-b border-[#C2C6D3] pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Jadwal Kegiatan</span>
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#191C1D] mt-0.5">Agenda Kegiatan</h1>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="i in 6" :key="i" class="h-36 rounded bg-white border border-[#C2C6D3] animate-pulse"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="agenda.length === 0" class="py-16 text-center text-xs text-[#737783] border border-dashed border-[#C2C6D3] rounded bg-white">
      Belum ada agenda kegiatan yang dijadwalkan.
    </div>

    <!-- Agenda Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="act in agenda"
        :key="act.id"
        class="rounded border border-[#C2C6D3] bg-white p-5 hover:border-[#00346F] transition duration-150 flex flex-col justify-between space-y-4 shadow-2xs"
      >
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="rounded bg-[#F3F4F5] px-2.5 py-0.5 text-[10px] font-bold text-[#00346F] border border-[#E1E3E4]">
              {{ act.tanggal_pelaksanaan ? new Date(act.tanggal_pelaksanaan).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-' }}
            </span>
            <span class="text-[10px] text-[#737783] truncate max-w-[130px]">{{ act.tempat || 'Kampus ITI' }}</span>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-[#191C1D] leading-snug">{{ act.judul }}</h2>
          <p class="text-xs text-[#424751] line-clamp-3 leading-relaxed">{{ act.deskripsi }}</p>
        </div>

        <div class="pt-3 border-t border-[#E1E3E4] flex items-center justify-end">
          <router-link
            :to="`/organizations/${subdomain}/agenda/${act.id}`"
            class="text-xs font-semibold text-[#00346F] hover:underline"
          >
            Lihat Rincian &rarr;
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicAgenda } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const agenda = ref<PublicAgenda[]>([])
const isLoading = ref(true)

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Agenda Kegiatan — ${orgName.value} | ORMAWA ITI`,
  description: `Jadwal agenda kegiatan resmi dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const loadAgenda = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  try {
    const res = await publicService.getAgenda(subdomain.value, { per_page: 30 })
    agenda.value = res.data || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newVal) => {
  if (newVal) loadAgenda()
})

onMounted(() => {
  loadAgenda()
})
</script>
