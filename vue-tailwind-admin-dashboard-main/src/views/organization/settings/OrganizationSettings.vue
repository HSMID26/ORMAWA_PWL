<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Pengaturan Website & Profil Organisasi'" />

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <SettingsIcon class="h-5 w-5 text-brand-600" />
          Manajemen Website & Profil Organisasi
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
          Kelola identitas, kustomisasi visual website publik, modul aktif, dan metadata SEO ormawa Anda.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <a
          :href="publicUrl"
          target="_blank"
          class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition"
        >
          <ExternalLinkIcon class="h-4 w-4 text-brand-600" />
          <span>Lihat Website Publik</span>
        </a>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="h-48 w-full animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800"></div>
      <div class="h-64 w-full animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800"></div>
    </div>

    <form v-else @submit.prevent="saveSettings" class="space-y-6">
      
      <!-- Top Navigation Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-800">
        <button
          type="button"
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'rounded-xl px-4 py-2 text-xs font-semibold transition',
            activeTab === tab.id
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- TAB 1: IDENTITAS ORGANISASI -->
      <div v-show="activeTab === 'identity'" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="lg:col-span-4 space-y-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
              Logo Resmi
            </h2>
            <div class="flex flex-col items-center text-center">
              <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden dark:border-gray-700 dark:bg-gray-800 mb-4">
                <img v-if="form.logo" :src="form.logo" alt="Logo" class="h-full w-full object-cover" />
                <span v-else class="font-bold text-2xl text-brand-600">
                  {{ form.nama.substring(0, 2).toUpperCase() }}
                </span>
              </div>
              <label class="inline-flex cursor-pointer items-center rounded-xl bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:bg-brand-900/30 dark:text-brand-300 transition">
                <UploadIcon class="h-4 w-4 mr-1.5" />
                Unggah Logo Baru
                <input type="file" accept="image/*" @change="handleLogoUpload" class="hidden" />
              </label>
              <p class="text-[10px] text-gray-400 mt-2">Format PNG atau WebP transparan (Max 2MB)</p>
            </div>
          </div>
        </div>

        <div class="lg:col-span-8 space-y-6">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 pb-2 border-b border-gray-100 dark:border-gray-800">
              Informasi Umum & Kontak Publik
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Organisasi</label>
                <input v-model="form.nama" type="text" disabled class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-semibold text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400" />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subdomain Publik</label>
                <input v-model="form.subdomain" type="text" disabled class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Slogan / Tagline Organisasi</label>
              <input v-model="form.slogan" type="text" placeholder="Contoh: Berkarya, Berprestasi, dan Mengabdi" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat (Hero & Card Preview)</label>
              <textarea v-model="form.deskripsi" rows="2" placeholder="Penjelasan singkat mengenai profil organisasi..." class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Lengkap (Visi, Misi, dan Profil)</label>
              <textarea v-model="form.deskripsi_lengkap" rows="4" placeholder="Tuliskan sejarah, visi, misi, dan program unggulan ormawa..." class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Resmi Publik</label>
                <input v-model="form.email_publik" type="email" placeholder="ormawa@iti.ac.id" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Instagram (@username)</label>
                <input v-model="form.instagram" type="text" placeholder="@ormawa_iti" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Website Eksternal (Opsional)</label>
                <input v-model="form.website_eksternal" type="url" placeholder="https://..." class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: WEBSITE PUBLIK & BRANDING VISUAL -->
      <div v-show="activeTab === 'branding'" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
            <div>
              <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Status Website Publik
              </h2>
              <p class="text-[11px] text-gray-500 mt-0.5">Aktifkan agar mahasiswa dan pengunjung umum dapat mengakses portal organisasi Anda.</p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
              <input type="checkbox" v-model="form.public_website_enabled" class="peer sr-only" />
              <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-green-600 peer-checked:after:translate-x-full dark:bg-gray-700"></div>
            </label>
          </div>

          <!-- Visual Theme Color -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Warna Aksen Utama (Primary Brand Color)</label>
              <div class="flex items-center gap-4">
                <input type="color" v-model="form.warna_tema" class="h-12 w-16 cursor-pointer rounded-xl border border-gray-200 bg-transparent p-1 dark:border-gray-700" />
                <div>
                  <span class="font-mono text-sm font-bold text-gray-800 dark:text-gray-200 uppercase">{{ form.warna_tema }}</span>
                  <p class="text-[10px] text-gray-400 mt-0.5">Warna tombol, header aksen, dan badge visual.</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Pratinjau Tampilan Aksen</label>
              <div class="flex items-center gap-3">
                <button type="button" :style="{ backgroundColor: form.warna_tema }" class="rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs">
                  Tombol Utama
                </button>
                <span :style="{ color: form.warna_tema, borderColor: form.warna_tema }" class="rounded-xl border px-3 py-1.5 text-xs font-semibold">
                  Badge Organisasi
                </span>
              </div>
            </div>
          </div>

          <!-- Hero Customization -->
          <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200">Kustomisasi Hero Banner</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Utama Hero (Opsional)</label>
                <input v-model="form.hero_title" type="text" :placeholder="form.nama" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan nama resmi organisasi.</p>
              </div>
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Subjudul Hero (Opsional)</label>
                <input v-model="form.hero_subtitle" type="text" :placeholder="form.slogan || 'Portal Resmi Organisasi'" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                <p class="text-[10px] text-gray-400 mt-1">Default: Menggunakan slogan atau deskripsi singkat.</p>
              </div>
            </div>
          </div>

          <!-- ─── Live Preview Mini-Card (Stitch Style) ─────────────────────── -->
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
              class="relative rounded-2xl overflow-hidden p-6 sm:p-8 text-white shadow-md transition-all duration-300"
              :style="{ backgroundColor: form.warna_tema || '#00346F' }"
            >
              <!-- Subtle Background Texture -->
              <div class="absolute inset-0 bg-gradient-to-tr from-black/40 via-transparent to-white/10 pointer-events-none"></div>

              <div class="relative z-10 max-w-xl space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-white/80 block">
                  {{ form.jenis }} &bull; Institut Teknologi Indonesia
                </span>
                <h4 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-snug">
                  {{ form.hero_title || form.nama || 'Nama Organisasi' }}
                </h4>
                <p class="text-xs text-white/90 leading-relaxed font-normal">
                  {{ form.hero_subtitle || form.slogan || form.deskripsi || 'Membangun sinergi dan inovasi mahasiswa di lingkungan Institut Teknologi Indonesia.' }}
                </p>

                <div class="flex items-center gap-3 pt-2">
                  <span class="rounded-lg bg-white px-4 py-2 text-[11px] font-bold text-gray-900 shadow-xs">
                    Tentang Kami
                  </span>
                  <span class="rounded-lg border border-white/80 bg-white/10 px-4 py-2 text-[11px] font-semibold text-white">
                    Baca Warta
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: KONFIGURASI MODUL PUBLIK -->
      <div v-show="activeTab === 'modules'" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 pb-2 border-b border-gray-100 dark:border-gray-800">
            Pilihan Modul Website Publik
          </h2>
          <p class="text-[11px] text-gray-500">Modul yang dinonaktifkan akan disembunyikan dari navigasi dan ditolak aksesnya oleh API publik.</p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <!-- Modul Berita -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
              <div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Berita & Artikel</h3>
                <p class="text-[10px] text-gray-500 mt-0.5">Mempublikasikan artikel dan rilis berita.</p>
              </div>
              <input type="checkbox" v-model="form.modul_aktif.posts" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
            </div>

            <!-- Modul Agenda -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
              <div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Agenda Acara</h3>
                <p class="text-[10px] text-gray-500 mt-0.5">Menampilkan kalender kegiatan mendatang.</p>
              </div>
              <input type="checkbox" v-model="form.modul_aktif.agenda" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
            </div>

            <!-- Modul Pengumuman -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
              <div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Pengumuman</h3>
                <p class="text-[10px] text-gray-500 mt-0.5">Maklumat resmi ormawa.</p>
              </div>
              <input type="checkbox" v-model="form.modul_aktif.announcements" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
            </div>

            <!-- Modul Galeri -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
              <div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Galeri Foto</h3>
                <p class="text-[10px] text-gray-500 mt-0.5">Dokumentasi foto kegiatan.</p>
              </div>
              <input type="checkbox" v-model="form.modul_aktif.galeri" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
            </div>

            <!-- Modul Dokumen -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
              <div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Dokumen Publik</h3>
                <p class="text-[10px] text-gray-500 mt-0.5">Berkas SK, pedoman, dan arsip publik.</p>
              </div>
              <input type="checkbox" v-model="form.modul_aktif.documents" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
            </div>

            <!-- Modul Struktur Organisasi -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
              <div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Modul Struktur Kepengurusan</h3>
                <p class="text-[10px] text-gray-500 mt-0.5">Daftar nama dan foto pengurus aktif.</p>
              </div>
              <input type="checkbox" v-model="form.modul_aktif.structure" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: PENGATURAN SEO & SOCIAL SHARING -->
      <div v-show="activeTab === 'seo'" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 pb-2 border-b border-gray-100 dark:border-gray-800">
            Optimasi Mesin Pencari (Search Engine Optimization)
          </h2>
          <p class="text-[11px] text-gray-500">Metadata yang dibaca oleh Google, Bing, WhatsApp, dan platform media sosial.</p>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">SEO Title Tag</label>
              <input v-model="form.seo_title" type="text" :placeholder="`${form.nama} | ORMAWA ITI`" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">SEO Meta Description</label>
              <textarea v-model="form.seo_description" rows="3" :placeholder="form.deskripsi || `Website resmi ${form.nama}`" class="w-full rounded-xl border border-gray-300 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Save Action Bar -->
      <div class="flex justify-end border-t border-gray-200 pt-5 dark:border-gray-800">
        <button
          type="submit"
          :disabled="isSubmitting"
          class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm disabled:opacity-50 transition"
        >
          <SaveIcon class="h-4 w-4" />
          {{ isSubmitting ? 'Menyimpan Pengaturan...' : 'Simpan Semua Pengaturan' }}
        </button>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { organizationService } from '@/services/organizationService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import {
  SettingsIcon,
  SaveIcon,
  ExternalLinkIcon,
  UploadIcon,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toastStore = useToastStore()

const isLoading = ref(true)
const isSubmitting = ref(false)
const activeTab = ref('identity')

const tabs = [
  { id: 'identity', label: '1. Identitas Organisasi' },
  { id: 'branding', label: '2. Website & Branding' },
  { id: 'modules', label: '3. Pilihan Modul Publik' },
  { id: 'seo', label: '4. Optimasi SEO' },
]

const form = ref({
  id: 0,
  nama: '',
  subdomain: '',
  jenis: 'HMPS',
  warna_tema: '#1d4ed8',
  logo: null as string | null,
  slogan: '',
  deskripsi: '',
  deskripsi_lengkap: '',
  hero_title: '',
  hero_subtitle: '',
  email_publik: '',
  instagram: '',
  website_eksternal: '',
  seo_title: '',
  seo_description: '',
  public_website_enabled: true,
  modul_aktif: {
    posts: true,
    agenda: true,
    announcements: true,
    galeri: true,
    documents: true,
    structure: true,
  }
})

const publicUrl = computed(() => {
  return `/organizations/${form.value.subdomain}`
})

const loadOrganization = async () => {
  isLoading.value = true
  try {
    const orgId = authStore.organization_id || (authStore.user as any)?.organization_id || authStore.user?.organization?.id
    if (!orgId) {
      toastStore.error('Organisasi tidak ditemukan.')
      return
    }

    const org = await organizationService.getById(orgId)
    const m = (org.modul_aktif || {}) as Record<string, any>
    form.value = {
      id: org.id,
      nama: org.nama,
      subdomain: org.subdomain,
      jenis: org.jenis,
      warna_tema: org.warna_tema || '#1d4ed8',
      logo: org.logo || null,
      slogan: typeof m.slogan === 'string' ? m.slogan : '',
      deskripsi: typeof m.deskripsi === 'string' ? m.deskripsi : '',
      deskripsi_lengkap: typeof m.deskripsi_lengkap === 'string' ? m.deskripsi_lengkap : '',
      hero_title: typeof m.hero_title === 'string' ? m.hero_title : '',
      hero_subtitle: typeof m.hero_subtitle === 'string' ? m.hero_subtitle : '',
      email_publik: typeof m.email_publik === 'string' ? m.email_publik : '',
      instagram: typeof m.instagram === 'string' ? m.instagram : '',
      website_eksternal: typeof m.website_eksternal === 'string' ? m.website_eksternal : '',
      seo_title: typeof m.seo_title === 'string' ? m.seo_title : '',
      seo_description: typeof m.seo_description === 'string' ? m.seo_description : '',
      public_website_enabled: m.public_website_enabled ?? true,
      modul_aktif: {
        posts: m.posts !== false,
        agenda: m.agenda !== false,
        announcements: m.announcements !== false,
        galeri: m.galeri !== false,
        documents: m.documents !== false,
        structure: m.structure !== false,
      }
    }
  } catch (error: any) {
    toastStore.error('Gagal memuat pengaturan organisasi: ' + error.message)
  } finally {
    isLoading.value = false
  }
}

const handleLogoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.logo = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const saveSettings = async () => {
  if (!form.value.id) return

  isSubmitting.value = true
  try {
    const payloadModul = {
      ...form.value.modul_aktif,
      slogan: form.value.slogan,
      deskripsi: form.value.deskripsi,
      deskripsi_lengkap: form.value.deskripsi_lengkap,
      hero_title: form.value.hero_title,
      hero_subtitle: form.value.hero_subtitle,
      email_publik: form.value.email_publik,
      instagram: form.value.instagram,
      website_eksternal: form.value.website_eksternal,
      seo_title: form.value.seo_title,
      seo_description: form.value.seo_description,
      public_website_enabled: form.value.public_website_enabled,
    }

    await organizationService.update(form.value.id, {
      nama: form.value.nama,
      subdomain: form.value.subdomain,
      jenis: form.value.jenis as any,
      warna_tema: form.value.warna_tema,
      logo: form.value.logo,
      modul_aktif: payloadModul,
    })

    toastStore.success('Pengaturan website dan profil organisasi berhasil disimpan!')
    await loadOrganization()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menyimpan pengaturan.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadOrganization()
})
</script>
