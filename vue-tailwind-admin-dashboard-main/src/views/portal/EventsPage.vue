<template>
  <PortalLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Upcoming Events" title="Events from every organization" />
        <div class="mt-8 grid gap-6 md:grid-cols-2">
          <article v-for="event in events" :key="event.id" class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <p class="text-sm font-semibold text-slate-500">{{ event.organizationName }}</p>
            <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ event.title }}</h3>
            <p class="mt-4 text-sm text-slate-600">{{ event.location }}</p>
            <p class="mt-3 text-sm font-medium text-slate-500">{{ formatDate(event.date) }}</p>
          </article>
        </div>
      </div>
    </section>
  </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/components/public/PortalLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import { usePortal } from '@/composables/usePortal'

const { events } = usePortal()

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
