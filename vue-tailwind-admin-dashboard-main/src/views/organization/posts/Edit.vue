<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Edit Artikel'" />

    <!-- ─── Header & Top Actions ─────────────────────────────────────────────── -->
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <PenToolIcon class="h-5 w-5 text-brand-500" />
          Edit Artikel
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
          Perbarui konten, status publikasi, dan pengaturan SEO artikel.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="isPreviewModalOpen = true"
          class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition"
        >
          <EyeIcon class="h-4 w-4" />
          Live Preview
        </button>

        <button
          type="button"
          @click="savePost('draft')"
          :disabled="isSubmitting"
          class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition disabled:opacity-50"
        >
          <SaveIcon class="h-4 w-4" />
          Simpan Draft
        </button>

        <button
          type="button"
          @click="handlePrimaryPublish"
          :disabled="isSubmitting"
          class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition disabled:opacity-50"
        >
          <SendIcon class="h-4 w-4" />
          {{ canPublish ? (form.status === 'published' ? 'Perbarui Publikasi' : 'Publikasikan') : 'Kirim untuk Review' }}
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="py-12 text-center text-xs text-gray-400">
      Memuat data artikel...
    </div>

    <!-- ─── 2-Column Professional CMS Layout ──────────────────────────────────── -->
    <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-12">
      <!-- ─── Left Column: Main Editor (8 Cols) ──────────────────────────────── -->
      <div class="space-y-6 lg:col-span-8">
        <!-- Judul Artikel -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
            Judul Artikel <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.judul"
            type="text"
            required
            placeholder="Masukkan judul artikel yang menarik..."
            class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-lg font-bold text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          />
          <div class="mt-2 flex items-center justify-between text-[11px] text-gray-400">
            <span>Slug URL: <span class="font-mono text-gray-600 dark:text-gray-300">{{ generatedSlug }}</span></span>
            <span>{{ form.judul.length }}/100 Karakter</span>
          </div>
        </div>

        <!-- Rich Content Editor (TipTap) -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            Isi Konten Artikel <span class="text-rose-500">*</span>
          </label>
          <RichTextEditor
            ref="richEditorRef"
            v-model="form.konten"
            placeholder="Tulis artikel Anda di sini. Gunakan tombol Sisipkan Foto di toolbar untuk menambahkan gambar..."
            @open-media-picker="isInlineMediaPickerOpen = true"
          />
        </div>

        <!-- Ringkasan Singkat (Excerpt) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
            Ringkasan Singkat (Excerpt)
          </label>
          <textarea
            v-model="form.excerpt"
            rows="3"
            placeholder="Tuliskan 1-2 kalimat ringkasan artikel ini..."
            class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
          ></textarea>
          <p class="text-[11px] text-gray-400 mt-1 text-right">{{ form.excerpt.length }}/250 Karakter</p>
        </div>
      </div>

      <!-- ─── Right Column: Publishing Sidebar (4 Cols) ───────────────────────── -->
      <div class="space-y-6 lg:col-span-4">
        <!-- Panel 1: Status & Visibility -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
            Publikasi & Workflow
          </h2>

          <div class="space-y-3.5 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Publikasi</label>
              <select
                v-model="form.status"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              >
                <option value="draft">Draft (Disimpan Pribadi)</option>
                <option value="review">Ajukan Review (Menunggu Persetujuan)</option>
                <option v-if="canPublish" value="published">Published (Terbit Publik)</option>
                <option v-if="canPublish" value="rejected">Ditolak (Rejected)</option>
              </select>
            </div>

            <div class="rounded-lg bg-gray-50 p-3 text-[11px] text-gray-600 dark:bg-gray-800 dark:text-gray-400 space-y-1">
              <div class="flex justify-between">
                <span>Visibilitas:</span>
                <span class="font-semibold text-gray-900 dark:text-white">Publik</span>
              </div>
              <div class="flex justify-between">
                <span>Penulis:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ authorName }}</span>
              </div>
              <div class="flex justify-between">
                <span>Role Anda:</span>
                <span class="font-semibold text-brand-600 dark:text-brand-400">{{ authStore.role }}</span>
              </div>
            </div>

            <div v-if="canPublish && form.status === 'review'" class="pt-2 flex gap-2">
              <button
                type="button"
                @click="savePost('published')"
                class="flex-1 rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition"
              >
                Setujui & Terbitkan
              </button>
              <button
                type="button"
                @click="savePost('rejected')"
                class="rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition"
              >
                Tolak
              </button>
            </div>
          </div>
        </div>

                <!-- Panel 2: Featured Image (Gambar Sampul) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
              Gambar Sampul (Featured Image)
            </h2>
            <button
              v-if="form.cover_image"
              type="button"
              @click="isCoverMediaPickerOpen = true"
              class="text-[11px] font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 inline-flex items-center gap-1 transition cursor-pointer"
            >
              <ImageIcon class="h-3.5 w-3.5" />
              Ganti Foto
            </button>
          </div>

          <div class="space-y-3">
            <!-- Preview Box when cover exists -->
            <div
              v-if="form.cover_image"
              class="relative aspect-video w-full rounded-xl overflow-hidden bg-gray-100 border border-gray-200 dark:border-gray-700 group"
            >
              <img :src="resolveImageUrl(form.cover_image)" alt="Featured Image Preview" class="h-full w-full object-cover" />
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <button
                  type="button"
                  @click="isCoverMediaPickerOpen = true"
                  class="rounded-lg bg-white/95 px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-white transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                >
                  <ImageIcon class="h-3.5 w-3.5 text-brand-600" />
                  Ganti Foto
                </button>
                <button
                  type="button"
                  @click="removeCoverImage"
                  class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                >
                  <Trash2Icon class="h-3.5 w-3.5" />
                  Hapus
                </button>
              </div>
            </div>

            <!-- Single Add Cover Button (opens Media Picker Modal) -->
            <div v-else>
              <button
                type="button"
                @click="isCoverMediaPickerOpen = true"
                class="w-full flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-6 text-center hover:border-brand-500 cursor-pointer dark:border-gray-700 dark:hover:border-brand-400 transition bg-gray-50/50 dark:bg-gray-800/30 group"
              >
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 mb-2 group-hover:scale-110 transition">
                  <PlusIcon class="h-5 w-5" />
                </div>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Pilih / Tambah Gambar Sampul</span>
                <span class="text-[10px] text-gray-400 mt-1">Pilih dari pustaka media atau upload foto baru</span>
              </button>
            </div>

            <!-- Alt Text for SEO -->
            <div>
              <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">
                Alt Text Gambar (SEO & Aksesibilitas)
              </label>
              <input
                v-model="altText"
                type="text"
                placeholder="Deskripsi gambar untuk pembaca tuna netra & search engine..."
                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              />
            </div>
          </div>
        </div>

        <!-- Panel 3: Kategori & Tag -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
            Kategori & Tag
          </h2>

          <div class="space-y-4 text-xs">
            <!-- Kategori Dropdown -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="font-semibold text-gray-700 dark:text-gray-300">
                  Kategori Artikel
                </label>
                <button
                  type="button"
                  @click="isAddingCategory = !isAddingCategory"
                  class="text-[11px] font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                >
                  {{ isAddingCategory ? 'Batal' : '+ Kategori Baru' }}
                </button>
              </div>

              <!-- Quick Add Category Input -->
              <div v-if="isAddingCategory" class="mb-2 flex items-center gap-2">
                <input
                  v-model="newCategoryName"
                  type="text"
                  placeholder="Nama kategori baru..."
                  class="h-8 flex-1 rounded-lg border border-brand-300 bg-white px-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  @keyup.enter="handleCreateCategory"
                />
                <button
                  type="button"
                  @click="handleCreateCategory"
                  :disabled="isCreatingCategory || !newCategoryName.trim()"
                  class="h-8 rounded-lg bg-brand-600 px-3 text-[11px] font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                >
                  {{ isCreatingCategory ? 'Menyimpan...' : 'Tambah' }}
                </button>
              </div>

              <select
                v-model="form.category_id"
                :disabled="isLoading"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-60"
              >
                <option v-if="isLoading" :value="undefined" disabled>Memuat daftar kategori...</option>
                <option v-else-if="categories.length === 0" :value="undefined" disabled>Belum ada kategori tersedia</option>
                <option v-else :value="undefined">-- Pilih Kategori --</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Tag Multi-select Chips -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="font-semibold text-gray-700 dark:text-gray-300">
                  Pilih Tag Terkait
                </label>
                <button
                  type="button"
                  @click="isAddingTag = !isAddingTag"
                  class="text-[11px] font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                >
                  {{ isAddingTag ? 'Batal' : '+ Tag Baru' }}
                </button>
              </div>

              <!-- Quick Add Tag Input -->
              <div v-if="isAddingTag" class="mb-2 flex items-center gap-2">
                <input
                  v-model="newTagName"
                  type="text"
                  placeholder="Nama tag baru..."
                  class="h-8 flex-1 rounded-lg border border-brand-300 bg-white px-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  @keyup.enter="handleCreateTag"
                />
                <button
                  type="button"
                  @click="handleCreateTag"
                  :disabled="isCreatingTag || !newTagName.trim()"
                  class="h-8 rounded-lg bg-brand-600 px-3 text-[11px] font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                >
                  {{ isCreatingTag ? 'Menyimpan...' : 'Tambah' }}
                </button>
              </div>

              <div v-if="isLoading" class="flex gap-2 animate-pulse">
                <div class="h-6 w-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                <div class="h-6 w-20 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                <div class="h-6 w-14 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
              </div>
              <div v-else-if="tags.length > 0" class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto pr-1">
                <button
                  type="button"
                  v-for="tag in tags"
                  :key="tag.id"
                  @click="toggleTag(tag.id)"
                  :class="[
                    'rounded-full px-2.5 py-1 text-[11px] font-medium transition',
                    form.tags.includes(tag.id)
                      ? 'bg-brand-600 text-white shadow-sm'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                  ]"
                >
                  #{{ tag.name }}
                </button>
              </div>
              <p v-else class="text-[11px] text-gray-400">Belum ada data tag.</p>
            </div>
          </div>
        </div>

        <!-- Panel 4: Pengaturan Metadata SEO -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
            Optimasi Mesin Pencari (SEO)
          </h2>

          <div class="space-y-3.5 text-xs">
            <div>
              <div class="flex justify-between items-center mb-1">
                <label class="font-semibold text-gray-700 dark:text-gray-300">Meta Title</label>
                <span class="text-[10px] text-gray-400">{{ (form.meta_title || '').length }}/60</span>
              </div>
              <input
                v-model="form.meta_title"
                type="text"
                placeholder="Judul artikel untuk Google SEO..."
                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              />
            </div>

            <div>
              <div class="flex justify-between items-center mb-1">
                <label class="font-semibold text-gray-700 dark:text-gray-300">Meta Description</label>
                <span class="text-[10px] text-gray-400">{{ (form.meta_description || '').length }}/160</span>
              </div>
              <textarea
                v-model="form.meta_description"
                rows="2.5"
                placeholder="Deskripsi singkat artikel untuk cuplikan Google..."
                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              ></textarea>
            </div>

            <!-- Google Snippet Simulation -->
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-xs">
              <span class="text-[10px] font-bold uppercase text-gray-400 block mb-1">Google SERP Preview</span>
              <p class="text-[11px] text-blue-700 dark:text-blue-400 font-medium truncate">
                {{ form.meta_title || form.judul || 'Judul Berita Organisasi' }}
              </p>
              <p class="text-[10px] text-emerald-700 dark:text-emerald-400 truncate mt-0.5">
                https://ormawa.kampus.ac.id/berita/{{ generatedSlug }}
              </p>
              <p class="text-[10px] text-gray-500 dark:text-gray-400 line-clamp-2 mt-0.5">
                {{ form.meta_description || form.excerpt || 'Cuplikan ringkasan artikel yang akan terlihat oleh pengguna saat mencari berita di Google...' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Modal Media Picker for Cover Image ──────────────────────────────── -->
    <MediaPickerModal
      :isOpen="isCoverMediaPickerOpen"
      mode="article"
      title="Pilih Gambar Sampul Artikel"
      @close="isCoverMediaPickerOpen = false"
      @select="handleCoverMediaSelect"
    />

    <!-- ─── Modal Media Picker for Inline Content ─────────────────────────────── -->
    <MediaPickerModal
      :isOpen="isInlineMediaPickerOpen"
      mode="article"
      title="Sisipkan Foto ke Isi Artikel"
      @close="isInlineMediaPickerOpen = false"
      @select="handleInlineMediaSelect"
    />

    <!-- ─── Modal Live Preview ────────────────────────────────────────────────── -->
    <div
      v-if="isPreviewModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="isPreviewModalOpen = false"
    >
      <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8 overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/60">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Live Preview Artikel</span>
            <span class="rounded bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-600 uppercase">{{ form.status }}</span>
          </div>

          <div class="flex items-center gap-2">
            <div class="flex rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-800">
              <button
                @click="previewDevice = 'desktop'"
                :class="['p-1 rounded text-xs', previewDevice === 'desktop' ? 'bg-brand-500 text-white' : 'text-gray-500 hover:text-gray-700']"
              >
                <MonitorIcon class="h-3.5 w-3.5" />
              </button>
              <button
                @click="previewDevice = 'mobile'"
                :class="['p-1 rounded text-xs', previewDevice === 'mobile' ? 'bg-brand-500 text-white' : 'text-gray-500 hover:text-gray-700']"
              >
                <SmartphoneIcon class="h-3.5 w-3.5" />
              </button>
            </div>
            <button @click="isPreviewModalOpen = false" class="rounded-lg p-1 text-gray-400 hover:text-gray-600">
              <XIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <div class="p-6 max-h-[75vh] overflow-y-auto flex justify-center bg-gray-100 dark:bg-gray-950">
          <div
            :class="[
              'bg-white dark:bg-gray-900 rounded-xl p-6 shadow-sm transition-all',
              previewDevice === 'mobile' ? 'max-w-sm border-4 border-gray-800 rounded-3xl' : 'w-full max-w-2xl'
            ]"
          >
            <div v-if="form.cover_image" class="mb-4 rounded-xl overflow-hidden aspect-video bg-gray-100">
              <img :src="resolveImageUrl(form.cover_image)" :alt="form.judul" class="h-full w-full object-cover" />
            </div>

            <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
              <span v-if="selectedCategoryName" class="font-bold text-brand-600">
                {{ selectedCategoryName }}
              </span>
              <span>•</span>
              <span>{{ new Date().toLocaleDateString('id-ID') }}</span>
              <span>•</span>
              <span>Oleh {{ authorName }}</span>
            </div>

            <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight mb-3">
              {{ form.judul || 'Judul Artikel Anda' }}
            </h1>

            <p v-if="form.excerpt" class="text-xs font-medium text-gray-600 dark:text-gray-300 italic mb-4 border-l-2 border-brand-500 pl-3">
              {{ form.excerpt }}
            </p>

            <div class="prose prose-sm dark:prose-invert max-w-none text-xs text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">
              {{ form.konten || 'Tuliskan konten artikel Anda di editor...' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import MediaPickerModal from '@/components/organization/MediaPickerModal.vue'
import RichTextEditor from '@/components/common/RichTextEditor.vue'
import { postService } from '@/services/postService'
import { taxonomyService } from '@/services/taxonomyService'
import type { MediaItem } from '@/services/mediaService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { resolveImageUrl } from '@/utils/imageUrl'
import type { Category, Tag } from '@/types/api'
import {
  PenToolIcon,
  EyeIcon,
  SaveIcon,
  SendIcon,
  BoldIcon,
  ItalicIcon,
  UnderlineIcon,
  StrikethroughIcon,
  ListIcon,
  ListOrderedIcon,
  QuoteIcon,
  LinkIcon,
  MinusIcon,
  CodeIcon,
  AlignCenterIcon,
  AlignRightIcon,
  UploadCloudIcon,
  XIcon,
  ImageIcon,
  MonitorIcon,
  SmartphoneIcon
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toastStore = useToastStore()

const postId = Number(route.params.id)
const isContributor = computed(() => authStore.role === 'Kontributor')
const canPublish = computed(() => authStore.hasPermission('posts.publish') || authStore.role === 'Super Admin')

const categories = ref<Category[]>([])
const tags = ref<Tag[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const isPreviewModalOpen = ref(false)
const previewDevice = ref<'desktop' | 'mobile'>('desktop')
const altText = ref('')
const authorName = ref('')
const isCoverMediaPickerOpen = ref(false)
const isInlineMediaPickerOpen = ref(false)

const handleCoverMediaSelect = (item: MediaItem) => {
  form.value.cover_image = item.url || item.image_url || null
  if (!altText.value) {
    altText.value = item.alt_text || item.title || item.name || ''
  }
}

const handleInlineMediaSelect = (item: MediaItem) => {
  const alt = item.alt_text || item.title || item.name || 'Foto Dokumentasi'
  const title = item.caption || item.alt_text || item.title || ''
  const imgUrl = resolveImageUrl(item.image_url || item.url)

  if (richEditorRef.value?.insertImage) {
    richEditorRef.value.insertImage({
      src: imgUrl,
      alt,
      title,
      caption: title
    })
  }

  isInlineMediaPickerOpen.value = false
}


const richEditorRef = ref<any>(null)

const form = ref<{
  judul: string
  konten: string
  excerpt: string
  cover_image: string | null
  status: 'draft' | 'review' | 'published' | 'rejected'
  category_id: number | undefined
  tags: number[]
  meta_title: string
  meta_description: string
}>({
  judul: '',
  konten: '',
  excerpt: '',
  cover_image: null,
  status: 'draft',
  category_id: undefined,
  tags: [],
  meta_title: '',
  meta_description: '',
})

const generatedSlug = computed(() => {
  return form.value.judul
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '') || 'artikel'
})

const selectedCategoryName = computed(() => {
  const c = categories.value.find(cat => cat.id === form.value.category_id)
  return c ? c.name : ''
})

const wordCount = computed(() => {
  const text = form.value.konten.trim()
  if (!text) return 0
  return text.split(/\s+/).length
})

const charCount = computed(() => form.value.konten.length)

const readingTime = computed(() => {
  const words = wordCount.value
  return Math.max(1, Math.ceil(words / 200))
})

const isAddingCategory = ref(false)
const newCategoryName = ref('')
const isCreatingCategory = ref(false)

const isAddingTag = ref(false)
const newTagName = ref('')
const isCreatingTag = ref(false)

const loadPostAndTaxonomies = async () => {
  isLoading.value = true
  try {
    const [postData, catsData, tagsData] = await Promise.all([
      postService.getById(postId),
      taxonomyService.getCategories(),
      taxonomyService.getTags()
    ])

    categories.value = catsData || []
    tags.value = tagsData || []

    form.value = {
      judul: postData.judul,
      konten: postData.konten,
      excerpt: postData.excerpt || '',
      cover_image: postData.cover_image || null,
      status: (['draft', 'review', 'published', 'rejected'].includes(postData.status) ? postData.status : 'draft') as 'draft' | 'review' | 'published' | 'rejected',
      category_id: postData.category_id || undefined,
      tags: postData.tags && Array.isArray(postData.tags) ? postData.tags.map((t: any) => typeof t === 'number' ? t : t.id) : [],
      meta_title: postData.meta_title || '',
      meta_description: postData.meta_description || '',
    }

    authorName.value = postData.user?.name || authStore.user?.name || 'Admin'
  } catch (error: any) {
    toastStore.error('Gagal memuat artikel: ' + error.message)
    router.push('/organization/posts')
  } finally {
    isLoading.value = false
  }
}

const handleCreateCategory = async () => {
  const name = newCategoryName.value.trim()
  if (!name) return

  isCreatingCategory.value = true
  try {
    const created = await taxonomyService.createCategory(name)
    categories.value.push(created)
    form.value.category_id = created.id
    newCategoryName.value = ''
    isAddingCategory.value = false
    toastStore.success(`Kategori "${created.name}" berhasil ditambahkan!`)
  } catch (err: any) {
    toastStore.error(err.message || 'Gagal menambahkan kategori.')
  } finally {
    isCreatingCategory.value = false
  }
}

const handleCreateTag = async () => {
  const name = newTagName.value.trim()
  if (!name) return

  isCreatingTag.value = true
  try {
    const created = await taxonomyService.createTag(name)
    tags.value.push(created)
    if (!form.value.tags.includes(created.id)) {
      form.value.tags.push(created.id)
    }
    newTagName.value = ''
    isAddingTag.value = false
    toastStore.success(`Tag #${created.name} berhasil ditambahkan!`)
  } catch (err: any) {
    toastStore.error(err.message || 'Gagal menambahkan tag.')
  } finally {
    isCreatingTag.value = false
  }
}

const toggleTag = (tagId: number) => {
  const idx = form.value.tags.indexOf(tagId)
  if (idx > -1) {
    form.value.tags.splice(idx, 1)
  } else {
    form.value.tags.push(tagId)
  }
}


const removeCoverImage = () => {
  form.value.cover_image = null
  altText.value = ''
}


const savePost = async (targetStatus?: 'draft' | 'review' | 'published' | 'rejected') => {
  if (!form.value.judul.trim()) {
    toastStore.error('Judul artikel wajib diisi.')
    return
  }
  if (!form.value.konten.trim()) {
    toastStore.error('Konten artikel wajib diisi.')
    return
  }

  if (targetStatus) {
    form.value.status = targetStatus
  }

  isSubmitting.value = true
  try {
    await postService.update(postId, {
      judul: form.value.judul,
      konten: form.value.konten,
      excerpt: form.value.excerpt,
      cover_image: form.value.cover_image,
      status: form.value.status,
      category_id: form.value.category_id,
      tags: form.value.tags as any,
      meta_title: form.value.meta_title || form.value.judul,
      meta_description: form.value.meta_description || form.value.excerpt,
    })

    toastStore.success('Artikel berhasil diperbarui!')
    router.push('/organization/posts')
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal memperbarui artikel.')
  } finally {
    isSubmitting.value = false
  }
}

const handlePrimaryPublish = () => {
  if (canPublish.value) {
    savePost(form.value.status === 'draft' ? 'published' : form.value.status)
  } else {
    savePost('review')
  }
}

onMounted(() => {
  loadPostAndTaxonomies()
})
</script>
