<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Struktur Organisasi" />

    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl flex items-center gap-2">
              <UsersIcon class="h-6 w-6 text-brand-500" />
              Struktur Organisasi
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Kelola struktur kepengurusan organisasi berdasarkan periode.
            </p>
          </div>

          <div v-if="canManage" class="flex items-center gap-3">
            <button
              @click="openCreateModal"
              class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20 disabled:opacity-50 transition"
              :disabled="isLoading"
            >
              <PlusIcon class="h-4 w-4" />
              <span>Tambah Pengurus</span>
            </button>
          </div>
        </div>

        <!-- Info Periode Aktif Card -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 border-t border-gray-100 dark:border-gray-800 pt-5">
          <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Organisasi</span>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
              {{ currentOrgName }}
            </p>
          </div>

          <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Periode Aktif</span>
            <p class="mt-1 text-base font-semibold" :class="activePeriod ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500'">
              {{ activePeriod ? activePeriod.period_name : 'Tidak Ada' }}
            </p>
          </div>

          <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Masa Berlaku</span>
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
              <template v-if="activePeriod">
                {{ formatDate(activePeriod.start_date) }} — {{ formatDate(activePeriod.end_date) }}
              </template>
              <template v-else>-</template>
            </p>
          </div>

          <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Pengurus</span>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
              {{ committees.length }} Pengurus
            </p>
          </div>
        </div>

        <!-- Warning jika tidak ada periode aktif -->
        <div
          v-if="!isLoading && !activePeriod && userRole === 'Admin Organisasi'"
          class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/30"
        >
          <div class="flex items-start gap-3">
            <AlertTriangleIcon class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
            <div>
              <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                Belum ada periode kepengurusan aktif
              </h4>
              <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                Organisasi Anda saat ini belum memiliki periode kepengurusan dengan status aktif. Anda dapat menambahkan pengurus baru setelah memilih periode kepengurusan.
              </p>
              <div class="mt-3">
                <router-link
                  to="/organization/period"
                  class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800 hover:text-amber-900 dark:text-amber-300 underline"
                >
                  Buka Manajemen Periode
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter & Search Bar -->
      <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <!-- Search -->
          <div class="relative flex-1 max-w-md">
            <SearchIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari nama, jabatan, atau divisi..."
              class="w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white dark:placeholder-gray-500"
            />
          </div>

          <!-- Select Filters -->
          <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Periode -->
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Periode:</label>
              <select
                v-model="filters.organization_period_id"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
              >
                <option :value="undefined">Semua Periode</option>
                <option
                  v-for="period in periodsList"
                  :key="period.id"
                  :value="period.id"
                >
                  {{ period.period_name }} ({{ period.status === 'active' ? 'Aktif' : period.status }})
                </option>
              </select>
            </div>

            <!-- Filter Status -->
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Status:</label>
              <select
                v-model="filters.status"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
              >
                <option value="">Semua</option>
                <option value="active">Aktif</option>
                <option value="inactive">Non-Aktif</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Loading State -->
        <div v-if="isLoading" class="p-12 text-center">
          <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-r-transparent"></div>
          <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Memuat data struktur kepengurusan...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredCommittees.length === 0" class="p-12 text-center">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400">
            <UsersIcon class="h-7 w-7" />
          </div>
          <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">
            Belum ada data pengurus
          </h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            {{ filters.search || filters.organization_period_id || filters.status ? 'Tidak ada pengurus yang sesuai dengan filter pencarian.' : 'Belum ada data pengurus untuk organisasi atau periode ini.' }}
          </p>
          <div v-if="canManage && !filters.search" class="mt-5">
            <button
              @click="openCreateModal"
              class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
            >
              <PlusIcon class="h-4 w-4" />
              <span>Tambah Pengurus Pertama</span>
            </button>
          </div>
        </div>

        <!-- Table Data -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/75 dark:border-gray-800 dark:bg-gray-900/50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                <th class="py-3.5 px-4 font-semibold">Pengurus</th>
                <th class="py-3.5 px-4 font-semibold">Jabatan & Divisi</th>
                <th class="py-3.5 px-4 font-semibold">Periode Kepengurusan</th>
                <th class="py-3.5 px-4 font-semibold">Status</th>
                <th class="py-3.5 px-4 font-semibold text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr
                v-for="item in filteredCommittees"
                :key="item.id"
                class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors"
              >
                <!-- Pengurus (Photo + Name) -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                      <img
                        v-if="item.photo_url"
                        :src="item.photo_url"
                        :alt="item.name"
                        class="h-full w-full object-cover"
                      />
                      <span v-else class="font-bold text-gray-500 dark:text-gray-400 text-sm">
                        {{ item.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                    <div>
                      <div class="font-bold text-gray-900 dark:text-white">
                        {{ item.name }}
                      </div>
                      <div class="text-[11px] text-gray-400">
                        ID: #{{ item.id }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Position & Department -->
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-gray-800 dark:text-gray-200">
                    {{ item.position }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ item.department || 'BPH / Utama' }}
                  </div>
                </td>

                <!-- Period -->
                <td class="py-3.5 px-4">
                  <div class="font-medium text-gray-800 dark:text-gray-200">
                    {{ item.organization_period?.period_name || item.period_name || item.period }}
                  </div>
                  <div v-if="item.organization_period" class="text-xs text-gray-400">
                    {{ formatDate(item.organization_period.start_date) }} — {{ formatDate(item.organization_period.end_date) }}
                  </div>
                </td>

                <!-- Status Badge -->
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase',
                      item.status === 'active'
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'
                    ]"
                  >
                    <span
                      class="mr-1.5 h-1.5 w-1.5 rounded-full"
                      :class="item.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400'"
                    ></span>
                    {{ item.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- Detail -->
                    <button
                      @click="openDetailModal(item)"
                      class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg dark:text-gray-400 dark:hover:text-brand-400 dark:hover:bg-brand-950/30 transition-colors"
                      title="Lihat Detail Pengurus"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </button>

                    <!-- Edit -->
                    <button
                      v-if="canManage"
                      @click="openEditModal(item)"
                      class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-950/30 transition-colors"
                      title="Edit Pengurus"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </button>

                    <!-- Delete -->
                    <button
                      v-if="canManage"
                      @click="openDeleteModal(item)"
                      class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg dark:text-gray-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/30 transition-colors"
                      title="Hapus Pengurus"
                    >
                      <Trash2Icon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ─── Modal Create / Edit Pengurus ──────────────────────────────────────── -->
    <div
      v-if="isFormModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm overflow-y-auto"
      @click.self="closeFormModal"
    >
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 my-8">
        <!-- Close Button -->
        <button
          @click="closeFormModal"
          class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        >
          <XIcon class="h-5 w-5" />
        </button>

        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Data Pengurus' : 'Tambah Pengurus Baru' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Lengkapi formulir informasi kepengurusan organisasi di bawah ini.
        </p>

        <form @submit.prevent="submitForm" class="mt-5 space-y-4">
          <!-- Nama Lengkap Pengurus -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Nama Pengurus <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="formData.name"
              type="text"
              required
              placeholder="Contoh: Fahkrie"
              class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <!-- Jabatan -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Jabatan <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="formData.position"
              type="text"
              required
              placeholder="Contoh: Ketua, Wakil Ketua, Sekretaris, Bendahara"
              class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <!-- Divisi / Departemen -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Divisi / Departemen
            </label>
            <input
              v-model="formData.department"
              type="text"
              placeholder="Contoh: Ketua, Hubungan Masyarakat, Pengembangan SDM"
              class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white"
            />
          </div>

          <!-- Periode Kepengurusan -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Periode Kepengurusan <span class="text-rose-500">*</span>
            </label>
            <select
              v-model="formData.organization_period_id"
              required
              class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option :value="undefined" disabled>-- Pilih Periode Kepengurusan --</option>
              <option
                v-for="period in periodsList"
                :key="period.id"
                :value="period.id"
              >
                {{ period.period_name }} ({{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}) - [{{ period.status === 'active' ? 'Aktif' : period.status }}]
              </option>
            </select>
          </div>

          <!-- Status Kepengurusan -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Status Kepengurusan
            </label>
            <select
              v-model="formData.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Non-Aktif</option>
            </select>
          </div>

          <!-- Upload Foto Profil -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Foto Profil
            </label>
            <input
              type="file"
              accept="image/*"
              @change="handlePhotoChange"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300"
            />
            <!-- Preview Foto -->
            <div v-if="photoPreview || formData.photo_url" class="mt-2 flex items-center gap-3">
              <img
                :src="photoPreview || formData.photo_url"
                alt="Preview"
                class="h-16 w-16 rounded-full object-cover border border-gray-200 dark:border-gray-700"
              />
              <span class="text-xs text-gray-400">Preview foto profil</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
            <button
              type="button"
              @click="closeFormModal"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50"
            >
              {{ isSubmitting ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Pengurus') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── Modal Detail Pengurus ───────────────────────────────────────────── -->
    <div
      v-if="isDetailModalOpen && selectedCommittee"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
      @click.self="isDetailModalOpen = false"
    >
      <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <button
          @click="isDetailModalOpen = false"
          class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        >
          <XIcon class="h-5 w-5" />
        </button>

        <div class="text-center">
          <div class="mx-auto h-20 w-20 overflow-hidden rounded-full border-2 border-brand-500 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
            <img
              v-if="selectedCommittee.photo_url"
              :src="selectedCommittee.photo_url"
              :alt="selectedCommittee.name"
              class="h-full w-full object-cover"
            />
            <span v-else class="text-2xl font-bold text-gray-600 dark:text-gray-300">
              {{ selectedCommittee.name.charAt(0).toUpperCase() }}
            </span>
          </div>

          <h3 class="mt-3 text-lg font-bold text-gray-900 dark:text-white">
            {{ selectedCommittee.name }}
          </h3>
          <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">
            {{ selectedCommittee.position }}
          </p>
          <p class="text-xs text-gray-500">
            {{ selectedCommittee.department || 'BPH / Utama' }}
          </p>
        </div>

        <div class="mt-6 space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-400">Periode:</span>
            <span class="font-medium text-gray-800 dark:text-gray-200">
              {{ selectedCommittee.organization_period?.period_name || selectedCommittee.period_name || selectedCommittee.period }}
            </span>
          </div>

          <div v-if="selectedCommittee.organization_period" class="flex justify-between">
            <span class="text-gray-400">Masa Bakti:</span>
            <span class="font-medium text-gray-800 dark:text-gray-200">
              {{ formatDate(selectedCommittee.organization_period.start_date) }} s/d {{ formatDate(selectedCommittee.organization_period.end_date) }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-400">Status Kepengurusan:</span>
            <span
              :class="selectedCommittee.status === 'active' ? 'text-emerald-600 font-semibold' : 'text-gray-500 font-semibold'"
            >
              {{ selectedCommittee.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
            </span>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
          <button
            @click="isDetailModalOpen = false"
            class="w-full rounded-lg bg-gray-100 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- ─── Modal Delete Confirmation ──────────────────────────────────────── -->
    <div
      v-if="isDeleteModalOpen && selectedCommittee"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
      @click.self="isDeleteModalOpen = false"
    >
      <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="text-center">
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400">
            <Trash2Icon class="h-6 w-6" />
          </div>

          <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">
            Hapus Data Pengurus?
          </h3>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Apakah Anda yakin ingin menghapus data pengurus <span class="font-bold text-gray-800 dark:text-white">{{ selectedCommittee.name }}</span> ({{ selectedCommittee.position }})? Tindakan ini tidak dapat dibatalkan.
          </p>
        </div>

        <div class="mt-6 flex items-center justify-center gap-3">
          <button
            type="button"
            @click="isDeleteModalOpen = false"
            class="flex-1 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            type="button"
            @click="confirmDelete"
            :disabled="isSubmitting"
            class="flex-1 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menghapus...' : 'Hapus Pengurus' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { committeeService } from '@/services/committeeService'
import { organizationPeriodService } from '@/services/organizationPeriodService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import type { Committee, OrganizationPeriod } from '@/types/api'

import {
  UsersIcon,
  PlusIcon,
  SearchIcon,
  EyeIcon,
  PencilIcon,
  Trash2Icon,
  AlertTriangleIcon,
  XIcon,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toastStore = useToastStore()

const userRole = computed(() => authStore.role)
const canManage = computed(() => userRole.value === 'Super Admin' || userRole.value === 'Admin Organisasi')
const currentOrgName = computed(() => authStore.user?.organization?.nama || 'Organisasi Anda')

const isLoading = ref(true)
const isSubmitting = ref(false)

const committees = ref<Committee[]>([])
const periodsList = ref<OrganizationPeriod[]>([])

// Filters
const filters = ref<{
  search: string
  organization_period_id: number | undefined
  status: string
}>({
  search: '',
  organization_period_id: undefined,
  status: '',
})

// Filtered committees
const filteredCommittees = computed(() => {
  return committees.value.filter(item => {
    // Filter Period
    if (filters.value.organization_period_id !== undefined) {
      if (item.organization_period_id !== filters.value.organization_period_id) {
        return false
      }
    }

    // Filter Status
    if (filters.value.status) {
      if (item.status !== filters.value.status) {
        return false
      }
    }

    // Search query
    if (filters.value.search.trim()) {
      const q = filters.value.search.toLowerCase()
      const matchName = item.name.toLowerCase().includes(q)
      const matchPos = item.position.toLowerCase().includes(q)
      const matchDept = item.department ? item.department.toLowerCase().includes(q) : false
      if (!matchName && !matchPos && !matchDept) {
        return false
      }
    }

    return true
  })
})

// Modal states
const isFormModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const isDetailModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedCommittee = ref<Committee | null>(null)

// Form data
const formData = ref<{
  name: string
  position: string
  department: string
  organization_period_id: number | undefined
  status: 'active' | 'inactive'
  photo: File | null
  photo_url?: string
}>({
  name: '',
  position: '',
  department: '',
  organization_period_id: undefined,
  status: 'active',
  photo: null,
  photo_url: '',
})

const photoPreview = ref<string | null>(null)

const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  try {
    const d = new Date(dateString)
    return d.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })
  } catch {
    return dateString
  }
}

const currentPeriod = ref<OrganizationPeriod | null>(null)

const activePeriod = computed(() => {
  if (currentPeriod.value) return currentPeriod.value
  const active = periodsList.value.find(p => p.status === 'active')
  if (active) return active
  const commWithPeriod = committees.value.find(c => c.organization_period)
  if (commWithPeriod?.organization_period) {
    return commWithPeriod.organization_period
  }
  return null
})

// Fetch all initial data
const fetchData = async () => {
  isLoading.value = true
  try {
    const orgId = authStore.organization_id || (authStore.user as any)?.organization_id || authStore.user?.organization?.id

    // 1. Fetch periods
    if (orgId) {
      const res = await organizationPeriodService.getPeriods(orgId)
      periodsList.value = res.data || []
      currentPeriod.value = res.current_period || null
    }

    // 2. Fetch committees
    const commList = await committeeService.list()
    committees.value = commList

    // 3. Fallback periodsList from committees if periodsList is still empty
    if (periodsList.value.length === 0 && commList.length > 0) {
      const uniquePeriods = new Map<number, OrganizationPeriod>()
      commList.forEach(c => {
        if (c.organization_period && !uniquePeriods.has(c.organization_period.id)) {
          uniquePeriods.set(c.organization_period.id, c.organization_period)
        }
      })
      if (uniquePeriods.size > 0) {
        periodsList.value = Array.from(uniquePeriods.values())
      }
    }
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal memuat data struktur organisasi.')
  } finally {
    isLoading.value = false
  }
}

// Open create modal
const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  selectedCommittee.value = null
  photoPreview.value = null
  formData.value = {
    name: '',
    position: '',
    department: '',
    organization_period_id: activePeriod.value ? activePeriod.value.id : (periodsList.value[0]?.id || undefined),
    status: 'active',
    photo: null,
    photo_url: '',
  }
  isFormModalOpen.value = true
}

// Open edit modal
const openEditModal = (committee: Committee) => {
  isEditing.value = true
  editingId.value = committee.id
  selectedCommittee.value = committee
  photoPreview.value = null
  formData.value = {
    name: committee.name,
    position: committee.position,
    department: committee.department || '',
    organization_period_id: committee.organization_period_id || undefined,
    status: committee.status,
    photo: null,
    photo_url: committee.photo_url || '',
  }
  isFormModalOpen.value = true
}

// Close form modal
const closeFormModal = () => {
  isFormModalOpen.value = false
  isEditing.value = false
  editingId.value = null
  selectedCommittee.value = null
  photoPreview.value = null
}

// Handle photo upload change
const handlePhotoChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target && target.files && target.files[0]) {
    const file = target.files[0]
    formData.value.photo = file
    photoPreview.value = URL.createObjectURL(file)
  }
}

// Submit form (create / update)
const submitForm = async () => {
  isSubmitting.value = true
  try {
    const data = new FormData()
    data.append('name', formData.value.name)
    data.append('position', formData.value.position)
    if (formData.value.department) {
      data.append('department', formData.value.department)
    }
    if (formData.value.organization_period_id) {
      data.append('organization_period_id', String(formData.value.organization_period_id))
    }
    data.append('status', formData.value.status)

    if (formData.value.photo) {
      data.append('photo', formData.value.photo)
    }

    if (isEditing.value && editingId.value) {
      await committeeService.update(editingId.value, data)
      toastStore.success('Data pengurus berhasil diperbarui!')
    } else {
      await committeeService.create(data)
      toastStore.success('Data pengurus berhasil ditambahkan!')
    }

    closeFormModal()
    await fetchData()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menyimpan data pengurus.')
  } finally {
    isSubmitting.value = false
  }
}

// Open detail modal
const openDetailModal = (committee: Committee) => {
  selectedCommittee.value = committee
  isDetailModalOpen.value = true
}

// Open delete modal
const openDeleteModal = (committee: Committee) => {
  selectedCommittee.value = committee
  isDeleteModalOpen.value = true
}

// Confirm delete
const confirmDelete = async () => {
  if (!selectedCommittee.value) return
  isSubmitting.value = true
  try {
    await committeeService.delete(selectedCommittee.value.id)
    toastStore.success('Data pengurus berhasil dihapus!')
    isDeleteModalOpen.value = false
    selectedCommittee.value = null
    await fetchData()
  } catch (error: any) {
    toastStore.error(error.message || 'Gagal menghapus data pengurus.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
