<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-5">
      <div>
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
          Optimasi Mesin Pencari (Search Engine Optimization) & Analytics
        </h2>
        <p class="text-[11px] text-gray-500 mt-0.5">
          Metadata yang dibaca oleh Google, Bing, pratinjau tautan WhatsApp/Twitter, serta pelacakan pengunjung.
        </p>
      </div>

      <div class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">SEO Title Tag</label>
          <input
            v-model="form.seo_title"
            type="text"
            :placeholder="`${form.nama} | ORMAWA Institut Teknologi Indonesia`"
            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          />
          <p class="text-[10px] text-gray-400 mt-1">Judul yang muncul pada tab peramban dan hasil pencarian Google.</p>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">SEO Meta Description</label>
          <textarea
            v-model="form.seo_description"
            rows="3"
            :placeholder="form.deskripsi || `Website resmi ${form.nama} Institut Teknologi Indonesia`"
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          ></textarea>
          <p class="text-[10px] text-gray-400 mt-1">Ringkasan artikel/halaman yang tampil di bawah judul hasil pencarian (150-160 karakter disarankan).</p>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Google Analytics Tracking ID (Opsional)</label>
          <input
            v-model="form.ga_tracking_id"
            type="text"
            placeholder="G-XXXXXXXXXX"
            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          />
          <p class="text-[10px] text-gray-400 mt-1">Hanya akan dimuat pada website publik yang aktif.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { OrganizationSettingsForm } from './types'

defineProps<{
  form: OrganizationSettingsForm
}>()
</script>
