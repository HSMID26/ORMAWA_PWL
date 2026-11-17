<template>
  <div class="min-h-screen bg-[#F8F9FA] text-[#191C1D] flex flex-col font-sans selection:bg-[#00346F] selection:text-white">
    
    <!-- ─── 01. HERO BANNER (STITCH INSTITUTIONAL HERO) ──────────────────────── -->
    <section
      class="relative text-white w-full overflow-hidden transition-all duration-300 bg-cover bg-center"
      :style="heroStyle"
      style="min-height: 46vh;"
    >
      <!-- Contrast Overlay: Darker gradient when custom hero image is active -->
      <div
        v-if="heroImageUrl && !hasHeroError"
        class="absolute inset-0 z-0 bg-gradient-to-tr from-black/80 via-black/55 to-black/35 pointer-events-none"
      ></div>
      <!-- Subtle Institutional Texture / Gradient Overlay when fallback theme color is active -->
      <div
        v-else
        class="absolute inset-0 z-0 bg-gradient-to-tr from-black/50 via-black/20 to-white/10 pointer-events-none"
      ></div>

      <!-- Watermark Logo Background if available -->
      <div
        v-if="organization.logo"
        class="absolute -right-16 -bottom-16 w-96 h-96 opacity-10 pointer-events-none rounded-full overflow-hidden"
      >
        <img :src="resolveImageUrl(organization.logo)" :alt="organization.nama" class="w-full h-full object-contain filter grayscale" />
      </div>

      <!-- Hidden preloader to verify hero image validity -->
      <img
        v-if="heroImageUrl"
        :src="heroImageUrl"
        @error="onHeroError"
        class="hidden"
        alt="hero preloader"
      />

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24 flex flex-col justify-center">
        <div class="max-w-3xl space-y-4">
          <!-- Eyebrow / Label Caps -->
          <div class="flex items-center gap-2">
            <span class="rounded bg-white/20 backdrop-blur-xs px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white">
              {{ organization.jenis }}
            </span>
            <span class="text-xs text-white/80 font-semibold tracking-wide">
              &bull; Institut Teknologi Indonesia
            </span>
          </div>

          <!-- Hero Headline (Display font) -->
          <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
            {{ organization.hero_title || organization.nama }}
          </h1>

          <!-- Hero Subtitle -->
          <p class="text-sm sm:text-base md:text-lg text-white/90 leading-relaxed max-w-2xl font-normal">
            {{ organization.hero_subtitle || organization.slogan || organization.deskripsi || 'Membangun sinergi, inovasi, dan prestasi mahasiswa di lingkungan Institut Teknologi Indonesia.' }}
          </p>

          <!-- Hero CTAs -->
          <div class="flex flex-wrap items-center gap-3.5 pt-4">
            <a
              href="#about-section"
              class="rounded bg-white px-6 py-3 text-xs sm:text-sm font-bold text-[#191C1D] hover:bg-slate-100 shadow-sm transition duration-150 inline-flex items-center justify-center cursor-pointer"
            >
              Tentang Kami
            </a>

            <router-link
              v-if="hasModule('posts')"
              :to="`/organizations/${organization.subdomain}/articles`"
              class="rounded border border-white/80 bg-white/10 backdrop-blur-xs px-6 py-3 text-xs sm:text-sm font-semibold text-white hover:bg-white/20 transition duration-150 inline-flex items-center justify-center"
            >
              {{ organization.label_menu?.posts || organization.label_menu?.berita || 'Baca Warta' }} &rarr;
            </router-link>

            <router-link
              v-else-if="hasModule('agenda')"
              :to="`/organizations/${organization.subdomain}/agenda`"
              class="rounded border border-white/80 bg-white/10 backdrop-blur-xs px-6 py-3 text-xs sm:text-sm font-semibold text-white hover:bg-white/20 transition duration-150 inline-flex items-center justify-center"
            >
              {{ organization.label_menu?.agenda || organization.label_menu?.kegiatan || 'Jelajahi Agenda' }} &rarr;
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── 02. ANNOUNCEMENT BULLETIN BANNER (IF ACTIVE & AVAILABLE) ─────────── -->
    <div
      v-if="hasModule('announcements') && latestAnnouncement"
      class="border-b border-[#C2C6D3] bg-amber-50/80 px-4 sm:px-6 lg:px-8 py-3"
    >
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
          <span class="rounded bg-amber-200 text-amber-900 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider shrink-0">
            {{ organization.label_menu?.announcements || organization.label_menu?.pengumuman || 'Pengumuman' }}
          </span>
          <span class="text-xs font-bold text-[#191C1D] truncate">
            {{ latestAnnouncement.title }}
          </span>
        </div>
        <router-link
          :to="`/organizations/${organization.subdomain}/announcements`"
          class="text-xs font-bold text-[#00346F] hover:underline shrink-0"
        >
          Lihat Selengkapnya &rarr;
        </router-link>
      </div>
    </div>

    <!-- ─── MAIN CONTENT CONTAINER ───────────────────────────────────────────── -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-16 flex-grow w-full">
      
      <!-- ─── 03. PROFIL & INFORMASI UTAMA (2-COLUMN STITCH LAYOUT) ──────────── -->
      <section id="about-section" class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Description (7 cols) -->
        <div class="md:col-span-7 space-y-6">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Profil Organisasi</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#191C1D] mt-1 tracking-tight">
              Tentang {{ organization.nama }}
            </h2>
          </div>

          <div class="text-xs sm:text-sm text-[#424751] leading-relaxed space-y-4 whitespace-pre-line">
            <p>
              {{ organization.deskripsi_lengkap || organization.deskripsi || 'Himpunan mahasiswa / unit kegiatan mahasiswa ini merupakan wadah resmi bagi mahasiswa di lingkungan Institut Teknologi Indonesia untuk mengembangkan minat, bakat, keilmuan, dan kepemimpinan.' }}
            </p>
            <p v-if="organization.slogan && organization.deskripsi_lengkap" class="p-4 border-l-4 border-[#00346F] bg-white rounded-r-lg shadow-2xs italic text-[#191C1D]">
              "{{ organization.slogan }}"
            </p>
          </div>
        </div>

        <!-- Right: "Informasi Utama" Panel (5 cols) -->
        <div class="md:col-span-5">
          <div class="rounded-xl border border-[#C2C6D3] bg-white p-6 shadow-sm space-y-5">
            <h3 class="text-sm font-bold text-[#191C1D] border-b border-[#E1E3E4] pb-3">
              Informasi Utama & Kontak
            </h3>

            <ul class="space-y-4 text-xs">
              <!-- Jenis Organisasi -->
              <li class="flex items-start gap-3 pb-3 border-b border-[#F3F4F5]">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-[#00346F] flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  🏛️
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Jenis Organisasi</p>
                  <p class="font-bold text-[#191C1D] mt-0.5">{{ formatJenis(organization.jenis) }}</p>
                </div>
              </li>

              <!-- Periode Kepengurusan -->
              <li class="flex items-start gap-3 pb-3 border-b border-[#F3F4F5]">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-[#00346F] flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  📅
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Periode Kepengurusan</p>
                  <p class="font-bold text-[#191C1D] mt-0.5">
                    {{ organization.current_period?.period_name || 'Periode Berjalan' }}
                  </p>
                </div>
              </li>

              <!-- Alamat Sekretariat -->
              <li v-if="organization.alamat" class="flex items-start gap-3 pb-3 border-b border-[#F3F4F5]">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-[#00346F] flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  📍
                </div>
                <div class="min-w-0">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Alamat Sekretariat</p>
                  <p class="font-semibold text-[#191C1D] mt-0.5 leading-relaxed">{{ organization.alamat }}</p>
                </div>
              </li>

              <!-- Email Publik -->
              <li v-if="organization.email || organization.email_publik" class="flex items-start gap-3 pb-3 border-b border-[#F3F4F5]">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-[#00346F] flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  ✉️
                </div>
                <div class="min-w-0">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Email Resmi</p>
                  <a :href="`mailto:${organization.email || organization.email_publik}`" class="font-semibold text-[#00346F] hover:underline mt-0.5 truncate block">
                    {{ organization.email || organization.email_publik }}
                  </a>
                </div>
              </li>

              <!-- Telepon / WA -->
              <li v-if="organization.telepon || organization.media_sosial?.whatsapp" class="flex items-start gap-3 pb-3 border-b border-[#F3F4F5]">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-emerald-600 flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  📞
                </div>
                <div class="min-w-0">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Kontak WhatsApp / Telepon</p>
                  <a
                    :href="getWhatsAppUrl(organization.telepon, organization.media_sosial?.whatsapp)"
                    target="_blank"
                    rel="noopener"
                    class="font-semibold text-[#00346F] hover:underline mt-0.5 truncate block"
                  >
                    {{ organization.telepon || organization.media_sosial?.whatsapp }}
                  </a>
                </div>
              </li>

              <!-- Media Sosial & Website Links -->
              <li v-if="hasSocialMedia" class="flex items-start gap-3 pb-3 border-b border-[#F3F4F5]">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-[#00346F] flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  🌐
                </div>
                <div class="min-w-0">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Media Sosial Resmi</p>
                  <div class="flex flex-wrap gap-2 mt-1">
                    <a
                      v-if="organization.instagram || organization.media_sosial?.instagram"
                      :href="getInstagramUrl(organization.instagram || organization.media_sosial?.instagram)"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 hover:bg-pink-50 hover:text-pink-600 text-[11px] font-semibold transition"
                    >
                      Instagram
                    </a>
                    <a
                      v-if="organization.media_sosial?.youtube"
                      :href="organization.media_sosial.youtube"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-red-600 text-[11px] font-semibold transition"
                    >
                      YouTube
                    </a>
                    <a
                      v-if="organization.media_sosial?.tiktok"
                      :href="organization.media_sosial.tiktok"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 hover:bg-gray-200 text-[11px] font-semibold transition"
                    >
                      TikTok
                    </a>
                    <a
                      v-if="organization.media_sosial?.linkedin"
                      :href="organization.media_sosial.linkedin"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 hover:bg-blue-50 hover:text-blue-600 text-[11px] font-semibold transition"
                    >
                      LinkedIn
                    </a>
                    <a
                      v-if="organization.website_eksternal || organization.media_sosial?.website"
                      :href="organization.website_eksternal || organization.media_sosial?.website"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 hover:bg-brand-50 hover:text-brand-600 text-[11px] font-semibold transition"
                    >
                      Website
                    </a>
                  </div>
                </div>
              </li>

              <!-- Status -->
              <li class="flex items-start gap-3 pt-1">
                <div class="h-8 w-8 rounded-lg bg-[#F8F9FA] text-emerald-600 flex items-center justify-center shrink-0 border border-[#E1E3E4] font-bold">
                  ✓
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-[#737783]">Status Resmi</p>
                  <span class="inline-block mt-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold">
                    Aktif & Terdaftar
                  </span>
                </div>
              </li>
            </ul>
          </div>
        </div>

      </section>

      <!-- ─── 04. LATEST CONTENT (STITCH EDITORIAL STYLE) ─────────────────────── -->
      <section v-if="hasAnyContentModule" class="space-y-8">
        
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#C2C6D3] pb-4">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#00346F]">Pembaruan & Publikasi</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#191C1D] mt-0.5 tracking-tight">
              {{ formatSectionTitle }}
            </h2>
            <p class="text-xs sm:text-sm text-[#424751] mt-1">
              Informasi terkini dan agenda resmi kegiatan {{ organization.nama }}.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <router-link
              v-if="hasModule('posts')"
              :to="`/organizations/${organization.subdomain}/articles`"
              class="text-xs font-bold text-[#00346F] hover:underline"
            >
              Semua {{ organization.label_menu?.posts || organization.label_menu?.berita || 'Warta' }} &rarr;
            </router-link>
            <router-link
              v-if="hasModule('agenda')"
              :to="`/organizations/${organization.subdomain}/agenda`"
              class="text-xs font-bold text-[#00346F] hover:underline"
            >
              Semua {{ organization.label_menu?.agenda || organization.label_menu?.kegiatan || 'Agenda' }} &rarr;
            </router-link>
          </div>
        </div>

        <!-- Dynamic 3-Column Content Grid -->
        <div v-if="combinedLatestItems.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <template v-for="item in combinedLatestItems" :key="item.key">
            
            <!-- Article Card (Stitch Style) -->
            <article
              v-if="item.type === 'article'"
              class="bg-white border border-[#C2C6D3] rounded-xl overflow-hidden group hover:shadow-md hover:border-[#00346F] transition duration-200 flex flex-col shadow-2xs"
            >
              <router-link
                :to="`/organizations/${organization.subdomain}/articles/${item.data.slug}`"
                class="w-full h-48 bg-[#F3F4F5] overflow-hidden relative block"
              >
                <img
                  v-if="item.data.cover_image"
                  :src="item.data.cover_image"
                  :alt="item.data.judul"
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                  loading="lazy"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-xs font-bold text-[#737783] bg-gradient-to-br from-[#F8F9FA] to-[#E7E8E9]">
                  {{ organization.nama }}
                </div>
                <span
                  v-if="item.data.category"
                  class="absolute top-3 left-3 rounded bg-white/95 px-2 py-0.5 text-[10px] font-bold text-[#00346F] shadow-xs"
                >
                  {{ item.data.category.name }}
                </span>
              </router-link>

              <div class="p-5 flex flex-col justify-between flex-grow space-y-3">
                <div class="space-y-1.5">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-[#00346F] block">
                    {{ organization.label_menu?.posts || organization.label_menu?.berita || 'Warta' }}
                  </span>
                  <h3 class="text-sm sm:text-base font-bold text-[#191C1D] group-hover:text-[#00346F] transition line-clamp-2 leading-snug">
                    <router-link :to="`/organizations/${organization.subdomain}/articles/${item.data.slug}`">
                      {{ item.data.judul }}
                    </router-link>
                  </h3>
                  <p class="text-xs text-[#424751] line-clamp-3 leading-relaxed">
                    {{ item.data.excerpt }}
                  </p>
                </div>

                <div class="pt-3 border-t border-[#E1E3E4] text-[10px] text-[#737783] flex items-center justify-between">
                  <span>{{ formatDate(item.data.published_at) }}</span>
                  <span class="font-semibold text-[#191C1D]">{{ item.data.author?.name || 'Redaksi' }}</span>
                </div>
              </div>
            </article>

            <!-- Agenda Card (Stitch Style) -->
            <article
              v-else-if="item.type === 'agenda'"
              class="bg-white border border-[#C2C6D3] rounded-xl overflow-hidden group hover:shadow-md hover:border-[#00346F] transition duration-200 flex flex-col shadow-2xs"
            >
              <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-[#00346F] block">
                    {{ organization.label_menu?.agenda || organization.label_menu?.kegiatan || 'Agenda Resmi' }}
                  </span>
                  <h3 class="text-base font-bold text-[#191C1D] group-hover:text-[#00346F] transition leading-snug">
                    <router-link :to="`/organizations/${organization.subdomain}/agenda`">
                      {{ item.data.judul }}
                    </router-link>
                  </h3>
                  <p class="text-xs text-[#424751] line-clamp-2 leading-relaxed">
                    {{ item.data.deskripsi }}
                  </p>
                </div>

                <!-- Date & Location Block -->
                <div class="pt-4 border-t border-[#E1E3E4] flex items-center gap-3.5">
                  <div
                    class="rounded p-2 text-center min-w-[54px] shrink-0 text-white shadow-2xs"
                    :style="{ backgroundColor: organization.warna_tema || '#00346F' }"
                  >
                    <span class="block text-[10px] font-bold uppercase leading-tight">{{ getMonthShort(item.data.tanggal_pelaksanaan) }}</span>
                    <span class="block text-xl font-extrabold leading-none mt-0.5">{{ getDay(item.data.tanggal_pelaksanaan) }}</span>
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-bold text-[#191C1D] truncate">{{ item.data.tempat || 'Kampus ITI' }}</p>
                    <p class="text-[11px] text-[#737783] mt-0.5">{{ item.data.waktu || 'Waktu dijadwalkan' }}</p>
                  </div>
                </div>
              </div>
            </article>

          </template>
        </div>

        <!-- Empty Content Fallback -->
        <div v-else class="py-12 text-center border border-dashed border-[#C2C6D3] rounded-xl bg-white p-6 space-y-2">
          <p class="text-xs sm:text-sm font-bold text-[#191C1D]">Belum ada warta atau agenda yang dipublikasikan.</p>
          <p class="text-xs text-[#737783]">Dokumentasi kegiatan dan rilis warta terbaru akan ditampilkan di bagian ini.</p>
        </div>
      </section>

      <!-- ─── 05. MODULAR SHORTCUT STRIPS (GALERI & DOKUMEN & STRUKTUR) ────────── -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Galeri Foto Shortcut -->
        <div
          v-if="hasModule('galeri') || hasModule('gallery')"
          class="rounded-xl border border-[#C2C6D3] bg-white p-5 space-y-3 shadow-2xs flex flex-col justify-between"
        >
          <div class="space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#00346F]">Dokumentasi Visual</span>
            <h3 class="text-sm font-bold text-[#191C1D]">
              {{ organization.label_menu?.galeri || organization.label_menu?.gallery || 'Galeri Foto Kegiatan' }}
            </h3>
            <p class="text-xs text-[#737783] leading-relaxed">
              Arsip foto dan dokumentasi kegiatan resmi yang diselenggarakan oleh {{ organization.nama }}.
            </p>
          </div>

          <div class="pt-2">
            <router-link
              :to="`/organizations/${organization.subdomain}/gallery`"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00346F] hover:underline"
            >
              <span>Buka {{ organization.label_menu?.galeri || organization.label_menu?.gallery || 'Galeri Foto' }}</span>
              <span>&rarr;</span>
            </router-link>
          </div>
        </div>

        <!-- Dokumen Publik Shortcut -->
        <div
          v-if="hasModule('documents')"
          class="rounded-xl border border-[#C2C6D3] bg-white p-5 space-y-3 shadow-2xs flex flex-col justify-between"
        >
          <div class="space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#00346F]">Arsip Resmi</span>
            <h3 class="text-sm font-bold text-[#191C1D]">
              {{ organization.label_menu?.documents || organization.label_menu?.dokumen || 'Dokumen & Berkas' }}
            </h3>
            <p class="text-xs text-[#737783] leading-relaxed">
              Unduh Surat Keputusan (SK), proposal, pedoman SOP, dan berkas format resmi.
            </p>
          </div>

          <div class="pt-2">
            <router-link
              :to="`/organizations/${organization.subdomain}/documents`"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00346F] hover:underline"
            >
              <span>Akses {{ organization.label_menu?.documents || organization.label_menu?.dokumen || 'Dokumen Publik' }}</span>
              <span>&rarr;</span>
            </router-link>
          </div>
        </div>

        <!-- Struktur Kepengurusan Shortcut -->
        <div
          v-if="hasModule('structure')"
          class="rounded-xl border border-[#C2C6D3] bg-white p-5 space-y-3 shadow-2xs flex flex-col justify-between"
        >
          <div class="space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#00346F]">Badan Pengurus</span>
            <h3 class="text-sm font-bold text-[#191C1D]">
              {{ organization.label_menu?.structure || organization.label_menu?.struktur || 'Struktur Organisasi' }}
            </h3>
            <p class="text-xs text-[#737783] leading-relaxed">
              Daftar susunan fungsionaris dan divisi kepengurusan periode {{ organization.current_period?.period_name || 'aktif' }}.
            </p>
          </div>

          <div class="pt-2">
            <router-link
              :to="`/organizations/${organization.subdomain}/structure`"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00346F] hover:underline"
            >
              <span>Lihat {{ organization.label_menu?.structure || organization.label_menu?.struktur || 'Bagan Struktur' }}</span>
              <span>&rarr;</span>
            </router-link>
          </div>
        </div>

      </section>

    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { publicService } from '@/services/publicService'
import { useSeoMeta } from '@/composables/useSeoMeta'
import { resolveImageUrl } from '@/utils/imageUrl'
import type {
  PublicOrganization,
  PublicArticle,
  PublicAgenda,
  PublicAnnouncement,
  PublicMedia,
} from '@/types/public'

const props = defineProps<{ organization: PublicOrganization }>()

const articles = ref<PublicArticle[]>([])
const agenda = ref<PublicAgenda[]>([])
const announcements = ref<PublicAnnouncement[]>([])
const gallery = ref<PublicMedia[]>([])
const hasHeroError = ref(false)

watch(() => props.organization?.hero_image, () => {
  hasHeroError.value = false
})

const onHeroError = () => {
  hasHeroError.value = true
}

const heroImageUrl = computed(() => {
  return resolveImageUrl(props.organization?.hero_image)
})

const heroStyle = computed(() => {
  const url = heroImageUrl.value
  if (url && !hasHeroError.value) {
    return {
      backgroundImage: `url("${url}")`,
      backgroundColor: props.organization?.warna_tema || '#00346F'
    }
  }
  return {
    backgroundColor: props.organization?.warna_tema || '#00346F'
  }
})

const hasSocialMedia = computed(() => {
  const s = props.organization.media_sosial || {}
  return !!(props.organization.instagram || s.instagram || s.youtube || s.tiktok || s.linkedin || s.website || props.organization.website_eksternal)
})

const formatSectionTitle = computed(() => {
  const postLabel = props.organization.label_menu?.posts || props.organization.label_menu?.berita || 'Berita'
  const agendaLabel = props.organization.label_menu?.agenda || props.organization.label_menu?.kegiatan || 'Agenda'
  if (hasModule('posts') && hasModule('agenda')) {
    return `${postLabel} & ${agendaLabel} Terbaru`
  }
  if (hasModule('posts')) return `${postLabel} Terbaru`
  if (hasModule('agenda')) return `${agendaLabel} Terbaru`
  return 'Pembaruan & Publikasi'
})

const hasModule = (modName: string) => {
  if (!props.organization.modules) return true
  const mods = props.organization.modules as Record<string, any>
  if (modName === 'announcements' || modName === 'pengumuman') {
    return mods.announcements !== false && mods.pengumuman !== false
  }
  if (modName === 'galeri' || modName === 'gallery') {
    return mods.galeri !== false && mods.gallery !== false
  }
  if (modName === 'posts' || modName === 'articles' || modName === 'berita') {
    return mods.posts !== false && mods.articles !== false && mods.berita !== false
  }
  if (modName === 'agenda' || modName === 'kegiatan' || modName === 'activities') {
    return mods.agenda !== false && mods.kegiatan !== false && mods.activities !== false
  }
  if (modName === 'documents' || modName === 'dokumen') {
    return mods.documents !== false && mods.dokumen !== false
  }
  if (modName === 'structure' || modName === 'struktur') {
    return mods.structure !== false && mods.struktur !== false
  }
  return mods[modName] !== false
}

const hasAnyContentModule = computed(() => {
  return hasModule('posts') || hasModule('agenda')
})

const latestAnnouncement = computed(() => {
  return announcements.value.length > 0 ? announcements.value[0] : null
})

const combinedLatestItems = computed(() => {
  const items: Array<{ key: string; type: 'article' | 'agenda'; data: any }> = []

  if (hasModule('posts') && articles.value.length > 0) {
    articles.value.slice(0, 2).forEach((art) => {
      items.push({ key: `art-${art.id}`, type: 'article', data: art })
    })
  }

  if (hasModule('agenda') && agenda.value.length > 0) {
    agenda.value.slice(0, 1).forEach((act) => {
      items.push({ key: `act-${act.id}`, type: 'agenda', data: act })
    })
  }

  return items.slice(0, 3)
})

const formatJenis = (jenis?: string) => {
  if (!jenis) return 'Organisasi Mahasiswa'
  if (jenis === 'HMPS') return 'Himpunan Mahasiswa Program Studi (HMPS)'
  if (jenis === 'UKM') return 'Unit Kegiatan Mahasiswa (UKM)'
  if (jenis === 'BEM') return 'Badan Eksekutif Mahasiswa (BEM)'
  if (jenis === 'Senat') return 'Dewan Perwakilan Mahasiswa (DPM)'
  return jenis
}

const getInstagramUrl = (handle?: string) => {
  if (!handle) return '#'
  if (handle.startsWith('http')) return handle
  const clean = handle.replace(/^@/, '')
  return `https://instagram.com/${clean}`
}

const getWhatsAppUrl = (phone?: string | null, waLink?: string | null) => {
  if (waLink && waLink.startsWith('http')) return waLink
  const raw = phone || waLink || ''
  const clean = raw.replace(/\D/g, '')
  const intl = clean.startsWith('0') ? '62' + clean.substring(1) : clean
  return `https://wa.me/${intl}`
}

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(new Date(dateStr))
}

const getMonthShort = (dateStr?: string | null) => {
  if (!dateStr) return 'OKT'
  try {
    return new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(new Date(dateStr)).toUpperCase()
  } catch {
    return 'OKT'
  }
}

const getDay = (dateStr?: string | null) => {
  if (!dateStr) return '01'
  try {
    return new Date(dateStr).getDate()
  } catch {
    return '01'
  }
}

useSeoMeta(() => ({
  title: props.organization.seo_title || `${props.organization.nama} — Institut Teknologi Indonesia`,
  description: props.organization.seo_description || props.organization.deskripsi || `Portal resmi ${props.organization.nama} Institut Teknologi Indonesia.`,
  ogImage: props.organization.og_image || props.organization.hero_image || props.organization.logo || undefined,
  ogType: 'website',
}))

const loadHomeData = async () => {
  const slug = props.organization.subdomain
  if (!slug) return

  try {
    if (hasModule('posts')) {
      const artRes = await publicService.getArticles(slug, { per_page: 3 })
      articles.value = artRes?.data || []
    }
  } catch (e) {
    console.error('Error fetching articles preview:', e)
  }

  try {
    if (hasModule('agenda')) {
      const actRes = await publicService.getAgenda(slug, { per_page: 3 })
      agenda.value = actRes?.data || []
    }
  } catch (e) {
    console.error('Error fetching agenda preview:', e)
  }

  try {
    if (hasModule('announcements')) {
      const annRes = await publicService.getAnnouncements(slug)
      announcements.value = Array.isArray(annRes) ? annRes : ((annRes as any)?.data || [])
    }
  } catch (e) {
    console.error('Error fetching announcements preview:', e)
  }

  try {
    if (hasModule('galeri') || hasModule('gallery')) {
      const galRes = await publicService.getGallery(slug, { per_page: 3 })
      gallery.value = galRes?.data || []
    }
  } catch (e) {
    console.error('Error fetching gallery preview:', e)
  }
}

onMounted(() => {
  loadHomeData()
})
</script>
