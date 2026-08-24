import api from './api'

export interface MediaItem {
  id: number
  filename: string
  url: string
  size: number
  mime_type: string
  uploader: string
  created_at: string
}

export const mediaService = {
  async list(): Promise<MediaItem[]> {
    const { data } = await api.get<{ status: string; data: MediaItem[] }>('/media')
    return data.data
  },

  async upload(file: File): Promise<{ status: string; id: number; url: string }> {
    const formData = new FormData()
    formData.append('image', file)
    const { data } = await api.post<{ status: string; id: number; url: string }>('/upload-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return data
  },

  async remove(id: number): Promise<void> {
    await api.delete(`/media/${id}`)
  }
}
