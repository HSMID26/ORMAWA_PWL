<template>
  <PortalLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="About" title="Why organizations matter" />
        <div class="mt-8 grid gap-6 md:grid-cols-3">
          <div v-for="item in benefits" :key="item.title" class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <h3 class="text-xl font-semibold text-slate-900">{{ item.title }}</h3>
            <p class="mt-3 text-sm leading-7 text-slate-600">{{ item.description }}</p>
          </div>
        </div>
      </div>
    </section>
  </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/components/public/PortalLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'

const benefits = [
  { title: 'Leadership', description: 'Develop leadership, responsibility, and public speaking through real programs and projects.' },
  { title: 'Networking', description: 'Meet peers, mentors, alumni, and industry partners through active communities.' },
  { title: 'Innovation', description: 'Turn ideas into initiatives and create valuable experiences for the campus ecosystem.' },
]
</script>
