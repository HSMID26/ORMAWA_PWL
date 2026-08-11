<template>
  <PortalLayout>
    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
      <div class="flex flex-col justify-center">
        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm">
          <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
          One Platform. Every Student Organization.
        </div>
        <h1 class="mt-8 max-w-3xl text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
          Discover every student organization in Institut Teknologi Indonesia.
        </h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
          Explore HMPS, UKM, BEM, and other student communities in one elegant portal.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <RouterLink to="/portal/organizations" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90">
            Browse Organizations
          </RouterLink>
          <RouterLink to="/portal/news" class="rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400">
            Latest News
          </RouterLink>
        </div>
      </div>

      <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-4 shadow-[0_25px_80px_-30px_rgba(15,23,42,0.35)]">
        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1400&q=80" alt="University organizations collaboration" class="h-[420px] w-full rounded-[1.4rem] object-cover" />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div class="grid gap-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-4">
        <div v-for="stat in stats" :key="stat.label" class="rounded-2xl bg-slate-50 p-4">
          <p class="text-3xl font-semibold text-slate-900">{{ stat.value }}</p>
          <p class="mt-2 text-sm text-slate-600">{{ stat.label }}</p>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Organization Directory" title="Browse every student organization" actionLabel="View all" actionHref="/portal/organizations" />
      <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <OrganizationCard v-for="organization in featuredOrganizations" :key="organization.id" :organization="organization" />
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Latest News" title="News from all organizations" actionLabel="More updates" actionHref="/portal/news" />
      <div class="mt-8 grid gap-6 md:grid-cols-2">
        <article v-for="item in news" :key="item.id" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center gap-3">
            <img :src="item.organizationLogo" :alt="item.organizationName" class="h-10 w-10 rounded-full object-cover" />
            <div>
              <p class="font-semibold text-slate-900">{{ item.organizationName }}</p>
              <p class="text-sm text-slate-500">{{ item.category }}</p>
            </div>
          </div>
          <h3 class="mt-5 text-xl font-semibold text-slate-900">{{ item.title }}</h3>
          <p class="mt-3 text-sm text-slate-500">Published {{ formatDate(item.publishDate) }}</p>
        </article>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Upcoming Events" title="Events across the ecosystem" actionLabel="View calendar" actionHref="/portal/events" />
      <div class="mt-8 grid gap-6 md:grid-cols-2">
        <article v-for="event in events" :key="event.id" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-slate-500">{{ event.organizationName }}</p>
              <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ event.title }}</h3>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600">{{ formatDate(event.date) }}</span>
          </div>
          <p class="mt-4 text-sm text-slate-600">{{ event.location }}</p>
        </article>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div class="rounded-[2rem] border border-slate-200 bg-slate-900 p-8 text-white shadow-sm sm:p-10">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">Join the ecosystem</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-tight">Explore every organization today.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Whether you are looking for leadership, innovation, networking, or competitions, the portal helps you find the right community.</p>
          </div>
          <RouterLink to="/portal/organizations" class="inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900">
            Browse Organizations
          </RouterLink>
        </div>
      </div>
    </section>
  </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/components/public/PortalLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import OrganizationCard from '@/components/public/OrganizationCard.vue'
import { usePortal } from '@/composables/usePortal'

const { featuredOrganizations, news, events } = usePortal()

const stats = [
  { label: 'Organizations', value: '25+' },
  { label: 'Published Articles', value: '180+' },
  { label: 'Events', value: '60+' },
  { label: 'Students', value: '3500+' },
]

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
