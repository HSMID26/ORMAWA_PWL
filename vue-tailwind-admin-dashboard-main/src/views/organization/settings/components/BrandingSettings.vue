<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-6">
      
      <!-- Status Website Switch -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
        <div>
          <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            Status Akses Website Publik
          </h2>
          <p class="text-[11px] text-gray-500 mt-0.5">
            Jika dinonaktifkan, pengunjung umum akan melihat pemberitahuan pemeliharaan resmi. Admin tetap dapat mengelola dashboard.
          </p>
        </div>
        <label class="relative inline-flex cursor-pointer items-center">
          <input type="checkbox" v-model="form.public_website_enabled" class="peer sr-only" />
          <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-emerald-600 peer-checked:after:translate-x-full dark:bg-gray-700"></div>
        </label>
      </div>

      <!-- Visual Theme Color -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
            Warna Aksen Utama (Primary Brand Color)
          </label>
          <div class="flex items-center gap-4">
            <input
              type="color"
              v-model="form.warna_tema"
              class="h-12 w-16 cursor-pointer rounded-xl border border-gray-200 bg-transparent p-1 dark:border-gray-700"
            />
            <div>
              <span class="font-mono text-sm font-bold text-gray-800 dark:text-gray-200 uppercase">{{ form.warna_tema }}</span>
              <p class="text-[10px] text-gray-400 mt-0.5">Warna aksen tombol, border aktif navbar, badge, dan gradien fallback hero.</p>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Pratinjau Elemen Aksen</label>
          <div class="flex items-center gap-3">
            <button
              type="button"
              :style="{ backgroundColor: form.warna_tema }"
              class="rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs"
            >
              Tombol Utama
            </button>
            <span
              :style="{ color: form.warna_tema, borderColor: form.warna_tema }"
              class="rounded-xl border px-3 py-1.5 text-xs font-semibold"
            >
              Badge Ormawa
            </span>
          </div>
        </div>
      </div>

      <!-- Hero Customization: Text & Background Image -->
      <div class="space-y-5 pt-4 border-t border-gray-100 dark:border-gray-800">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            Kustomisasi Hero Banner Website
          </h3>
          <p class="text-[11px] text-gray-500 mt-0.5">
            Sesuaikan teks headline, subjudul, dan gambar latar belakang banner utama.
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Utama Hero (Opsional)</label>
            <input
              v-model="form.hero_title"
              type="text"
              :placeholder="form.nama || 'Nama Organisasi'"
              class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
            <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan nama resmi organisasi.</p>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subjudul Hero (Opsional)</label>
            <input
              v-model="form.hero_subtitle"
              type="text"
              :placeholder="form.slogan || form.deskripsi || 'Portal Resmi Organisasi Mahasiswa ITI'"
              class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
            <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan slogan atau deskripsi singkat.</p>
          </div>
        </div>

        <!-- Hero Background Image Upload & Controls -->
        <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                <ImageIcon class="h-4 w-4 text-brand-600" />
                Foto Latar Belakang Hero (Hero Background Image)
              </h4>
              <p class="text-[10px] text-gray-500 mt-0.5">
                Rasio disarankan landscape 16:9 (Maksimal 10MB). Jika kosong, hero otomatis menggunakan warna tema aksen.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <label class="inline-flex cursor-pointer items-center rounded-xl bg-white border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition shadow-xs">
                <UploadCloudIcon class="h-4 w-4 mr-1.5 text-brand-600" />
                {{ form.hero_image || heroPreview ? 'Ganti Foto Hero' : 'Unggah Foto Hero' }}
                <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" @change="$emit('heroSelected', $event)" class="hidden" />
              </label>

              <button
                v-if="form.hero_image || heroPreview"
                type="button"
                @click="$emit('removeHero')"
                class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300 transition"
              >
                <Trash2Icon class="h-3.5 w-3.5 mr-1" />
                Hapus Foto
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Preview Mini-Card -->
      <div class="space-y-2 pt-4 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            Pratinjau Langsung Hero (Live Preview)
          </span>
          <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded dark:bg-emerald-950/40 dark:text-emerald-400">
            Real-Time
          </span>
        </div>

        <div
          class="relative rounded-2xl overflow-hidden p-6 sm:p-8 text-white shadow-md transition-all duration-300 bg-cover bg-center"
          :style="livePreviewHeroStyle"
        >
          <!-- Contrast Dark Overlay -->
          <div
            v-if="heroPreview || form.hero_image"
            class="absolute inset-0 z-0 bg-gradient-to-tr from-black/80 via-black/55 to-black/35 pointer-events-none"
          ></div>
          <div
            v-else
            class="absolute inset-0 z-0 bg-gradient-to-tr from-black/40 via-transparent to-white/10 pointer-events-none"
          ></div>

          <div class="relative z-10 max-w-xl space-y-3">
            <div class="flex items-center gap-2">
              <span class="rounded bg-white/20 backdrop-blur-xs px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                {{ form.jenis }}
              </span>
              <span class="text-[11px] text-white/80 font-semibold tracking-wide">
                &bull; Institut Teknologi Indonesia
              </span>
            </div>

            <h4 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-snug">
              {{ form.hero_title || form.nama || 'Nama Organisasi' }}
            </h4>
            <p class="text-xs text-white/90 leading-relaxed font-normal">
              {{ form.hero_subtitle || form.slogan || form.deskripsi || 'Membangun sinergi, inovasi, dan prestasi mahasiswa di lingkungan Institut Teknologi Indonesia.' }}
            </p>

            <div class="flex items-center gap-3 pt-2">
              <span class="rounded-lg bg-white px-4 py-2 text-[11px] font-bold text-gray-900 shadow-xs">
                Tentang Kami
              </span>
              <span class="rounded-lg border border-white/80 bg-white/10 px-4 py-2 text-[11px] font-semibold text-white">
                {{ form.label_menu?.posts || 'Baca Warta' }} &rarr;
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import type { StyleValue } from 'vue'
import { ImageIcon, UploadCloudIcon, Trash2Icon } from 'lucide-vue-next'
import type { OrganizationSettingsForm } from './types'

defineProps<{
  form: OrganizationSettingsForm
  heroPreview: string | null
  livePreviewHeroStyle: StyleValue
}>()

defineEmits<{
  (e: 'heroSelected', event: Event): void
  (e: 'removeHero'): void
}>()
</script>
