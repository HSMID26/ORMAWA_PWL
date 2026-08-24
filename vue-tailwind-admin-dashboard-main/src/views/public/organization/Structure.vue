<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 pb-4 text-center max-w-xl mx-auto space-y-1">
      <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Struktur Organisasi</span>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-950">Susunan Kepengurusan</h1>
      <p class="text-xs text-slate-500">
        Periode kepengurusan aktif {{ organization.current_period?.period_name || '' }}
      </p>
    </div>

    <div v-if="isLoading" class="py-16 text-center text-xs text-slate-500">Memuat struktur pengurus...</div>
    
    <div v-else-if="!structureData || structureData.members.length === 0" class="py-14 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
      Susunan kepengurusan belum dipublikasikan.
    </div>

    <div v-else class="space-y-8">
      <div
        v-for="(members, dept) in structureData.by_department"
        :key="dept"
        class="space-y-4"
      >
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 bg-slate-100 py-1.5 px-3 rounded-md">
          {{ dept }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <div
            v-for="member in members"
            :key="member.id"
            class="rounded-xl border border-slate-200 bg-white p-4 text-center space-y-2 hover:border-slate-300 transition"
          >
            <div class="h-16 w-16 mx-auto rounded-full bg-slate-100 overflow-hidden flex items-center justify-center border border-slate-200">
              <img v-if="member.photo" :src="member.photo" :alt="member.name" class="w-full h-full object-cover" />
              <span v-else class="text-base font-bold text-slate-800">{{ member.name.charAt(0) }}</span>
            </div>
            <div>
              <h3 class="text-xs font-bold text-slate-950 leading-snug">{{ member.name }}</h3>
              <p class="text-[11px] font-semibold text-blue-900 mt-0.5">{{ member.position }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicCommittee } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()

const structureData = ref<{
  organization: any
  members: PublicCommittee[]
  by_department: Record<string, PublicCommittee[]>
} | null>(null)

const isLoading = ref(true)

useSeoMeta(() => ({
  title: `Susunan Kepengurusan | ${props.organization.nama}`,
  description: `Susunan pengurus resmi ${props.organization.nama} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const loadStructure = async () => {
  isLoading.value = true
  try {
    const data = await publicService.getStructure(props.organization.subdomain)
    structureData.value = data
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadStructure()
})
</script>
