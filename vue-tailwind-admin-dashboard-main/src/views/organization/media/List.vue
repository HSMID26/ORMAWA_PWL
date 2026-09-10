<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Media Library'" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-6 shadow-sm">
      <!-- ─── Header & Upload Button ─────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <FolderArchiveIcon class="h-5 w-5 text-brand-600 dark:text-brand-400" />
            Media Library Internal
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Pusat penyimpanan seluruh aset media internal organisasi. Digunakan untuk artikel dan materi publikasi.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <!-- View Switcher -->
          <div class="flex rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-800">
            <button
              @click="viewMode = 'grid'"
              :class="['p-1.5 rounded text-xs transition', viewMode === 'grid' ? 'bg-brand-500 text-white shadow-xs' : 'text-gray-500 hover:text-gray-700']"
              title="Tampilan Grid"
            >
              <LayoutGridIcon class="h-4 w-4" />
            </button>
            <button
              @click="viewMode = 'list'"
              :class="['p-1.5 rounded text-xs transition', viewMode === 'list' ? 'bg-brand-500 text-white shadow-xs' : 'text-gray-500 hover:text-gray-700']"
              title="Tampilan Tabel"
            >
              <ListIcon class="h-4 w-4" />
            </button>
          </div>

          <button
            v-if="canUpload"
            @click="openUploadModal"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition"
          >
            <PlusIcon class="h-4 w-4" />
            Upload Media Baru
          </button>
        </div>
      </div>

      <!-- ─── Filter & Search Bar ─────────────────────────────────────────────── -->
      <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <!-- Type Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5">
          <button
            v-for="tab in typeTabs"
            :key="tab.id"
            @click="activeType = tab.id"
            :class="[
              'rounded-lg px-3 py-1.5 text-xs font-medium transition',
              activeType === tab.id
                ? 'bg-brand-50 text-brand-700 font-semibold border border-brand-200 dark:bg-brand-950/40 dark:border-brand-800 dark:text-brand-300'
                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- Search & Sort -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <div class="relative flex-1 md:w-64">
            <SearchIcon class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama, caption, alt..."
              class="h-9 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <select
            v-model="sortBy"
            class="h-9 rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-700 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
          >
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
            <option value="name">Nama (A-Z)</option>
            <option value="size">Ukuran Terbesar</option>
          </select>
        </div>
      </div>

      <!-- ─── Loading Skeleton ───────────────────────────────────────────────── -->
      <div v-if="isLoading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 py-6">
        <div v-for="i in 12" :key="i" class="aspect-square animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
      </div>

      <!-- ─── Error State ────────────────────────────────────────────────────── -->
      <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
        <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
        <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
        <button @click="loadMedia" class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
          Coba Lagi
        </button>
      </div>

      <!-- ─── Grid View ──────────────────────────────────────────────────────── -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <div
          v-for="item in sortedMedia"
          :key="item.id"
          @click="openDetail(item)"
          class="group relative aspect-square rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 shadow-xs hover:border-brand-400 hover:shadow-md cursor-pointer transition dark:border-gray-800 dark:bg-gray-800"
        >
          <!-- Thumbnail -->
          <img
            v-if="item.is_image"
            :src="resolveImageUrl(item.image_url || item.url)"
            :alt="item.alt_text || item.title || item.filename"
            class="h-full w-full object-cover group-hover:scale-105 transition duration-300"
            loading="lazy"
          />
          <div v-else class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-400">
            <FileTextIcon v-if="!item.is_video" class="h-10 w-10" />
            <VideoIcon v-else class="h-10 w-10 text-brand-500" />
          </div>

          <!-- Badges -->
          <div class="absolute top-2 left-2 flex flex-col gap-1">
            <span
              v-if="item.is_published_to_gallery"
              class="inline-flex items-center gap-1 rounded bg-emerald-600/90 backdrop-blur-xs px-1.5 py-0.5 text-[9px] font-semibold text-white shadow-xs"
              title="Dipublikasikan di Galeri Publik"
            >
              <EyeIcon class="h-2.5 w-2.5" /> Galeri
            </span>
            <span
              v-if="item.used_count && item.used_count > 0"
              class="inline-flex items-center gap-1 rounded bg-indigo-600/90 backdrop-blur-xs px-1.5 py-0.5 text-[9px] font-semibold text-white shadow-xs"
              :title="`Digunakan di ${item.used_count} artikel`"
            >
              <FileTextIcon class="h-2.5 w-2.5" /> {{ item.used_count }} Artikel
            </span>
          </div>

          <!-- Hover Overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition p-3 flex flex-col justify-end">
            <p class="text-[11px] font-bold text-white truncate" :title="item.title || item.filename">
              {{ item.title || item.filename }}
            </p>
            <p class="text-[10px] text-gray-300">
              {{ item.formatted_size || formatBytes(item.size) }}
            </p>
          </div>
        </div>

        <!-- Empty Grid State -->
        <div v-if="sortedMedia.length === 0" class="col-span-full py-16 text-center">
          <FolderArchiveIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-700 mb-2" />
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum ada aset media.</h3>
          <p class="text-xs text-gray-400 mt-1">Unggah berkas gambar atau dokumen untuk kebutuhan konten organisasi.</p>
          <button
            v-if="canUpload"
            @click="openUploadModal"
            class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm"
          >
            <PlusIcon class="h-4 w-4" /> Upload Media
          </button>
        </div>
      </div>

      <!-- ─── List / Table View ──────────────────────────────────────────────── -->
      <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-semibold">
            <tr>
              <th class="p-3 w-16">Preview</th>
              <th class="p-3">Nama Berkas</th>
              <th class="p-3">Kategori</th>
              <th class="p-3">Ukuran</th>
              <th class="p-3">Status Galeri</th>
              <th class="p-3">Penggunaan Artikel</th>
              <th class="p-3">Uploader</th>
              <th class="p-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <tr
              v-for="item in sortedMedia"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition cursor-pointer"
              @click="openDetail(item)"
            >
              <td class="p-3">
                <img
                  v-if="item.is_image"
                  :src="resolveImageUrl(item.image_url || item.url)"
                  class="h-10 w-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                />
                <div v-else class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                  <FileTextIcon class="h-5 w-5" />
                </div>
              </td>
              <td class="p-3">
                <p class="font-semibold text-gray-900 dark:text-white truncate max-w-xs">{{ item.title || item.filename }}</p>
                <p class="text-[10px] text-gray-400">{{ item.filename }}</p>
              </td>
              <td class="p-3 text-gray-600 dark:text-gray-300">{{ item.category || '-' }}</td>
              <td class="p-3 text-gray-600 dark:text-gray-300">{{ item.formatted_size || formatBytes(item.size) }}</td>
              <td class="p-3">
                <span
                  v-if="item.is_published_to_gallery"
                  class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                >
                  <EyeIcon class="h-3 w-3" /> Publik
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                >
                  <EyeOffIcon class="h-3 w-3" /> Internal
                </span>
              </td>
              <td class="p-3">
                <span v-if="item.used_count && item.used_count > 0" class="text-indigo-600 dark:text-indigo-400 font-semibold">
                  {{ item.used_count }} artikel
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="p-3 text-gray-600 dark:text-gray-300">{{ item.uploader }}</td>
              <td class="p-3 text-right" @click.stop>
                <button
                  @click="openDetail(item)"
                  class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                  title="Detail & Kelola"
                >
                  <MoreHorizontalIcon class="h-4 w-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Drawer Detail Media ──────────────────────────────────────────────── -->
    <div
      v-if="selectedItem"
      class="fixed inset-0 z-50 flex justify-end bg-black/50 backdrop-blur-xs transition-opacity"
      @click.self="selectedItem = null"
    >
      <div class="h-full w-full max-w-md bg-white p-6 shadow-2xl overflow-y-auto dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 flex flex-col justify-between">
        <div>
          <!-- Drawer Header -->
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4 mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <InfoIcon class="h-4 w-4 text-brand-500" /> Detail Aset Media
            </h3>
            <button @click="selectedItem = null" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
              <XIcon class="h-5 w-5" />
            </button>
          </div>

          <!-- Preview -->
          <div class="aspect-video w-full rounded-xl overflow-hidden border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-800 mb-4 flex items-center justify-center">
            <img
              v-if="selectedItem.is_image"
              :src="resolveImageUrl(selectedItem.image_url || selectedItem.url)"
              class="h-full w-full object-contain"
            />
            <FileTextIcon v-else class="h-12 w-12 text-gray-400" />
          </div>

          <!-- Publication Status Card -->
          <div class="rounded-xl border p-3.5 mb-4" :class="selectedItem.is_published_to_gallery ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40'">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-bold" :class="selectedItem.is_published_to_gallery ? 'text-emerald-800 dark:text-emerald-300' : 'text-gray-800 dark:text-gray-200'">
                  {{ selectedItem.is_published_to_gallery ? 'Dipublikasikan ke Galeri Publik' : 'Aset Internal Media Library' }}
                </p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                  {{ selectedItem.is_published_to_gallery ? 'Foto ini dapat dilihat oleh pengunjung di website publik.' : 'Foto ini hanya tersimpan internal untuk artikel dan belum tayang di galeri publik.' }}
                </p>
              </div>
              <button
                v-if="canEdit"
                @click="handleToggleGallery(selectedItem)"
                :disabled="isTogglingGallery"
                class="rounded-lg px-3 py-1.5 text-xs font-semibold shadow-xs transition"
                :class="selectedItem.is_published_to_gallery ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-emerald-600 text-white hover:bg-emerald-700'"
              >
                {{ selectedItem.is_published_to_gallery ? 'Tarik dari Galeri' : 'Publikasikan' }}
              </button>
            </div>
          </div>

          <!-- Metadata Information -->
          <div class="space-y-3 text-xs">
            <div>
              <span class="text-gray-400 block mb-0.5">Judul / Nama Media:</span>
              <p class="font-semibold text-gray-800 dark:text-gray-200">{{ selectedItem.title || selectedItem.name || '-' }}</p>
            </div>
            <div v-if="selectedItem.caption">
              <span class="text-gray-400 block mb-0.5">Caption:</span>
              <p class="text-gray-700 dark:text-gray-300">{{ selectedItem.caption }}</p>
            </div>
            <div v-if="selectedItem.alt_text">
              <span class="text-gray-400 block mb-0.5">Alt Text:</span>
              <p class="text-gray-700 dark:text-gray-300">{{ selectedItem.alt_text }}</p>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
              <div>
                <span class="text-gray-400 block">Ukuran File:</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ selectedItem.formatted_size || formatBytes(selectedItem.size) }}</p>
              </div>
              <div>
                <span class="text-gray-400 block">Tipe MIME:</span>
                <p class="font-mono text-gray-700 dark:text-gray-300">{{ selectedItem.mime_type }}</p>
              </div>
              <div>
                <span class="text-gray-400 block">Diunggah Oleh:</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ selectedItem.uploader }}</p>
              </div>
              <div>
                <span class="text-gray-400 block">Tanggal Upload:</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ selectedItem.created_at }}</p>
              </div>
            </div>

            <!-- Articles Using This Media -->
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
              <span class="text-gray-400 block mb-1.5 font-semibold">Digunakan Pada Artikel:</span>
              <div v-if="selectedItem.used_in_articles && selectedItem.used_in_articles.length > 0" class="space-y-1.5">
                <div
                  v-for="art in selectedItem.used_in_articles"
                  :key="art.id"
                  class="flex items-center justify-between rounded-lg bg-indigo-50/60 p-2 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50"
                >
                  <span class="font-medium text-indigo-900 dark:text-indigo-300 truncate max-w-[240px]">{{ art.judul }}</span>
                  <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-mono bg-indigo-200/60 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">{{ art.status }}</span>
                </div>
              </div>
              <p v-else class="text-gray-400 italic">Belum digunakan oleh artikel mana pun.</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
          <button
            v-if="canDelete"
            @click="handleDelete(selectedItem)"
            :disabled="isDeleting"
            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400 transition"
          >
            <Trash2Icon class="h-4 w-4" /> Hapus Berkas
          </button>
          <div v-else></div>

          <a
            :href="resolveImageUrl(selectedItem.image_url || selectedItem.url)"
            target="_blank"
            download
            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200"
          >
            <DownloadIcon class="h-4 w-4" /> Buka / Download
          </a>
        </div>
      </div>
    </div>

    <!-- ─── Modal Upload Media ──────────────────────────────────────────────── -->
    <div
      v-if="isUploadModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
      @click.self="isUploadModalOpen = false"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
          <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <UploadCloudIcon class="h-5 w-5 text-brand-500" />
            Upload Media ke Library
          </h3>
          <button @click="isUploadModalOpen = false" class="text-gray-400 hover:text-gray-600">
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <form @submit.prevent="handleUploadSubmit" class="space-y-4 text-xs">
          <!-- Dropzone -->
          <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            :class="[
              'rounded-xl border-2 border-dashed p-6 text-center cursor-pointer transition',
              isDragging ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/20' : 'border-gray-300 dark:border-gray-700 hover:border-brand-400'
            ]"
            @click="$refs.fileInput.click()"
          >
            <input
              ref="fileInput"
              type="file"
              accept="image/*,video/*"
              class="hidden"
              @change="handleFileSelected"
            />
            <UploadCloudIcon class="mx-auto h-10 w-10 text-brand-500 mb-2" />
            <p v-if="!selectedFile" class="font-semibold text-gray-800 dark:text-gray-200">
              Klik untuk memilih berkas atau seret ke sini
            </p>
            <p v-else class="font-bold text-brand-600 dark:text-brand-400">
              {{ selectedFile.name }} ({{ formatBytes(selectedFile.size) }})
            </p>
            <p class="text-[10px] text-gray-400 mt-1">Mendukung JPG, PNG, WebP, AVIF, MP4 (Max 10MB)</p>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul / Nama Media</label>
            <input
              v-model="uploadForm.title"
              type="text"
              placeholder="Contoh: Rapat Kerja Anggota 2026"
              class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
            <select
              v-model="uploadForm.category"
              class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="Dokumentasi">Dokumentasi</option>
              <option value="Kegiatan">Kegiatan</option>
              <option value="Prestasi">Prestasi</option>
              <option value="Fasilitas">Fasilitas</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Caption / Keterangan</label>
            <textarea
              v-model="uploadForm.caption"
              rows="2"
              placeholder="Keterangan singkat seputar media..."
              class="w-full rounded-lg border border-gray-300 bg-transparent p-2.5 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            ></textarea>
          </div>

          <!-- Publish to Gallery Option -->
          <div class="flex items-center gap-2.5 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-800/40">
            <input
              id="publishToGallery"
              v-model="uploadForm.publish_to_gallery"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
            />
            <label for="publishToGallery" class="cursor-pointer">
              <span class="block font-semibold text-gray-800 dark:text-gray-200">Publikasikan langsung ke Galeri Publik</span>
              <span class="block text-[10px] text-gray-400">Jika tidak dicentang, media hanya disimpan di Media Library internal.</span>
            </label>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
            <button
              type="button"
              @click="isUploadModalOpen = false"
              class="rounded-lg px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="!selectedFile || isUploading"
              class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
            >
              <UploadCloudIcon class="h-4 w-4" />
              {{ isUploading ? 'Mengunggah...' : 'Upload Media' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { mediaService, type MediaItem } from '@/services/mediaService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { resolveImageUrl } from '@/utils/imageUrl'
import {
  FolderArchiveIcon,
  LayoutGridIcon,
  ListIcon,
  PlusIcon,
  SearchIcon,
  AlertTriangleIcon,
  EyeIcon,
  EyeOffIcon,
  FileTextIcon,
  VideoIcon,
  MoreHorizontalIcon,
  InfoIcon,
  XIcon,
  Trash2Icon,
  DownloadIcon,
  UploadCloudIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toast = useToastStore()

const canUpload = computed(() => authStore.role === 'Super Admin' || authStore.hasPermission('gallery.create'))
const canEdit = computed(() => authStore.role === 'Super Admin' || authStore.hasPermission('gallery.update') || authStore.hasPermission('gallery.create'))
const canDelete = computed(() => authStore.role === 'Super Admin' || authStore.hasPermission('gallery.delete'))

const viewMode = ref<'grid' | 'list'>('grid')
const mediaList = ref<MediaItem[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const activeType = ref<'all' | 'image' | 'video' | 'document'>('all')
const sortBy = ref<'newest' | 'oldest' | 'name' | 'size'>('newest')

const selectedItem = ref<MediaItem | null>(null)
const isTogglingGallery = ref(false)
const isDeleting = ref(false)

// Upload Modal State
const isUploadModalOpen = ref(false)
const isDragging = ref(false)
const selectedFile = ref<File | null>(null)
const isUploading = ref(false)
const uploadForm = ref({
  title: '',
  category: 'Dokumentasi',
  caption: '',
  publish_to_gallery: false
})

const typeTabs: { id: 'all' | 'image' | 'video' | 'document'; label: string }[] = [
  { id: 'all', label: 'Semua Media' },
  { id: 'image', label: 'Gambar' },
  { id: 'video', label: 'Video' },
  { id: 'document', label: 'Dokumen' }
]

const loadMedia = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const data = await mediaService.list({ type: 'all' })
    mediaList.value = data
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat media library.'
  } finally {
    isLoading.value = false
  }
}

const filteredMedia = computed(() => {
  return mediaList.value.filter(item => {
    // Type Filter
    if (activeType.value === 'image' && !item.is_image) return false
    if (activeType.value === 'video' && !item.is_video) return false
    if (activeType.value === 'document' && (item.is_image || item.is_video)) return false

    // Search Filter
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      const title = (item.title || item.name || '').toLowerCase()
      const filename = (item.filename || '').toLowerCase()
      const caption = (item.caption || '').toLowerCase()
      const alt = (item.alt_text || '').toLowerCase()
      if (!title.includes(q) && !filename.includes(q) && !caption.includes(q) && !alt.includes(q)) {
        return false
      }
    }
    return true
  })
})

const sortedMedia = computed(() => {
  return [...filteredMedia.value].sort((a, b) => {
    if (sortBy.value === 'newest') return (b.id || 0) - (a.id || 0)
    if (sortBy.value === 'oldest') return (a.id || 0) - (b.id || 0)
    if (sortBy.value === 'name') return (a.title || a.name || '').localeCompare(b.title || b.name || '')
    if (sortBy.value === 'size') return (b.size || 0) - (a.size || 0)
    return 0
  })
})

const openDetail = (item: MediaItem) => {
  selectedItem.value = item
}

const handleToggleGallery = async (item: MediaItem) => {
  isTogglingGallery.value = true
  try {
    const updated = await mediaService.toggleGallery(item.id)
    item.is_published_to_gallery = updated.is_published_to_gallery
    toast.success(item.is_published_to_gallery ? 'Media berhasil dipublikasikan ke Galeri Publik!' : 'Media berhasil ditarik dari Galeri Publik.')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal mengubah status publikasi.')
  } finally {
    isTogglingGallery.value = false
  }
}

const handleDelete = async (item: MediaItem) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus berkas "${item.title || item.filename}" secara permanen?`)) return
  isDeleting.value = true
  try {
    await mediaService.remove(item.id)
    mediaList.value = mediaList.value.filter(m => m.id !== item.id)
    selectedItem.value = null
    toast.success('Berkas media berhasil dihapus secara aman.')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Berkas tidak dapat dihapus karena sedang digunakan.')
  } finally {
    isDeleting.value = false
  }
}

const openUploadModal = () => {
  selectedFile.value = null
  uploadForm.value = {
    title: '',
    category: 'Dokumentasi',
    caption: '',
    publish_to_gallery: false
  }
  isUploadModalOpen.value = true
}

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
    selectedFile.value = e.dataTransfer.files[0]
    if (!uploadForm.value.title) {
      uploadForm.value.title = selectedFile.value.name.replace(/\.[^/.]+$/, '')
    }
  }
}

const handleFileSelected = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files && input.files[0]) {
    selectedFile.value = input.files[0]
    if (!uploadForm.value.title) {
      uploadForm.value.title = selectedFile.value.name.replace(/\.[^/.]+$/, '')
    }
  }
}

const handleUploadSubmit = async () => {
  if (!selectedFile.value) return
  isUploading.value = true
  try {
    const formData = new FormData()
    formData.append('image', selectedFile.value)
    formData.append('title', uploadForm.value.title)
    formData.append('category', uploadForm.value.category)
    formData.append('caption', uploadForm.value.caption)
    formData.append('publish_to_gallery', uploadForm.value.publish_to_gallery ? '1' : '0')

    await mediaService.upload(formData)
    toast.success('Media baru berhasil ditambahkan ke library!')
    isUploadModalOpen.value = false
    await loadMedia()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Terjadi kesalahan saat upload.')
  } finally {
    isUploading.value = false
  }
}

const formatBytes = (bytes: number) => {
  if (!bytes || bytes <= 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

onMounted(() => {
  loadMedia()
})
</script>
