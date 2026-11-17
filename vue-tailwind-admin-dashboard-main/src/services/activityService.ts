import api from './api'
import type { Activity, ApiListResponse, ApiSingleResponse } from '@/types/api'

export const activityService = {
  async list() {
    const { data } = await api.get<ApiListResponse<Activity>>('/activities')
    return data.data
  },

  async getById(id: number) {
    const { data } = await api.get<ApiSingleResponse<Activity>>(`/activities/${id}`)
    return data.data
  },

  async create(payload: Partial<Activity>) {
    const { data } = await api.post<ApiSingleResponse<Activity>>('/activities', payload)
    return data.data
  },

  async update(id: number, payload: Partial<Activity>) {
    const { data } = await api.put<ApiSingleResponse<Activity>>(`/activities/${id}`, payload)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/activities/${id}`)
  },
}
