<template>
  <PortalLayout>
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <SectionHeading eyebrow="Contact" title="Get in touch with the portal" />
        <div class="mt-8 grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
          <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <p class="text-sm leading-7 text-slate-600">For questions, partnership opportunities, or organizational inquiries, reach us directly at the university contact points below.</p>
            <div class="mt-6 space-y-3 text-sm text-slate-600">
              <p><span class="font-semibold text-slate-900">Email:</span> ormawa@iti.ac.id</p>
              <p><span class="font-semibold text-slate-900">Campus:</span> Institut Teknologi Indonesia</p>
            </div>
          </div>
          <form class="space-y-5">
            <div>
              <label class="text-sm font-medium text-slate-700">Name</label>
              <input class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Your name" />
            </div>
            <div>
              <label class="text-sm font-medium text-slate-700">Email</label>
              <input class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="you@email.com" />
            </div>
            <div>
              <label class="text-sm font-medium text-slate-700">Message</label>
              <textarea class="mt-2 min-h-32 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Write your message..." />
            </div>
            <button class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white">Send Message</button>
          </form>
        </div>
      </div>
    </section>
  </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/components/public/PortalLayout.vue'
import SectionHeading from '@/components/public/SectionHeading.vue'
</script>
