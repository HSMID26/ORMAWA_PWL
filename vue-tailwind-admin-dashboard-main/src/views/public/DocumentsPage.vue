<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Dokumen" title="Dokumen dan referensi organisasi" />
        <div class="mt-8 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
          <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <h3 class="text-lg font-semibold text-slate-900">Kategori</h3>
            <div class="mt-4 flex flex-wrap gap-2">
              <span v-for="category in ['Panduan','Form','Laporan']" :key="category" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-600">{{ category }}</span>
            </div>
          </div>
          <div class="space-y-6">
            <DocumentCard
              v-for="item in documents"
              :key="item.slug"
              :title="item.title"
              :description="item.description"
              :category="item.category"
              :size="item.size"
              :updated-at="item.updatedAt"
              :preview="item.preview"
            />
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import DocumentCard from '@/components/public/DocumentCard.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { documents } = usePublicSite()
</script>
