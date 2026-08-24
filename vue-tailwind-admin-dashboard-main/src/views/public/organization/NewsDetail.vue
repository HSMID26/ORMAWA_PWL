<template>
  <div class="min-h-screen bg-white text-slate-900 font-sans selection:bg-slate-900 selection:text-white">
    
    <!-- ─── 00. READING PROGRESS BAR ───────────────────────────── -->
    <div
      class="fixed top-0 left-0 h-1 bg-blue-900 z-50 transition-all duration-150"
      :style="{ width: `${readingProgress}%` }"
      aria-hidden="true"
    ></div>

    <!-- Toast Notification -->
    <Teleport to="body">
      <transition
        enter-active-class="transition ease-out duration-200 transform"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150 transform"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
      >
        <div
          v-if="toastMessage"
          class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-xl flex items-center gap-2 border border-slate-700"
          role="status"
          aria-live="polite"
        >
          <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ toastMessage }}</span>
        </div>
      </transition>
    </Teleport>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
      <div class="h-4 w-48 bg-slate-100 rounded animate-pulse"></div>
      <div class="h-12 w-full bg-slate-100 rounded-xl animate-pulse"></div>
      <div class="h-6 w-3/4 bg-slate-100 rounded animate-pulse"></div>
      <div class="h-80 w-full bg-slate-100 rounded-2xl animate-pulse"></div>
      <div class="space-y-3 pt-6">
        <div class="h-4 w-full bg-slate-100 rounded animate-pulse"></div>
        <div class="h-4 w-5/6 bg-slate-100 rounded animate-pulse"></div>
        <div class="h-4 w-4/6 bg-slate-100 rounded animate-pulse"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="!article" class="max-w-xl mx-auto my-20 p-8 text-center border border-dashed border-slate-200 rounded-2xl space-y-4">
      <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-800 mx-auto flex items-center justify-center font-bold text-base">
        404
      </div>
      <h1 class="text-xl font-bold text-slate-950">Warta Tidak Ditemukan</h1>
      <p class="text-xs text-slate-500 leading-relaxed">
        Artikel yang Anda cari tidak tersedia, berstatus draft, atau tautan telah kedaluwarsa.
      </p>
      <router-link
        :to="`/organizations/${subdomain}/articles`"
        class="inline-block rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition"
      >
        &larr; Kembali ke Daftar Berita
      </router-link>
    </div>

    <!-- Article Content -->
    <article v-else class="pb-16">
      
      <!-- ─── 01. EDITORIAL ARTICLE HERO ───────────────────────────── -->
      <header class="bg-slate-50/70 border-b border-slate-200/80 pt-8 pb-10 sm:pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
          
          <!-- Breadcrumb & Back -->
          <div class="flex items-center justify-between gap-4 text-xs">
            <nav class="flex items-center gap-1.5 text-slate-500 overflow-hidden text-ellipsis whitespace-nowrap" aria-label="Breadcrumb">
              <router-link to="/" class="hover:text-slate-900 transition">Beranda</router-link>
              <span>/</span>
              <router-link to="/organizations" class="hover:text-slate-900 transition">Ormawa</router-link>
              <span>/</span>
              <router-link :to="`/organizations/${subdomain}`" class="hover:text-slate-900 transition">{{ orgName }}</router-link>
              <span>/</span>
              <router-link :to="`/organizations/${subdomain}/articles`" class="hover:text-slate-900 transition">Berita</router-link>
            </nav>

            <router-link
              :to="`/organizations/${subdomain}/articles`"
              class="hidden sm:inline-flex items-center gap-1 text-slate-600 hover:text-blue-900 font-semibold transition shrink-0"
            >
              &larr; Semua Warta
            </router-link>
          </div>

          <!-- Category Badge & Organization -->
          <div class="flex items-center gap-2 pt-1">
            <span
              v-if="article.category"
              class="rounded-md bg-blue-900 text-white px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider shadow-2xs"
            >
              {{ article.category.name }}
            </span>
            <span class="text-xs font-bold text-slate-700">
              {{ orgName }}
            </span>
          </div>

          <!-- Headline H1 -->
          <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-slate-950 tracking-tight leading-[1.14]">
            {{ article.judul }}
          </h1>

          <!-- Excerpt / Subtitle -->
          <p v-if="article.excerpt" class="text-sm sm:text-lg text-slate-600 leading-relaxed font-normal pt-1 max-w-3xl">
            {{ article.excerpt }}
          </p>

          <!-- Metadata Horizontal Strip -->
          <div class="pt-3 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-3">
              <div class="h-9 w-9 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center font-bold text-xs text-slate-800 shrink-0">
                {{ article.author.name.charAt(0) }}
              </div>
              <div>
                <span class="font-bold text-slate-900 block leading-tight">{{ article.author.name }}</span>
                <span class="text-[11px] text-slate-500">Kontributor Redaksi &bull; {{ orgName }}</span>
              </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 text-[11px] font-medium">
              <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formattedDate }}
              </span>
              <span>&bull;</span>
              <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ readingTime }}
              </span>
            </div>
          </div>

        </div>
      </header>

      <!-- ─── 02. MAIN CONTENT AREA ─────────────────────────────────── -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Featured Hero Image -->
        <div class="max-w-4xl mx-auto mb-10">
          <div v-if="article.cover_image" class="rounded-xl sm:rounded-2xl overflow-hidden max-h-[500px] w-full bg-slate-100 border border-slate-200/80 shadow-xs">
            <img
              :src="article.cover_image"
              :alt="article.judul"
              class="w-full h-full object-cover"
              loading="eager"
            />
          </div>
        </div>

        <!-- Two-Column Editorial Composition on Desktop -->
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10">
          
          <!-- Main Reading Column (8 Cols) -->
          <div class="lg:col-span-8 space-y-8">
            
            <!-- Quick Share Action Bar -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 text-xs">
              <span class="text-slate-400 font-medium">Bagikan warta:</span>
              <div class="flex items-center gap-2">
                <button
                  @click="shareWhatsApp"
                  class="rounded-md bg-emerald-50 text-emerald-800 hover:bg-emerald-100 px-2.5 py-1 font-semibold transition"
                  aria-label="Bagikan via WhatsApp"
                >
                  WhatsApp
                </button>
                <button
                  @click="shareTwitter"
                  class="rounded-md bg-sky-50 text-sky-800 hover:bg-sky-100 px-2.5 py-1 font-semibold transition"
                  aria-label="Bagikan via X"
                >
                  X (Twitter)
                </button>
                <button
                  @click="copyArticleLink"
                  class="rounded-md bg-slate-100 text-slate-800 hover:bg-slate-200 px-2.5 py-1 font-semibold transition flex items-center gap-1"
                  aria-label="Salin tautan artikel"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  Salin Tautan
                </button>
              </div>
            </div>

            <!-- Rich Body Text (Editorial Academic Typography) -->
            <div
              class="article-body text-[17px] sm:text-[18px] text-slate-800 leading-[1.8] space-y-6"
              v-html="article.konten"
            ></div>

            <!-- Tags Section -->
            <div v-if="article.tags && article.tags.length > 0" class="pt-6 border-t border-slate-200 space-y-2">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Topik Terkait</span>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="tag in article.tags"
                  :key="tag.id"
                  class="rounded-lg bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition"
                >
                  #{{ tag.name }}
                </span>
              </div>
            </div>

            <!-- Author Card -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-5 flex items-center gap-4">
              <div class="h-12 w-12 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center font-bold text-sm text-slate-900 shrink-0">
                {{ article.author.name.charAt(0) }}
              </div>
              <div class="space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-900">Penulis Artikel</span>
                <h3 class="text-sm font-bold text-slate-950">{{ article.author.name }}</h3>
                <p class="text-xs text-slate-500">
                  Kontributor resmi publikasi {{ orgName }} &bull; Institut Teknologi Indonesia
                </p>
              </div>
            </div>

            <!-- Bottom Share CTA -->
            <div class="pt-4 flex items-center justify-between text-xs">
              <router-link
                :to="`/organizations/${subdomain}/articles`"
                class="text-blue-900 hover:underline font-semibold"
              >
                &larr; Lihat Semua Warta {{ orgName }}
              </router-link>

              <div class="flex items-center gap-2">
                <button
                  @click="copyArticleLink"
                  class="rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 transition flex items-center gap-1.5"
                >
                  <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  Salin Tautan
                </button>
              </div>
            </div>

          </div>

          <!-- Editorial Sidebar Column (4 Cols) -->
          <aside class="lg:col-span-4 space-y-6">
            <div class="sticky top-24 space-y-6">
              
              <!-- Organization Mini Card -->
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-5 space-y-3">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-lg border border-slate-200 bg-white flex items-center justify-center font-bold text-xs text-slate-900 overflow-hidden shrink-0">
                    <img v-if="organization?.logo" :src="organization.logo" :alt="orgName" class="h-full w-full object-cover" />
                    <span v-else>{{ orgName.charAt(0) }}</span>
                  </div>
                  <div>
                    <h3 class="text-xs font-bold text-slate-950 leading-tight">{{ orgName }}</h3>
                    <span class="text-[10px] text-slate-500">{{ organization?.jenis || 'Ormawa' }} &bull; ITI</span>
                  </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                  {{ organization?.slogan || organization?.deskripsi || 'Organisasi kemahasiswaan resmi Institut Teknologi Indonesia.' }}
                </p>
                <router-link
                  :to="`/organizations/${subdomain}/profil`"
                  class="block text-center rounded-lg bg-white border border-slate-200 hover:bg-slate-50 py-1.5 text-xs font-semibold text-slate-800 transition shadow-2xs"
                >
                  Kunjungi Profil Organisasi &rarr;
                </router-link>
              </div>

              <!-- Related Stories in Sidebar -->
              <div v-if="article.related_articles && article.related_articles.length > 0" class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 pb-2 border-b border-slate-100">
                  Warta Terkait
                </h3>
                <div class="space-y-4 divide-y divide-slate-100">
                  <div
                    v-for="(rel, idx) in article.related_articles"
                    :key="rel.id"
                    :class="[idx > 0 ? 'pt-3.5' : '', 'space-y-1.5 group']"
                  >
                    <span v-if="rel.category" class="text-[9px] font-bold text-blue-900 uppercase">
                      {{ rel.category.name }}
                    </span>
                    <h4 class="text-xs font-bold text-slate-950 group-hover:text-blue-900 transition line-clamp-2 leading-snug">
                      <router-link :to="`/organizations/${subdomain}/articles/${rel.slug}`">
                        {{ rel.judul }}
                      </router-link>
                    </h4>
                    <span class="text-[10px] text-slate-400 block">
                      {{ new Date(rel.published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}
                    </span>
                  </div>
                </div>
              </div>

            </div>
          </aside>

        </div>

        <!-- ─── 03. RELATED ARTICLES BOTTOM SECTION ─────────────────── -->
        <section v-if="article.related_articles && article.related_articles.length > 0" class="max-w-5xl mx-auto pt-14 mt-12 border-t border-slate-200 space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Rekomendasi Publikasi</span>
              <h2 class="text-xl font-bold text-slate-950 mt-0.5">Warta Lainnya dari {{ orgName }}</h2>
            </div>
            <router-link
              :to="`/organizations/${subdomain}/articles`"
              class="text-xs font-semibold text-blue-900 hover:underline"
            >
              Lihat Semua &rarr;
            </router-link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
            <article
              v-for="rel in article.related_articles"
              :key="rel.id"
              class="rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 transition flex flex-col group"
            >
              <router-link :to="`/organizations/${subdomain}/articles/${rel.slug}`" class="block h-40 bg-slate-100 overflow-hidden relative">
                <img v-if="rel.cover_image" :src="rel.cover_image" :alt="rel.judul" class="h-full w-full object-cover group-hover:scale-102 transition" loading="lazy" />
                <div v-else class="h-full w-full flex items-center justify-center text-slate-300 font-bold text-xs bg-slate-100">
                  {{ orgName }}
                </div>
                <span v-if="rel.category" class="absolute top-2.5 left-2.5 rounded bg-white/95 px-2 py-0.5 text-[9px] font-bold text-slate-900 shadow-xs">
                  {{ rel.category.name }}
                </span>
              </router-link>

              <div class="p-4 flex flex-col justify-between flex-grow space-y-2">
                <h3 class="text-xs sm:text-sm font-bold text-slate-950 group-hover:text-blue-900 transition line-clamp-2 leading-snug">
                  <router-link :to="`/organizations/${subdomain}/articles/${rel.slug}`">
                    {{ rel.judul }}
                  </router-link>
                </h3>
                <div class="pt-2 border-t border-slate-100 text-[10px] text-slate-400 flex justify-between font-medium">
                  <span>{{ new Date(rel.published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</span>
                  <span class="text-blue-900 font-semibold">Baca &rarr;</span>
                </div>
              </div>
            </article>
          </div>
        </section>

      </div>
    </article>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicArticle } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const article = ref<PublicArticle | null>(null)
const isLoading = ref(true)
const toastMessage = ref('')
const readingProgress = ref(0)

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')

const formattedDate = computed(() => {
  if (!article.value?.published_at) return ''
  return new Date(article.value.published_at).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
})

const readingTime = computed(() => {
  if (!article.value?.konten) return '2 menit baca'
  const text = article.value.konten.replace(/<[^>]*>/g, '')
  const words = text.trim().split(/\s+/).length
  const minutes = Math.max(1, Math.ceil(words / 200))
  return `${minutes} menit baca`
})

const canonicalUrl = computed(() => {
  if (typeof window === 'undefined') return ''
  return `${window.location.origin}/organizations/${subdomain.value}/articles/${route.params.articleSlug}`
})

const shareText = computed(() => {
  return `${article.value?.judul || ''} | ${orgName.value} - ${canonicalUrl.value}`
})

// Scroll Progress Tracker
const handleScroll = () => {
  const totalHeight = document.documentElement.scrollHeight - window.innerHeight
  if (totalHeight > 0) {
    readingProgress.value = Math.min(100, Math.max(0, (window.scrollY / totalHeight) * 100))
  }
}

// Share Handlers
const shareWhatsApp = () => {
  window.open(`https://wa.me/?text=${encodeURIComponent(shareText.value)}`, '_blank', 'noopener,noreferrer')
}

const shareTwitter = () => {
  window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}`, '_blank', 'noopener,noreferrer')
}

const copyArticleLink = async () => {
  try {
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(canonicalUrl.value)
    } else {
      const el = document.createElement('textarea')
      el.value = canonicalUrl.value
      document.body.appendChild(el)
      el.select()
      document.execCommand('copy')
      document.body.removeChild(el)
    }
    showToast('Tautan artikel berhasil disalin ke clipboard!')
  } catch (err) {
    showToast('Gagal menyalin tautan.')
  }
}

let toastTimer: any = null
const showToast = (msg: string) => {
  toastMessage.value = msg
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toastMessage.value = ''
  }, 2500)
}

useSeoMeta(() => ({
  title: article.value?.seo?.meta_title || (article.value?.judul ? `${article.value.judul} | ${orgName.value}` : 'Artikel'),
  description: article.value?.seo?.meta_description || article.value?.excerpt,
  ogImage: article.value?.seo?.og_image || article.value?.cover_image || props.organization?.logo || undefined,
  ogType: 'article',
  publishedTime: article.value?.published_at,
  author: article.value?.author.name,
  canonical: canonicalUrl.value,
  jsonLd: article.value ? {
    '@context': 'https://schema.org',
    '@type': 'NewsArticle',
    headline: article.value.judul,
    description: article.value.excerpt,
    image: article.value.cover_image ? [article.value.cover_image] : [],
    datePublished: article.value.published_at,
    author: [{
      '@type': 'Person',
      name: article.value.author.name,
    }],
    publisher: {
      '@type': 'Organization',
      name: orgName.value,
      logo: {
        '@type': 'ImageObject',
        url: props.organization?.logo || '',
      }
    }
  } : undefined
}))

const loadArticle = async () => {
  const articleSlug = route.params.articleSlug as string
  if (!subdomain.value || !articleSlug) return
  isLoading.value = true
  try {
    const data = await publicService.getArticleBySlug(subdomain.value, articleSlug)
    article.value = data
  } catch (err) {
    console.error('Failed to load article:', err)
  } finally {
    isLoading.value = false
  }
}

watch(() => route.params.articleSlug, () => {
  loadArticle()
})

onMounted(() => {
  loadArticle()
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
:deep(.article-body h2) {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  margin-top: 2rem;
  margin-bottom: 0.75rem;
  line-height: 1.25;
}

:deep(.article-body h3) {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
  line-height: 1.3;
}

:deep(.article-body p) {
  margin-bottom: 1.25rem;
}

:deep(.article-body blockquote) {
  border-left: 4px solid #1e3a8a;
  background-color: #f8fafc;
  padding: 1rem 1.25rem;
  border-radius: 0 0.5rem 0.5rem 0;
  font-style: italic;
  color: #334155;
  margin: 1.5rem 0;
}

:deep(.article-body ul) {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1.25rem;
  space-y: 0.5rem;
}

:deep(.article-body ol) {
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin-bottom: 1.25rem;
  space-y: 0.5rem;
}

:deep(.article-body img) {
  border-radius: 0.75rem;
  margin: 1.5rem auto;
  max-width: 100%;
  height: auto;
  border: 1px solid #e2e8f0;
}

:deep(.article-body a) {
  color: #1e3a8a;
  text-decoration: underline;
  font-weight: 600;
}

:deep(.article-body a:hover) {
  color: #1d4ed8;
}
</style>
