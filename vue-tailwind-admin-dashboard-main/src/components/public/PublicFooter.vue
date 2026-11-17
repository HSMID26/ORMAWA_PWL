<template>
  <footer
    class="bg-[#191C1D] text-[#C6C5CF] pt-14 pb-10 text-xs border-t border-slate-800"
    role="contentinfo"
    :style="footerStyle"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      
      <!-- ═══════════════════════════════════════════════════════════════════════ -->
      <!-- CONTEXT 1: FOOTER WEBSITE ORMAWA / UKM                                  -->
      <!-- ═══════════════════════════════════════════════════════════════════════ -->
      <div v-if="organization" class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
        
        <!-- Col 1: Identity & Profile (4 Cols) -->
        <div class="md:col-span-4 space-y-4">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-white overflow-hidden flex items-center justify-center p-1 shadow-2xs shrink-0">
              <img
                v-if="organization.logo"
                :src="resolveImageUrl(organization.logo) || ''"
                :alt="organization.nama"
                class="h-full w-full object-contain"
              />
              <span v-else class="font-black text-sm text-[#00346F]">
                {{ organization.nama ? organization.nama.substring(0, 2).toUpperCase() : 'OM' }}
              </span>
            </div>
            <div>
              <span class="text-sm font-bold text-white tracking-tight block">
                {{ organization.nama }}
              </span>
              <span class="text-[10px] text-[#A0A0A8] block">
                {{ organization.jenis || 'Organisasi Mahasiswa' }} &bull; Institut Teknologi Indonesia
              </span>
            </div>
          </div>

          <p class="text-[#A0A0A8] text-xs leading-relaxed max-w-sm">
            {{ orgDescription }}
          </p>

          <div v-if="organization.slogan" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/5 border border-white/10 text-[11px] text-slate-300 italic">
            <span>&ldquo;{{ organization.slogan }}&rdquo;</span>
          </div>
        </div>

        <!-- Col 2: Navigation Ormawa (2 Cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Navigasi Ormawa</h4>
          <ul class="space-y-2">
            <li>
              <router-link :to="`/organizations/${organization.subdomain}`" class="hover:text-white transition duration-150">
                Beranda
              </router-link>
            </li>
            <li v-if="organization.modules?.posts !== false">
              <router-link :to="`/organizations/${organization.subdomain}/articles`" class="hover:text-white transition duration-150">
                {{ organization.label_menu?.posts || 'Berita & Artikel' }}
              </router-link>
            </li>
            <li v-if="organization.modules?.agenda !== false">
              <router-link :to="`/organizations/${organization.subdomain}/agenda`" class="hover:text-white transition duration-150">
                {{ organization.label_menu?.agenda || 'Agenda Kegiatan' }}
              </router-link>
            </li>
            <li v-if="organization.modules?.announcements !== false">
              <router-link :to="`/organizations/${organization.subdomain}/announcements`" class="hover:text-white transition duration-150">
                {{ organization.label_menu?.announcements || 'Pengumuman' }}
              </router-link>
            </li>
            <li v-if="organization.modules?.galeri !== false && organization.modules?.gallery !== false">
              <router-link :to="`/organizations/${organization.subdomain}/gallery`" class="hover:text-white transition duration-150">
                {{ organization.label_menu?.galeri || 'Galeri Foto' }}
              </router-link>
            </li>
            <li v-if="organization.modules?.documents !== false && organization.modules?.dokumen !== false">
              <router-link :to="`/organizations/${organization.subdomain}/documents`" class="hover:text-white transition duration-150">
                {{ organization.label_menu?.documents || 'Dokumen Publik' }}
              </router-link>
            </li>
            <li v-if="organization.modules?.structure !== false && organization.modules?.struktur !== false">
              <router-link :to="`/organizations/${organization.subdomain}/structure`" class="hover:text-white transition duration-150">
                {{ organization.label_menu?.structure || 'Struktur Organisasi' }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Col 3: Media Sosial & Tautan Eksternal (3 Cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Media Sosial & Tautan</h4>
          
          <div class="flex flex-wrap gap-2 pt-1">
            <a
              v-if="orgSocials.instagram"
              :href="formatUrl(orgSocials.instagram)"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-pink-600/20 hover:border-pink-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="Instagram"
            >
              <InstagramIcon class="h-3.5 w-3.5 text-pink-400" />
              <span>Instagram</span>
            </a>

            <a
              v-if="orgSocials.facebook"
              :href="formatUrl(orgSocials.facebook)"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-blue-600/20 hover:border-blue-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="Facebook"
            >
              <FacebookIcon class="h-3.5 w-3.5 text-blue-400" />
              <span>Facebook</span>
            </a>

            <a
              v-if="orgSocials.youtube"
              :href="formatUrl(orgSocials.youtube)"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-red-600/20 hover:border-red-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="YouTube"
            >
              <YoutubeIcon class="h-3.5 w-3.5 text-red-400" />
              <span>YouTube</span>
            </a>

            <a
              v-if="orgSocials.tiktok"
              :href="formatUrl(orgSocials.tiktok)"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-cyan-600/20 hover:border-cyan-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="TikTok"
            >
              <VideoIcon class="h-3.5 w-3.5 text-cyan-400" />
              <span>TikTok</span>
            </a>

            <a
              v-if="orgSocials.linkedin"
              :href="formatUrl(orgSocials.linkedin)"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-sky-600/20 hover:border-sky-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="LinkedIn"
            >
              <LinkedinIcon class="h-3.5 w-3.5 text-sky-400" />
              <span>LinkedIn</span>
            </a>

            <a
              v-if="orgSocials.website"
              :href="formatUrl(orgSocials.website)"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-indigo-600/20 hover:border-indigo-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="Website Resmi Organisasi"
            >
              <GlobeIcon class="h-3.5 w-3.5 text-indigo-400" />
              <span>Situs Web</span>
            </a>

            <a
              v-if="orgWhatsAppUrl"
              :href="orgWhatsAppUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-emerald-600/20 hover:border-emerald-500/40 text-slate-300 hover:text-white transition duration-150 text-[11px]"
              title="WhatsApp"
            >
              <MessageCircleIcon class="h-3.5 w-3.5 text-emerald-400" />
              <span>WhatsApp</span>
            </a>
          </div>

          <div class="pt-2 space-y-1.5">
            <router-link to="/" class="text-[#ABC7FF] hover:text-white transition duration-150 font-medium inline-flex items-center gap-1 text-[11px]">
              &larr; Portal Utama ORMAWA ITI
            </router-link>
            <br />
            <router-link to="/organizations" class="text-slate-400 hover:text-white transition duration-150 inline-flex items-center gap-1 text-[11px]">
              Direktori Seluruh Ormawa &rarr;
            </router-link>
          </div>
        </div>

        <!-- Col 4: Contact & Secretariat (3 Cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Sekretariat & Kontak</h4>
          
          <div class="space-y-2 text-[#A0A0A8] text-xs">
            <div class="flex items-start gap-2">
              <MapPinIcon class="h-3.5 w-3.5 text-slate-400 shrink-0 mt-0.5" />
              <p class="leading-relaxed">
                {{ organization.alamat || 'Gedung Pusat Kegiatan Mahasiswa (PKM) ITI, Jl. Raya Puspiptek Serpong, Tangerang Selatan' }}
              </p>
            </div>

            <div v-if="orgEmail" class="flex items-center gap-2">
              <MailIcon class="h-3.5 w-3.5 text-slate-400 shrink-0" />
              <a :href="`mailto:${orgEmail}`" class="hover:text-white transition">
                {{ orgEmail }}
              </a>
            </div>

            <div v-if="orgPhone" class="flex items-center gap-2">
              <PhoneIcon class="h-3.5 w-3.5 text-slate-400 shrink-0" />
              <span>{{ orgPhone }}</span>
            </div>
          </div>

          <div class="pt-2">
            <router-link
              to="/login"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 text-slate-300 hover:text-white text-[11px] transition"
            >
              <UserIcon class="h-3.5 w-3.5 text-slate-400" />
              <span>Login Pengurus {{ organization.nama }}</span>
            </router-link>
          </div>
        </div>

      </div>

      <!-- ═══════════════════════════════════════════════════════════════════════ -->
      <!-- CONTEXT 2: FOOTER WEBSITE PUBLIK UTAMA (PLATFORM FOOTER)                -->
      <!-- ═══════════════════════════════════════════════════════════════════════ -->
      <div v-else class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
        
        <!-- Col 1: Identity & Mission (4 Cols) -->
        <div class="md:col-span-4 space-y-4">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-white overflow-hidden flex items-center justify-center p-0.5 shadow-2xs shrink-0">
              <img src="/images/logo/iti-logo.png" :alt="activePlatformSettings.campusName || 'Institut Teknologi Indonesia'" class="h-full w-full object-contain" />
            </div>
            <div>
              <span class="text-sm font-bold text-white tracking-tight block">ORMAWA ITI</span>
              <span class="text-[10px] text-[#A0A0A8] block">{{ activePlatformSettings.campusName || 'Institut Teknologi Indonesia' }}</span>
            </div>
          </div>
          <p class="text-[#A0A0A8] text-xs leading-relaxed max-w-sm">
            {{ activePlatformSettings.footerDescription || 'Portal publik resmi tata kelola, warta berita, agenda kegiatan, dan dokumen seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.' }}
          </p>

          <!-- Social Links for Platform -->
          <div class="flex flex-wrap items-center gap-2 pt-1">
            <a
              v-if="activePlatformSettings.footerInstagram"
              :href="formatUrl(activePlatformSettings.footerInstagram)"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 hover:bg-pink-600/20 hover:border-pink-500/40 text-slate-300 hover:text-pink-400 flex items-center justify-center transition"
              title="Instagram ITI"
            >
              <InstagramIcon class="h-4 w-4" />
            </a>
            <a
              v-if="activePlatformSettings.footerFacebook"
              :href="formatUrl(activePlatformSettings.footerFacebook)"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 hover:bg-blue-600/20 hover:border-blue-500/40 text-slate-300 hover:text-blue-400 flex items-center justify-center transition"
              title="Facebook ITI"
            >
              <FacebookIcon class="h-4 w-4" />
            </a>
            <a
              v-if="activePlatformSettings.footerYoutube"
              :href="formatUrl(activePlatformSettings.footerYoutube)"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 hover:bg-red-600/20 hover:border-red-500/40 text-slate-300 hover:text-red-400 flex items-center justify-center transition"
              title="YouTube ITI"
            >
              <YoutubeIcon class="h-4 w-4" />
            </a>
            <a
              v-if="activePlatformSettings.footerTiktok"
              :href="formatUrl(activePlatformSettings.footerTiktok)"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 hover:bg-cyan-600/20 hover:border-cyan-500/40 text-slate-300 hover:text-cyan-400 flex items-center justify-center transition"
              title="TikTok ITI"
            >
              <VideoIcon class="h-4 w-4" />
            </a>
            <a
              v-if="platformWhatsappUrl"
              :href="platformWhatsappUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 hover:bg-emerald-600/20 hover:border-emerald-500/40 text-slate-300 hover:text-emerald-400 flex items-center justify-center transition"
              title="WhatsApp Kampus"
            >
              <MessageCircleIcon class="h-4 w-4" />
            </a>
          </div>
        </div>

        <!-- Col 2: Navigation (2 Cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Navigasi Publik</h4>
          <ul class="space-y-2">
            <li><router-link to="/" class="hover:text-white transition duration-150">Beranda</router-link></li>
            <li><router-link to="/organizations" class="hover:text-white transition duration-150">Direktori Ormawa</router-link></li>
            <li><router-link to="/berita" class="hover:text-white transition duration-150">Warta Mahasiswa</router-link></li>
            <li><router-link to="/agenda" class="hover:text-white transition duration-150">Agenda Kegiatan</router-link></li>
            <li><router-link to="/pengumuman" class="hover:text-white transition duration-150">Pengumuman Resmi</router-link></li>
          </ul>
        </div>

        <!-- Col 3: Organization Types (3 Cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Kategori Organisasi</h4>
          <ul class="space-y-2">
            <li><router-link to="/organizations?jenis=HMPS" class="hover:text-white transition duration-150">Himpunan Mahasiswa (HMPS)</router-link></li>
            <li><router-link to="/organizations?jenis=UKM" class="hover:text-white transition duration-150">Unit Kegiatan Mahasiswa (UKM)</router-link></li>
            <li><router-link to="/organizations?jenis=BEM" class="hover:text-white transition duration-150">Badan Eksekutif Mahasiswa (BEM)</router-link></li>
            <li><router-link to="/login" class="hover:text-white transition duration-150">Portal Login Pengurus</router-link></li>
          </ul>
        </div>

        <!-- Col 4: Contact & Campus Address (3 Cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">{{ activePlatformSettings.campusName || 'Institut Teknologi Indonesia' }}</h4>
          <p class="leading-relaxed text-[#A0A0A8] text-xs whitespace-pre-line">
            {{ activePlatformSettings.footerAddress || 'Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314.\nPusat Kemahasiswaan & Alumni (PKA) ITI' }}
          </p>

          <div class="space-y-1 text-[#A0A0A8] text-xs pt-1">
            <div v-if="activePlatformSettings.footerEmail" class="flex items-center gap-1.5">
              <MailIcon class="h-3.5 w-3.5 text-slate-400 shrink-0" />
              <a :href="`mailto:${activePlatformSettings.footerEmail}`" class="hover:text-white transition">
                {{ activePlatformSettings.footerEmail }}
              </a>
            </div>

            <div v-if="activePlatformSettings.footerPhone" class="flex items-center gap-1.5">
              <PhoneIcon class="h-3.5 w-3.5 text-slate-400 shrink-0" />
              <span>{{ activePlatformSettings.footerPhone }}</span>
            </div>
          </div>

          <div class="pt-1">
            <a
              :href="formatUrl(activePlatformSettings.footerWebsite || 'https://iti.ac.id')"
              target="_blank"
              rel="noopener"
              class="text-[#ABC7FF] hover:text-white transition duration-150 font-semibold inline-flex items-center gap-1"
            >
              Situs Resmi ITI &rarr;
            </a>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════════════ -->
      <!-- BOTTOM COPYRIGHT STRIP                                                  -->
      <!-- ═══════════════════════════════════════════════════════════════════════ -->
      <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-3 text-[11px] text-[#737783]">
        <p>
          <template v-if="organization">
            &copy; {{ currentYear }} {{ organization.copyright || `${organization.nama}. Hak Cipta Dilindungi.` }}
          </template>
          <template v-else>
            &copy; {{ currentYear }} {{ activePlatformSettings.footerCopyright || `${activePlatformSettings.campusName || 'Institut Teknologi Indonesia'}. Hak Cipta Dilindungi.` }}
          </template>
        </p>
        <p>
          <template v-if="organization">
            ORMAWA {{ activePlatformSettings.campusName || 'Institut Teknologi Indonesia' }}
          </template>
          <template v-else>
            Sistem Informasi Manajemen Organisasi Kemahasiswaan
          </template>
        </p>
      </div>

    </div>
  </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { resolveImageUrl } from '@/utils/imageUrl'
import { publicService } from '@/services/publicService'
import type { PublicOrganization, PlatformSettings } from '@/types/public'
import {
  MapPinIcon,
  MailIcon,
  PhoneIcon,
  GlobeIcon,
  InstagramIcon,
  FacebookIcon,
  YoutubeIcon,
  VideoIcon,
  LinkedinIcon,
  MessageCircleIcon,
  UserIcon,
} from 'lucide-vue-next'

const props = defineProps<{
  organization?: PublicOrganization | null
  platformSettings?: PlatformSettings | null
}>()

const currentYear = new Date().getFullYear()
const localPlatformSettings = ref<PlatformSettings | null>(null)

const defaultPlatformSettings: PlatformSettings = {
  campusName: 'Institut Teknologi Indonesia',
  footerDescription: 'Portal publik resmi tata kelola, warta berita, agenda kegiatan, dan dokumen seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.',
  footerAddress: 'Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314.\nPusat Kemahasiswaan & Alumni (PKA) ITI',
  footerEmail: 'pka@iti.ac.id',
  footerPhone: '021-7561092',
  footerWhatsapp: '081234567890',
  footerInstagram: 'https://instagram.com/iti_official',
  footerFacebook: 'https://facebook.com/itiofficial',
  footerYoutube: 'https://youtube.com/@itiofficial',
  footerTiktok: 'https://tiktok.com/@iti_official',
  footerWebsite: 'https://iti.ac.id',
  footerCopyright: 'Institut Teknologi Indonesia. Hak Cipta Dilindungi.',
}

const activePlatformSettings = computed<PlatformSettings>(() => {
  return props.platformSettings || localPlatformSettings.value || defaultPlatformSettings
})

const footerStyle = computed(() => {
  if (props.organization?.warna_tema) {
    return {
      '--org-primary': props.organization.warna_tema,
    }
  }
  return {}
})

// Organization specific computed properties
const orgDescription = computed(() => {
  if (!props.organization) return ''
  return (
    props.organization.footer_description ||
    props.organization.deskripsi ||
    props.organization.slogan ||
    `Portal publik resmi tata kelola, warta kegiatan, dan dokumentasi ${props.organization.nama} Institut Teknologi Indonesia.`
  )
})

const orgSocials = computed(() => {
  const org = props.organization
  if (!org) return {}
  const ms = (org.media_sosial || {}) as Record<string, any>
  return {
    instagram: org.instagram || ms.instagram || null,
    facebook: org.facebook || ms.facebook || null,
    youtube: org.youtube || ms.youtube || null,
    tiktok: org.tiktok || ms.tiktok || null,
    linkedin: org.linkedin || ms.linkedin || null,
    website: org.website_eksternal || ms.website || null,
    whatsapp: org.whatsapp || ms.whatsapp || null,
  }
})

const orgEmail = computed(() => {
  if (!props.organization) return null
  return props.organization.email || props.organization.email_publik || null
})

const orgPhone = computed(() => {
  if (!props.organization) return null
  return props.organization.telepon || orgSocials.value.whatsapp || null
})

const orgWhatsAppUrl = computed(() => {
  const wa = orgSocials.value.whatsapp || props.organization?.telepon
  if (!wa) return null
  if (wa.startsWith('http://') || wa.startsWith('https://')) return wa
  const clean = wa.replace(/[^0-9]/g, '')
  const phone = clean.startsWith('0') ? '62' + clean.slice(1) : clean
  return `https://wa.me/${phone}`
})

const platformWhatsappUrl = computed(() => {
  const wa = activePlatformSettings.value.footerWhatsapp || activePlatformSettings.value.footerPhone
  if (!wa) return null
  if (wa.startsWith('http://') || wa.startsWith('https://')) return wa
  const clean = wa.replace(/[^0-9]/g, '')
  const phone = clean.startsWith('0') ? '62' + clean.slice(1) : clean
  return `https://wa.me/${phone}`
})

const formatUrl = (url?: string | null) => {
  if (!url) return '#'
  if (url.startsWith('http://') || url.startsWith('https://')) return url
  return `https://${url}`
}

onMounted(async () => {
  if (!props.organization && !props.platformSettings) {
    try {
      localPlatformSettings.value = await publicService.getPlatformSettings()
    } catch {
      // Fallback defaults used
    }
  }
})
</script>
