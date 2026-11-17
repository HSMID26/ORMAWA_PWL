<template>
  <AdminLayout>
    <PageBreadcrumb pageTitle="Struktur Organisasi" />

    <div class="space-y-6">
      <!-- Header Banner & Info Cards -->
      <CommitteeHeaderBanner
        :can-manage="canManage"
        :is-loading="isLoading"
        :current-org-name="currentOrgName"
        :active-period="activePeriod"
        :committees-count="committees.length"
        :user-role="userRole"
        :format-date="formatDate"
        @create="openCreateModal"
      />

      <!-- Filter & Search Bar -->
      <CommitteeFilterBar
        v-model:search="filters.search"
        v-model:organization-period-id="filters.organization_period_id"
        v-model:status="filters.status"
        :periods-list="periodsList"
      />

      <!-- Table Section -->
      <CommitteeTable
        :committees="filteredCommittees"
        :is-loading="isLoading"
        :can-manage="canManage"
        :has-active-filter="hasActiveFilter"
        :format-date="formatDate"
        @create="openCreateModal"
        @detail="openDetailModal"
        @edit="openEditModal"
        @delete="openDeleteModal"
      />
    </div>

    <!-- ─── Modal Create / Edit Pengurus ──────────────────────────────────────── -->
    <CommitteeFormModal
      :is-open="isFormModalOpen"
      :is-editing="isEditing"
      :is-submitting="isSubmitting"
      :form-data="formData"
      :photo-preview="photoPreview"
      :periods-list="periodsList"
      :format-date="formatDate"
      @close="closeFormModal"
      @submit="submitForm"
      @photo-change="handlePhotoChange"
    />

    <!-- ─── Modal Detail Pengurus ───────────────────────────────────────────── -->
    <CommitteeDetailModal
      :is-open="isDetailModalOpen"
      :committee="selectedCommittee"
      :format-date="formatDate"
      @close="isDetailModalOpen = false"
    />

    <!-- ─── Modal Delete Confirmation ──────────────────────────────────────── -->
    <CommitteeDeleteModal
      :is-open="isDeleteModalOpen"
      :committee="selectedCommittee"
      :is-submitting="isSubmitting"
      @close="isDeleteModalOpen = false"
      @confirm="confirmDelete"
    />
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

import type { CommitteeFormData, CommitteeFilterParams } from './components/types'
import CommitteeHeaderBanner from './components/CommitteeHeaderBanner.vue'
import CommitteeFilterBar from './components/CommitteeFilterBar.vue'
import CommitteeTable from './components/CommitteeTable.vue'
import CommitteeFormModal from './components/CommitteeFormModal.vue'
import CommitteeDetailModal from './components/CommitteeDetailModal.vue'
import CommitteeDeleteModal from './components/CommitteeDeleteModal.vue'

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
const filters = ref<CommitteeFilterParams>({
  search: '',
  organization_period_id: undefined,
  status: '',
})

const hasActiveFilter = computed(() => {
  return !!(filters.value.search || filters.value.organization_period_id !== undefined || filters.value.status)
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
const formData = ref<CommitteeFormData>({
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
