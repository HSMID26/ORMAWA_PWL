<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Berita" title="Berita dan update terbaru" />
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
          <div class="space-y-6">
            <ArticleCard
              v-for="item in news"
              :key="item.slug"
              :title="item.title"
              :excerpt="item.excerpt"
              :category="item.category"
              :published-at="item.publishedAt"
              :image="item.image"
              :href="`/news/${item.slug}`"
            />
          </div>
          <div class="space-y-6 rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900">Kategori</h3>
              <div class="mt-4 flex flex-wrap gap-2">
                <span v-for="tag in ['Workshop','Seminar','Teknologi']" :key="tag" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-600">{{ tag }}</span>
              </div>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-slate-900">Cari</h3>
              <input class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Cari berita..." />
            </div>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import ArticleCard from '@/components/public/ArticleCard.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { news } = usePublicSite()
</script>
