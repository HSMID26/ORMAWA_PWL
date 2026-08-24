<template>
  <div class="space-y-10">
    <!-- ─── 01. ORMAWA HERO BANNER (CLEAN & SUBTLE ACCENT) ────────── -->
    <section class="border-b border-slate-200 py-10 sm:py-14 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          
          <div class="lg:col-span-8 space-y-3.5 text-left">
            <div class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
              <span :style="{ backgroundColor: organization.warna_tema || '#0f172a' }" class="h-2 w-2 rounded-full"></span>
              <span>{{ organization.jenis }}</span>
              <span>&bull;</span>
              <span>{{ organization.current_period?.period_name || 'Periode Aktif' }}</span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-bold text-slate-950 tracking-tight leading-tight">
              {{ organization.hero_title || organization.nama }}
            </h1>

            <p class="text-sm sm:text-base text-slate-600 max-w-2xl leading-relaxed">
              {{ organization.hero_subtitle || organization.slogan || organization.deskripsi || 'Website resmi organisasi kemahasiswaan Institut Teknologi Indonesia.' }}
            </p>

            <div class="flex flex-wrap gap-3 pt-1">
              <router-link
                :to="`/organizations/${organization.subdomain}/profil`"
                :style="{ backgroundColor: organization.warna_tema || '#0f172a' }"
                class="rounded-lg px-4 py-2 text-xs font-semibold text-white hover:opacity-90 transition"
              >
                Tentang Kami
              </router-link>
              <router-link
                v-if="organization.modules?.posts !== false"
                :to="`/organizations/${organization.subdomain}/articles`"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
              >
                Lihat Warta
              </router-link>
            </div>
          </div>

          <!-- Official Info Badge -->
          <div class="lg:col-span-4 flex justify-start lg:justify-end">
            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-5 space-y-3 max-w-xs w-full">
              <div class="h-14 w-14 rounded-lg border border-slate-200 bg-white flex items-center justify-center overflow-hidden">
                <img v-if="organization.logo" :src="organization.logo" :alt="organization.nama" class="h-full w-full object-cover" />
                <span v-else class="text-lg font-bold text-slate-900">{{ organization.nama.charAt(0) }}</span>
              </div>
              <div>
                <h3 class="text-xs font-bold text-slate-950">{{ organization.nama }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ organization.email_publik || `${organization.subdomain}@iti.ac.id` }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ─── 02. EDITORIAL 2-COLUMN PROFILE OVERVIEW ───────────────── -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="border-b border-slate-200 pb-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Description -->
        <div class="lg:col-span-8 space-y-2">
          <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Profil & Visi</span>
          <h2 class="text-lg font-bold text-slate-950">Tentang Organisasi</h2>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pt-1 whitespace-pre-line">
            {{ organization.deskripsi_lengkap || organization.deskripsi || `${organization.nama} merupakan organisasi kemahasiswaan resmi di Institut Teknologi Indonesia yang berdedikasi dalam pengembangan potensi, kepemimpinan, dan keilmuan mahasiswa.` }}
          </p>
        </div>

        <!-- Right Column: Key Details -->
        <div class="lg:col-span-4 space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Informasi Pokok</span>
          <div class="space-y-2 divide-y divide-slate-200/60">
            <div class="pt-1 flex justify-between">
              <span class="text-slate-500">Jenis:</span>
              <span class="font-semibold text-slate-900">{{ organization.jenis }}</span>
            </div>
            <div class="pt-2 flex justify-between">
              <span class="text-slate-500">Periode:</span>
              <span class="font-semibold text-slate-900">{{ organization.current_period?.period_name || 'Aktif' }}</span>
            </div>
            <div class="pt-2 flex justify-between">
              <span class="text-slate-500">Email:</span>
              <span class="font-semibold text-slate-900">{{ organization.email_publik || '-' }}</span>
            </div>
            <div class="pt-2 flex justify-between">
              <span class="text-slate-500">Instagram:</span>
              <span class="font-semibold text-slate-900">{{ organization.instagram || '-' }}</span>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ─── 03. LATEST PUBLISHED ARTICLES ─────────────────────────── -->
    <section v-if="organization.modules?.posts !== false" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
      <div class="flex items-end justify-between border-b border-slate-200 pb-3">
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Publikasi</span>
          <h2 class="text-lg font-bold text-slate-950 mt-0.5">Warta & Berita Terbaru</h2>
        </div>
        <router-link :to="`/organizations/${organization.subdomain}/articles`" class="text-xs font-semibold text-blue-900 hover:underline">
          Semua Warta &rarr;
        </router-link>
      </div>

      <div v-if="isArticlesLoading" class="py-8 text-center text-xs text-slate-500">Memuat artikel...</div>
      
      <div v-else-if="articles.length === 0" class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-xs text-slate-500">
        Belum ada warta yang dipublikasikan saat ini.
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <article
          v-for="post in articles.slice(0, 3)"
          :key="post.id"
          class="rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 transition flex flex-col group"
        >
          <router-link :to="`/organizations/${organization.subdomain}/articles/${post.slug}`" class="block h-40 bg-slate-100 overflow-hidden relative">
            <img v-if="post.cover_image" :src="post.cover_image" :alt="post.judul" class="h-full w-full object-cover group-hover:scale-102 transition" />
            <div v-else class="h-full w-full flex items-center justify-center text-slate-400 font-bold text-xs bg-slate-100">
              {{ organization.nama }}
            </div>
            <span v-if="post.category" class="absolute top-2.5 left-2.5 rounded bg-white/95 px-2 py-0.5 text-[9px] font-bold text-slate-900 shadow-xs">
              {{ post.category.name }}
            </span>
          </router-link>

          <div class="p-4 flex flex-col justify-between flex-grow space-y-2">
            <div>
              <h3 class="text-xs sm:text-sm font-bold text-slate-950 group-hover:text-blue-900 transition line-clamp-2 leading-snug">
                <router-link :to="`/organizations/${organization.subdomain}/articles/${post.slug}`">
                  {{ post.judul }}
                </router-link>
              </h3>
              <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">{{ post.excerpt }}</p>
            </div>
            <div class="pt-2 border-t border-slate-100 text-[10px] text-slate-400 flex justify-between font-medium">
              <span>{{ new Date(post.published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</span>
              <span class="text-slate-600">{{ post.author.name }}</span>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- ─── 04. UPCOMING AGENDA & ANNOUNCEMENTS ────────────────────── -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Agenda -->
        <div v-if="organization.modules?.agenda !== false" class="space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <h3 class="text-sm font-bold text-slate-950">Agenda Terdekat</h3>
            <router-link :to="`/organizations/${organization.subdomain}/agenda`" class="text-xs font-semibold text-blue-900">Lihat Kalender</router-link>
          </div>
          <div v-if="agendaList.length === 0" class="rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-500">
            Tidak ada agenda dalam waktu dekat.
          </div>
          <div v-else class="space-y-2.5">
            <div v-for="act in agendaList.slice(0, 3)" :key="act.id" class="rounded-lg border border-slate-200 bg-white p-3.5 space-y-1">
              <div class="flex justify-between items-center text-[10px] text-slate-500">
                <span class="font-bold text-slate-800">{{ act.tanggal_pelaksanaan ? new Date(act.tanggal_pelaksanaan).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-' }}</span>
                <span>{{ act.tempat }}</span>
              </div>
              <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ act.judul }}</h4>
            </div>
          </div>
        </div>

        <!-- Announcements -->
        <div v-if="organization.modules?.announcements !== false" class="space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <h3 class="text-sm font-bold text-slate-950">Pengumuman Resmi</h3>
            <router-link :to="`/organizations/${organization.subdomain}/announcements`" class="text-xs font-semibold text-blue-900">Semua</router-link>
          </div>
          <div v-if="announcementsList.length === 0" class="rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-500">
            Belum ada pengumuman resmi.
          </div>
          <div v-else class="space-y-2.5">
            <div v-for="ann in announcementsList.slice(0, 3)" :key="ann.id" class="rounded-lg border border-slate-200 bg-white p-3.5 space-y-1">
              <div class="flex justify-between items-center text-[10px]">
                <span class="rounded bg-amber-100 text-amber-900 px-1.5 py-0.2 font-bold uppercase text-[9px]">{{ ann.priority }}</span>
                <span class="text-slate-400">{{ new Date(ann.published_at || '').toLocaleDateString('id-ID') }}</span>
              </div>
              <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ ann.title }}</h4>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicArticle, PublicAgenda, PublicAnnouncement } from '@/types/public'

const props = defineProps<{
  organization: PublicOrganization
}>()

const articles = ref<PublicArticle[]>([])
const agendaList = ref<PublicAgenda[]>([])
const announcementsList = ref<PublicAnnouncement[]>([])
const isArticlesLoading = ref(true)

useSeoMeta(() => ({
  title: props.organization.seo_title || props.organization.nama,
  description: props.organization.seo_description || props.organization.deskripsi || `Website resmi ${props.organization.nama}`,
  ogImage: props.organization.og_image || props.organization.logo || undefined,
  ogType: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: props.organization.nama,
    url: window.location.href,
    logo: props.organization.logo,
    description: props.organization.deskripsi,
  }
}))

const loadPreviews = async () => {
  const slug = props.organization.subdomain
  try {
    const [artRes, agRes, anRes] = await Promise.all([
      publicService.getArticles(slug, { per_page: 3 }).catch(() => ({ data: [] })),
      publicService.getAgenda(slug, { tab: 'upcoming', per_page: 3 }).catch(() => ({ data: [] })),
      publicService.getAnnouncements(slug).catch(() => []),
    ])
    articles.value = (artRes as any).data || []
    agendaList.value = (agRes as any).data || []
    announcementsList.value = (anRes as any) || []
  } catch (err) {
    console.error('Failed to load preview data:', err)
  } finally {
    isArticlesLoading.value = false
  }
}

onMounted(() => {
  loadPreviews()
})
</script>
