import api from './api'

export interface ArticleReference {
  id: number
  judul: string
  slug: string
  status: string
}

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
  is_published_to_gallery: boolean
  filename: string
  url: string
  image_url?: string
  download_url?: string
  size: number
  formatted_size?: string
  mime_type: string
  is_image: boolean
  is_video?: boolean
  uploader: string
  used_in_articles?: ArticleReference[]
  used_count?: number
  created_at: string
}

export const mediaService = {
  async list(params?: {
    type?: 'images' | 'image' | 'videos' | 'video' | 'documents' | 'document' | 'all'
    category?: string
    search?: string
    scope?: 'gallery' | 'media_library'
    published_only?: boolean
    published_to_gallery?: boolean | string
  }): Promise<MediaItem[]> {
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

  async update(id: number, payload: Partial<MediaItem>): Promise<MediaItem> {
    const { data } = await api.put<{ status: string; data: MediaItem }>(`/media/${id}`, payload)
    return data.data
  },

  async toggleGallery(id: number, is_published_to_gallery?: boolean): Promise<MediaItem> {
    const { data } = await api.patch<{ status: string; data: MediaItem }>(`/media/${id}/toggle-gallery`, {
      is_published_to_gallery,
    })
    return data.data
  },

  async remove(id: number): Promise<void> {
    await api.delete(`/media/${id}`)
  }
}
