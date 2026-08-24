<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      @keydown.escape="close"
      @click="close"
      class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 pt-16 sm:pt-24 bg-slate-950/60 backdrop-blur-xs flex items-start justify-center cursor-pointer"
      role="dialog"
      aria-modal="true"
      aria-label="Pencarian Global Platform"
    >
      <div
        class="w-full max-w-xl transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all border border-slate-200 cursor-default"
        @click.stop
      >
        <!-- Search Input Bar -->
        <div class="relative border-b border-slate-200 flex items-center px-4 bg-slate-50/50">
          <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            ref="inputRef"
            v-model="query"
            @input="onSearch"
            type="text"
            placeholder="Cari nama ormawa, topik berita, agenda kegiatan..."
            class="h-12 w-full border-0 bg-transparent pl-3 pr-4 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0"
          />
          <button
            @click="close"
            class="rounded px-2 py-1 text-[10px] font-semibold text-slate-500 hover:bg-slate-200/60 transition"
            aria-label="Tutup pencarian"
          >
            ESC
          </button>
        </div>

        <!-- Search Results List -->
        <div class="max-h-80 overflow-y-auto p-3 space-y-3">
          <div v-if="isLoading" class="py-6 text-center text-xs text-slate-500 font-medium">
            Mencari informasi...
          </div>

          <div v-else-if="!query.trim()" class="py-6 text-center text-xs text-slate-400">
            Ketik kata kunci untuk mencari seluruh entitas dan publikasi mahasiswa ITI.
          </div>

          <div v-else-if="organizations.length === 0 && articles.length === 0" class="py-6 text-center text-xs text-slate-500">
            Tidak ditemukan hasil untuk "<span class="font-semibold text-slate-800">{{ query }}</span>".
          </div>

          <template v-else>
            <!-- Organizations Section -->
            <div v-if="organizations.length > 0" class="space-y-1">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2.5 block">Organisasi</span>
              <router-link
                v-for="org in organizations"
                :key="org.id"
                :to="`/organizations/${org.subdomain}`"
                @click="close"
                class="flex items-center gap-3 rounded-lg p-2 hover:bg-slate-50 transition group"
              >
                <div class="h-7 w-7 rounded-md border border-slate-200 bg-white flex items-center justify-center text-xs font-bold text-slate-800 shrink-0 overflow-hidden">
                  <img v-if="org.logo" :src="org.logo" :alt="org.nama" class="h-full w-full object-cover" />
                  <span v-else>{{ org.nama.charAt(0) }}</span>
                </div>
                <div class="flex-grow min-w-0">
                  <span class="text-xs font-semibold text-slate-900 group-hover:text-blue-700 truncate block">{{ org.nama }}</span>
                  <span class="text-[10px] text-slate-500">{{ org.jenis }} &bull; {{ org.subdomain }}.iti.ac.id</span>
                </div>
                <span class="text-xs text-slate-400 group-hover:text-blue-700 font-bold">&rarr;</span>
              </router-link>
            </div>

            <!-- Articles Section -->
            <div v-if="articles.length > 0" class="space-y-1 pt-2 border-t border-slate-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2.5 block">Warta & Berita</span>
              <router-link
                v-for="art in articles"
                :key="art.id"
                :to="`/organizations/${art.organization?.subdomain}/articles/${art.slug}`"
                @click="close"
                class="flex items-center justify-between gap-3 rounded-lg p-2 hover:bg-slate-50 transition group"
              >
                <div class="flex-grow min-w-0">
                  <span class="text-xs font-semibold text-slate-900 group-hover:text-blue-700 truncate block">{{ art.judul }}</span>
                  <span class="text-[10px] text-slate-500">{{ art.organization?.nama }} &bull; {{ new Date(art.published_at).toLocaleDateString('id-ID') }}</span>
                </div>
                <span class="text-xs text-slate-400 group-hover:text-blue-700 font-bold">&rarr;</span>
              </router-link>
            </div>
          </template>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { publicService } from '@/services/publicService'
import type { PublicOrganization, PublicArticle } from '@/types/public'

const props = defineProps<{ isOpen: boolean }>()
const emit = defineEmits(['update:isOpen'])

const query = ref('')
const inputRef = ref<HTMLInputElement | null>(null)
const organizations = ref<PublicOrganization[]>([])
const articles = ref<PublicArticle[]>([])
const isLoading = ref(false)

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    query.value = ''
    organizations.value = []
    articles.value = []
    nextTick(() => {
      inputRef.value?.focus()
    })
  }
})

let debounceTimer: any = null
const onSearch = () => {
  clearTimeout(debounceTimer)
  if (!query.value.trim()) {
    organizations.value = []
    articles.value = []
    return
  }
  debounceTimer = setTimeout(async () => {
    isLoading.value = true
    try {
      const [orgRes, homeRes] = await Promise.all([
        publicService.getOrganizations({ search: query.value.trim(), per_page: 5 }).catch(() => ({ data: [] })),
        publicService.getHome().catch(() => ({ featured_articles: [] })),
      ])
      organizations.value = (orgRes as any).data || []
      
      const q = query.value.toLowerCase()
      articles.value = (homeRes.featured_articles || []).filter((a: any) => 
        a.judul.toLowerCase().includes(q) || a.excerpt?.toLowerCase().includes(q)
      ).slice(0, 5)
    } finally {
      isLoading.value = false
    }
  }, 200)
}

const close = () => {
  emit('update:isOpen', false)
}
</script>
