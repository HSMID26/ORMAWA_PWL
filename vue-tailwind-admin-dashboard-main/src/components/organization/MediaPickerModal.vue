<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-6 animate-fade-in"
      @click.self="close"
      @keydown.esc="close"
    >
      <div
        class="relative flex flex-col w-full max-w-4xl h-[88vh] max-h-[760px] rounded-2xl bg-white shadow-2xl border border-gray-100 dark:border-gray-800 dark:bg-gray-900 overflow-hidden transition-all"
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800 shrink-0">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'flex h-10 w-10 items-center justify-center rounded-xl shrink-0',
                mode === 'gallery'
                  ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                  : 'bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400'
              ]"
            >
              <EyeIcon v-if="mode === 'gallery'" class="h-5 w-5" />
              <ImageIcon v-else class="h-5 w-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ modalTitle }}
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ modalSubtitle }}
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="close"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition"
            title="Tutup Modal"
          >
            <XIcon class="h-5 w-5" />
          </button>
        </div>

        <!-- Navigation Tabs & Search Toolbar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-6 py-2.5 dark:border-gray-800 dark:bg-gray-800/50 shrink-0">
          <!-- Mode Tabs -->
          <div class="flex items-center gap-1.5 bg-gray-200/70 dark:bg-gray-800 p-1 rounded-xl self-start">
            <button
              type="button"
              @click="switchToTab('library')"
              :class="[
                'flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                activeTab === 'library'
                  ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-900 dark:text-white'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]"
            >
              <LayoutGridIcon class="h-3.5 w-3.5" />
              {{ libraryTabLabel }}
            </button>

            <button
              v-if="canUpload"
              type="button"
              @click="switchToTab('upload')"
              :class="[
                'flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                activeTab === 'upload'
                  ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-900 dark:text-white'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]"
            >
              <UploadCloudIcon class="h-3.5 w-3.5" />
              Upload Foto Baru
            </button>
          </div>

          <!-- Search Bar (Library Tab - only when in grid selection) -->
          <div v-if="activeTab === 'library' && !(mode === 'gallery' && selectedItem)" class="flex items-center gap-2">
            <div class="relative w-full sm:w-64">
              <SearchIcon class="absolute left-3 top-2.5 h-3.5 w-3.5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari foto..."
                class="h-8 w-full rounded-lg border border-gray-300 bg-white pl-8 pr-3 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
              />
            </div>

            <button
              type="button"
              @click="loadMedia"
              class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-medium transition"
              title="Segarkan Media"
            >
              <RefreshCwIcon class="h-3.5 w-3.5" :class="{ 'animate-spin': isLoading }" />
            </button>
          </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- TAB 1: MEDIA LIBRARY                                              -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'library'" class="flex-1 overflow-y-auto p-6 min-h-0">
          <!-- Gallery Mode + Photo Selected: Existing Metadata READ-ONLY View -->
          <div v-if="mode === 'gallery' && selectedItem" class="max-w-2xl mx-auto space-y-4 animate-fade-in">
            <!-- Back to Grid Button & Source Badge -->
            <div class="flex items-center justify-between">
              <button
                type="button"
                @click="selectedItem = null"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 transition"
              >
                <ArrowLeftIcon class="h-4 w-4" />
                <span>Pilih Foto Lain dari Media Library</span>
              </button>

              <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                <FolderArchiveIcon class="h-3.5 w-3.5" /> Media Library Internal
              </span>
            </div>

            <!-- Preview Card -->
            <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl border border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
              <div class="relative h-28 w-28 shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                <img
                  :src="resolveImageUrl(selectedItem.image_url || selectedItem.url)"
                  :alt="selectedItem.title || selectedItem.filename"
                  class="h-full w-full object-cover"
                />
              </div>
              <div class="min-w-0 flex-1 text-xs">
                <p class="font-bold text-gray-900 dark:text-white truncate text-sm">
                  {{ selectedItem.title || selectedItem.name || selectedItem.filename }}
                </p>
                <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-1">
                  File: <code class="text-gray-700 dark:text-gray-300 font-mono">{{ selectedItem.filename }}</code>
                </p>
                <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">
                  Ukuran: {{ selectedItem.formatted_size || (selectedItem.size ? Math.round(selectedItem.size / 1024) + ' KB' : '-') }}
                </p>
                <p class="text-emerald-600 dark:text-emerald-400 text-[11px] font-medium mt-1.5 flex items-center gap-1">
                  <CheckIcon class="h-3.5 w-3.5 stroke-[2.5]" /> Metadata tersimpan akan langsung digunakan di Galeri Publik tanpa duplikasi file.
                </p>
              </div>
            </div>

            <!-- Read-Only Metadata Display -->
            <div class="space-y-3.5 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Foto</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                  {{ selectedItem.title || selectedItem.name || selectedItem.filename }}
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                  <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ selectedItem.category || selectedItem.kategori || 'Dokumentasi' }}
                  </div>
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alt Text (Aksesibilitas)</label>
                  <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 truncate">
                    {{ selectedItem.alt_text || selectedItem.title || '-' }}
                  </div>
                </div>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Caption / Keterangan</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 min-h-[50px] whitespace-pre-wrap">
                  {{ selectedItem.caption || selectedItem.deskripsi || '-' }}
                </div>
              </div>

              <div v-if="uploadError" class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400">
                {{ uploadError }}
              </div>
            </div>
          </div>

          <!-- Standard Grid View (Article Mode OR Gallery Mode before selection) -->
          <div v-else>
            <!-- Loading State -->
            <div v-if="isLoading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
              <div v-for="i in 8" :key="i" class="aspect-square animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20 my-auto">
              <AlertTriangleIcon class="mx-auto h-8 w-8 text-rose-500 mb-2" />
              <p class="text-xs text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
              <button
                type="button"
                @click="loadMedia"
                class="mt-3 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
              >
                Coba Lagi
              </button>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredItems.length === 0" class="flex flex-col items-center justify-center py-16 text-center my-auto">
              <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 mb-3">
                <ImageIcon class="h-7 w-7" />
              </div>
              <h4 class="text-sm font-bold text-gray-800 dark:text-white">
                {{ searchQuery ? 'Foto tidak ditemukan' : 'Belum ada foto di Media Library' }}
              </h4>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
                {{ searchQuery ? 'Tidak ada media yang cocok dengan kata kunci pencarian.' : 'Belum ada foto yang tersimpan di Media Library organisasi ini. Anda dapat mengunggah foto baru.' }}
              </p>
              <button
                v-if="canUpload && !searchQuery"
                type="button"
                @click="switchToTab('upload')"
                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition"
              >
                <UploadCloudIcon class="h-3.5 w-3.5" />
                Upload Foto Baru
              </button>
            </div>

            <!-- Grid View -->
            <div v-else class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4">
              <div
                v-for="item in filteredItems"
                :key="item.id"
                @click="selectItem(item)"
                @dblclick="handleDoubleClick(item)"
                class="group relative aspect-square rounded-xl overflow-hidden border bg-gray-50 dark:bg-gray-800 cursor-pointer transition select-none"
                :class="[
                  selectedItem?.id === item.id
                    ? 'border-brand-500 ring-2 ring-brand-500/40 dark:border-brand-400'
                    : 'border-gray-200 hover:border-brand-300 dark:border-gray-700'
                ]"
              >
                <!-- Thumbnail -->
                <img
                  :src="resolveImageUrl(item.image_url || item.url)"
                  :alt="item.alt_text || item.title || item.name || item.filename"
                  class="h-full w-full object-cover transition group-hover:scale-105 duration-200"
                  loading="lazy"
                />

                <!-- Selected Badge Checkmark -->
                <div
                  v-if="selectedItem?.id === item.id"
                  class="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white shadow-md ring-2 ring-white dark:ring-gray-900 z-10"
                >
                  <CheckIcon class="h-3.5 w-3.5 stroke-[3]" />
                </div>

                <!-- Bottom Overlay Caption -->
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2.5 pt-6 text-white">
                  <p class="truncate text-[11px] font-semibold">
                    {{ item.title || item.name || item.filename }}
                  </p>
                  <div class="flex items-center justify-between text-[9px] text-gray-300 mt-0.5">
                    <span>{{ item.formatted_size || (item.size ? Math.round(item.size / 1024) + ' KB' : '') }}</span>
                    <span v-if="item.category" class="rounded bg-black/40 px-1 py-0.5">{{ item.category }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- TAB 2: UPLOAD FOTO BARU                                           -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <div v-else-if="activeTab === 'upload'" class="flex-1 overflow-y-auto p-6 min-h-0">
          <!-- Gallery Mode Upload Workflow (Full Editable Metadata Form) -->
          <div v-if="mode === 'gallery'" class="max-w-2xl mx-auto space-y-4 animate-fade-in">
            <!-- Dropzone Area -->
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="[
                'relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition cursor-pointer',
                isDragging
                  ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20'
                  : 'border-gray-300 hover:border-emerald-400 dark:border-gray-700 dark:hover:border-emerald-500 bg-gray-50/50 dark:bg-gray-800/30'
              ]"
            >
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
              />

              <div v-if="uploadPreviewUrl" class="relative w-full aspect-video rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 mb-3 border border-gray-200 dark:border-gray-700">
                <img :src="uploadPreviewUrl" alt="Upload Preview" class="h-full w-full object-contain" />
                <button
                  type="button"
                  @click.stop="clearUploadFile"
                  class="absolute top-2 right-2 rounded-lg bg-black/60 p-1 text-white hover:bg-rose-600 transition"
                  title="Hapus foto terpilih"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <div v-else @click="fileInputRef?.click()" class="py-4">
                <UploadCloudIcon class="mx-auto h-10 w-10 text-emerald-500 mb-2" />
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                  Klik untuk memilih file atau seret foto ke sini
                </p>
                <p class="text-[11px] text-gray-400 mt-1">
                  Format didukung: JPG, PNG, WebP, AVIF (Maks. 5MB)
                </p>
              </div>
            </div>

            <!-- Full Editable Form for Gallery Mode -->
            <div class="space-y-3.5 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  Judul Foto <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="galleryUploadForm.title"
                  type="text"
                  placeholder="Misal: Dokumentasi Kegiatan Seminar Nasional 2026..."
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  required
                />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                  <select
                    v-model="galleryUploadForm.category"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  >
                    <option value="Dokumentasi">Dokumentasi</option>
                    <option value="Kegiatan">Kegiatan</option>
                    <option value="Prestasi">Prestasi</option>
                    <option value="Banner / Sampul">Banner / Sampul</option>
                    <option value="Fasilitas">Fasilitas</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alt Text (Aksesibilitas)</label>
                  <input
                    v-model="galleryUploadForm.alt_text"
                    type="text"
                    placeholder="Deskripsi foto untuk tuna netra & SEO..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  />
                </div>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Caption / Keterangan</label>
                <textarea
                  v-model="galleryUploadForm.caption"
                  rows="3"
                  placeholder="Keterangan singkat mengenai foto ini..."
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                ></textarea>
              </div>

              <div v-if="uploadError" class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400">
                {{ uploadError }}
              </div>
            </div>
          </div>

          <!-- Article Mode Upload Workflow (Clean & Lightweight) -->
          <div v-else class="max-w-xl mx-auto space-y-4">
            <!-- Dropzone Area -->
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="[
                'relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition cursor-pointer',
                isDragging
                  ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/20'
                  : 'border-gray-300 hover:border-brand-400 dark:border-gray-700 dark:hover:border-brand-500 bg-gray-50/50 dark:bg-gray-800/30'
              ]"
            >
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
              />

              <div v-if="uploadPreviewUrl" class="relative w-full aspect-video rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 mb-3 border border-gray-200 dark:border-gray-700">
                <img :src="uploadPreviewUrl" alt="Upload Preview" class="h-full w-full object-contain" />
                <button
                  type="button"
                  @click.stop="clearUploadFile"
                  class="absolute top-2 right-2 rounded-lg bg-black/60 p-1 text-white hover:bg-rose-600 transition"
                  title="Hapus foto terpilih"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <div v-else @click="fileInputRef?.click()" class="py-4">
                <UploadCloudIcon class="mx-auto h-10 w-10 text-gray-400 mb-2" />
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                  Klik untuk memilih file atau seret foto ke sini
                </p>
                <p class="text-[11px] text-gray-400 mt-1">
                  Format didukung: JPG, PNG, WebP, AVIF (Maks. 5MB)
                </p>
              </div>
            </div>

            <!-- Upload Form Fields for Article Mode (Clean & Fast) -->
            <div class="space-y-3 text-xs">
              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  Judul / Nama Foto
                </label>
                <input
                  v-model="articleUploadForm.title"
                  type="text"
                  placeholder="Misal: Dokumentasi Kegiatan Seminar..."
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  Alt Text (Aksesibilitas / SEO)
                </label>
                <input
                  v-model="articleUploadForm.alt_text"
                  type="text"
                  placeholder="Deskripsi singkat gambar untuk aksesibilitas..."
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
              </div>

              <div v-if="uploadError" class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400">
                {{ uploadError }}
              </div>
            </div>
          </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- MODAL UNIFIED FOOTER                                              -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/80 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-800/80 shrink-0">
          <!-- Footer Left Status -->
          <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-sm">
            <!-- Gallery Mode Status -->
            <template v-if="mode === 'gallery'">
              <template v-if="activeTab === 'library'">
                <span v-if="selectedItem">
                  Foto Terpilih: <strong class="text-gray-800 dark:text-white">{{ selectedItem.title || selectedItem.name || selectedItem.filename }}</strong>
                </span>
                <span v-else>
                  Pilih salah satu foto dari Media Library untuk ditampilkan di Galeri Publik.
                </span>
              </template>
              <template v-else>
                <span>Foto yang diunggah akan disimpan dan dipublikasikan ke Galeri Publik.</span>
              </template>
            </template>

            <!-- Article Mode Status -->
            <template v-else>
              <template v-if="activeTab === 'library'">
                <span v-if="selectedItem">
                  Foto Terpilih: <strong class="text-gray-800 dark:text-white">{{ selectedItem.title || selectedItem.name || selectedItem.filename }}</strong>
                </span>
                <span v-else>
                  Pilih salah satu foto dari daftar untuk dimasukkan ke artikel.
                </span>
              </template>
              <template v-else>
                <span>Foto yang diunggah akan otomatis disimpan ke Media Library dan disisipkan ke artikel.</span>
              </template>
            </template>
          </div>

          <!-- Footer Right Actions -->
          <div class="flex items-center justify-end gap-2">
            <!-- ─── Gallery Mode Actions ─────────────────────────────────── -->
            <template v-if="mode === 'gallery'">
              <button
                type="button"
                @click="close"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
              >
                Batal
              </button>

              <!-- Gallery Tab 1 Action (Existing Media -> Add to Gallery) -->
              <template v-if="activeTab === 'library'">
                <button
                  type="button"
                  @click="submitGalleryExisting"
                  :disabled="!selectedItem || isSubmitting"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <RefreshCwIcon v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin" />
                  <CheckIcon v-else class="h-4 w-4 stroke-[2.5]" />
                  <span>{{ isSubmitting ? 'Menyimpan...' : 'Tambahkan ke Galeri Publik' }}</span>
                </button>
              </template>

              <!-- Gallery Tab 2 Action (New Upload -> Upload & Publish) -->
              <template v-else>
                <button
                  type="button"
                  @click="submitGalleryUpload"
                  :disabled="!uploadFile || !galleryUploadForm.title.trim() || isSubmitting"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <RefreshCwIcon v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin" />
                  <UploadCloudIcon v-else class="h-3.5 w-3.5" />
                  <span>{{ isSubmitting ? 'Mengunggah...' : 'Unggah & Publikasikan' }}</span>
                </button>
              </template>
            </template>

            <!-- ─── Article Mode Actions ─────────────────────────────────── -->
            <template v-else>
              <!-- If in Library Tab -->
              <template v-if="activeTab === 'library'">
                <button
                  type="button"
                  @click="close"
                  class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
                >
                  Batal
                </button>
                <button
                  type="button"
                  @click="confirmSelection"
                  :disabled="!selectedItem"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <CheckIcon class="h-4 w-4" />
                  Gunakan Foto Ini
                </button>
              </template>

              <!-- If in Upload Tab -->
              <template v-else>
                <button
                  type="button"
                  @click="close"
                  class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
                >
                  Batal
                </button>
                <button
                  type="button"
                  @click="submitArticleUpload"
                  :disabled="!uploadFile || isSubmitting"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <RefreshCwIcon v-if="isSubmitting" class="h-3.5 w-3.5 animate-spin" />
                  <UploadCloudIcon v-else class="h-3.5 w-3.5" />
                  {{ isSubmitting ? 'Mengunggah...' : 'Upload & Gunakan Foto' }}
                </button>
              </template>
            </template>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { mediaService, type MediaItem } from '@/services/mediaService'
import { resolveImageUrl } from '@/utils/imageUrl'
import { useAuthStore } from '@/stores/auth'
import {
  ImageIcon,
  EyeIcon,
  SearchIcon,
  XIcon,
  CheckIcon,
  RefreshCwIcon,
  AlertTriangleIcon,
  LayoutGridIcon,
  UploadCloudIcon,
  ArrowLeftIcon,
  FolderArchiveIcon
} from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    isOpen: boolean
    title?: string
    mode?: 'article' | 'gallery'
    initialTab?: 'library' | 'upload'
    initialSelectedUrl?: string | null
  }>(),
  {
    isOpen: false,
    mode: 'article',
    initialTab: 'library',
    initialSelectedUrl: null
  }
)

const emit = defineEmits<{
  close: []
  select: [item: MediaItem]
  published: [item: MediaItem]
}>()

const authStore = useAuthStore()

// User permissions
const canUpload = computed(() => {
  return (
    authStore.role === 'Super Admin' ||
    authStore.hasPermission('gallery.create') ||
    authStore.role === 'Admin Organisasi' ||
    authStore.role === 'Editor'
  )
})

const activeTab = ref<'library' | 'upload'>('library')
const mediaList = ref<MediaItem[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const selectedItem = ref<MediaItem | null>(null)
const isSubmitting = ref(false)

// Titles and Labels based on Mode
const modalTitle = computed(() => {
  if (props.title) return props.title
  return props.mode === 'gallery' ? 'Kelola Foto Galeri Publik' : 'Pilih Foto'
})

const modalSubtitle = computed(() => {
  if (props.mode === 'gallery') {
    return 'Pilih foto dari Media Library atau unggah foto baru untuk ditampilkan di Galeri Publik organisasi.'
  }
  return 'Pilih foto dari Media Library atau unggah foto baru untuk digunakan di artikel.'
})

const libraryTabLabel = computed(() => {
  if (props.mode === 'gallery') {
    return `Pilih dari Media Library (${mediaList.value.length})`
  }
  return `Media Library (${mediaList.value.length})`
})

// Form for Gallery Upload (Mode 2)
const galleryUploadForm = ref({
  title: '',
  category: 'Dokumentasi',
  alt_text: '',
  caption: ''
})

const resetGalleryUploadForm = () => {
  galleryUploadForm.value = {
    title: '',
    category: 'Dokumentasi',
    alt_text: '',
    caption: ''
  }
}

// Upload state
const fileInputRef = ref<HTMLInputElement | null>(null)
const uploadFile = ref<File | null>(null)
const uploadPreviewUrl = ref<string | null>(null)
const isDragging = ref(false)
const uploadError = ref('')

// Form for Article Upload
const articleUploadForm = ref({
  title: '',
  alt_text: ''
})

const loadMedia = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    mediaList.value = await mediaService.list({ type: 'images' })
    if (props.initialSelectedUrl) {
      const match = mediaList.value.find(
        (m) => m.url === props.initialSelectedUrl || m.image_url === props.initialSelectedUrl
      )
      if (match) {
        selectItem(match)
      }
    }
  } catch (error: any) {
    errorMessage.value = error.message || 'Gagal memuat daftar media.'
  } finally {
    isLoading.value = false
  }
}

const switchToTab = (tab: 'library' | 'upload') => {
  activeTab.value = tab
  uploadError.value = ''
}

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      activeTab.value = props.initialTab || 'library'
      searchQuery.value = ''
      selectedItem.value = null
      clearUploadFile()
      resetGalleryUploadForm()
      loadMedia()
    }
  }
)

const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) {
    return mediaList.value
  }
  const q = searchQuery.value.toLowerCase().trim()
  return mediaList.value.filter(
    (item) =>
      (item.title && item.title.toLowerCase().includes(q)) ||
      (item.name && item.name.toLowerCase().includes(q)) ||
      (item.filename && item.filename.toLowerCase().includes(q)) ||
      (item.caption && item.caption.toLowerCase().includes(q)) ||
      (item.alt_text && item.alt_text.toLowerCase().includes(q))
  )
})

const selectItem = (item: MediaItem) => {
  if (props.mode === 'gallery') {
    selectedItem.value = item
    uploadError.value = ''
  } else {
    if (selectedItem.value?.id === item.id) {
      selectedItem.value = null
    } else {
      selectedItem.value = item
    }
  }
}

const handleDoubleClick = (item: MediaItem) => {
  selectItem(item)
  if (props.mode === 'article') {
    confirmSelection()
  }
}

const confirmSelection = () => {
  if (selectedItem.value) {
    emit('select', selectedItem.value)
    emit('close')
  }
}

const close = () => {
  emit('close')
}

// Upload Handling
const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    setUploadFile(target.files[0])
  }
}

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
    setUploadFile(e.dataTransfer.files[0])
  }
}

const setUploadFile = (file: File) => {
  uploadFile.value = file
  uploadPreviewUrl.value = URL.createObjectURL(file)
  const cleanTitle = file.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ')

  if (props.mode === 'gallery') {
    galleryUploadForm.value.title = cleanTitle
    galleryUploadForm.value.alt_text = cleanTitle
  } else {
    articleUploadForm.value.title = cleanTitle
    articleUploadForm.value.alt_text = cleanTitle
  }
}

const clearUploadFile = () => {
  uploadFile.value = null
  uploadPreviewUrl.value = null
  uploadError.value = ''
  resetGalleryUploadForm()
  articleUploadForm.value = {
    title: '',
    alt_text: ''
  }
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

// ─── Gallery Mode Submissions ────────────────────────────────────────────────
// Mode 1: Existing Media -> publish to gallery without re-uploading
const submitGalleryExisting = async () => {
  if (!selectedItem.value) return

  isSubmitting.value = true
  uploadError.value = ''

  try {
    const updatedMedia = await mediaService.toggleGallery(selectedItem.value.id, true)

    emit('select', updatedMedia)
    emit('published', updatedMedia)
    emit('close')
    selectedItem.value = null
  } catch (err: any) {
    uploadError.value = err.response?.data?.message || err.message || 'Gagal menambahkan foto ke galeri.'
  } finally {
    isSubmitting.value = false
  }
}

// Mode 2: New Upload -> upload with user-input metadata & publish to gallery
const submitGalleryUpload = async () => {
  if (!uploadFile.value) return
  if (!galleryUploadForm.value.title.trim()) {
    uploadError.value = 'Silakan masukkan judul foto.'
    return
  }

  isSubmitting.value = true
  uploadError.value = ''

  try {
    const formData = new FormData()
    formData.append('image', uploadFile.value)
    formData.append('title', galleryUploadForm.value.title.trim())
    formData.append('category', galleryUploadForm.value.category)
    formData.append('alt_text', galleryUploadForm.value.alt_text.trim() || galleryUploadForm.value.title.trim())
    formData.append('caption', galleryUploadForm.value.caption.trim())
    formData.append('publish_to_gallery', '1')
    formData.append('is_published_to_gallery', '1')

    const res = await mediaService.upload(formData)
    await loadMedia()

    const newMedia = res.data || res || (mediaList.value.length > 0 ? mediaList.value[0] : null)
    if (newMedia) {
      emit('select', newMedia)
      emit('published', newMedia)
    }
    emit('close')
    clearUploadFile()
    resetGalleryUploadForm()
  } catch (err: any) {
    uploadError.value = err.response?.data?.message || err.message || 'Gagal mengunggah foto ke galeri.'
  } finally {
    isSubmitting.value = false
  }
}

// ─── Article Mode Submission ─────────────────────────────────────────────────
const submitArticleUpload = async () => {
  if (!uploadFile.value) return
  const effectiveTitle = articleUploadForm.value.title.trim() || uploadFile.value.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ')

  isSubmitting.value = true
  uploadError.value = ''

  try {
    const formData = new FormData()
    formData.append('image', uploadFile.value)
    formData.append('title', effectiveTitle)
    formData.append('alt_text', articleUploadForm.value.alt_text || effectiveTitle)
    formData.append('category', 'Dokumentasi')
    formData.append('is_published_to_gallery', '0')
    formData.append('publish_to_gallery', '0')

    const res = await mediaService.upload(formData)
    await loadMedia()

    const newMedia = res.data || res || (mediaList.value.length > 0 ? mediaList.value[0] : null)
    if (newMedia) {
      selectedItem.value = newMedia
      emit('select', newMedia)
      emit('close')
      clearUploadFile()
      return
    }

    activeTab.value = 'library'
    clearUploadFile()
  } catch (err: any) {
    uploadError.value = err.response?.data?.message || err.message || 'Gagal mengunggah foto.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
