<template>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="border-b border-slate-200 pb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Publikasi & Warta</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 mt-0.5">Berita & Artikel</h1>
      </div>

      <!-- Search Input -->
      <div class="w-full sm:w-64">
        <input
          v-model="searchQuery"
          @input="onSearch"
          type="text"
          placeholder="Cari judul warta..."
          class="w-full h-9 rounded-lg border border-slate-200 px-3 text-xs text-slate-900 focus:border-blue-700 focus:outline-none"
        />
      </div>
    </div>

    <div v-if="isLoading" class="py-16 text-center text-xs text-slate-500">Memuat artikel...</div>
    
    <div v-else-if="articles.length === 0" class="py-14 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-white">
      Tidak ada artikel yang sesuai dengan pencarian.
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
      <article
        v-for="post in articles"
        :key="post.id"
        class="rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 transition flex flex-col group"
      >
        <router-link :to="`/organizations/${organization.subdomain}/articles/${post.slug}`" class="block h-44 bg-slate-100 overflow-hidden relative">
          <img v-if="post.cover_image" :src="post.cover_image" :alt="post.judul" class="h-full w-full object-cover group-hover:scale-102 transition" />
          <div v-else class="h-full w-full flex items-center justify-center text-slate-300 font-bold text-xs bg-slate-100">
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import type { PublicOrganization, PublicArticle } from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()

const articles = ref<PublicArticle[]>([])
const isLoading = ref(true)
const searchQuery = ref('')

useSeoMeta(() => ({
  title: `Warta & Berita | ${props.organization.nama}`,
  description: `Arsip warta, kegiatan, dan publikasi resmi ${props.organization.nama}.`,
  ogType: 'website',
}))

let debounceTimer: any = null
const onSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadArticles()
  }, 200)
}

const loadArticles = async () => {
  isLoading.value = true
  try {
    const res = await publicService.getArticles(props.organization.subdomain, {
      search: searchQuery.value || undefined,
      per_page: 30,
    })
    articles.value = res.data || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadArticles()
})
</script>
