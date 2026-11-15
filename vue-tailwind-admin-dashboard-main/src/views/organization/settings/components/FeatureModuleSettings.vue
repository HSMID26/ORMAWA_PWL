<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-5">
      <div>
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
          Pilihan Modul & Kustomisasi Label Menu
        </h2>
        <p class="text-[11px] text-gray-500 mt-0.5">
          Aktifkan/nonaktifkan modul website serta ubah teks label navigasi yang tampil pada navbar dan beranda publik.
        </p>
      </div>

      <!-- Master Switch Website Publik -->
      <div class="p-4 rounded-xl border border-brand-200 bg-brand-50/50 dark:border-brand-900/50 dark:bg-brand-950/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h3 class="text-xs font-bold text-gray-900 dark:text-white">Status Website Publik Organisasi (Master Switch)</h3>
          <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
            Jika dinonaktifkan, seluruh website publik organisasi akan menampilkan status tidak tersedia tanpa menghapus data internal ormawa.
          </p>
        </div>
        <label class="relative inline-flex cursor-pointer items-center shrink-0">
          <input type="checkbox" v-model="form.public_website_enabled" class="peer sr-only" />
          <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-hidden dark:bg-gray-700 dark:border-gray-600"></div>
        </label>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
        <!-- Modul Berita -->
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Berita & Artikel</h3>
              <p class="text-[10px] text-gray-500 mt-0.5">Mempublikasikan artikel, rilis warta, dan berita.</p>
            </div>
            <input type="checkbox" v-model="form.modul_aktif.posts" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
          </div>
          <div v-if="form.modul_aktif.posts" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
            <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
            <input v-model="form.label_menu.posts" type="text" placeholder="Berita / Warta" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
        </div>

        <!-- Modul Agenda -->
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Agenda Acara</h3>
              <p class="text-[10px] text-gray-500 mt-0.5">Menampilkan kalender kegiatan mendatang.</p>
            </div>
            <input type="checkbox" v-model="form.modul_aktif.agenda" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
          </div>
          <div v-if="form.modul_aktif.agenda" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
            <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
            <input v-model="form.label_menu.agenda" type="text" placeholder="Agenda / Kalender Acara" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
        </div>

        <!-- Modul Pengumuman -->
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Pengumuman</h3>
              <p class="text-[10px] text-gray-500 mt-0.5">Maklumat resmi dan pengumuman ormawa.</p>
            </div>
            <input type="checkbox" v-model="form.modul_aktif.announcements" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
          </div>
          <div v-if="form.modul_aktif.announcements" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
            <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
            <input v-model="form.label_menu.announcements" type="text" placeholder="Pengumuman / Maklumat" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
        </div>

        <!-- Modul Galeri -->
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Galeri Foto</h3>
              <p class="text-[10px] text-gray-500 mt-0.5">Dokumentasi foto kegiatan dan album ormawa.</p>
            </div>
            <input type="checkbox" v-model="form.modul_aktif.galeri" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
          </div>
          <div v-if="form.modul_aktif.galeri" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
            <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
            <input v-model="form.label_menu.galeri" type="text" placeholder="Galeri / Dokumentasi" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
        </div>

        <!-- Modul Dokumen -->
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Dokumen Publik</h3>
              <p class="text-[10px] text-gray-500 mt-0.5">Arsip berkas SK, proposal, SOP, dan pedoman.</p>
            </div>
            <input type="checkbox" v-model="form.modul_aktif.documents" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
          </div>
          <div v-if="form.modul_aktif.documents" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
            <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
            <input v-model="form.label_menu.documents" type="text" placeholder="Dokumen / Arsip" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
        </div>

        <!-- Modul Struktur Kepengurusan -->
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Struktur Kepengurusan</h3>
              <p class="text-[10px] text-gray-500 mt-0.5">Bagan organisasi dan daftar fungsionaris aktif.</p>
            </div>
            <input type="checkbox" v-model="form.modul_aktif.structure" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
          </div>
          <div v-if="form.modul_aktif.structure" class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
            <label class="block text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Label Menu Navbar</label>
            <input v-model="form.label_menu.structure" type="text" placeholder="Struktur / Pengurus" class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
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
