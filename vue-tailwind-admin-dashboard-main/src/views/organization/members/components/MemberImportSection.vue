<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Card Ekspor -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600">
            <DownloadIcon class="h-5 w-5" />
          </div>
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white">Ekspor Data Anggota</h3>
            <p class="text-xs text-gray-500">Unduh data anggota organisasi saat ini ke dalam berkas CSV.</p>
          </div>
        </div>

        <p class="text-xs text-gray-600 dark:text-gray-400">
          Format ekspor mencakup NIM, Nama Lengkap, Email, Telepon, Program Studi, Angkatan, dan Status Keanggotaan.
        </p>

        <button
          @click="$emit('exportCsv')"
          class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-emerald-700 transition"
        >
          <DownloadIcon class="h-4 w-4" />
          <span>Unduh CSV Data Anggota</span>
        </button>
      </div>

      <!-- Card Template -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600">
            <FileSpreadsheetIcon class="h-5 w-5" />
          </div>
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white">Template Impor CSV</h3>
            <p class="text-xs text-gray-500">Gunakan format kolom resmi untuk menghindari kesalahan validasi.</p>
          </div>
        </div>

        <p class="text-xs text-gray-600 dark:text-gray-400">
          Header wajib: <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-gray-800 dark:text-gray-200">nim, nama, email, telepon, jurusan, angkatan, status, catatan</code>
        </p>

        <button
          @click="$emit('downloadTemplate')"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-2xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition"
        >
          <DownloadIcon class="h-4 w-4" />
          <span>Unduh Template CSV</span>
        </button>
      </div>
    </div>

    <!-- Section Impor CSV dengan Preview Dua Tahap -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] space-y-4 shadow-2xs">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-lg bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600">
          <UploadCloudIcon class="h-5 w-5" />
        </div>
        <div>
          <h3 class="font-bold text-gray-900 dark:text-white">Impor Anggota Massal (Dua Tahap: Validasi &amp; Eksekusi)</h3>
          <p class="text-xs text-gray-500">
            Data akan diverifikasi terlebih dahulu sebelum benar-benar dimasukkan ke database.
          </p>
        </div>
      </div>

      <!-- File Picker -->
      <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center dark:border-gray-700">
        <input
          type="file"
          accept=".csv,text/csv"
          @change="$emit('fileSelected', $event)"
          class="hidden"
          id="csv-file-input"
        />
        <label for="csv-file-input" class="cursor-pointer">
          <UploadCloudIcon class="h-10 w-10 mx-auto text-gray-400 mb-2" />
          <div class="text-xs font-semibold text-gray-800 dark:text-gray-200">
            {{ selectedFile ? selectedFile.name : 'Pilih file CSV untuk diimpor' }}
          </div>
          <p class="text-[11px] text-gray-400 mt-1">Maksimal 5MB, format .csv</p>
        </label>
      </div>

      <div v-if="selectedFile" class="flex items-center gap-3">
        <button
          @click="$emit('runPreview')"
          :disabled="isImporting"
          class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-600 disabled:opacity-50 transition"
        >
          <EyeIcon class="h-4 w-4" />
          <span>{{ isImporting ? 'Memvalidasi...' : 'Tahap 1: Cek & Validasi Baris (Preview)' }}</span>
        </button>

        <button
          v-if="importPreviewResult && importPreviewResult.errors.length === 0 && importPreviewResult.success_count > 0"
          @click="$emit('runCommit')"
          :disabled="isImporting"
          class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-emerald-700 disabled:opacity-50 transition"
        >
          <CheckCircleIcon class="h-4 w-4" />
          <span>Tahap 2: Proses &amp; Simpan ke Database ({{ importPreviewResult.success_count }} data)</span>
        </button>
      </div>

      <!-- Preview & Errors Table -->
      <div v-if="importPreviewResult" class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between text-xs font-semibold">
          <span class="text-gray-700 dark:text-gray-300">Hasil Analisis Berkas:</span>
          <div class="flex items-center gap-3">
            <span class="text-emerald-600">Valid: {{ importPreviewResult.success_count }} baris</span>
            <span v-if="importPreviewResult.error_count > 0" class="text-rose-600">Error: {{ importPreviewResult.error_count }} baris</span>
          </div>
        </div>

        <!-- Error Listing if any -->
        <div v-if="importPreviewResult.errors.length > 0" class="rounded-xl border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-900/50 dark:bg-rose-950/20 text-xs">
          <h5 class="font-bold text-rose-800 dark:text-rose-300 mb-2">Terdapat Kesalahan pada Berkas CSV:</h5>
          <ul class="list-disc list-inside space-y-1 text-rose-700 dark:text-rose-400">
            <li v-for="err in importPreviewResult.errors" :key="err.row">
              Baris {{ err.row }}: {{ err.message }} (NIM: {{ err.nim || '-' }})
            </li>
          </ul>
        </div>

        <!-- Preview Data Table -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
          <table class="w-full text-left text-[11px]">
            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-800">
              <tr>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Baris</th>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">NIM</th>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Nama</th>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Jurusan</th>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Angkatan</th>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                <th class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Aksi Terdeteksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="row in importPreviewResult.preview" :key="row.row">
                <td class="py-2 px-3 font-mono text-gray-600 dark:text-gray-400">{{ row.row }}</td>
                <td class="py-2 px-3 font-bold text-gray-900 dark:text-white">{{ row.nim }}</td>
                <td class="py-2 px-3 text-gray-800 dark:text-gray-200">{{ row.nama }}</td>
                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ row.jurusan || '-' }}</td>
                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ row.angkatan || '-' }}</td>
                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ row.status }}</td>
                <td class="py-2 px-3">
                  <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase" :class="row.action === 'update' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                    {{ row.action === 'update' ? 'Perbarui' : 'Tambah Baru' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { DownloadIcon, FileSpreadsheetIcon, UploadCloudIcon, EyeIcon, CheckCircleIcon } from 'lucide-vue-next'
import type { MemberImportResult } from '@/services/memberService'

defineProps<{
  selectedFile: File | null
  isImporting: boolean
  importPreviewResult: MemberImportResult | null
}>()

defineEmits<{
  (e: 'exportCsv'): void
  (e: 'downloadTemplate'): void
  (e: 'fileSelected', event: Event): void
  (e: 'runPreview'): void
  (e: 'runCommit'): void
}>()
</script>
