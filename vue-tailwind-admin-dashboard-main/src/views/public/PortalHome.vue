<template>
  <div class="min-h-screen bg-gray-50 flex flex-col font-sans selection:bg-brand-500 selection:text-white">
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-brand-900 text-white rounded flex items-center justify-center font-bold text-xs">
              CMS
            </div>
            <span class="text-xl font-bold text-gray-900 tracking-tight">ORMAWA</span>
          </div>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex gap-8 items-center">
            <a href="#beranda" class="text-sm font-semibold text-gray-900 hover:text-brand-600 transition-colors">Beranda</a>
            <a href="#organisasi" class="text-sm font-semibold text-gray-600 hover:text-brand-600 transition-colors">Organisasi</a>
            <a href="#tentang" class="text-sm font-semibold text-gray-600 hover:text-brand-600 transition-colors">Tentang</a>
            <div class="w-px h-5 bg-gray-300 mx-2"></div>
            <router-link to="/login" class="text-sm font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 px-5 py-2.5 rounded-lg transition-colors border border-brand-100">
              Login Admin
            </router-link>
          </nav>

          <!-- Mobile menu button -->
          <div class="md:hidden flex items-center">
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none p-2" aria-label="Toggle Menu">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="isMobileMenuOpen" class="md:hidden bg-white border-t border-gray-100 shadow-lg absolute w-full">
          <div class="px-4 pt-2 pb-6 space-y-2">
            <a href="#beranda" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-md text-base font-semibold text-gray-900 hover:bg-gray-50">Beranda</a>
            <a href="#organisasi" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-md text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Organisasi</a>
            <a href="#tentang" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-md text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Tentang</a>
            <div class="pt-4 mt-2 border-t border-gray-100">
              <router-link to="/login" class="block w-full text-center px-4 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-600 hover:bg-brand-700">
                Login Admin
              </router-link>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <main class="flex-grow">
      <!-- Hero Section -->
      <section id="beranda" class="relative bg-white overflow-hidden border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
          <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
            <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
              <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold text-brand-800 bg-brand-100 mb-6">
                <span class="flex-1">CMS ORMAWA</span>
              </div>
              <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl lg:leading-tight">
                Portal Organisasi <br class="hidden lg:block"/>
                <span class="text-brand-600">Kemahasiswaan</span>
              </h1>
              <p class="mt-6 text-base text-gray-600 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 leading-relaxed">
                Temukan informasi, kegiatan, dan website resmi seluruh organisasi kemahasiswaan dalam satu portal institusi yang terintegrasi.
              </p>
              <div class="mt-8 sm:max-w-lg sm:mx-auto sm:text-center lg:text-left lg:mx-0 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="#organisasi" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 md:text-lg transition-colors shadow-sm">
                  Jelajahi Organisasi
                </a>
                <router-link to="/org/hmif" class="inline-flex items-center justify-center px-8 py-3.5 border-2 border-gray-200 text-base font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 md:text-lg transition-colors">
                  Lihat Contoh Website
                </router-link>
              </div>
            </div>
            <div class="mt-16 lg:mt-0 lg:col-span-6 flex justify-center lg:justify-end">
              <!-- Decorative graphic representation of an institutional portal -->
              <div class="relative w-full max-w-lg">
                <div class="absolute inset-0 bg-gradient-to-tr from-brand-100 to-gray-50 transform skew-y-3 sm:skew-y-0 sm:-rotate-3 sm:rounded-3xl"></div>
                <div class="relative bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                  <div class="h-10 bg-gray-50 border-b border-gray-100 flex items-center px-4 gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                  </div>
                  <div class="p-6">
                    <div class="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
                    <div class="h-2 bg-gray-100 rounded w-full mb-2"></div>
                    <div class="h-2 bg-gray-100 rounded w-5/6 mb-6"></div>
                    <div class="grid grid-cols-2 gap-4">
                      <div class="h-24 bg-brand-50 rounded-xl border border-brand-100"></div>
                      <div class="h-24 bg-gray-50 rounded-xl border border-gray-100"></div>
                      <div class="h-24 bg-gray-50 rounded-xl border border-gray-100"></div>
                      <div class="h-24 bg-gray-50 rounded-xl border border-gray-100"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Features Section -->
      <section id="tentang" class="py-16 sm:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Mengapa Menggunakan Portal Ini?</h2>
            <p class="mt-4 text-lg text-gray-600">Sistem terpusat yang memudahkan penyebaran informasi kemahasiswaan dengan arsitektur modern dan terstruktur.</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
              <div class="w-12 h-12 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center text-xl font-black mb-6">01</div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">Informasi Organisasi</h3>
              <p class="text-gray-600 leading-relaxed">Temukan informasi resmi, visi misi, struktur kepengurusan, dan dokumen publik dari seluruh organisasi mahasiswa.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
              <div class="w-12 h-12 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center text-xl font-black mb-6">02</div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">Berita & Kegiatan</h3>
              <p class="text-gray-600 leading-relaxed">Ikuti informasi terbaru, pengumuman penting, dan kalender kegiatan organisasi mahasiswa di lingkungan kampus.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
              <div class="w-12 h-12 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center text-xl font-black mb-6">03</div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">Website Organisasi</h3>
              <p class="text-gray-600 leading-relaxed">Akses website mandiri masing-masing organisasi melalui portal ini dengan sistem manajemen konten yang terpadu.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Organizations Directory -->
      <section id="organisasi" class="py-16 sm:py-24 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="mb-12 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
              <h2 class="text-3xl font-bold text-gray-900">Organisasi Kemahasiswaan</h2>
              <p class="mt-3 text-lg text-gray-600 max-w-2xl">Daftar himpunan, unit kegiatan mahasiswa, dan badan eksekutif yang aktif.</p>
            </div>
          </div>

          <!-- API Available State -->
          <div v-if="!orgError && !isOrgLoading && organizations.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <OrganizationCard 
              v-for="org in organizations" 
              :key="org.id" 
              :organization="{
                id: String(org.id),
                logo: '/default-logo.png',
                name: org.nama,
                category: ['HMPS', 'UKM', 'BEM', 'Faculty'].includes(org.jenis) ? (org.jenis as any) : 'Other',
                shortDescription: 'Organisasi Kemahasiswaan',
                website: `/org/${org.subdomain}`,
                slug: org.subdomain
              }" 
            />
          </div>

          <!-- API Unavailable / Graceful State -->
          <div v-else class="rounded-2xl bg-gray-50 border border-gray-200 p-8 sm:p-12 text-center">
            <div class="mx-auto w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4 text-gray-500">
              <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ orgError || 'Data belum tersedia' }}</h3>
            <p class="mt-2 text-gray-600 max-w-lg mx-auto mb-8">
              Sistem backend publik saat ini belum terhubung. Namun, Anda dapat melihat presentasi demo website organisasi melalui tautan di bawah ini.
            </p>
            
            <!-- Fallback HMIF Demo Presentation Card -->
            <div class="max-w-sm mx-auto text-left">
              <OrganizationCard 
                :organization="{
                  id: 'demo',
                  logo: 'https://ui-avatars.com/api/?name=HMIF&background=0F172A&color=fff&size=128',
                  name: 'HMIF',
                  category: 'HMPS',
                  shortDescription: 'Contoh implementasi website organisasi yang dapat dikembangkan melalui CMS ORMAWA (Demo Presentasi).',
                  website: '/org/hmif',
                  slug: 'hmif'
                }" 
              />
            </div>
          </div>
        </div>
      </section>

      <!-- Latest Content Section -->
      <section class="py-16 sm:py-24 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Kabar Terbaru</h2>
            <p class="mt-3 text-lg text-gray-600">Berita dan artikel dari seluruh organisasi.</p>
          </div>

          <!-- API Available State -->
          <div v-if="!newsError && !isNewsLoading && posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <ArticleCard 
              v-for="post in posts" 
              :key="post.id"
              :title="post.judul"
              :excerpt="post.konten.substring(0, 100) || '...'"
              category="Berita"
              :publishedAt="new Date(post.created_at || '').toLocaleDateString('id-ID')"
              :image="post.cover_image || 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'"
              :href="`/org/demo/post/${post.slug || post.id}`"
            />
          </div>

          <!-- API Unavailable / Graceful State -->
          <div v-else class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
            <div class="mx-auto w-12 h-12 text-gray-400 mb-3">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">{{ newsError || 'Belum ada publikasi' }}</h3>
            <p class="mt-1 text-sm text-gray-500">Berita publik akan muncul di sini setelah layanan API tersedia.</p>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 pt-16 pb-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-brand-900 text-white rounded flex items-center justify-center font-bold text-xs">
              CMS
            </div>
            <div>
              <span class="block text-lg font-bold text-gray-900 tracking-tight">ORMAWA</span>
              <span class="block text-sm text-gray-500">Portal Organisasi Kemahasiswaan</span>
            </div>
          </div>
          <div class="flex gap-6">
            <a href="#beranda" class="text-sm text-gray-600 hover:text-brand-600 font-medium">Beranda</a>
            <a href="#organisasi" class="text-sm text-gray-600 hover:text-brand-600 font-medium">Direktori</a>
            <router-link to="/login" class="text-sm text-gray-600 hover:text-brand-600 font-medium">Admin Portal</router-link>
          </div>
        </div>
        <div class="pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-sm text-gray-500">
            &copy; {{ new Date().getFullYear() }} CMS ORMAWA. All rights reserved.
          </p>
          <p class="text-sm text-gray-400">
            Institutional Platform
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import OrganizationCard from '@/components/public/OrganizationCard.vue'
import ArticleCard from '@/components/public/ArticleCard.vue'
import type { Organization, Post } from '@/types/api'

const isMobileMenuOpen = ref(false)

const organizations = ref<Organization[]>([])
const isOrgLoading = ref(true)
const orgError = ref<string | null>(null)

const posts = ref<Post[]>([])
const isNewsLoading = ref(true)
const newsError = ref<string | null>(null)

const loadData = async () => {
  try {
    isOrgLoading.value = true
    orgError.value = null
    const orgsData = await publicService.getOrganizations()
    organizations.value = orgsData
  } catch (e: any) {
    orgError.value = e.message || 'Layanan belum tersedia'
  } finally {
    isOrgLoading.value = false
  }

  try {
    isNewsLoading.value = true
    newsError.value = null
    const postsData = await publicService.getPostsByTenant('global')
    posts.value = postsData
  } catch (e: any) {
    newsError.value = e.message || 'Kabar terbaru belum dapat dimuat.'
  } finally {
    isNewsLoading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
html {
  scroll-behavior: smooth;
}
</style>
