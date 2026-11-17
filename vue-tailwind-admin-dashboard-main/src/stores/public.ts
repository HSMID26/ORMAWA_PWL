import { defineStore } from 'pinia'
import { ref } from 'vue'
import { publicService } from '@/services/publicService'
import type { PublicOrganization } from '@/types/public'

export const usePublicStore = defineStore('public', () => {
  const currentTenantSlug = ref<string | null>(null)
  const currentOrganization = ref<PublicOrganization | null>(null)
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
