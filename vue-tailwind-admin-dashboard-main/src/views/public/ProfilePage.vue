<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Profil Organisasi</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Sejarah, visi, dan arah organisasi</h1>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="text-sm text-slate-500">Dibangun untuk organisasi yang terus berkembang</p>
          </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
          <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-8">
            <h2 class="text-2xl font-semibold text-slate-900">Sejarah Singkat</h2>
            <p class="mt-4 text-base leading-8 text-slate-600">Organisasi ini lahir dari kebutuhan komunitas mahasiswa untuk berkolaborasi, mengembangkan kompetensi, dan memberi kontribusi nyata di lingkungan kampus dan masyarakat.</p>
            <p class="mt-4 text-base leading-8 text-slate-600">Seiring waktu, organisasi ini berkembang menjadi wadah pembelajaran, inspirasi, dan eksekusi program yang berdampak.</p>
          </div>
          <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Visi & Misi</h2>
            <div class="mt-6 space-y-5 text-sm leading-7 text-slate-600">
              <div>
                <p class="font-semibold text-slate-900">Visi</p>
                <p class="mt-2">Menjadi organisasi mahasiswa yang inspiratif dan unggul dalam ranah teknologi dan kepemimpinan.</p>
              </div>
              <div>
                <p class="font-semibold text-slate-900">Misi</p>
                <p class="mt-2">Mendorong pengembangan kompetensi, memperkuat kolaborasi, dan menyalurkan ide untuk memberi dampak positif.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Nilai Organisasi" title="Prinsip yang kami pegang" />
      <div class="mt-8 grid gap-6 md:grid-cols-3">
        <div v-for="item in ['Kolaboratif','Inovatif','Berkelanjutan']" :key="item" class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
          <h3 class="text-xl font-semibold text-slate-900">{{ item }}</h3>
          <p class="mt-3 text-sm leading-7 text-slate-600">Nilai utama yang menjadi fondasi setiap program dan kegiatan organisasi.</p>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Pimpinan" title="Tokoh yang memimpin organisasi" />
      <div class="mt-8 grid gap-6 md:grid-cols-3">
        <div v-for="member in leadership" :key="member.name" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
          <img :src="member.image" :alt="member.name" class="h-40 w-full rounded-2xl object-cover" />
          <h3 class="mt-5 text-xl font-semibold text-slate-900">{{ member.name }}</h3>
          <p class="mt-2 text-sm font-medium text-slate-500">{{ member.role }}</p>
          <p class="mt-2 text-sm text-slate-600">{{ member.division }}</p>
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
