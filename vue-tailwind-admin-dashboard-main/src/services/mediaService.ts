import api from './api'

export interface MediaItem {
  id: number
  name?: string
  title?: string
  judul?: string
  caption?: string | null
  deskripsi?: string | null
  taken_at?: string | null
  alt_text?: string | null
  category?: string | null
  kategori?: string | null
  visibility?: 'public' | 'internal'
  filename: string
  url: string
  image_url?: string
  download_url?: string
  size: number
  formatted_size?: string
  mime_type: string
  is_image: boolean
  uploader: string
  created_at: string
}

export const mediaService = {
  async list(params?: { type?: 'images' | 'documents' | 'all'; category?: string; search?: string }): Promise<MediaItem[]> {
    const { data } = await api.get<{ status: string; data: MediaItem[] }>('/media', { params })
    return data.data
  },

  async upload(payload: FormData | File): Promise<any> {
    const formData = payload instanceof FormData ? payload : new FormData()
    if (payload instanceof File) {
      formData.append('image', payload)
    }
    const { data } = await api.post('/upload-image', formData, {
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

