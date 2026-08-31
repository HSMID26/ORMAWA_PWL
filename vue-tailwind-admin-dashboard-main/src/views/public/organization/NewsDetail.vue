<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] font-sans selection:bg-[#00346F] selection:text-white">
    
    <!-- Reading Progress Bar -->
    <div
      class="fixed top-0 left-0 h-1 bg-[#00346F] z-50 transition-all duration-150"
      :style="{ width: `${readingProgress}%` }"
      aria-hidden="true"
    ></div>

    <!-- Toast Notification Feedback -->
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
          class="fixed bottom-6 right-6 z-50 bg-[#191C1D] text-white text-xs font-semibold px-4 py-2.5 rounded shadow-xl flex items-center gap-2 border border-slate-700"
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
    <div v-if="isLoading" class="max-w-[760px] mx-auto px-4 md:px-0 py-12 md:py-16 space-y-6">
      <div class="h-4 w-56 bg-[#E7E8E9] rounded animate-pulse"></div>
      <div class="h-12 sm:h-16 w-full bg-[#E7E8E9] rounded animate-pulse"></div>
      <div class="h-6 w-3/4 bg-[#E7E8E9] rounded animate-pulse"></div>
      <div class="aspect-video w-full bg-[#E7E8E9] rounded-xl animate-pulse"></div>
      <div class="space-y-3 pt-6">
        <div class="h-4 w-full bg-[#E7E8E9] rounded animate-pulse"></div>
        <div class="h-4 w-5/6 bg-[#E7E8E9] rounded animate-pulse"></div>
        <div class="h-4 w-4/6 bg-[#E7E8E9] rounded animate-pulse"></div>
      </div>
    </div>

    <!-- Error / Not Found State -->
    <div v-else-if="!article" class="max-w-[760px] mx-auto my-20 p-8 text-center border border-dashed border-[#C2C6D3] rounded-xl bg-white space-y-4 shadow-2xs">
      <div class="h-12 w-12 rounded bg-[#F3F4F5] text-[#00346F] mx-auto flex items-center justify-center font-extrabold text-base">
        404
      </div>
      <h1 class="text-xl font-bold text-[#191C1D]">Warta Tidak Ditemukan</h1>
      <p class="text-xs text-[#737783] leading-relaxed max-w-md mx-auto">
        Artikel yang Anda cari tidak tersedia, berstatus draf, atau tautan telah kedaluwarsa.
      </p>
      <div class="pt-2 flex justify-center gap-3">
        <router-link
          :to="`/organizations/${subdomain}/articles`"
          class="inline-block rounded-lg bg-[#00346F] px-4 py-2 text-xs font-semibold text-white hover:bg-[#004A99] transition shadow-2xs"
        >
          &larr; Warta {{ orgName }}
        </router-link>
        <router-link
          to="/berita"
          class="inline-block rounded-lg border border-[#C2C6D3] bg-[#F8F9FA] px-4 py-2 text-xs font-semibold text-[#191C1D] hover:bg-white transition"
        >
          Semua Berita
        </router-link>
      </div>
    </div>

    <!-- Main Stitch Article Content Canvas -->
    <main v-else class="max-w-[760px] mx-auto px-4 md:px-0 py-8 md:py-12">
      
      <!-- Breadcrumb (Stitch Style) -->
      <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-[#737783] mb-6">
        <router-link to="/" class="hover:text-[#00346F] transition-colors">Beranda</router-link>
        <span class="text-[#C2C6D3]">&rsaquo;</span>
        <router-link to="/berita" class="hover:text-[#00346F] transition-colors">Berita</router-link>
        <span class="text-[#C2C6D3]">&rsaquo;</span>
        <router-link :to="`/organizations/${subdomain}`" class="hover:text-[#00346F] transition-colors truncate max-w-[160px] sm:max-w-none">{{ orgName }}</router-link>
        <span class="text-[#C2C6D3]">&rsaquo;</span>
        <span class="text-[#191C1D] font-bold truncate max-w-[140px] sm:max-w-[240px]">{{ article.judul }}</span>
      </nav>

      <!-- Article Header -->
      <header class="mb-6">
        <!-- Display Hero H1 -->
        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-[48px] font-extrabold text-[#191C1D] leading-[1.2] tracking-tight mb-4">
          {{ article.judul }}
        </h1>

        <!-- Metadata Bar -->
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[#424751] text-xs sm:text-sm border-b border-[#C2C6D3] pb-3.5 mb-6">
          
          <!-- Author Info -->
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-[#E7E8E9] flex items-center justify-center overflow-hidden shrink-0 border border-[#C2C6D3]">
              <span class="text-[11px] font-bold text-[#00346F]">{{ authorInitial }}</span>
            </div>
            <span class="font-medium text-[#191C1D]">{{ article.author?.name || 'Tim Redaksi' }}</span>
          </div>

          <span class="text-[#C2C6D3] hidden sm:inline">&bull;</span>

          <!-- Publication Date -->
          <div class="flex items-center gap-1 text-[#424751]">
            <time :datetime="article.published_at">{{ formattedDate }}</time>
          </div>

          <template v-if="article.category">
            <span class="text-[#C2C6D3] hidden sm:inline">&bull;</span>
            <!-- Category Badge -->
            <span class="bg-[#E7E8E9] text-[#00346F] px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider">
              {{ article.category.name }}
            </span>
          </template>

          <span class="text-[#C2C6D3] hidden sm:inline">&bull;</span>

          <!-- Reading Time Calculator -->
          <div class="flex items-center gap-1 text-[#737783]">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ estimatedReadingTime }} min read</span>
          </div>
        </div>

        <!-- Organization Context Card (Stitch Institutional) -->
        <router-link
          :to="`/organizations/${subdomain}`"
          class="group inline-flex items-center gap-3 p-3 rounded-lg border border-[#C2C6D3] bg-white hover:border-[#00346F] hover:bg-[#F8F9FA] transition-all mb-6 w-full shadow-2xs"
        >
          <div class="w-10 h-10 rounded bg-white flex items-center justify-center border border-[#E7E8E9] overflow-hidden shrink-0 p-1">
            <img v-if="orgLogo" :src="orgLogo" :alt="orgName" class="w-full h-full object-contain" />
            <span v-else class="text-xs font-bold text-[#00346F]">{{ orgName.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="flex-grow min-w-0">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">DIPUBLIKASIKAN OLEH</div>
            <div class="font-semibold text-xs sm:text-sm text-[#191C1D] group-hover:text-[#00346F] transition-colors truncate">
              {{ orgName }}
            </div>
          </div>
          <svg class="w-4 h-4 text-[#737783] group-hover:text-[#00346F] transition-colors shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </router-link>
      </header>

      <!-- Featured Image (16:9 Aspect Ratio) -->
      <figure v-if="article.cover_image" class="mb-8">
        <img
          :src="article.cover_image"
          :alt="article.judul || 'Dokumentasi Artikel'"
          class="w-full h-auto rounded-xl border border-[#C2C6D3] object-cover aspect-[16/9] shadow-2xs"
          loading="eager"
        />
        <figcaption v-if="article.excerpt" class="text-center text-xs sm:text-[13px] text-[#737783] mt-2.5 italic max-w-xl mx-auto">
          {{ article.excerpt }}
        </figcaption>
      </figure>

      <!-- Fallback Surface if no cover image -->
      <div v-else class="mb-8 rounded-xl border border-[#C2C6D3] bg-[#F3F4F5] p-6 text-center shadow-2xs">
        <div class="text-xs font-bold text-[#00346F] uppercase tracking-wider mb-1">ORMAWA ITI</div>
        <div class="text-xs text-[#737783]">Publikasi Resmi {{ orgName }}</div>
      </div>

      <!-- Editorial Article Body (Stitch 19px / 1.75 Line-Height) -->
      <article class="font-sans text-[18px] sm:text-[19px] text-[#191C1D] leading-[1.75] font-normal space-y-6 pt-2">
        <div v-if="hasHtmlContent" v-html="sanitizedContent" class="article-content space-y-6"></div>
        <div v-else class="space-y-6">
          <p class="first-letter-dropcap">
            {{ article.konten }}
          </p>
        </div>
      </article>

      <!-- Article Footer (Tags + Social Share) -->
      <footer class="mt-12 pt-6 border-t border-[#C2C6D3]">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          
          <!-- Tags -->
          <div v-if="article.tags && article.tags.length > 0" class="flex flex-wrap gap-2">
            <span
              v-for="tag in article.tags"
              :key="tag.id"
              class="px-3 py-1 bg-[#F3F4F5] text-[#424751] text-xs font-medium rounded-full hover:bg-[#E7E8E9] transition-colors"
            >
              #{{ tag.name }}
            </span>
          </div>
          <div v-else></div>

          <!-- Social Share Buttons -->
          <div class="flex items-center gap-2.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#737783]">BAGIKAN:</span>
            
            <!-- Native Share -->
            <button
              v-if="canShare"
              @click="shareNative"
              aria-label="Bagikan artikel"
              class="w-9 h-9 rounded-full border border-[#C2C6D3] bg-white flex items-center justify-center text-[#424751] hover:border-[#00346F] hover:text-[#00346F] transition-colors cursor-pointer shadow-2xs"
              title="Bagikan"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
              </svg>
            </button>

            <!-- WhatsApp -->
            <a
              :href="whatsappShareUrl"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Bagikan ke WhatsApp"
              class="w-9 h-9 rounded-full border border-[#C2C6D3] bg-white flex items-center justify-center text-[#424751] hover:border-[#00346F] hover:text-[#00346F] transition-colors shadow-2xs"
              title="WhatsApp"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M12.015 1.5c-5.795 0-10.495 4.704-10.495 10.5 0 1.956.517 3.824 1.498 5.485L1.5 22.5l5.143-1.485a10.435 10.435 0 0 0 5.372 1.485c5.795 0 10.495-4.704 10.495-10.5 0-5.796-4.7-10.5-10.495-10.5zm0 19.263a8.775 8.775 0 0 1-4.475-1.226l-.32-.19-3.326.96.89-3.235-.208-.33a8.753 8.753 0 0 1-1.34-4.742c0-4.843 3.94-8.786 8.78-8.786 4.84 0 8.78 3.943 8.78 8.786 0 4.843-3.94 8.786-8.78 8.786zm4.81-6.57c-.264-.132-1.56-.77-1.804-.857-.243-.088-.42-.132-.596.132-.176.264-.683.857-.837 1.034-.154.176-.308.198-.572.066-.264-.132-1.115-.412-2.124-1.307-.785-.697-1.316-1.558-1.47-1.823-.154-.264-.016-.407.116-.54.118-.118.264-.308.396-.462.132-.154.176-.264.264-.44.088-.176.044-.33-.022-.462-.066-.132-.596-1.436-.816-1.964-.215-.515-.434-.445-.596-.454-.154-.008-.33-.01-.506-.01-.176 0-.462.066-.704.33-.243.264-.925.903-.925 2.203 0 1.3 1.05 2.822 1.156 2.977.106.154 2.11 3.226 5.11 4.524 2.502 1.08 2.502.726 2.942.682.44-.044 1.41-.577 1.607-1.133.198-.557.198-1.035.132-1.134-.066-.098-.243-.154-.507-.286z"></path>
              </svg>
            </a>

            <!-- X (Twitter) -->
            <a
              :href="twitterShareUrl"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Bagikan ke X"
              class="w-9 h-9 rounded-full border border-[#C2C6D3] bg-white flex items-center justify-center text-[#424751] hover:border-[#00346F] hover:text-[#00346F] transition-colors shadow-2xs"
              title="X (Twitter)"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
              </svg>
            </a>

            <!-- Copy Link -->
            <button
              @click="copyArticleLink"
              aria-label="Salin Tautan Artikel"
              class="w-9 h-9 rounded-full border border-[#C2C6D3] bg-white flex items-center justify-center text-[#424751] hover:border-[#00346F] hover:text-[#00346F] transition-colors cursor-pointer shadow-2xs"
              title="Salin Tautan"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
            </button>
          </div>

        </div>
      </footer>
    </main>

    <!-- Related Articles Section (Stitch 3-Column Editorial Grid) -->
    <section v-if="relatedArticles.length > 0" class="bg-[#F8F9FA] py-12 border-t border-[#C2C6D3]">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl sm:text-2xl font-bold text-[#191C1D] mb-6 text-center md:text-left tracking-tight">
          ARTIKEL TERKAIT
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <article
            v-for="rel in relatedArticles.slice(0, 3)"
            :key="rel.id"
            class="group block border border-[#C2C6D3] rounded-xl overflow-hidden bg-white hover:border-[#00346F] hover:shadow-md transition-all duration-200 flex flex-col justify-between shadow-2xs"
          >
            <div>
              <router-link
                :to="`/organizations/${rel.organization?.subdomain || subdomain}/articles/${rel.slug}`"
                class="aspect-[16/9] w-full bg-[#F3F4F5] overflow-hidden relative block"
              >
                <img
                  v-if="rel.cover_image"
                  :src="rel.cover_image"
                  :alt="rel.judul || 'Artikel Terkait'"
                  class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300"
                  loading="lazy"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-xs font-bold text-[#737783]">
                  ORMAWA ITI
                </div>
                <div
                  v-if="rel.category"
                  class="absolute top-3 left-3 bg-white/90 backdrop-blur-xs px-2.5 py-0.5 rounded text-[10px] font-bold text-[#00346F] shadow-2xs"
                >
                  {{ rel.category.name }}
                </div>
              </router-link>
              
              <div class="p-4 sm:p-5">
                <h3 class="text-sm sm:text-base font-bold text-[#191C1D] mb-2 group-hover:text-[#00346F] transition-colors line-clamp-2 leading-snug">
                  <router-link :to="`/organizations/${rel.organization?.subdomain || subdomain}/articles/${rel.slug}`">
                    {{ rel.judul }}
                  </router-link>
                </h3>
              </div>
            </div>

            <div class="px-4 sm:px-5 pb-4 pt-2 border-t border-[#E1E3E4] flex items-center gap-1.5 text-xs text-[#737783]">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <time :datetime="rel.published_at">{{ new Date(rel.published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</time>
            </div>
          </article>
        </div>

        <!-- Organization CTA Button -->
        <div class="mt-10 flex justify-center">
          <router-link
            :to="`/organizations/${subdomain}`"
            class="inline-flex items-center justify-center px-6 py-2.5 border border-[#00346F] text-[#00346F] hover:bg-[#00346F] hover:text-white font-semibold text-xs sm:text-sm rounded-lg transition-colors shadow-2xs"
          >
            Kunjungi Website {{ orgName }} &rarr;
          </router-link>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicArticle } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const article = ref<PublicArticle | null>(null)
const relatedArticles = ref<PublicArticle[]>([])
const isLoading = ref(true)
const toastMessage = ref('')
const readingProgress = ref(0)

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || article.value?.organization?.nama || 'Organisasi')
const orgLogo = computed(() => props.organization?.logo || article.value?.organization?.logo || null)

const authorInitial = computed(() => {
  const name = article.value?.author?.name || 'R'
  return name.charAt(0).toUpperCase()
})

const formattedDate = computed(() => {
  if (!article.value?.published_at) return ''
  try {
    return new Date(article.value.published_at).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  } catch {
    return article.value.published_at
  }
})

const hasHtmlContent = computed(() => {
  if (!article.value?.konten) return false
  return /<[a-z][\s\S]*>/i.test(article.value.konten)
})

const sanitizedContent = computed(() => {
  return article.value?.konten || ''
})

const estimatedReadingTime = computed(() => {
  if (!article.value?.konten) return 1
  const textOnly = article.value.konten.replace(/<[^>]*>/g, ' ')
  const words = textOnly.trim().split(/\s+/).filter(Boolean).length
  return Math.max(1, Math.ceil(words / 200))
})

const canShare = computed(() => typeof navigator !== 'undefined' && !!navigator.share)

const canonicalUrl = computed(() => {
  if (typeof window === 'undefined') return ''
  return `${window.location.origin}/organizations/${subdomain.value}/articles/${route.params.articleSlug || article.value?.slug || ''}`
})

const whatsappShareUrl = computed(() => {
  if (typeof window === 'undefined') return '#'
  const text = encodeURIComponent(`${article.value?.judul || 'Warta Mahasiswa'} — ${canonicalUrl.value}`)
  return `https://api.whatsapp.com/send?text=${text}`
})

const twitterShareUrl = computed(() => {
  if (typeof window === 'undefined') return '#'
  const text = encodeURIComponent(`${article.value?.judul || 'Warta Mahasiswa'}`)
  const url = encodeURIComponent(canonicalUrl.value)
  return `https://twitter.com/intent/tweet?text=${text}&url=${url}`
})

useSeoMeta(() => ({
  title: article.value ? `${article.value.seo?.meta_title || article.value.judul} — ${orgName.value} | ORMAWA ITI` : 'Warta Organisasi',
  description: article.value?.seo?.meta_description || article.value?.excerpt || article.value?.konten?.slice(0, 160),
  image: article.value?.seo?.og_image || article.value?.cover_image || orgLogo.value || undefined,
  ogType: 'article',
  publishedTime: article.value?.published_at,
  author: article.value?.author?.name,
  jsonLd: article.value ? {
    '@context': 'https://schema.org',
    '@type': 'NewsArticle',
    headline: article.value.judul,
    image: article.value.cover_image ? [article.value.cover_image] : [],
    datePublished: article.value.published_at,
    author: [{
      '@type': 'Person',
      name: article.value.author?.name || 'Tim Redaksi',
    }],
    publisher: {
      '@type': 'Organization',
      name: orgName.value,
      logo: orgLogo.value ? {
        '@type': 'ImageObject',
        url: orgLogo.value
      } : undefined
    },
    description: article.value.excerpt || article.value.seo?.meta_description,
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': canonicalUrl.value
    }
  } : undefined,
}))

const showToast = (msg: string) => {
  toastMessage.value = msg
  setTimeout(() => {
    toastMessage.value = ''
  }, 2500)
}

const copyArticleLink = async () => {
  try {
    const url = canonicalUrl.value || window.location.href
    await navigator.clipboard.writeText(url)
    showToast('Tautan artikel berhasil disalin.')
  } catch {
    showToast('Gagal menyalin tautan.')
  }
}

const shareNative = async () => {
  if (navigator.share && article.value) {
    try {
      await navigator.share({
        title: article.value.judul,
        text: article.value.excerpt || article.value.judul,
        url: canonicalUrl.value || window.location.href,
      })
    } catch {
      // Ignored if cancelled
    }
  }
}

const updateReadingProgress = () => {
  const scrollTop = window.scrollY || document.documentElement.scrollTop
  const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight
  if (docHeight > 0) {
    readingProgress.value = Math.min(100, Math.max(0, Math.round((scrollTop / docHeight) * 100)))
  }
}

const loadArticle = async () => {
  const articleSlug = (route.params.articleSlug as string) || (route.params.slug as string) || ''
  if (!subdomain.value || !articleSlug) return

  isLoading.value = true
  try {
    const data = await publicService.getArticleBySlug(subdomain.value, articleSlug)
    article.value = data
    
    // Check if API returned related_articles
    if (data?.related_articles && Array.isArray(data.related_articles) && data.related_articles.length > 0) {
      relatedArticles.value = data.related_articles.filter((a: any) => a.id !== data.id)
    } else {
      // Fallback query related articles
      try {
        const relatedRes = await publicService.getArticles(subdomain.value, {
          category: data?.category?.slug,
          per_page: 4,
        })
        relatedArticles.value = (relatedRes.data || []).filter(a => a.id !== data?.id)
      } catch (err) {
        console.error('Failed to load related articles:', err)
      }
    }
  } catch (err) {
    console.error('Failed to load article detail:', err)
    article.value = null
  } finally {
    isLoading.value = false
  }
}

watch(() => route.params.articleSlug, () => {
  loadArticle()
})

onMounted(() => {
  loadArticle()
  window.addEventListener('scroll', updateReadingProgress, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateReadingProgress)
})
</script>

<style scoped>
.first-letter-dropcap::first-letter {
  float: left;
  font-size: 3.5rem;
  line-height: 1;
  padding-right: 0.5rem;
  color: #00346f;
  font-weight: 800;
  margin-top: -0.1rem;
}

:deep(.article-content p:first-of-type::first-letter) {
  float: left;
  font-size: 3.5rem;
  line-height: 1;
  padding-right: 0.5rem;
  color: #00346f;
  font-weight: 800;
  margin-top: -0.1rem;
}

:deep(.article-content) {
  color: #191c1d;
  font-size: 19px;
  line-height: 1.75;
}

:deep(.article-content p) {
  margin-bottom: 1.5rem;
}

:deep(.article-content h2) {
  font-size: 1.75rem;
  font-weight: 700;
  color: #191c1d;
  margin-top: 2rem;
  margin-bottom: 1rem;
  line-height: 1.3;
}

:deep(.article-content h3) {
  font-size: 1.35rem;
  font-weight: 700;
  color: #191c1d;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  line-height: 1.35;
}

:deep(.article-content blockquote) {
  border-left: 4px solid #00346f;
  background-color: #f3f4f5;
  padding: 1.25rem 1.5rem;
  margin: 1.75rem 0;
  border-radius: 0 0.5rem 0.5rem 0;
  font-style: italic;
  color: #424751;
}

:deep(.article-content blockquote footer) {
  margin-top: 0.75rem;
  font-weight: 600;
  font-style: normal;
  color: #191c1d;
  font-size: 0.95rem;
}

:deep(.article-content ul) {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

:deep(.article-content ol) {
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

:deep(.article-content li) {
  margin-bottom: 0.35rem;
}

:deep(.article-content img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  border: 1px solid #c2c6d3;
  margin: 1.75rem auto;
}

:deep(.article-content a) {
  color: #00346f;
  text-decoration: underline;
  font-weight: 600;
}
:deep(.article-content a:hover) {
  color: #004a99;
}
</style>
