<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Struktur Organisasi" title="Organisasi yang tertata dan terarah" />
        <div class="mt-10 grid gap-6 md:grid-cols-3">
          <div v-for="member in leadership" :key="member.name" class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 text-center">
            <img :src="member.image" :alt="member.name" class="mx-auto h-24 w-24 rounded-full object-cover" />
            <h3 class="mt-5 text-lg font-semibold text-slate-900">{{ member.name }}</h3>
            <p class="mt-2 text-sm font-medium text-slate-500">{{ member.role }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ member.division }}</p>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { leadership } = usePublicSite()
</script>
