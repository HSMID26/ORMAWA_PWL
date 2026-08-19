<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Struktur Kepengurusan
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola bagan struktur, profil pengurus, dan jabatan per periode.
          </p>
        </div>
        <button class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-theme-xs transition-colors">
          + Tambah Pengurus
        </button>
      </div>

      <!-- Search & Filter Bar -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row">
        <!-- Pencarian Text -->
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama pengurus atau jabatan..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          />
        </div>
        
        <!-- Filter Periode -->
        <div class="sm:w-48">
          <select
            v-model="filterPeriode"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Periode</option>
            <option value="2026/2027">Periode 2026/2027</option>
            <option value="2025/2026">Periode 2025/2026</option>
          </select>
        </div>

        <!-- Filter Divisi -->
        <div class="sm:w-48">
          <select
            v-model="filterDivisi"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
          >
            <option value="">Semua Divisi</option>
            <option value="BPH">Badan Pengurus Harian</option>
            <option value="Medinfo">Media & Informasi</option>
            <option value="Humas">Hubungan Masyarakat</option>
            <option value="Ristek">Riset & Teknologi</option>
          </select>
        </div>
      </div>

      <!-- Tabel Data Struktur Organisasi -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Profil Pengurus</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Divisi / Departemen</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400">Periode</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-400 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="person in filteredPengurus" 
              :key="person.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <img :src="person.foto" :alt="person.nama" class="h-10 w-10 shrink-0 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                  <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                      {{ person.nama }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ person.nim }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4">
                <span class="inline-flex rounded-md bg-brand-50 px-2 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                  {{ person.jabatan }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                {{ person.divisi }}
              </td>
              <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90 font-medium">
                {{ person.periode }}
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex justify-center items-center gap-3">
                  <button class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition-colors" title="Edit Data">Edit</button>
                  <button class="text-sm font-medium text-error-500 hover:text-error-600 dark:text-error-400 transition-colors" title="Hapus Pengurus">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredPengurus.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Data pengurus tidak ditemukan pada filter ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

// Title Halaman
const currentPageTitle = ref('Struktur Organisasi')

// State Pencarian dan Filter
const searchQuery = ref('')
const filterPeriode = ref('2026/2027') // Default active period
const filterDivisi = ref('')

// Simulasi Data Pengurus (Merujuk pada entitas Struktur Organisasi di PRD)
const dataPengurus = ref([
  {
    id: 1,
    nama: 'Bagas Wicaksono',
    nim: '1120230001',
    jabatan: 'Ketua Umum',
    divisi: 'BPH',
    periode: '2026/2027',
    foto: 'https://ui-avatars.com/api/?name=Bagas+Wicaksono&background=random'
  },
  {
    id: 2,
    nama: 'Nadia Prameswari',
    nim: '1120230045',
    jabatan: 'Sekretaris Umum',
    divisi: 'BPH',
    periode: '2026/2027',
    foto: 'https://ui-avatars.com/api/?name=Nadia+Prameswari&background=random'
  },
  {
    id: 3,
    nama: 'Dimas Prasetyo',
    nim: '1120240112',
    jabatan: 'Kepala Departemen',
    divisi: 'Medinfo',
    periode: '2026/2027',
    foto: 'https://ui-avatars.com/api/?name=Dimas+Prasetyo&background=random'
  },
  {
    id: 4,
    nama: 'Salsabila Rania',
    nim: '1120240188',
    jabatan: 'Staff Ahli',
    divisi: 'Humas',
    periode: '2026/2027',
    foto: 'https://ui-avatars.com/api/?name=Salsabila+Rania&background=random'
  },
  {
    id: 5,
    nama: 'Kevin Sanjaya',
    nim: '1120220090',
    jabatan: 'Ketua Umum',
    divisi: 'BPH',
    periode: '2025/2026',
    foto: 'https://ui-avatars.com/api/?name=Kevin+Sanjaya&background=random'
  }
])

// Logika Filter Reaktif
const filteredPengurus = computed(() => {
  return dataPengurus.value.filter(person => {
    // 1. Pencarian Text (Berdasarkan nama atau jabatan)
    const matchSearch = person.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                        person.jabatan.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // 2. Filter Periode
    const matchPeriode = filterPeriode.value === '' || person.periode === filterPeriode.value
    
    // 3. Filter Divisi
    const matchDivisi = filterDivisi.value === '' || person.divisi === filterDivisi.value

    return matchSearch && matchPeriode && matchDivisi
  })
})
</script>