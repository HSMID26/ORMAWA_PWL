import api from './api'
import type { ApiListResponse, ApiSingleResponse, Category, Tag } from '@/types/api'

export const taxonomyService = {
  async getCategories() {
    const { data } = await api.get<ApiListResponse<Category>>('/categories')
    return data.data
  },

  async createCategory(name: string) {
    const { data } = await api.post<ApiSingleResponse<Category>>('/categories', { name })
    return data.data
  },

  async getTags() {
    const { data } = await api.get<ApiListResponse<Tag>>('/tags')
    return data.data
  },

  async createTag(name: string) {
    const { data } = await api.post<ApiSingleResponse<Tag>>('/tags', { name })
    return data.data
  },
}
