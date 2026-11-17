<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-col gap-1">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">
          Manajemen Periode
        </h2>
        <p v-if="organization" class="text-sm font-medium text-gray-500 dark:text-gray-400 break-words">
          Organisasi: <span class="text-gray-900 dark:text-white font-semibold">{{ organization.nama }}</span>
        </p>
        <nav class="mt-1">
          <ol class="flex flex-wrap items-center gap-1.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            <li>
              <router-link class="inline-flex items-center gap-1.5 hover:text-brand-500 transition-colors" to="/dashboard">
                Home
              </router-link>
            </li>
            <li>
              <span class="text-gray-400">/</span>
            </li>
            <li>
              <router-link class="inline-flex items-center gap-1.5 hover:text-brand-500 transition-colors" to="/super-admin/organizations">
                Organisasi
              </router-link>
            </li>
            <li v-if="organization">
              <span class="text-gray-400">/</span>
            </li>
            <li v-if="organization" class="font-medium text-gray-700 dark:text-gray-300 max-w-[200px] truncate" :title="organization.nama">
              {{ organization.nama }}
            </li>
            <li>
              <span class="text-gray-400">/</span>
            </li>
            <li class="font-medium text-gray-900 dark:text-white">
              Manajemen Periode
            </li>
          </ol>
        </nav>
      </div>
    </div>
    
    <!-- Loading State for Initial Org Load -->
    <div v-if="isLoading && !organization" class="py-16 flex flex-col items-center justify-center space-y-4 rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-900/50">
       <div class="h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-brand-500"></div>
       <p class="text-sm text-gray-500 dark:text-gray-400">Memuat data organisasi...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage && !organization" class="py-12 px-6 text-center flex flex-col items-center justify-center rounded-2xl border border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/20">
       <div class="h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center mb-3 text-red-600 dark:text-red-400">
         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
           <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
         </svg>
       </div>
       <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Organisasi Tidak Ditemukan</h3>
       <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 max-w-md">{{ errorMessage }}</p>
       <router-link to="/super-admin/organizations" class="rounded-lg bg-gray-900 text-white px-4 py-2 text-sm font-medium hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
         Kembali ke Daftar Organisasi
       </router-link>
    </div>

    <div v-else class="space-y-6">
      
      <!-- Header Info Organisasi -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
          <div>
            <div class="flex items-center gap-2.5">
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ organization?.nama }}</h3>
              <span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                {{ organization?.jenis }}
              </span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Subdomain: {{ organization?.subdomain }}</p>
          </div>
          <div class="flex items-center gap-3">
            <button 
              v-if="!currentPeriod" 
              @click="openCreateModal" 
              class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-600 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              Set Initial Period
            </button>
          </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pt-5">
           <div>
              <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status Organisasi</p>
              <div>
                <span v-if="organization?.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                  Aktif
                </span>
                <span v-else class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                  Tidak Aktif
                </span>
              </div>
           </div>
           <div>
              <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Periode Aktif</p>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">
                 <template v-if="currentPeriod">
                   {{ currentPeriod.period_name }}
                   <span class="block text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">
                     {{ formatDateOnly(currentPeriod.start_date) }} - {{ formatDateOnly(currentPeriod.end_date) }}
                   </span>
                 </template>
                 <template v-else>
                   <span class="text-gray-400 font-normal">Belum Dikonfigurasi</span>
                 </template>
              </p>
           </div>
           <div>
              <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Total Riwayat Periode</p>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ periods.length }} Periode
              </p>
           </div>
        </div>
      </div>

      <!-- Tabel Daftar Periode -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
              Daftar Periode
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Kelola riwayat kepengurusan dan perpanjangan periode organisasi.
            </p>
          </div>
        </div>

        <!-- Table Loading State -->
        <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400 flex flex-col items-center justify-center space-y-2">
          <div class="h-6 w-6 animate-spin rounded-full border-2 border-gray-300 border-t-brand-500"></div>
          <p>Memuat data periode...</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-400">Nama Periode</th>
                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-400">Mulai</th>
                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-400">Berakhir</th>
                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-400 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr 
                v-for="period in periods" 
                :key="period.id"
                class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
              >
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                  {{ period.period_name }}
                  <span v-if="period.status === 'rejected' && period.rejection_reason" class="block text-xs font-normal text-red-500 mt-0.5">
                    Alasan: {{ period.rejection_reason }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ formatDateOnly(period.start_date) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ formatDateOnly(period.end_date) }}</td>
                <td class="px-4 py-3">
                  <span v-if="period.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                    Aktif
                  </span>
                  <span v-else-if="period.status === 'pending'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                    Pending
                  </span>
                  <span v-else-if="period.status === 'rejected'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    Ditolak
                  </span>
                  <span v-else class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Expired
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex justify-center items-center gap-2">
                    <!-- Detail Button -->
                    <button 
                      @click="openNotes(period)" 
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                      title="Lihat Detail Periode"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Detail
                    </button>

                    <!-- Edit Button -->
                    <button 
                      @click="openEditModal(period)" 
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20 transition-colors"
                      title="Edit Periode"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Edit
                    </button>

                    <!-- Active Toggle: Deactivate Button -->
                    <button 
                      v-if="period.status === 'active'" 
                      @click="deactivatePeriod(period.id)" 
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 transition-colors"
                      title="Nonaktifkan Periode"
                    >
                      Nonaktifkan
                    </button>

                    <!-- Inactive Toggle: Activate Button -->
                    <button 
                      v-else-if="period.status !== 'pending'" 
                      @click="activatePeriod(period.id)" 
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 transition-colors"
                      title="Jadikan Aktif (Otomatis nonaktifkan periode aktif lain)"
                    >
                      Aktifkan
                    </button>

                    <!-- Approve Button (if pending) -->
                    <button 
                      v-if="period.status === 'pending'" 
                      @click="approvePeriod(period.id)" 
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20 transition-colors"
                      title="Setujui Pengajuan"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                      Approve
                    </button>

                    <!-- Reject Button (if pending) -->
                    <button 
                      v-if="period.status === 'pending'" 
                      @click="openRejectModal(period)" 
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors"
                      title="Tolak Pengajuan"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="periods.length === 0">
                <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="font-medium">Belum ada periode organisasi.</p>
                    <p class="text-xs text-gray-400">Klik "Set Initial Period" untuk mengkonfigurasi periode pertama.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form (Create Initial & Edit Period) -->
    <Modal v-if="isModalOpen" @close="closeModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
              {{ isEditMode ? 'Edit Periode Organisasi' : 'Set Initial Period' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="handleFormSubmit" class="space-y-4">
            <!-- Organization Name info -->
            <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800/50 dark:text-gray-300 flex items-center justify-between">
              <span>Organisasi: <strong>{{ organization?.nama }}</strong></span>
              <span v-if="isEditMode && editingPeriod" class="capitalize">
                Status: 
                <strong :class="{
                  'text-green-600 dark:text-green-400': editingPeriod.status === 'active',
                  'text-yellow-600 dark:text-yellow-400': editingPeriod.status === 'pending',
                  'text-red-600 dark:text-red-400': editingPeriod.status === 'rejected',
                  'text-gray-600 dark:text-gray-400': editingPeriod.status === 'expired'
                }">
                  {{ editingPeriod.status === 'active' ? 'Aktif' : editingPeriod.status === 'pending' ? 'Pending' : editingPeriod.status === 'rejected' ? 'Ditolak' : 'Expired' }}
                </strong>
              </span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nama Periode <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.period_name" 
                required 
                type="text" 
                placeholder="Misal: Kepengurusan 2026" 
                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" 
              />
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tanggal Mulai <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.start_date" 
                  required 
                  type="date" 
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tanggal Berakhir <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.end_date" 
                  required 
                  type="date" 
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" 
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
              <textarea 
                v-model="form.notes" 
                rows="3" 
                placeholder="Catatan tambahan (opsional)..." 
                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
              ></textarea>
            </div>

            <div v-if="isEditMode" class="text-xs text-gray-500 dark:text-gray-400">
              * Status periode dikelola secara otomatis oleh sistem berdasarkan tanggal dan persetujuan.
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
              <button 
                type="button" 
                @click="closeModal" 
                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors" 
                :disabled="isSubmitting"
              >
                Batal
              </button>
              <button 
                type="submit" 
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 transition-colors" 
                :disabled="isSubmitting"
              >
                <span v-if="isSubmitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                <span>{{ isSubmitting ? 'Menyimpan perubahan...' : (isEditMode ? 'Simpan Perubahan' : 'Simpan') }}</span>
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>

    <!-- Confirmation Modal for Active Period Edit -->
    <Modal v-if="isActivePeriodConfirmOpen" @close="closeActivePeriodConfirm" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-warning-100 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Ubah Periode Aktif?
              </h3>
              <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Perubahan tanggal periode aktif dapat memengaruhi masa operasional organisasi. Pastikan tanggal yang dimasukkan sudah benar.
              </p>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button 
              type="button" 
              @click="closeActivePeriodConfirm" 
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
              :disabled="isSubmitting"
            >
              Batal
            </button>
            <button 
              type="button" 
              @click="executeSubmit" 
              class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 transition-colors"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              <span>{{ isSubmitting ? 'Menyimpan perubahan...' : 'Simpan Perubahan' }}</span>
            </button>
          </div>
        </div>
      </template>
    </Modal>
    
    <!-- Modal Reject -->
    <Modal v-if="isRejectModalOpen" @close="closeRejectModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white mb-4">
            Tolak Pengajuan Perpanjangan
          </h3>
          
          <form @submit.prevent="submitReject" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Alasan Penolakan <span class="text-red-500">*</span>
              </label>
              <textarea 
                v-model="rejectReason" 
                required 
                rows="3" 
                placeholder="Tuliskan alasan penolakan..." 
                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-gray-700 dark:text-white"
              ></textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button 
                type="button" 
                @click="closeRejectModal" 
                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" 
                :disabled="isSubmitting"
              >
                Batal
              </button>
              <button 
                type="submit" 
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50" 
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Menyimpan...' : 'Tolak Pengajuan' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
    
    <!-- Modal Detail Periode -->
    <Modal v-if="isNotesModalOpen" @close="closeNotesModal" :fullScreenBackdrop="true">
      <template #body>
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
              Informasi Periode
            </h3>
            <button @click="closeNotesModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="space-y-4">
             <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Nama Organisasi</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ organization?.nama || '-' }}</p>
             </div>
             <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Nama Periode</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ selectedPeriod?.period_name || '-' }}</p>
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div>
                  <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Tanggal Mulai</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ formatDateOnly(selectedPeriod?.start_date) }}</p>
               </div>
               <div>
                  <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Tanggal Berakhir</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ formatDateOnly(selectedPeriod?.end_date) }}</p>
               </div>
             </div>
             <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Status</p>
                <div class="mt-1">
                  <span v-if="selectedPeriod?.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                    Aktif
                  </span>
                  <span v-else-if="selectedPeriod?.status === 'pending'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                    Pending
                  </span>
                  <span v-else-if="selectedPeriod?.status === 'rejected'" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    Ditolak
                  </span>
                  <span v-else class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Expired
                  </span>
                </div>
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div>
                  <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Disetujui Oleh</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                    {{ selectedPeriod?.reviewer?.name || (selectedPeriod?.approved_by ? 'Super Admin' : '-') }}
                  </p>
               </div>
               <div>
                  <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Tanggal Persetujuan</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ formatDateOnly(selectedPeriod?.approved_at) }}</p>
               </div>
             </div>
             <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Catatan</p>
                <p class="text-sm text-gray-800 dark:text-gray-200 mt-0.5 whitespace-pre-wrap">{{ selectedPeriod?.notes || '-' }}</p>
             </div>
             <div v-if="selectedPeriod?.status === 'rejected'">
                <p class="text-xs uppercase tracking-wider text-red-500 font-medium">Alasan Penolakan</p>
                <p class="text-sm text-red-600 font-medium mt-0.5">{{ selectedPeriod?.rejection_reason || '-' }}</p>
             </div>
          </div>

          <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
              <button 
                type="button" 
                @click="closeNotesModal" 
                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
              >
                ← Kembali
              </button>
              
              <button 
                type="button" 
                @click="switchToEditFromDetails" 
                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Periode
              </button>
          </div>
        </div>
      </template>
    </Modal>
  </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import Modal from '@/components/ui/Modal.vue'
import api from '@/services/api'
import { organizationPeriodService } from '@/services/organizationPeriodService'
import { useToastStore } from '@/stores/toast'
import type { Organization, OrganizationPeriod } from '@/types/api'

const route = useRoute()
const toastStore = useToastStore()
const organizationId = Number(route.params.id)

const organization = ref<Organization | null>(null)
const periods = ref<OrganizationPeriod[]>([])
const currentPeriod = ref<OrganizationPeriod | null>(null)

const isLoading = ref(false)
const errorMessage = ref('')

const isModalOpen = ref(false)
const isEditMode = ref(false)
const editingPeriod = ref<OrganizationPeriod | null>(null)
const isActivePeriodConfirmOpen = ref(false)

const isRejectModalOpen = ref(false)
const isNotesModalOpen = ref(false)
const isSubmitting = ref(false)

const form = ref<{
  period_name: string
  start_date: string
  end_date: string
  notes: string
}>({
  period_name: '',
  start_date: '',
  end_date: '',
  notes: ''
})

const rejectReason = ref('')
const selectedPeriod = ref<OrganizationPeriod | null>(null)

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const orgRes = await api.get(`/organizations/${organizationId}`)
    organization.value = orgRes.data.data

    const perRes = await organizationPeriodService.getPeriods(organizationId)
    periods.value = perRes.data
    currentPeriod.value = perRes.current_period
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat data periode.'
  } finally {
    isLoading.value = false
  }
}

const formatDateToInput = (dateString?: string | null) => {
  if (!dateString) return ''
  return dateString.substring(0, 10)
}

const openCreateModal = () => {
  isEditMode.value = false
  editingPeriod.value = null
  form.value = { period_name: '', start_date: '', end_date: '', notes: '' }
  isModalOpen.value = true
}

const openEditModal = (period: OrganizationPeriod) => {
  isEditMode.value = true
  editingPeriod.value = period
  form.value = {
    period_name: period.period_name,
    start_date: formatDateToInput(period.start_date),
    end_date: formatDateToInput(period.end_date),
    notes: period.notes || ''
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingPeriod.value = null
}

const handleFormSubmit = () => {
  // If editing an ACTIVE period, show confirmation modal first
  if (isEditMode.value && editingPeriod.value?.status === 'active') {
    isActivePeriodConfirmOpen.value = true
    return
  }

  // Otherwise, proceed directly
  executeSubmit()
}

const closeActivePeriodConfirm = () => {
  isActivePeriodConfirmOpen.value = false
}

const executeSubmit = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  
  try {
    if (isEditMode.value && editingPeriod.value) {
      await organizationPeriodService.updatePeriod(editingPeriod.value.id, {
        period_name: form.value.period_name.trim(),
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        notes: form.value.notes
      })
      toastStore.success('Periode berhasil diperbarui.')
    } else {
      await organizationPeriodService.createPeriod(organizationId, {
        period_name: form.value.period_name.trim(),
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        notes: form.value.notes
      })
      toastStore.success('Periode awal berhasil dikonfigurasi.')
    }
    
    closeActivePeriodConfirm()
    closeModal()
    await loadData()
  } catch (error: any) {
    const backendMessage = error.response?.data?.message
    toastStore.error(backendMessage || 'Gagal memperbarui periode. Silakan periksa kembali data yang dimasukkan.')
  } finally {
    isSubmitting.value = false
  }
}

const approvePeriod = async (id: number) => {
    if (!confirm('Setujui pengajuan perpanjangan periode ini? Periode aktif sebelumnya akan otomatis dinonaktifkan.')) return
    
    try {
        await organizationPeriodService.approvePeriod(id)
        toastStore.success('Periode berhasil disetujui dan diaktifkan.')
        await loadData()
    } catch (error: any) {
        toastStore.error(error.response?.data?.message || 'Gagal menyetujui periode.')
    }
}

const activatePeriod = async (id: number) => {
    if (!confirm('Aktifkan periode ini? Periode aktif sebelumnya untuk organisasi ini akan otomatis dinonaktifkan.')) return
    
    try {
        await organizationPeriodService.activatePeriod(id)
        toastStore.success('Periode berhasil diaktifkan.')
        await loadData()
    } catch (error: any) {
        toastStore.error(error.response?.data?.message || 'Gagal mengaktifkan periode.')
    }
}

const deactivatePeriod = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menonaktifkan periode ini?')) return
    
    try {
        await organizationPeriodService.deactivatePeriod(id)
        toastStore.success('Periode berhasil dinonaktifkan.')
        await loadData()
    } catch (error: any) {
        toastStore.error(error.response?.data?.message || 'Gagal menonaktifkan periode.')
    }
}

const openRejectModal = (period: OrganizationPeriod) => {
    selectedPeriod.value = period
    rejectReason.value = ''
    isRejectModalOpen.value = true
}

const closeRejectModal = () => {
    isRejectModalOpen.value = false
    selectedPeriod.value = null
}

const submitReject = async () => {
    if (!selectedPeriod.value || !rejectReason.value.trim()) return
    isSubmitting.value = true
    try {
        await organizationPeriodService.rejectPeriod(selectedPeriod.value.id, rejectReason.value.trim())
        toastStore.success('Pengajuan berhasil ditolak.')
        closeRejectModal()
        await loadData()
    } catch (error: any) {
        toastStore.error(error.response?.data?.message || 'Gagal menolak pengajuan.')
    } finally {
        isSubmitting.value = false
    }
}

const openNotes = (period: OrganizationPeriod) => {
    selectedPeriod.value = period
    isNotesModalOpen.value = true
}

const closeNotesModal = () => {
    isNotesModalOpen.value = false
    selectedPeriod.value = null
}

const switchToEditFromDetails = () => {
    if (!selectedPeriod.value) return
    const targetPeriod = selectedPeriod.value
    closeNotesModal()
    openEditModal(targetPeriod)
}

const formatDateOnly = (dateString?: string | null) => {
  if (!dateString) return '-'
  
  const datePart = dateString.substring(0, 10)
  const date = new Date(datePart)
  
  if (isNaN(date.getTime())) return '-'
  
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  }).format(date)
}
</script>
