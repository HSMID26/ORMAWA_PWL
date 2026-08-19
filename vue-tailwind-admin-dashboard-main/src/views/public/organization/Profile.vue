<template>
  <div class="flex-grow bg-white">
    <!-- State: Loading -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
      <div class="w-8 h-8 border-4 border-gray-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
      <p class="text-gray-500 font-medium">Memuat profil organisasi...</p>
    </div>
    
    <!-- State: Error / Endpoint Unavailable -->
    <div v-else-if="error" class="max-w-3xl mx-auto py-24 px-4 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-4">Profil Belum Tersedia</h1>
      <p class="text-lg text-gray-600 mb-8 max-w-xl mx-auto">
        Informasi profil publik organisasi akan tampil setelah layanan konten terhubung.
      </p>
    </div>

    <!-- State: Success -->
    <div v-else-if="currentOrganization">
      <div class="bg-gray-50 py-16 border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
            Profil Organisasi
          </h1>
          <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
            Mengenal lebih dekat sejarah, visi, dan misi {{ currentOrganization.nama }}.
          </p>
        </div>
      </div>

      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        
        <!-- Identitas -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Identitas Organisasi</h2>
          <div class="bg-white border border-gray-200 rounded-xl p-8 flex flex-col md:flex-row gap-8 items-start shadow-sm">
            <div class="w-32 h-32 bg-gray-100 rounded-full flex-shrink-0 flex items-center justify-center border border-gray-200">
               <span class="text-5xl font-bold text-gray-400">{{ currentOrganization.nama.charAt(0) }}</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">{{ currentOrganization.nama }}</h3>
              <dl class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 mt-4 text-sm">
                <div>
                  <dt class="font-medium text-gray-500">Kategori</dt>
                  <dd class="mt-1 text-gray-900">{{ currentOrganization.jenis }}</dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Singkatan</dt>
                  <dd class="mt-1 text-gray-900 uppercase">{{ currentTenantSlug }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </section>

        <!-- Sejarah / Visi Misi placeholder -->
        <section class="prose prose-brand max-w-none text-gray-600">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200 not-prose">Tentang Organisasi</h2>
          <p>
            Informasi detail mengenai sejarah, visi, dan misi organisasi saat ini belum tersedia di database publik. 
            Data ini nantinya akan dirender secara dinamis setelah endpoint profil lengkap terhubung.
          </p>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()
const { currentTenantSlug, currentOrganization, isLoading, error } = storeToRefs(publicStore)
</script>
