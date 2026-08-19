<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Galeri" title="Dokumentasi kegiatan dan momen organisasi" />
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <GalleryCard
            v-for="item in gallery"
            :key="item.slug"
            :title="item.title"
            :description="item.description"
            :image="item.image"
            :album="item.album"
          />
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import GalleryCard from '@/components/public/GalleryCard.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { gallery } = usePublicSite()
</script>
