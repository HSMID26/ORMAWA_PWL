<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#424751]" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-[#00346F]">Beranda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-[#00346F]">{{ orgName }}</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <span class="text-[#00346F] font-bold">Pengumuman</span>
    </nav>

    <!-- Header -->
    <div class="border-b border-[#C2C6D3] pb-4">
      <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Papan Warta Resmi</span>
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#191C1D] mt-0.5">Pengumuman Organisasi</h1>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-28 rounded bg-white border border-[#C2C6D3] animate-pulse"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="announcements.length === 0" class="py-16 text-center text-xs text-[#737783] border border-dashed border-[#C2C6D3] rounded bg-white">
      Belum ada pengumuman aktif saat ini.
    </div>

    <!-- List -->
    <div v-else class="space-y-4">
      <article
        v-for="ann in announcements"
        :key="ann.id"
        :class="[
          'rounded border p-5 transition space-y-2 bg-white shadow-2xs',
          ann.priority === 'urgent' ? 'border-l-4 border-l-[#BA1A1A] border-[#C2C6D3]' :
          ann.priority === 'high' ? 'border-l-4 border-l-amber-500 border-[#C2C6D3]' :
          'border-l-4 border-l-[#00346F] border-[#C2C6D3]'
        ]"
      >
        <div class="flex items-center justify-between gap-3 text-xs">
          <span
            :class="[
              'rounded px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider',
              ann.priority === 'urgent' ? 'bg-[#FFDAD6] text-[#93000A]' :
              ann.priority === 'high' ? 'bg-amber-100 text-amber-900' :
              'bg-[#D7E2FF] text-[#001B3F]'
            ]"
          >
            {{ ann.priority }}
          </span>
          <span class="text-[#737783] text-[11px]">
            Diterbitkan {{ new Date(ann.published_at || '').toLocaleDateString('id-ID', { dateStyle: 'long' }) }}
            <span v-if="ann.expires_at" class="ml-1 text-[#00346F] font-semibold">
              &bull; Berlaku s/d {{ new Date(ann.expires_at).toLocaleDateString('id-ID', { dateStyle: 'long' }) }}
            </span>
          </span>
        </div>

        <h2 class="text-sm sm:text-base font-bold text-[#191C1D]">{{ ann.title }}</h2>
        <p class="text-xs sm:text-sm text-[#424751] leading-relaxed whitespace-pre-line">{{ ann.content }}</p>
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

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

useSeoMeta(() => ({
  title: `Pengumuman — ${orgName.value} | ORMAWA ITI`,
  description: `Pengumuman resmi dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const loadAnnouncements = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  try {
    const res = await publicService.getAnnouncements(subdomain.value)
    announcements.value = res || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newVal) => {
  if (newVal) loadAnnouncements()
})

onMounted(() => {
  loadAnnouncements()
})
</script>
