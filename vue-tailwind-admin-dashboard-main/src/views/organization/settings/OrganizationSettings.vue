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
          Pusat kendali resmi (Single Source of Truth) untuk identitas, branding visual, modul publik, dan SEO ormawa.
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
            'rounded-xl px-4 py-2 text-xs font-semibold transition cursor-pointer',
            activeTab === tab.id
              ? 'bg-brand-600 text-white shadow-xs'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab 1: Identitas Organisasi & Kontak -->
      <IdentitySettings
        v-show="activeTab === 'identity'"
        :form="form"
        :logo-preview="logoPreview"
        @logo-selected="handleLogoSelect"
        @remove-logo="removeLogo"
      />

      <!-- Tab 2: Website Publik & Branding Visual -->
      <BrandingSettings
        v-show="activeTab === 'branding'"
        :form="form"
        :hero-preview="heroPreview"
        :live-preview-hero-style="livePreviewHeroStyle"
        @hero-selected="handleHeroSelect"
        @remove-hero="removeHeroImage"
      />

      <!-- Tab 3: Konfigurasi Modul Publik & Label Menu -->
      <FeatureModuleSettings
        v-show="activeTab === 'modules'"
        :form="form"
      />

      <!-- Tab 4: Kustomisasi Footer Ormawa -->
      <FooterSettings
        v-show="activeTab === 'footer'"
        :form="form"
        :logo-preview="logoPreview"
      />

      <!-- Tab 5: Pengaturan SEO & Analytics -->
      <SEOSettings
        v-show="activeTab === 'seo'"
        :form="form"
      />

      <!-- Bottom Save Action Bar -->
      <div class="flex items-center justify-between border-t border-gray-200 pt-5 dark:border-gray-800">
        <span v-if="hasPendingUploads" class="text-xs text-amber-600 font-semibold dark:text-amber-400 flex items-center gap-1.5">
          <AlertCircleIcon class="h-4 w-4" />
          Terdapat berkas gambar baru yang siap disimpan.
        </span>
        <span v-else class="text-xs text-gray-400">
          Perubahan akan langsung diterapkan ke website publik setelah disimpan.
        </span>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-xs disabled:opacity-50 transition cursor-pointer"
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
import { resolveImageUrl } from '@/utils/imageUrl'
import {
  SettingsIcon,
  SaveIcon,
  ExternalLinkIcon,
  AlertCircleIcon,
} from 'lucide-vue-next'

import type { OrganizationSettingsForm } from './components/types'
import IdentitySettings from './components/IdentitySettings.vue'
import BrandingSettings from './components/BrandingSettings.vue'
import FeatureModuleSettings from './components/FeatureModuleSettings.vue'
import FooterSettings from './components/FooterSettings.vue'
import SEOSettings from './components/SEOSettings.vue'

const authStore = useAuthStore()
const toastStore = useToastStore()

const isLoading = ref(true)
const isSubmitting = ref(false)
const activeTab = ref('identity')

const tabs = [
  { id: 'identity', label: '1. Identitas Organisasi & Kontak' },
  { id: 'branding', label: '2. Website & Branding' },
  { id: 'modules', label: '3. Pilihan Modul & Label Menu' },
  { id: 'footer', label: '4. Kustomisasi Footer Ormawa' },
  { id: 'seo', label: '5. Optimasi SEO & Analytics' },
]

// Pending file uploads
const selectedLogoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)

const selectedHeroFile = ref<File | null>(null)
const heroPreview = ref<string | null>(null)

const form = ref<OrganizationSettingsForm>({
  id: 0,
  nama: '',
  subdomain: '',
  jenis: 'HMPS',
  warna_tema: '#1d4ed8',
  logo: null,
  hero_image: null,
  slogan: '',
  deskripsi: '',
  deskripsi_lengkap: '',
  hero_title: '',
  hero_subtitle: '',
  email: '',
  telepon: '',
  alamat: '',
  copyright: '',
  footer_description: '',
  media_sosial: {
    instagram: '',
    facebook: '',
    youtube: '',
    tiktok: '',
    linkedin: '',
    website: '',
    whatsapp: '',
  },
  label_menu: {
    posts: '',
    agenda: '',
    announcements: '',
    galeri: '',
    documents: '',
    structure: '',
  },
  seo_title: '',
  seo_description: '',
  ga_tracking_id: '',
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

const hasPendingUploads = computed(() => {
  return !!selectedLogoFile.value || !!selectedHeroFile.value
})

const publicUrl = computed(() => {
  return `/organizations/${form.value.subdomain}`
})

const livePreviewHeroStyle = computed(() => {
  const bg = heroPreview.value || resolveImageUrl(form.value.hero_image)
  if (bg) {
    return {
      backgroundImage: `url("${bg}")`,
      backgroundColor: form.value.warna_tema || '#00346F'
    }
  }
  return {
    backgroundColor: form.value.warna_tema || '#00346F'
  }
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
    const labels = (org.label_menu || {}) as Record<string, string>
    const socials = (org.media_sosial || {}) as Record<string, string>

    form.value = {
      id: org.id,
      nama: org.nama,
      subdomain: org.subdomain,
      jenis: org.jenis,
      warna_tema: org.warna_tema || '#1d4ed8',
      logo: org.logo || null,
      hero_image: (org as any).hero_image || m.hero_image || null,
      slogan: typeof m.slogan === 'string' ? m.slogan : '',
      deskripsi: typeof m.deskripsi === 'string' ? m.deskripsi : '',
      deskripsi_lengkap: typeof m.deskripsi_lengkap === 'string' ? m.deskripsi_lengkap : '',
      hero_title: typeof m.hero_title === 'string' ? m.hero_title : '',
      hero_subtitle: typeof m.hero_subtitle === 'string' ? m.hero_subtitle : '',
      email: (org as any).email || (typeof m.email_publik === 'string' ? m.email_publik : ''),
      telepon: (org as any).telepon || '',
      alamat: (org as any).alamat || '',
      copyright: typeof m.copyright === 'string' ? m.copyright : (typeof m.footer_copyright === 'string' ? m.footer_copyright : ''),
      footer_description: typeof m.footer_description === 'string' ? m.footer_description : '',
      media_sosial: {
        instagram: socials.instagram || (typeof m.instagram === 'string' ? m.instagram : ''),
        facebook: socials.facebook || (typeof m.facebook === 'string' ? m.facebook : ''),
        youtube: socials.youtube || '',
        tiktok: socials.tiktok || '',
        linkedin: socials.linkedin || '',
        website: socials.website || (typeof m.website_eksternal === 'string' ? m.website_eksternal : ''),
        whatsapp: socials.whatsapp || '',
      },
      label_menu: {
        posts: labels.posts || labels.berita || '',
        agenda: labels.agenda || labels.kegiatan || '',
        announcements: labels.announcements || labels.pengumuman || '',
        galeri: labels.galeri || labels.gallery || '',
        documents: labels.documents || labels.dokumen || '',
        structure: labels.structure || labels.struktur || '',
      },
      seo_title: typeof m.seo_title === 'string' ? m.seo_title : '',
      seo_description: typeof m.seo_description === 'string' ? m.seo_description : '',
      ga_tracking_id: (org as any).ga_tracking_id || '',
      public_website_enabled: m.public_website_enabled !== false,
      modul_aktif: {
        posts: m.posts !== false && m.articles !== false,
        agenda: m.agenda !== false,
        announcements: m.announcements !== false && m.pengumuman !== false,
        galeri: m.galeri !== false && m.gallery !== false,
        documents: m.documents !== false && m.dokumen !== false,
        structure: m.structure !== false && m.struktur !== false,
      }
    }

    selectedLogoFile.value = null
    logoPreview.value = null
    selectedHeroFile.value = null
    heroPreview.value = null
  } catch (error: any) {
    toastStore.error('Gagal memuat pengaturan organisasi: ' + error.message)
  } finally {
    isLoading.value = false
  }
}

const handleLogoSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    selectedLogoFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeLogo = () => {
  selectedLogoFile.value = null
  logoPreview.value = null
  form.value.logo = null
}

const handleHeroSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    selectedHeroFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      heroPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeHeroImage = async () => {
  selectedHeroFile.value = null
  heroPreview.value = null
  form.value.hero_image = null

  if (form.value.id) {
    try {
      await organizationService.removeHero(form.value.id)
      toastStore.success('Foto hero berhasil dihapus.')
    } catch (e: any) {
      // Ignored if unsaved
    }
  }
}

const saveSettings = async () => {
  if (!form.value.id) return

  isSubmitting.value = true
  try {
    // 1. Upload logo if newly selected
    if (selectedLogoFile.value) {
      const logoRes = await organizationService.uploadLogo(form.value.id, selectedLogoFile.value)
      form.value.logo = (logoRes as any).logo || form.value.logo
      selectedLogoFile.value = null
      logoPreview.value = null
    }

    // 2. Upload hero if newly selected
    if (selectedHeroFile.value) {
      const heroRes = await organizationService.uploadHero(form.value.id, selectedHeroFile.value)
      form.value.hero_image = (heroRes as any).hero_image || form.value.hero_image
      selectedHeroFile.value = null
      heroPreview.value = null
    }

    // 3. Save standard settings
    const payloadModul = {
      ...form.value.modul_aktif,
      slogan: form.value.slogan,
      deskripsi: form.value.deskripsi,
      deskripsi_lengkap: form.value.deskripsi_lengkap,
      hero_title: form.value.hero_title,
      hero_subtitle: form.value.hero_subtitle,
      hero_image: form.value.hero_image,
      email_publik: form.value.email,
      instagram: form.value.media_sosial.instagram,
      facebook: form.value.media_sosial.facebook,
      youtube: form.value.media_sosial.youtube,
      tiktok: form.value.media_sosial.tiktok,
      linkedin: form.value.media_sosial.linkedin,
      website_eksternal: form.value.media_sosial.website,
      copyright: form.value.copyright,
      footer_copyright: form.value.copyright,
      footer_description: form.value.footer_description,
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
      hero_image: form.value.hero_image,
      email: form.value.email,
      telepon: form.value.telepon,
      alamat: form.value.alamat,
      media_sosial: form.value.media_sosial,
      label_menu: form.value.label_menu,
      ga_tracking_id: form.value.ga_tracking_id,
      modul_aktif: payloadModul,
    } as any)

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
