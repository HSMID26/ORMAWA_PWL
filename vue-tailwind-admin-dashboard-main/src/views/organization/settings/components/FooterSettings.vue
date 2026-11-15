<template>
  <div class="space-y-6">
    <!-- Live Preview Mockup for Organization Footer -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
        <div>
          <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <SparklesIcon class="h-4 w-4 text-brand-600" />
            Pratinjau Langsung Footer Website Organisasi (Live Preview)
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">
            Tampilan footer dinamis yang dilihat pengunjung saat membuka website resmi {{ form.nama || 'organisasi' }}.
          </p>
        </div>
        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
          Live Preview
        </span>
      </div>

      <!-- Live Mockup Component Container -->
      <div class="rounded-xl bg-[#191C1D] text-[#C6C5CF] p-6 text-xs border border-slate-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
          
          <!-- Col 1: Identity & Description -->
          <div class="md:col-span-4 space-y-2">
            <div class="flex items-center gap-2.5">
              <div class="h-9 w-9 rounded-xl bg-white overflow-hidden flex items-center justify-center p-0.5 shrink-0">
                <img
                  v-if="logoPreview || form.logo"
                  :src="logoPreview || resolveImageUrl(form.logo) || ''"
                  alt="Logo"
                  class="h-full w-full object-contain"
                />
                <span v-else class="font-bold text-xs text-[#00346F]">
                  {{ form.nama ? form.nama.substring(0, 2).toUpperCase() : 'OM' }}
                </span>
              </div>
              <div>
                <span class="text-xs font-bold text-white block">{{ form.nama || 'Nama Organisasi' }}</span>
                <span class="text-[9px] text-[#A0A0A8] block">{{ form.jenis }} &bull; Institut Teknologi Indonesia</span>
              </div>
            </div>
            <p class="text-[11px] text-[#A0A0A8] leading-relaxed">
              {{ form.footer_description || form.deskripsi || form.slogan || 'Portal publik resmi tata kelola dan warta kegiatan mahasiswa.' }}
            </p>
            <div v-if="form.slogan" class="text-[10px] text-slate-400 italic">
              &ldquo;{{ form.slogan }}&rdquo;
            </div>
          </div>

          <!-- Col 2: Navigation -->
          <div class="md:col-span-2 space-y-2">
            <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Navigasi Ormawa</h5>
            <ul class="space-y-1 text-[11px] text-slate-400">
              <li>Beranda</li>
              <li v-if="form.modul_aktif.posts">{{ form.label_menu.posts || 'Berita' }}</li>
              <li v-if="form.modul_aktif.agenda">{{ form.label_menu.agenda || 'Agenda' }}</li>
              <li v-if="form.modul_aktif.announcements">{{ form.label_menu.announcements || 'Pengumuman' }}</li>
              <li v-if="form.modul_aktif.galeri">{{ form.label_menu.galeri || 'Galeri' }}</li>
              <li v-if="form.modul_aktif.documents">{{ form.label_menu.documents || 'Dokumen' }}</li>
              <li v-if="form.modul_aktif.structure">{{ form.label_menu.structure || 'Struktur' }}</li>
            </ul>
          </div>

          <!-- Col 3: Media Sosial -->
          <div class="md:col-span-3 space-y-2">
            <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Media Sosial & Tautan</h5>
            <div class="flex flex-wrap gap-1.5 pt-0.5">
              <span v-if="form.media_sosial.instagram" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-pink-300">Instagram</span>
              <span v-if="form.media_sosial.facebook" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-blue-300">Facebook</span>
              <span v-if="form.media_sosial.youtube" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-red-300">YouTube</span>
              <span v-if="form.media_sosial.tiktok" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-cyan-300">TikTok</span>
              <span v-if="form.media_sosial.linkedin" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-sky-300">LinkedIn</span>
              <span v-if="form.media_sosial.website" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-indigo-300">Website</span>
              <span v-if="form.media_sosial.whatsapp" class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-emerald-300">WhatsApp</span>
            </div>
          </div>

          <!-- Col 4: Sekretariat & Kontak -->
          <div class="md:col-span-3 space-y-1.5 text-[11px]">
            <h5 class="text-white font-bold uppercase tracking-wider text-[10px]">Sekretariat & Kontak</h5>
            <p class="text-[#A0A0A8] leading-tight">{{ form.alamat || 'Gedung PKM Kampus ITI, Serpong' }}</p>
            <p v-if="form.email" class="text-slate-300">{{ form.email }}</p>
            <p v-if="form.telepon" class="text-slate-300">{{ form.telepon }}</p>
          </div>

        </div>

        <!-- Bottom Strip -->
        <div class="pt-4 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-500">
          <p>&copy; {{ new Date().getFullYear() }} {{ form.copyright || `${form.nama || 'Organisasi'}. Hak Cipta Dilindungi.` }}</p>
          <p>ORMAWA Institut Teknologi Indonesia</p>
        </div>
      </div>
    </div>

    <!-- Settings Cards -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Card 1: Informasi Deskripsi & Hak Cipta Footer -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
          <h3 class="text-sm font-bold text-gray-900 dark:text-white">
            1. Deskripsi & Hak Cipta Footer
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">Penyesuaian teks ringkasan dan kalimat hak cipta khusus untuk ormawa ini.</p>
        </div>

        <div class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Footer (Opsional)</label>
            <textarea
              v-model="form.footer_description"
              rows="3"
              :placeholder="form.deskripsi || 'Penjelasan singkat fokus organisasi pada kolom footer...'"
              class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            ></textarea>
            <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan Deskripsi Singkat pada Tab 1.</p>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Teks Hak Cipta / Copyright Footer</label>
            <input
              v-model="form.copyright"
              type="text"
              :placeholder="`${form.nama || 'Organisasi'}. Hak Cipta Dilindungi.`"
              class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
            <p class="text-[10px] text-gray-400 mt-1">Tahun akan disematkan secara otomatis di bagian depan kalimat.</p>
          </div>
        </div>
      </div>

      <!-- Card 2: Kontak Sekretariat Ringkas -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
          <h3 class="text-sm font-bold text-gray-900 dark:text-white">
            2. Kontak Sekretariat & Media Sosial
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">Data terhubung langsung dengan Tab 1 Identitas Organisasi.</p>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Resmi</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="organisasi@iti.ac.id"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon / WhatsApp</label>
            <input
              v-model="form.telepon"
              type="text"
              placeholder="081234567890"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Sekretariat</label>
            <input
              v-model="form.alamat"
              type="text"
              placeholder="Gedung PKM Lt. 2 Kampus ITI"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { SparklesIcon } from 'lucide-vue-next'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { OrganizationSettingsForm } from './types'

defineProps<{
  form: OrganizationSettingsForm
  logoPreview: string | null
}>()
</script>
