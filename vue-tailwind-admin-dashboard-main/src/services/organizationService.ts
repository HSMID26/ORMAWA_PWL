import api from './api'
import type { ApiListResponse, ApiSingleResponse, Organization } from '@/types/api'

export const organizationService = {
  async list() {
    const { data } = await api.get<ApiListResponse<Organization>>('/organizations')
    return data.data
  },

  async getById(id: number) {
    const { data } = await api.get<ApiSingleResponse<Organization>>(`/organizations/${id}`)
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

  async activate(id: number) {
    const { data } = await api.post<ApiSingleResponse<Organization>>(`/organizations/${id}/activate`)
    return data.data
  },

  async deactivate(id: number) {
    const { data } = await api.post<ApiSingleResponse<Organization>>(`/organizations/${id}/deactivate`)
    return data.data
  },

  async uploadLogo(id: number, file: File) {
    const formData = new FormData()
    formData.append('logo', file)
    const { data } = await api.post<ApiSingleResponse<Organization>>(`/organizations/${id}/upload-logo`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return data
  },

  async uploadHero(id: number, file: File) {
    const formData = new FormData()
    formData.append('hero_image', file)
    const { data } = await api.post<ApiSingleResponse<Organization>>(`/organizations/${id}/upload-hero`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return data
  },

  async removeHero(id: number) {
    const { data } = await api.delete<ApiSingleResponse<Organization>>(`/organizations/${id}/hero`)
    return data
  },
}
