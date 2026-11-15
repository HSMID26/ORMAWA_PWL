<template>
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    <!-- Left: Logo Management -->
    <div class="lg:col-span-4 space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
          Logo Resmi Organisasi
        </h2>
        <div class="flex flex-col items-center text-center">
          <div class="relative flex h-28 w-28 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden dark:border-gray-700 dark:bg-gray-800 mb-4 shadow-inner">
            <img
              v-if="logoPreview || form.logo"
              :src="logoPreview || resolveImageUrl(form.logo) || ''"
              alt="Logo Organisasi"
              class="h-full w-full object-contain p-1"
            />
            <span v-else class="font-bold text-3xl text-brand-600">
              {{ form.nama ? form.nama.substring(0, 2).toUpperCase() : 'OM' }}
            </span>
          </div>

          <div class="flex flex-wrap items-center justify-center gap-2">
            <label class="inline-flex cursor-pointer items-center rounded-xl bg-brand-50 px-3.5 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:bg-brand-900/30 dark:text-brand-300 transition">
              <UploadIcon class="h-3.5 w-3.5 mr-1.5" />
              {{ form.logo ? 'Ganti Logo' : 'Unggah Logo' }}
              <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="$emit('logoSelected', $event)" class="hidden" />
            </label>

            <button
              v-if="form.logo || logoPreview"
              type="button"
              @click="$emit('removeLogo')"
              class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300 transition"
            >
              <Trash2Icon class="h-3.5 w-3.5 mr-1" />
              Hapus
            </button>
          </div>

          <p class="text-[10px] text-gray-400 mt-2.5">
            Format PNG, WebP, JPG, atau SVG (Maksimal 5MB)
          </p>
        </div>
      </div>
    </div>

    <!-- Right: Profile, Descriptions & Contacts -->
    <div class="lg:col-span-8 space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-5">
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 pb-2 border-b border-gray-100 dark:border-gray-800">
          Informasi Umum
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Organisasi</label>
            <input
              v-model="form.nama"
              type="text"
              disabled
              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-semibold text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subdomain Publik (Slug)</label>
            <input
              v-model="form.subdomain"
              type="text"
              disabled
              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Slogan / Tagline Organisasi</label>
          <input
            v-model="form.slogan"
            type="text"
            placeholder="Contoh: Berkarya, Berprestasi, dan Mengabdi untuk Bangsa"
            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat (Ringkasan & Card Preview)</label>
          <textarea
            v-model="form.deskripsi"
            rows="2"
            placeholder="Penjelasan ringkas mengenai fokus dan profil ormawa..."
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          ></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Lengkap (Profil, Visi, Misi & Sejarah)</label>
          <textarea
            v-model="form.deskripsi_lengkap"
            rows="4"
            placeholder="Tuliskan latar belakang, visi, misi, dan program kerja unggulan ormawa..."
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
          ></textarea>
        </div>

        <!-- Kontak Resmi -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            Kontak Resmi & Sekretariat
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Resmi Publik</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="himatif@iti.ac.id"
                class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon / WhatsApp</label>
              <input
                v-model="form.telepon"
                type="text"
                placeholder="081234567890"
                class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Sekretariat / Lokasi Kampus</label>
            <input
              v-model="form.alamat"
              type="text"
              placeholder="Gedung PKM Lt. 2, Kampus Institut Teknologi Indonesia, Serpong"
              class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>
        </div>

        <!-- Media Sosial -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            Tautan Media Sosial & Eksternal
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Instagram</label>
              <input
                v-model="form.media_sosial.instagram"
                type="text"
                placeholder="https://instagram.com/..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Facebook</label>
              <input
                v-model="form.media_sosial.facebook"
                type="text"
                placeholder="https://facebook.com/..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
              <input
                v-model="form.media_sosial.youtube"
                type="text"
                placeholder="https://youtube.com/@..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">TikTok</label>
              <input
                v-model="form.media_sosial.tiktok"
                type="text"
                placeholder="https://tiktok.com/@..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">LinkedIn</label>
              <input
                v-model="form.media_sosial.linkedin"
                type="text"
                placeholder="https://linkedin.com/company/..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Website Eksternal</label>
              <input
                v-model="form.media_sosial.website"
                type="url"
                placeholder="https://..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Link WhatsApp</label>
              <input
                v-model="form.media_sosial.whatsapp"
                type="text"
                placeholder="https://wa.me/628..."
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UploadIcon, Trash2Icon } from 'lucide-vue-next'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { OrganizationSettingsForm } from './types'

defineProps<{
  form: OrganizationSettingsForm
  logoPreview: string | null
}>()

defineEmits<{
  (e: 'logoSelected', event: Event): void
  (e: 'removeLogo'): void
}>()
</script>
