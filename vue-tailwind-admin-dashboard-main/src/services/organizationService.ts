import api from './api'
import type { ApiListResponse, ApiSingleResponse, Organization } from '@/types/api'

export const organizationService = {
  async list() {
    const { data } = await api.get<ApiListResponse<Organization>>('/organizations')
    return data.data
  },

  async create(payload: Partial<Organization>) {
    const { data } = await api.post<ApiSingleResponse<Organization>>('/organizations', payload)
    return data.data
  },

  async update(id: number, payload: Partial<Organization>) {
    const { data } = await api.put<ApiSingleResponse<Organization>>(`/organizations/${id}`, payload)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/organizations/${id}`)
  },
}
