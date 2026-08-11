import { defineStore } from 'pinia'
import { ref } from 'vue'
import { publicService } from '@/services/publicService'
import type { Organization } from '@/types/api'

export const usePublicStore = defineStore('public', () => {
  const currentTenantSlug = ref<string | null>(null)
  const currentOrganization = ref<Organization | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const setTenant = async (slug: string | null) => {
    currentTenantSlug.value = slug
    error.value = null

    if (!slug) {
      currentOrganization.value = null
      return
    }

    isLoading.value = true
    try {
      // NOTE: publicService.getOrganizationBySlug currently throws an unavailable error 
      // because the backend endpoint does not exist yet. The UI will catch this state.
      currentOrganization.value = await publicService.getOrganizationBySlug(slug)
    } catch (err) {
      currentOrganization.value = null
      error.value = err instanceof Error ? err.message : 'Backend Endpoint Unavailable'
    } finally {
      isLoading.value = false
    }
  }

  return {
    currentTenantSlug,
    currentOrganization,
    isLoading,
    error,
    setTenant
  }
})
