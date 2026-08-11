<template>
  <PortalLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Latest News" title="Updates from across every organization" />
        <div class="mt-8 grid gap-6 md:grid-cols-2">
          <article v-for="item in news" :key="item.id" class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
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
      </div>
    </section>
  </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/components/public/PortalLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
import { usePortal } from '@/composables/usePortal'

const { news } = usePortal()

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
