import api from './api'
import type { ApiSingleResponse, OrganizationPeriod } from '@/types/api'

export interface OrganizationPeriodsResponse {
  status: string
  data: OrganizationPeriod[]
  current_period: OrganizationPeriod | null
}

export const organizationPeriodService = {
  async getAllPeriods(params?: { status?: string; organization_id?: number; search?: string }) {
    const { data } = await api.get<{ status: string; data: OrganizationPeriod[] }>('/organization-periods', { params })
    return data.data
  },

  async getPeriods(organizationId: number) {
    const { data } = await api.get<OrganizationPeriodsResponse>(`/organizations/${organizationId}/periods`)
    return data
  },

  async getPeriod(id: number) {
    const { data } = await api.get<ApiSingleResponse<OrganizationPeriod>>(`/organization-periods/${id}`)
    return data.data
  },

  async createPeriod(organizationId: number, payload: Partial<OrganizationPeriod>) {
    const { data } = await api.post<ApiSingleResponse<OrganizationPeriod>>(`/organizations/${organizationId}/periods`, payload)
    return data.data
  },

  async updatePeriod(id: number, payload: Partial<OrganizationPeriod>) {
    const { data } = await api.put<ApiSingleResponse<OrganizationPeriod>>(`/organization-periods/${id}`, payload)
    return data.data
  },

  async approvePeriod(id: number) {
    const { data } = await api.post<ApiSingleResponse<OrganizationPeriod>>(`/organization-periods/${id}/approve`)
    return data.data
  },

  async rejectPeriod(id: number, reason: string) {
    const { data } = await api.post<ApiSingleResponse<OrganizationPeriod>>(`/organization-periods/${id}/reject`, { reason })
    return data.data
  },
}
