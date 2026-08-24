<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="border-b border-slate-200 pb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Kalender Mahasiswa</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 mt-0.5">Agenda Kegiatan</h1>
      </div>

      <!-- Clean Tabs -->
      <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
        <button
          v-for="t in tabs"
          :key="t.value"
          @click="activeTab = t.value; loadAgenda()"
          :class="[
            'rounded-md px-3 py-1 text-xs font-semibold transition',
            activeTab === t.value ? 'bg-white text-slate-950 shadow-2xs' : 'text-slate-600 hover:text-slate-950'
          ]"
        >
          {{ t.label }}
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="py-16 text-center text-xs text-slate-500">Memuat agenda...</div>
    
    <div v-else-if="agendaItems.length === 0" class="py-14 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
      Tidak ada agenda kegiatan pada kategori ini.
    </div>

    <!-- Event Rows List -->
    <div v-else class="space-y-3">
      <div
        v-for="act in agendaItems"
        :key="act.id"
        class="rounded-xl border border-slate-200 bg-white p-4 hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div class="flex items-start gap-4">
          <!-- Date Block Anchor -->
          <div class="h-12 w-12 rounded-lg bg-slate-100 border border-slate-200 flex flex-col items-center justify-center shrink-0">
            <span class="text-sm font-bold text-slate-900 leading-none">
              {{ act.tanggal_pelaksanaan ? new Date(act.tanggal_pelaksanaan).getDate() : '-' }}
            </span>
            <span class="text-[9px] font-bold text-blue-900 uppercase leading-none mt-1">
              {{ act.tanggal_pelaksanaan ? new Date(act.tanggal_pelaksanaan).toLocaleDateString('id-ID', { month: 'short' }) : '-' }}
            </span>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-bold text-slate-950 leading-snug">{{ act.judul }}</h3>
            <div class="flex items-center gap-3 text-xs text-slate-500">
              <span>{{ act.tempat || 'Kampus ITI' }}</span>
              <span>&bull;</span>
              <span>{{ act.tanggal_pelaksanaan ? new Date(act.tanggal_pelaksanaan).toLocaleDateString('id-ID', { dateStyle: 'long' }) : '-' }}</span>
            </div>
            <p class="text-xs text-slate-600 line-clamp-1 max-w-xl">{{ act.deskripsi }}</p>
          </div>
        </div>

        <router-link
          :to="`/organizations/${organization.subdomain}/agenda/${act.id}`"
          class="text-xs font-semibold text-blue-900 hover:text-blue-700 shrink-0 self-end sm:self-center"
        >
          Lihat Rincian &rarr;
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicAgenda } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()

const agendaItems = ref<PublicAgenda[]>([])
const isLoading = ref(true)
const activeTab = ref<'upcoming' | 'today' | 'past'>('upcoming')

const tabs = [
  { label: 'Mendatang', value: 'upcoming' as const },
  { label: 'Hari Ini', value: 'today' as const },
  { label: 'Lampau', value: 'past' as const },
]

useSeoMeta(() => ({
  title: `Agenda Kegiatan | ${props.organization.nama}`,
  description: `Kalender jadwal kegiatan resmi mahasiswa ${props.organization.nama}.`,
  ogType: 'website',
}))

const loadAgenda = async () => {
  isLoading.value = true
  try {
    const res = await publicService.getAgenda(props.organization.subdomain, {
      tab: activeTab.value,
      per_page: 30,
    })
    agendaItems.value = res.data || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadAgenda()
})
</script>
