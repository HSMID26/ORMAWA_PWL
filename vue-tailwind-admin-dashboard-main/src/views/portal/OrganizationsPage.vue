<template>
  <PortalLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Organization Directory</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Find the right organization for you</h1>
          </div>
          <div class="flex flex-wrap gap-3">
            <input class="rounded-full border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Search organization" />
            <select class="rounded-full border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
              <option>All Categories</option>
              <option>HMPS</option>
              <option>UKM</option>
              <option>BEM</option>
              <option>Faculty</option>
            </select>
          </div>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <OrganizationCard v-for="organization in organizations" :key="organization.id" :organization="organization" />
        </div>
      </div>
    </section>
  </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/components/public/PortalLayout.vue'
import OrganizationCard from '@/components/public/OrganizationCard.vue'
import { usePortal } from '@/composables/usePortal'

const { organizations } = usePortal()
</script>
