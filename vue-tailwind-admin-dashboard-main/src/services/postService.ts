import api from './api'
import type { ApiListResponse, ApiSingleResponse, Post } from '@/types/api'

export const postService = {
  async list() {
    const { data } = await api.get<ApiListResponse<Post>>('/posts')
    return data.data
  },

  async getById(id: number) {
    const { data } = await api.get<ApiSingleResponse<Post>>(`/posts/${id}`)
    return data.data
  },

  async create(payload: Partial<Post>) {
    const { data } = await api.post<ApiSingleResponse<Post>>('/posts', payload)
    return data.data
  },

  async update(id: number, payload: Partial<Post>) {
    const { data } = await api.put<ApiSingleResponse<Post>>(`/posts/${id}`, payload)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/posts/${id}`)
  },
}
