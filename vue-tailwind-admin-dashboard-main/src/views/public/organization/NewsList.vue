<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#424751]" aria-label="Breadcrumb">
      <router-link to="/" class="hover:text-[#00346F]">Beranda</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <router-link :to="`/organizations/${subdomain}`" class="hover:text-[#00346F]">{{ orgName }}</router-link>
      <span class="text-[#737783]">&rsaquo;</span>
      <span class="text-[#00346F] font-bold">{{ pageTitle }}</span>
    </nav>

    <!-- Page Header -->
    <div class="border-b border-[#C2C6D3] pb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Publikasi Resmi</span>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#191C1D] mt-0.5">{{ pageTitle }}</h1>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="h-64 rounded bg-white border border-[#C2C6D3] animate-pulse"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="articles.length === 0" class="py-16 text-center text-xs text-[#737783] border border-dashed border-[#C2C6D3] rounded bg-white">
      Belum ada warta berita yang dipublikasikan.
    </div>

    <!-- Articles Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <article
        v-for="art in articles"
        :key="art.id"
        class="rounded border border-[#C2C6D3] bg-white overflow-hidden hover:border-[#00346F] transition duration-150 flex flex-col justify-between group shadow-2xs"
      >
        <router-link :to="`/organizations/${subdomain}/articles/${art.slug}`" class="block h-48 bg-[#F3F4F5] overflow-hidden relative">
          <img v-if="art.cover_image" :src="resolveImageUrl(art.cover_image)" :alt="art.judul" class="h-full w-full object-cover group-hover:scale-102 transition duration-200" />
          <div v-else class="h-full w-full flex items-center justify-center text-xs font-bold text-[#737783]">ORMAWA ITI</div>
          <span v-if="art.category" class="absolute top-3 left-3 rounded bg-white/95 px-2.5 py-0.5 text-[9px] font-bold text-[#191C1D] shadow-xs">
            {{ art.category.name }}
          </span>
        </router-link>

        <div class="p-5 space-y-2 flex flex-col justify-between flex-grow">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
              <router-link :to="`/organizations/${subdomain}/articles/${art.slug}`">
                {{ art.judul }}
              </router-link>
            </h2>
            <p class="text-xs text-[#424751] line-clamp-2 mt-1.5 leading-relaxed">{{ art.excerpt }}</p>
          </div>

          <div class="pt-3 border-t border-[#E1E3E4] flex items-center justify-between text-[11px] text-[#737783]">
            <span>{{ new Date(art.published_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</span>
            <span class="font-semibold text-[#191C1D]">{{ art.author?.name || 'Redaksi' }}</span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { PublicOrganization, PublicArticle } from '@/types/public'

const props = defineProps<{ organization?: PublicOrganization }>()
const route = useRoute()

const articles = ref<PublicArticle[]>([])
const isLoading = ref(true)

const subdomain = computed(() => props.organization?.subdomain || (route.params.slug as string) || '')
const orgName = computed(() => props.organization?.nama || 'Organisasi')
const pageTitle = computed(() => props.organization?.label_menu?.posts || props.organization?.label_menu?.berita || 'Warta & Artikel')

useSeoMeta(() => ({
  title: `${pageTitle.value} — ${orgName.value} | ORMAWA ITI`,
  description: `Kumpulan artikel ${pageTitle.value.toLowerCase()} resmi dari ${orgName.value} Institut Teknologi Indonesia.`,
  ogType: 'website',
}))

const loadArticles = async () => {
  if (!subdomain.value) return
  isLoading.value = true
  try {
    const res = await publicService.getArticles(subdomain.value, { per_page: 30 })
    articles.value = res.data || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

watch(() => subdomain.value, (newVal) => {
  if (newVal) loadArticles()
})

onMounted(() => {
  loadArticles()
})
</script>
