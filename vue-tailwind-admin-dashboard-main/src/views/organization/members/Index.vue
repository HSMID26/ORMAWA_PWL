<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Top Header & Navigation Banner -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <PageBreadcrumb pageTitle="Manajemen Anggota &amp; Kepengurusan" />
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
              Manajemen Anggota &amp; Kepengurusan
            </h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Pengelolaan terpadu basis data anggota ormawa, penetapan struktur organisasi per periode, evaluasi status efektif, dan histori kepengurusan.
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div v-if="canManage" class="flex items-center gap-2 flex-wrap">
            <button
              @click="openCreateMemberModal"
              class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-600 transition"
            >
              <UserPlusIcon class="h-4 w-4" />
              <span>Tambah Anggota</span>
            </button>
            <button
              @click="openCreateCommitteeModal"
              class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-indigo-700 transition"
            >
              <ShieldCheckIcon class="h-4 w-4" />
              <span>Tempatkan Pengurus</span>
            </button>
            <button
              @click="activeTab = 'import-export'"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-2xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition"
            >
              <FileSpreadsheetIcon class="h-4 w-4" />
              <span>Import / Export</span>
            </button>
          </div>
        </div>

        <!-- Tab Navigation Bar -->
        <div class="mt-6 flex items-center gap-2 border-b border-gray-200 dark:border-gray-800 overflow-x-auto pb-px">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold border-b-2 transition whitespace-nowrap"
            :class="[
              activeTab === tab.id
                ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.badge !== undefined"
              class="ml-1 rounded-full px-2 py-0.5 text-[10px] font-bold"
              :class="activeTab === tab.id ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
            >
              {{ tab.badge }}
            </span>
          </button>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 1: RINGKASAN (HUB) -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'summary'" class="space-y-6">
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium text-gray-500">Total Basis Anggota</span>
              <div class="h-9 w-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600">
                <UsersIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalMembers }}</div>
            <p class="mt-1 text-[11px] text-gray-400">Terdaftar di database organisasi</p>
          </div>

          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium text-gray-500">Anggota Status Aktif</span>
              <div class="h-9 w-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600">
                <UserCheckIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.activeMembers }}</div>
            <p class="mt-1 text-[11px] text-gray-400">Siap diangkat menjadi pengurus</p>
          </div>

          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium text-gray-500">Pengurus Aktif Efektif</span>
              <div class="h-9 w-9 rounded-xl bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600">
                <ShieldCheckIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.effectiveCommittees }}</div>
            <p class="mt-1 text-[11px] text-gray-400">Ditampilkan pada website publik saat ini</p>
          </div>
        </div>

        <!-- Ongoing Period Status Banner -->
        <div class="rounded-2xl border p-6 bg-white dark:bg-white/[0.03] shadow-2xs space-y-4" :class="ongoingPeriod ? 'border-emerald-200 dark:border-emerald-900/40' : 'border-amber-200 dark:border-amber-900/40'">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
              <div class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0" :class="ongoingPeriod ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400'">
                <CalendarClockIcon class="h-5 w-5" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-bold text-gray-900 dark:text-white">
                    {{ ongoingPeriod ? ongoingPeriod.period_name : 'Tidak Ada Periode Aktif Berjalan' }}
                  </h3>
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="ongoingPeriod ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
                  >
                    {{ ongoingPeriod ? 'Aktif Berjalan' : 'Kedaluwarsa' }}
                  </span>
                </div>
                <p v-if="ongoingPeriod" class="text-xs text-gray-500 mt-1">
                  Masa bakti: {{ formatDate(ongoingPeriod.start_date) }} s.d. {{ formatDate(ongoingPeriod.end_date) }}
                </p>
                <p v-else class="text-xs text-gray-500 mt-1">
                  Seluruh periode sebelumnya telah kedaluwarsa. Silakan ajukan periode baru atau perpanjang periode yang ada.
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="ongoingPeriod"
                @click="viewPeriodStructure(ongoingPeriod.id)"
                class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition"
              >
                Kelola Struktur Periode Ini &rarr;
              </button>
              <button
                v-if="canManage && ongoingPeriod"
                @click="openEditPeriodModal(ongoingPeriod)"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition"
              >
                <PencilIcon class="h-3.5 w-3.5" />
                <span>Ubah Tanggal</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Hub Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div @click="activeTab = 'members'" class="cursor-pointer rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-500 transition shadow-2xs group">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 group-hover:scale-105 transition">
                <UsersIcon class="h-5 w-5" />
              </div>
              <div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Data Anggota</h4>
                <p class="text-xs text-gray-500">Kelola {{ stats.totalMembers }} data mahasiswa</p>
              </div>
            </div>
          </div>

          <div @click="activeTab = 'structure'" class="cursor-pointer rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-500 transition shadow-2xs group">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 group-hover:scale-105 transition">
                <ShieldCheckIcon class="h-5 w-5" />
              </div>
              <div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Struktur Pengurus</h4>
                <p class="text-xs text-gray-500">Susunan jabatan &amp; evaluasi status</p>
              </div>
            </div>
          </div>

          <div @click="activeTab = 'import-export'" class="cursor-pointer rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-500 transition shadow-2xs group">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 group-hover:scale-105 transition">
                <FileSpreadsheetIcon class="h-5 w-5" />
              </div>
              <div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Impor &amp; Ekspor CSV</h4>
                <p class="text-xs text-gray-500">Unggah massal dengan verifikasi 2 tahap</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 2: DATA ANGGOTA -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'members'" class="space-y-4">
        <MemberFilterBar
          v-model:search="memberFilters.search"
          v-model:status="memberFilters.status"
          v-model:jurusan="memberFilters.jurusan"
          v-model:angkatan="memberFilters.angkatan"
          @filter-change="debounceFetchMembers"
        />

        <MemberTable
          :members="membersList"
          :is-loading="isLoadingMembers"
          :pagination="memberPagination"
          :can-manage="canManage"
          @edit="openEditMemberModal"
          @delete="deleteMember"
          @change-page="changeMemberPage"
        />
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: STRUKTUR ORGANISASI -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'structure'" class="space-y-4">
        <CommitteeTable
          :committees="committeesList"
          :periods="periods"
          v-model:selected-period-id="selectedStructurePeriodId"
          :selected-period-obj="selectedPeriodObj"
          :is-loading="isLoadingCommittees"
          :can-manage="canManage"
          :format-date="formatDate"
          @period-change="fetchCommittees"
          @quick-status="openQuickStatusModal"
          @edit="openEditCommitteeModal"
          @delete="deleteCommittee"
        />
      </div>

      <!-- ========================================== -->
      <!-- TAB 4: PERIODE KEPENGURUSAN -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'periods'" class="space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs flex items-center justify-between">
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white">Daftar Periode Kepengurusan</h3>
            <p class="text-xs text-gray-500">Status periode dihitung secara otomatis setiap hari berdasarkan rentang tanggal kalender.</p>
          </div>
          <router-link
            to="/organization/period"
            class="rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-600 transition"
          >
            Buka Modul Pengajuan Periode &rarr;
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="p in periods"
            :key="p.id"
            class="rounded-2xl border p-5 bg-white dark:bg-white/[0.03] space-y-3 shadow-2xs"
            :class="[
              p.calculated_status === 'ongoing' ? 'border-emerald-300 ring-2 ring-emerald-500/10' : 'border-gray-200 dark:border-gray-800'
            ]"
          >
            <div class="flex items-center justify-between">
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
                :class="[
                  p.calculated_status === 'ongoing' ? 'bg-emerald-100 text-emerald-800' :
                  p.calculated_status === 'expired' ? 'bg-gray-100 text-gray-600' :
                  'bg-blue-100 text-blue-800'
                ]"
              >
                {{ p.calculated_status === 'ongoing' ? 'Sedang Berjalan' : (p.calculated_status === 'expired' ? 'Kedaluwarsa' : 'Mendatang') }}
              </span>
              <span class="text-xs text-gray-400">ID: #{{ p.id }}</span>
            </div>

            <h4 class="text-base font-bold text-gray-900 dark:text-white">{{ p.period_name }}</h4>
            <div class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
              <div>Mulai: <span class="font-medium">{{ formatDate(p.start_date) }}</span></div>
              <div>Berakhir: <span class="font-medium">{{ formatDate(p.end_date) }}</span></div>
            </div>

            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
              <button
                @click="viewPeriodStructure(p.id)"
                class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400"
              >
                Lihat Susunan Pengurus &rarr;
              </button>
              <button
                v-if="canManage"
                @click="openEditPeriodModal(p)"
                class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400 transition"
              >
                <PencilIcon class="h-3.5 w-3.5" />
                <span>Edit Periode</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 5: RIWAYAT KEPENGURUSAN (ARSIP) -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'history'" class="space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs">
          <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <ArchiveIcon class="h-5 w-5 text-brand-500" />
            Arsip Kepengurusan Lampau
          </h3>
          <p class="mt-1 text-xs text-gray-500">
            Riwayat susunan pengurus dari tahun-tahun sebelumnya tetap tersimpan utuh dan tidak terhapus saat terjadi pergantian periode.
          </p>
        </div>

        <div v-if="expiredPeriods.length === 0" class="py-12 text-center text-gray-500 border border-dashed rounded-2xl bg-white dark:bg-white/[0.02]">
          Belum ada arsip periode terdahulu.
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="ep in expiredPeriods"
            :key="ep.id"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-2xs"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white">{{ ep.period_name }}</h4>
                <p class="text-xs text-gray-500">Masa jabatan: {{ formatDate(ep.start_date) }} s.d. {{ formatDate(ep.end_date) }}</p>
              </div>
              <button
                @click="viewPeriodStructure(ep.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
              >
                Buka Struktur Periode Ini
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 6: IMPORT & EXPORT -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'import-export'">
        <MemberImportSection
          :selected-file="selectedFile"
          :is-importing="isImporting"
          :import-preview-result="importPreviewResult"
          @export-csv="exportMembersCsv"
          @download-template="downloadTemplateCsv"
          @file-selected="onFileSelected"
          @run-preview="runImportPreview"
          @run-commit="runImportCommit"
        />
      </div>
    </div>

    <!-- ========================================== -->
    <!-- MODALS -->
    <!-- ========================================== -->
    <MemberFormModal
      :is-open="isMemberModalOpen"
      :is-editing="!!editingMemberId"
      :form="memberForm"
      :is-submitting="isSubmitting"
      :linkable-users="linkableUsersList"
      @close="isMemberModalOpen = false"
      @submit="saveMember"
    />

    <CommitteeFormModal
      :is-open="isCommitteeModalOpen"
      :is-editing="!!editingCommitteeId"
      :form="committeeForm"
      :is-submitting="isSubmitting"
      :periods="periods"
      :linkable-members="linkableMembersList"
      :linkable-users="linkableUsersList"
      @close="isCommitteeModalOpen = false"
      @submit="saveCommittee"
      @member-selected="onCommitteeMemberSelected"
      @photo-selected="onCommitteePhotoSelected"
    />

    <QuickStatusModal
      :is-open="isQuickStatusModalOpen"
      :committee="quickStatusCommittee"
      :form="quickStatusForm"
      :is-submitting="isSubmitting"
      @close="isQuickStatusModalOpen = false"
      @submit="submitQuickStatus"
    />

    <EditPeriodModal
      :is-open="isEditPeriodModalOpen"
      :period="editingPeriod"
      :form="periodForm"
      :is-submitting="isSubmitting"
      @close="isEditPeriodModalOpen = false"
      @submit="savePeriod"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { memberService, type MemberFilterParams, type MemberImportResult } from '@/services/memberService'
import { committeeService, type LinkableMember, type LinkableUser } from '@/services/committeeService'
import { organizationPeriodService } from '@/services/organizationPeriodService'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import type { Member, Committee, OrganizationPeriod } from '@/types/api'

// Subcomponents
import MemberFilterBar from './components/MemberFilterBar.vue'
import MemberTable from './components/MemberTable.vue'
import CommitteeTable from './components/CommitteeTable.vue'
import MemberImportSection from './components/MemberImportSection.vue'
import MemberFormModal from './components/MemberFormModal.vue'
import CommitteeFormModal from './components/CommitteeFormModal.vue'
import QuickStatusModal from './components/QuickStatusModal.vue'
import EditPeriodModal from './components/EditPeriodModal.vue'

import {
  UsersIcon,
  UserCheckIcon,
  UserPlusIcon,
  ShieldCheckIcon,
  CalendarClockIcon,
  PencilIcon,
  LayersIcon,
  ArchiveIcon,
  FileSpreadsheetIcon,
} from 'lucide-vue-next'

const route = useRoute()
const authStore = useAuthStore()
const toast = useToastStore()

const canManage = computed(() => {
  const role = authStore.role
  return role === 'Super Admin' || role === 'Admin Organisasi' || authStore.hasPermission('structure.manage')
})

// Tab Navigation Setup
const activeTab = ref('summary')

const tabs = computed(() => [
  { id: 'summary', label: 'Ringkasan', icon: LayersIcon },
  { id: 'members', label: 'Data Anggota', icon: UsersIcon, badge: stats.value.totalMembers },
  { id: 'structure', label: 'Struktur Organisasi', icon: ShieldCheckIcon, badge: stats.value.effectiveCommittees },
  { id: 'periods', label: 'Periode Kepengurusan', icon: CalendarClockIcon },
  { id: 'history', label: 'Riwayat Kepengurusan', icon: ArchiveIcon },
  { id: 'import-export', label: 'Import & Export', icon: FileSpreadsheetIcon },
])

// Stats
const stats = ref({
  totalMembers: 0,
  activeMembers: 0,
  effectiveCommittees: 0,
})

// Periods Data
const periods = ref<OrganizationPeriod[]>([])
const ongoingPeriod = computed(() => periods.value.find(p => p.calculated_status === 'ongoing' || p.is_ongoing))
const expiredPeriods = computed(() => periods.value.filter(p => p.calculated_status === 'expired'))

// Members Tab State
const isLoadingMembers = ref(false)
const membersList = ref<Member[]>([])
const memberFilters = reactive<MemberFilterParams>({
  search: '',
  status: '',
  jurusan: '',
  angkatan: '',
})
const memberPagination = reactive({
  page: 1,
  last_page: 1,
  total: 0,
})

// Committees Tab State
const isLoadingCommittees = ref(false)
const committeesList = ref<Committee[]>([])
const selectedStructurePeriodId = ref<number | null>(null)
const selectedPeriodObj = computed(() => periods.value.find(p => p.id === selectedStructurePeriodId.value))

// Linkable Helpers
const linkableMembersList = ref<LinkableMember[]>([])
const linkableUsersList = ref<LinkableUser[]>([])

// Modals State
const isMemberModalOpen = ref(false)
const editingMemberId = ref<number | null>(null)
const memberForm = reactive({
  nama: '',
  nim: '',
  email: '',
  telepon: '',
  jurusan: '',
  angkatan: '',
  status: 'active',
  user_id: null as number | null,
  catatan: '',
})

const isCommitteeModalOpen = ref(false)
const editingCommitteeId = ref<number | null>(null)
const committeeForm = reactive({
  name: '',
  position: '',
  department: '',
  organization_period_id: null as number | null,
  member_id: null as number | null,
  user_id: null as number | null,
  start_date: '',
  end_date: '',
  status: 'active',
  status_mode: 'auto' as 'auto' | 'manual',
  status_reason: '',
  notes: '',
  photo: null as File | null,
})

// Quick Manual Status Toggle Modal
const isQuickStatusModalOpen = ref(false)
const quickStatusCommittee = ref<Committee | null>(null)
const quickStatusForm = reactive({
  status_mode: 'auto' as 'auto' | 'manual',
  status: 'active' as 'active' | 'inactive',
  status_reason: '',
})

// Edit Period Modal
const isEditPeriodModalOpen = ref(false)
const editingPeriod = ref<OrganizationPeriod | null>(null)
const periodForm = reactive({
  period_name: '',
  start_date: '',
  end_date: '',
  notes: '',
})

const isSubmitting = ref(false)

// Import/Export State
const selectedFile = ref<File | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)
const isImporting = ref(false)
const importPreviewResult = ref<MemberImportResult | null>(null)

// Format date helper
const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return dateStr
  }
}

// Fetch Periods
const fetchPeriods = async () => {
  try {
    const orgId = (authStore.user as any)?.organization_id || authStore.user?.organization?.id || route.query.organization_id || 1
    const res = await organizationPeriodService.getPeriods(orgId)
    periods.value = res.data || []
    if (!selectedStructurePeriodId.value) {
      if (ongoingPeriod.value) {
        selectedStructurePeriodId.value = ongoingPeriod.value.id
      } else if (periods.value.length > 0) {
        selectedStructurePeriodId.value = periods.value[0].id
      }
    }
  } catch (err: any) {
    console.error(err)
  }
}

// Fetch Members
let debounceTimer: any = null
const debounceFetchMembers = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchMembers()
  }, 350)
}

const fetchMembers = async () => {
  isLoadingMembers.value = true
  try {
    const res = await memberService.list({
      ...memberFilters,
      page: memberPagination.page,
    })
    membersList.value = res.data || []
    if (res.meta) {
      memberPagination.page = res.meta.current_page
      memberPagination.last_page = res.meta.last_page
      memberPagination.total = res.meta.total
      stats.value.totalMembers = res.meta.total
    }
    // Calculate active count
    stats.value.activeMembers = membersList.value.filter(m => m.status === 'active').length
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memuat daftar anggota.')
  } finally {
    isLoadingMembers.value = false
  }
}

const changeMemberPage = (newPage: number) => {
  if (newPage >= 1 && newPage <= memberPagination.last_page) {
    memberPagination.page = newPage
    fetchMembers()
  }
}

// Fetch Committees for Selected Period
const fetchCommittees = async () => {
  isLoadingCommittees.value = true
  try {
    const data = await committeeService.list({
      organization_period_id: selectedStructurePeriodId.value ?? undefined,
    })
    committeesList.value = data || []
    stats.value.effectiveCommittees = committeesList.value.filter(c => c.is_active_effective).length
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memuat susunan pengurus.')
  } finally {
    isLoadingCommittees.value = false
  }
}

const viewPeriodStructure = (periodId: number) => {
  selectedStructurePeriodId.value = periodId
  activeTab.value = 'structure'
  fetchCommittees()
}

// Linkables
const loadLinkables = async () => {
  try {
    const [mems, usrs] = await Promise.all([
      committeeService.getLinkableMembers({
        organization_period_id: selectedStructurePeriodId.value ?? undefined,
      }),
      committeeService.getLinkableUsers({
        organization_period_id: selectedStructurePeriodId.value ?? undefined,
      })
    ])
    linkableMembersList.value = mems || []
    linkableUsersList.value = usrs || []
  } catch (err) {
    console.error(err)
  }
}

// Member CRUD
const openCreateMemberModal = () => {
  editingMemberId.value = null
  Object.assign(memberForm, {
    nama: '',
    nim: '',
    email: '',
    telepon: '',
    jurusan: '',
    angkatan: '',
    status: 'active',
    user_id: null,
    catatan: '',
  })
  loadLinkables()
  isMemberModalOpen.value = true
}

const openEditMemberModal = (member: Member) => {
  editingMemberId.value = member.id
  Object.assign(memberForm, {
    nama: member.nama,
    nim: member.nim,
    email: member.email || '',
    telepon: member.telepon || '',
    jurusan: member.jurusan || '',
    angkatan: member.angkatan || '',
    status: member.status || 'active',
    user_id: member.user_id || null,
    catatan: member.catatan || '',
  })
  loadLinkables()
  isMemberModalOpen.value = true
}

const saveMember = async () => {
  isSubmitting.value = true
  try {
    if (editingMemberId.value) {
      await memberService.update(editingMemberId.value, memberForm)
      toast.success('Data anggota berhasil diperbarui!')
    } else {
      await memberService.create(memberForm)
      toast.success('Anggota baru berhasil ditambahkan!')
    }
    isMemberModalOpen.value = false
    fetchMembers()
    loadLinkables()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data anggota.')
  } finally {
    isSubmitting.value = false
  }
}

const deleteMember = async (member: Member) => {
  if (!confirm(`Hapus data anggota ${member.nama} (${member.nim})?`)) return
  try {
    await memberService.delete(member.id)
    toast.success('Anggota berhasil dihapus!')
    fetchMembers()
    loadLinkables()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal menghapus anggota.')
  }
}

// Committee CRUD
const openCreateCommitteeModal = () => {
  editingCommitteeId.value = null
  Object.assign(committeeForm, {
    name: '',
    position: '',
    department: '',
    organization_period_id: selectedStructurePeriodId.value || ongoingPeriod.value?.id || null,
    member_id: null,
    user_id: null,
    start_date: selectedPeriodObj.value?.start_date || '',
    end_date: selectedPeriodObj.value?.end_date || '',
    status: 'active',
    status_mode: 'auto',
    status_reason: '',
    notes: '',
    photo: null,
  })
  loadLinkables()
  isCommitteeModalOpen.value = true
}

const onCommitteeMemberSelected = () => {
  if (committeeForm.member_id) {
    const mem = linkableMembersList.value.find(m => m.id === committeeForm.member_id)
    if (mem) {
      committeeForm.name = mem.nama
      if (mem.user_id) committeeForm.user_id = mem.user_id
    }
  }
}

const openEditCommitteeModal = (c: Committee) => {
  editingCommitteeId.value = c.id
  Object.assign(committeeForm, {
    name: c.name,
    position: c.position,
    department: c.department || '',
    organization_period_id: c.organization_period_id || null,
    member_id: c.member_id || null,
    user_id: c.user_id || null,
    start_date: c.start_date || '',
    end_date: c.end_date || '',
    status: c.status_mode === 'manual' ? (c.status || 'active') : 'active',
    status_mode: c.status_mode || 'auto',
    status_reason: c.status_reason || '',
    notes: c.notes || '',
    photo: null,
  })
  loadLinkables()
  isCommitteeModalOpen.value = true
}

const onCommitteePhotoSelected = (e: any) => {
  if (e.target.files && e.target.files[0]) {
    committeeForm.photo = e.target.files[0]
  }
}

const saveCommittee = async () => {
  isSubmitting.value = true
  try {
    if (committeeForm.status_mode === 'auto') {
      committeeForm.status = 'active'
    }

    const formData = new FormData()
    formData.append('name', committeeForm.name)
    formData.append('position', committeeForm.position)
    if (committeeForm.department) formData.append('department', committeeForm.department)
    if (committeeForm.organization_period_id) formData.append('organization_period_id', String(committeeForm.organization_period_id))
    if (committeeForm.member_id) formData.append('member_id', String(committeeForm.member_id))
    if (committeeForm.user_id) formData.append('user_id', String(committeeForm.user_id))
    if (committeeForm.start_date) formData.append('start_date', committeeForm.start_date)
    if (committeeForm.end_date) formData.append('end_date', committeeForm.end_date)
    formData.append('status', committeeForm.status)
    formData.append('status_mode', committeeForm.status_mode)
    if (committeeForm.status_reason) formData.append('status_reason', committeeForm.status_reason)
    if (committeeForm.notes) formData.append('notes', committeeForm.notes)
    if (committeeForm.photo) formData.append('photo', committeeForm.photo)

    if (editingCommitteeId.value) {
      await committeeService.update(editingCommitteeId.value, formData)
      toast.success('Data pengurus berhasil diperbarui!')
    } else {
      await committeeService.create(formData)
      toast.success('Pengurus baru berhasil ditambahkan!')
    }
    isCommitteeModalOpen.value = false
    fetchCommittees()
    loadLinkables()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan pengurus.')
  } finally {
    isSubmitting.value = false
  }
}

const deleteCommittee = async (c: Committee) => {
  if (!confirm(`Hapus ${c.name} (${c.position}) dari struktur kepengurusan?`)) return
  try {
    await committeeService.delete(c.id)
    toast.success('Pengurus berhasil dihapus!')
    fetchCommittees()
    loadLinkables()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal menghapus pengurus.')
  }
}

// Quick Status Actions
const openQuickStatusModal = (c: Committee) => {
  quickStatusCommittee.value = c
  quickStatusForm.status_mode = c.status_mode || 'auto'
  quickStatusForm.status = c.status || 'active'
  quickStatusForm.status_reason = c.status_reason || ''
  isQuickStatusModalOpen.value = true
}

const submitQuickStatus = async () => {
  if (!quickStatusCommittee.value) return
  if (quickStatusForm.status_mode === 'manual' && !quickStatusForm.status_reason.trim()) {
    toast.error('Alasan perubahan status wajib diisi untuk mode manual.')
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      status_mode: quickStatusForm.status_mode,
      status: quickStatusForm.status,
      status_reason: quickStatusForm.status_mode === 'manual' ? quickStatusForm.status_reason : null,
    }
    await committeeService.update(quickStatusCommittee.value.id, payload)
    toast.success('Status pengurus berhasil diperbarui!')
    isQuickStatusModalOpen.value = false
    await fetchCommittees()
    await loadLinkables()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal mengubah status pengurus.')
  } finally {
    isSubmitting.value = false
  }
}

// Edit Period Actions
const openEditPeriodModal = (p: OrganizationPeriod) => {
  editingPeriod.value = p
  periodForm.period_name = p.period_name
  periodForm.start_date = p.start_date ? p.start_date.substring(0, 10) : ''
  periodForm.end_date = p.end_date ? p.end_date.substring(0, 10) : ''
  periodForm.notes = p.notes || ''
  isEditPeriodModalOpen.value = true
}

const savePeriod = async () => {
  if (!editingPeriod.value) return
  if (periodForm.end_date < periodForm.start_date) {
    toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.')
    return
  }

  isSubmitting.value = true
  try {
    await organizationPeriodService.updatePeriod(editingPeriod.value.id, {
      period_name: periodForm.period_name,
      start_date: periodForm.start_date,
      end_date: periodForm.end_date,
      notes: periodForm.notes,
    })
    toast.success('Periode kepengurusan berhasil diperbarui! Status efektivitas pengurus telah disinkronkan otomatis.')
    isEditPeriodModalOpen.value = false
    await fetchPeriods()
    await fetchCommittees()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memperbarui periode.')
  } finally {
    isSubmitting.value = false
  }
}

// Import & Export
const downloadTemplateCsv = async () => {
  try {
    await memberService.downloadTemplate()
    toast.success('Template CSV berhasil diunduh.')
  } catch (err) {
    toast.error('Gagal mengunduh template CSV.')
  }
}

const exportMembersCsv = async () => {
  try {
    await memberService.export(memberFilters)
    toast.success('Data anggota berhasil diekspor ke CSV.')
  } catch (err) {
    toast.error('Gagal mengekspor data anggota.')
  }
}

const onFileSelected = (e: any) => {
  if (e.target.files && e.target.files[0]) {
    selectedFile.value = e.target.files[0]
    importPreviewResult.value = null
  }
}

const runImportPreview = async () => {
  if (!selectedFile.value) return
  isImporting.value = true
  try {
    const res = await memberService.import(selectedFile.value, 'preview')
    importPreviewResult.value = res
    if (res.errors.length > 0) {
      toast.warning(`Terdapat ${res.errors.length} baris tidak valid pada file CSV.`)
    } else {
      toast.success(`Validasi berhasil! ${res.success_count} data siap dimasukkan.`)
    }
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memvalidasi berkas CSV.')
  } finally {
    isImporting.value = false
  }
}

const runImportCommit = async () => {
  if (!selectedFile.value) return
  isImporting.value = true
  try {
    const res = await memberService.import(selectedFile.value, 'commit')
    toast.success(`Berhasil mengimpor ${res.success_count} anggota ke database!`)
    selectedFile.value = null
    importPreviewResult.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    fetchMembers()
    activeTab.value = 'members'
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memproses impor ke database.')
  } finally {
    isImporting.value = false
  }
}

watch(() => route.query.tab, (newTab) => {
  if (newTab && typeof newTab === 'string') {
    activeTab.value = newTab
  }
})

onMounted(async () => {
  if (route.query.tab && typeof route.query.tab === 'string') {
    activeTab.value = route.query.tab
  }
  await fetchPeriods()
  await Promise.all([
    fetchMembers(),
    fetchCommittees(),
  ])
})
</script>
