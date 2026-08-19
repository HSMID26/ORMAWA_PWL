<template>
  <PublicLayout>
    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-24">
      <div class="flex flex-col justify-center">
        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm">
          <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: config.accentColor }"></span>
          {{ config.domain }}
        </div>
        <h1 class="mt-8 max-w-3xl text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
          {{ config.organizationName }}
        </h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">{{ config.tagline }}</p>
        <div class="mt-8 flex flex-wrap gap-3">
          <RouterLink :to="resolvePath('/news')" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90">
            Baca Berita
          </RouterLink>
          <RouterLink :to="resolvePath('/contact')" class="rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400">
            Hubungi Kami
          </RouterLink>
        </div>
      </div>

      <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-4 shadow-[0_25px_80px_-30px_rgba(15,23,42,0.35)]">
        <img :src="config.heroImage" alt="Hero illustration" class="h-[420px] w-full rounded-[1.4rem] object-cover" />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
      <div class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-4">
        <div v-for="stat in config.stats" :key="stat.label" class="rounded-2xl bg-slate-50 p-4">
          <p class="text-3xl font-semibold text-slate-900">{{ stat.value }}</p>
          <p class="mt-2 text-sm text-slate-600">{{ stat.label }}</p>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Tentang Kami" title="Organisasi yang bergerak untuk komunitas" />
      <div class="mt-8 grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
          <p class="text-lg leading-8 text-slate-600">{{ config.intro }}</p>
          <div class="mt-8 flex items-center gap-4">
            <img :src="config.logo" alt="Organization logo" class="h-16 w-16 rounded-full object-cover" />
            <div>
              <p class="font-semibold text-slate-900">{{ config.organizationName }}</p>
              <p class="text-sm text-slate-500">{{ config.tagline }}</p>
            </div>
          </div>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
          <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <h3 class="text-xl font-semibold text-slate-900">Visi</h3>
            <p class="mt-4 text-sm leading-7 text-slate-600">Menjadi organisasi mahasiswa yang inspiratif, inovatif, dan berdampak luas.</p>
          </div>
          <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <h3 class="text-xl font-semibold text-slate-900">Misi</h3>
            <p class="mt-4 text-sm leading-7 text-slate-600">Mengembangkan kapasitas, memperkuat kolaborasi, dan menginisiasi program nyata.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Berita Terkini" title="Informasi terbaru organisasi" actionLabel="Lihat semua" :actionHref="resolvePath('/news')" />
      <div class="mt-8 grid gap-6 md:grid-cols-2">
        <ArticleCard
          v-for="item in news.slice(0, 2)"
          :key="item.slug"
          :title="item.title"
          :excerpt="item.excerpt"
          :category="item.category"
          :published-at="item.publishedAt"
          :image="item.image"
          :href="`/news/${item.slug}`"
        />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Agenda" title="Kegiatan yang akan datang" actionLabel="Lihat agenda" :actionHref="resolvePath('/agenda')" />
      <div class="mt-8 grid gap-6 md:grid-cols-2">
        <EventCard
          v-for="item in events.slice(0, 2)"
          :key="item.slug"
          :title="item.title"
          :description="item.description"
          :date="item.date"
          :location="item.location"
          :category="item.category"
          :image="item.image"
        />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Galeri" title="Koleksi visual organisasi" actionLabel="Lihat semua" :actionHref="resolvePath('/gallery')" />
      <div class="mt-8 grid gap-6 md:grid-cols-3">
        <GalleryCard
          v-for="item in gallery.slice(0, 3)"
          :key="item.slug"
          :title="item.title"
          :description="item.description"
          :image="item.image"
          :album="item.album"
        />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Dokumen" title="Dokumen dan referensi organisasi" actionLabel="Lihat semua" :actionHref="resolvePath('/documents')" />
      <div class="mt-8 grid gap-6 md:grid-cols-2">
        <DocumentCard
          v-for="item in documents.slice(0, 2)"
          :key="item.slug"
          :title="item.title"
          :description="item.description"
          :category="item.category"
          :size="item.size"
          :updated-at="item.updatedAt"
          :preview="item.preview"
        />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div class="rounded-[2rem] border border-slate-200 bg-slate-900 p-8 text-white shadow-sm sm:p-10">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">Bergabung</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-tight">Jadilah bagian dari perjalanan organisasi.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Ayo ikut berkontribusi, belajar, dan membangun komunitas yang lebih baik.</p>
          </div>
          <RouterLink to="/contact" class="inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900">
            Hubungi Kami
          </RouterLink>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/components/public/PublicLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import ArticleCard from '@/components/public/ArticleCard.vue'
import EventCard from '@/components/public/EventCard.vue'
import GalleryCard from '@/components/public/GalleryCard.vue'
import DocumentCard from '@/components/public/DocumentCard.vue'
import { usePublicSite } from '@/composables/usePublicSite'

const { config, news, events, gallery, documents, resolvePath } = usePublicSite()
</script>
