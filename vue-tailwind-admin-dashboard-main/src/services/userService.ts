import api from './api'
import type { ApiListResponse, ApiSingleResponse, UserProfile } from '@/types/api'

export const userService = {
  async list() {
    const { data } = await api.get<ApiListResponse<UserProfile>>('/users')
    return data.data
  },

  async create(payload: Record<string, unknown>) {
    const { data } = await api.post<ApiSingleResponse<UserProfile>>('/users', payload)
    return data.data
  },

  async update(id: number, payload: Record<string, unknown>) {
    const { data } = await api.put<ApiSingleResponse<UserProfile>>(`/users/${id}`, payload)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/users/${id}`)
  },
}
