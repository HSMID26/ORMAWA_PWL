import api from './api'
import type { ApiListResponse, Post, Activity, Announcement, Media } from '@/types/api'

export interface ContentFilterParams {
  organization_id?: number
  status?: string
  search?: string
}

export const contentService = {
  async getGlobalPosts(params?: ContentFilterParams) {
    const { data } = await api.get<ApiListResponse<Post>>('/posts', { params })
    return data.data
  },

  async getGlobalActivities(params?: ContentFilterParams) {
    const { data } = await api.get<ApiListResponse<Activity>>('/activities', { params })
    return data.data
  },

  async getGlobalAnnouncements(params?: ContentFilterParams) {
    const { data } = await api.get<ApiListResponse<Announcement>>('/announcements', { params })
    return data.data
  },

  async getGlobalMedia(params?: ContentFilterParams) {
    const { data } = await api.get<ApiListResponse<Media>>('/media', { params })
    return data.data
  },
}
