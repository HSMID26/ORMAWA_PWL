<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Tentang Organisasi</span>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 mt-1">Profil Resmi {{ organization.nama }}</h1>
    </div>

    <!-- Identity & Detail Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      
      <!-- Narrative (8 Cols) -->
      <div class="lg:col-span-8 space-y-4">
        <h2 class="text-base font-bold text-slate-950">Gambaran Umum & Visi</h2>
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
          {{ organization.deskripsi_lengkap || organization.deskripsi || `${organization.nama} adalah organisasi kemahasiswaan resmi di Institut Teknologi Indonesia yang berkomitmen untuk mewadahi aspirasi, minat, bakat, serta peningkatan kompetensi akademik dan kepemimpinan mahasiswa.` }}
        </p>
      </div>

      <!-- Quick Facts Panel (4 Cols) -->
      <div class="lg:col-span-4 space-y-3 bg-slate-50 p-5 rounded-xl border border-slate-200 text-xs">
        <div class="flex items-center gap-3 border-b border-slate-200/80 pb-3">
          <div class="h-10 w-10 rounded-lg border border-slate-200 bg-white flex items-center justify-center font-bold text-sm text-slate-900">
            <img v-if="organization.logo" :src="organization.logo" :alt="organization.nama" class="h-full w-full object-cover" />
            <span v-else>{{ organization.nama.charAt(0) }}</span>
          </div>
          <div>
            <h3 class="text-xs font-bold text-slate-950 leading-tight">{{ organization.nama }}</h3>
            <span class="text-[10px] text-slate-500">{{ organization.jenis }}</span>
          </div>
        </div>

        <div class="space-y-2 divide-y divide-slate-200/60 pt-1">
          <div class="pt-1 flex justify-between">
            <span class="text-slate-500">Status:</span>
            <span class="font-bold text-emerald-700">Aktif Terdaftar</span>
          </div>
          <div class="pt-2 flex justify-between">
            <span class="text-slate-500">Periode:</span>
            <span class="font-semibold text-slate-900">{{ organization.current_period?.period_name || 'Aktif' }}</span>
          </div>
          <div class="pt-2 flex justify-between">
            <span class="text-slate-500">Email:</span>
            <span class="font-semibold text-slate-900">{{ organization.email_publik || '-' }}</span>
          </div>
          <div class="pt-2 flex justify-between">
            <span class="text-slate-500">Instagram:</span>
            <span class="font-semibold text-slate-900">{{ organization.instagram || '-' }}</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()

useSeoMeta(() => ({
  title: `Profil Lengkap | ${props.organization.nama}`,
  description: `Profil resmi, visi misi, dan sejarah ${props.organization.nama} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))
</script>
