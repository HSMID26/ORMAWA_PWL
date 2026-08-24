<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    
    <div class="p-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
      
      <!-- Header & Tombol Tambah -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Tata Kelola Organisasi Mahasiswa</h2>
          <p class="text-xs text-gray-500 mt-0.5">Kelola seluruh ormawa (BEM, DPM, UKM, HMPS), status operasional, dan admin terkait.</p>
        </div>
        <button @click="openModal()" class="flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-brand-700 transition">
          <PlusIcon class="h-4 w-4" />
          <span>Tambah Organisasi</span>
        </button>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama atau subdomain..."
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:text-white"
        />
        <select
          v-model="selectedJenis"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Semua Jenis (BEM/UKM/HMPS)</option>
          <option value="BEM">BEM</option>
          <option value="UKM">UKM</option>
          <option value="HMPS">HMPS</option>
        </select>
        <select
          v-model="selectedStatus"
          class="h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </div>

      <!-- Tabel Data -->
      <div v-if="isLoading" class="py-12 text-center text-sm text-gray-500">
        Memuat organisasi...
      </div>
      <div v-else-if="filteredOrganizations.length === 0" class="py-12 text-center text-sm text-gray-500">
        Data organisasi tidak ditemukan.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-600 dark:text-gray-400">
              <th class="px-4 py-3">Organisasi</th>
              <th class="px-4 py-3">Admin Organisasi</th>
              <th class="px-4 py-3">Periode Aktif</th>
              <th class="px-4 py-3">Pengguna</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
            <tr 
              v-for="org in filteredOrganizations" 
              :key="org.id"
              class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]"
            >
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 text-sm font-bold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ org.nama.charAt(0).toUpperCase() }}
                  </div>
                  <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-gray-900 dark:text-white">{{ org.nama }}</span>
                      <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ org.jenis }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ org.subdomain }}.ormawa.id</span>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                <div v-if="org.admin_user">
                  <span class="font-medium text-gray-900 dark:text-white">{{ org.admin_user.name }}</span>
                  <div class="text-[10px] text-gray-400">{{ org.admin_user.email }}</div>
                </div>
                <span v-else class="text-xs text-orange-500 italic">Belum ada admin</span>
              </td>
              <td class="px-4 py-3.5 text-xs">
                <span v-if="org.current_period" class="font-semibold text-gray-800 dark:text-gray-200">
                  {{ org.current_period.period_name }}
                </span>
                <span v-else class="text-xs text-orange-500 italic">Belum dikonfigurasi</span>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-600 dark:text-gray-400">
                {{ org.users_count || 0 }} Pengguna
              </td>
              <td class="px-4 py-3.5">
                <span v-if="org.status === 'active'" class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                  Aktif
                </span>
                <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                  <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                  Nonaktif
                </span>
              </td>
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="openDetail(org)" class="rounded-lg p-1.5 text-brand-600 hover:bg-brand-50" title="Detail Organisasi">
                    <EyeIcon class="h-4 w-4" />
                  </button>
                  <router-link :to="`/super-admin/organizations/${org.id}/periods`" class="rounded-lg p-1.5 text-purple-600 hover:bg-purple-50" title="Kelola Periode">
                    <CalendarIcon class="h-4 w-4" />
                  </router-link>
                  <button @click="openModal(org)" class="rounded-lg p-1.5 text-gray-600 hover:bg-gray-100" title="Edit Organisasi">
                    <EditIcon class="h-4 w-4" />
                  </button>
                  <button v-if="org.status === 'inactive'" @click="openStatusModal(org, 'activate')" class="rounded-lg p-1.5 text-green-600 hover:bg-green-50" title="Aktifkan">
                    <CheckCircleIcon class="h-4 w-4" />
                  </button>
                  <button v-if="org.status === 'active'" @click="openStatusModal(org, 'deactivate')" class="rounded-lg p-1.5 text-orange-600 hover:bg-orange-50" title="Nonaktifkan">
                    <XCircleIcon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Organisasi' : 'Tambah Organisasi Baru' }}
        </h3>
        
        <form @submit.prevent="saveOrganization" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Organisasi</label>
            <input v-model="form.nama" type="text" required class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Jenis</label>
            <select v-model="form.jenis" required class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              <option value="UKM">UKM</option>
              <option value="HMPS">HMPS</option>
              <option value="BEM">BEM</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Subdomain</label>
            <input v-model="form.subdomain" type="text" required class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
          </div>

          <div v-if="modalError" class="text-xs text-red-600 bg-red-50 p-2.5 rounded-lg">
            {{ modalError }}
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showModal = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
            <button type="submit" :disabled="isSubmitting" class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Status Confirmation (Activate / Deactivate) -->
    <div v-if="showStatusConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Konfirmasi Perubahan Status</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Apakah Anda yakin ingin <b>{{ statusAction === 'activate' ? 'mengaktifkan' : 'menonaktifkan' }}</b> organisasi <b>{{ selectedOrg?.nama }}</b>?
        </p>
        <div class="flex justify-end gap-3 pt-2">
          <button @click="showStatusConfirmModal = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
          <button @click="confirmStatusChange" :disabled="isSubmitting" :class="statusAction === 'activate' ? 'bg-green-600 hover:bg-green-700' : 'bg-orange-600 hover:bg-orange-700'" class="rounded-xl px-5 py-2 text-sm font-semibold text-white disabled:opacity-50">
            {{ isSubmitting ? 'Memproses...' : 'Ya, Lanjutkan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Organisasi -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-xl font-bold text-brand-600">
              {{ detailOrg?.nama?.charAt(0) }}
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ detailOrg?.nama }}</h3>
              <p class="text-xs text-gray-500">{{ detailOrg?.subdomain }}.ormawa.id</p>
            </div>
          </div>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <!-- Detail Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
            <p class="text-[10px] text-gray-500 uppercase">Total Pengguna</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailOrg?.users_count || 0 }}</p>
          </div>
          <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
            <p class="text-[10px] text-gray-500 uppercase">Artikel Terbit</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailOrg?.published_posts_count || 0 }}</p>
          </div>
          <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
            <p class="text-[10px] text-gray-500 uppercase">Agenda Acara</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailOrg?.activities_count || 0 }}</p>
          </div>
          <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
            <p class="text-[10px] text-gray-500 uppercase">Pengumuman</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailOrg?.announcements_count || 0 }}</p>
          </div>
        </div>

        <div class="border-t pt-3 space-y-2 dark:border-gray-700 text-xs">
          <div class="flex justify-between py-1 border-b dark:border-gray-800">
            <span class="text-gray-500">Admin Organisasi:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ detailOrg?.admin_user?.name || 'Belum ditugaskan' }}</span>
          </div>
          <div class="flex justify-between py-1 border-b dark:border-gray-800">
            <span class="text-gray-500">Periode Aktif:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ detailOrg?.current_period?.period_name || 'Belum ada' }}</span>
          </div>
          <div class="flex justify-between py-1 border-b dark:border-gray-800">
            <span class="text-gray-500">Jumlah Editor / Kontributor:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ detailOrg?.editors_count || 0 }} Editor, {{ detailOrg?.contributors_count || 0 }} Kontributor</span>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button @click="showDetailModal = false" class="rounded-xl bg-gray-200 px-5 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-white">
            Tutup
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { PlusIcon, EyeIcon, CalendarIcon, EditIcon, CheckCircleIcon, XCircleIcon } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { organizationService } from '@/services/organizationService'
import type { Organization } from '@/types/api'

const currentPageTitle = ref('Semua Organisasi')
const isLoading = ref(true)
const isSubmitting = ref(false)
const organizations = ref<Organization[]>([])

const searchQuery = ref('')
const selectedJenis = ref('')
const selectedStatus = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const modalError = ref<string | null>(null)

const showStatusConfirmModal = ref(false)
const selectedOrg = ref<Organization | null>(null)
const statusAction = ref<'activate' | 'deactivate'>('activate')

const showDetailModal = ref(false)
const detailOrg = ref<any>(null)

const form = ref({
  id: null as number | null,
  nama: '',
  jenis: 'UKM',
  subdomain: '',
})

const filteredOrganizations = computed(() => {
  return organizations.value.filter((org) => {
    const matchSearch = !searchQuery.value ||
      org.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      org.subdomain.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchJenis = !selectedJenis.value || org.jenis === selectedJenis.value
    const matchStatus = !selectedStatus.value || org.status === selectedStatus.value
    return matchSearch && matchJenis && matchStatus
  })
})

const loadOrganizations = async () => {
  isLoading.value = true
  try {
    const data = await organizationService.list()
    organizations.value = data || []
  } catch (err) {
    console.error('Failed to load organizations:', err)
  } finally {
    isLoading.value = false
  }
}

const openModal = (org?: Organization) => {
  modalError.value = null
  if (org) {
    isEditing.value = true
    form.value = {
      id: org.id,
      nama: org.nama,
      jenis: org.jenis,
      subdomain: org.subdomain,
    }
  } else {
    isEditing.value = false
    form.value = {
      id: null,
      nama: '',
      jenis: 'UKM',
      subdomain: '',
    }
  }
  showModal.value = true
}

const saveOrganization = async () => {
  isSubmitting.value = true
  modalError.value = null
  try {
    const payload: Partial<Organization> = {
      nama: form.value.nama,
      jenis: form.value.jenis,
      subdomain: form.value.subdomain,
    }
    if (isEditing.value && form.value.id) {
      await organizationService.update(form.value.id, payload)
    } else {
      await organizationService.create(payload)
    }
    showModal.value = false
    await loadOrganizations()
  } catch (err: any) {
    modalError.value = err?.message || 'Gagal menyimpan organisasi.'
  } finally {
    isSubmitting.value = false
  }
}

const openStatusModal = (org: Organization, action: 'activate' | 'deactivate') => {
  selectedOrg.value = org
  statusAction.value = action
  showStatusConfirmModal.value = true
}

const confirmStatusChange = async () => {
  if (!selectedOrg.value) return
  isSubmitting.value = true
  try {
    if (statusAction.value === 'activate') {
      await organizationService.activate(selectedOrg.value.id)
    } else {
      await organizationService.deactivate(selectedOrg.value.id)
    }
    showStatusConfirmModal.value = false
    await loadOrganizations()
  } catch (err: any) {
    alert(err?.message || 'Gagal mengubah status.')
  } finally {
    isSubmitting.value = false
  }
}

const openDetail = async (org: Organization) => {
  try {
    const data = await organizationService.getById(org.id)
    detailOrg.value = data
    showDetailModal.value = true
  } catch (err) {
    console.error('Failed to load organization detail:', err)
  }
}

onMounted(() => {
  loadOrganizations()
})
</script>
