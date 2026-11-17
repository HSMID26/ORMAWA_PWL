import api from './api'

export interface OrgDocument {
  id: number
  name: string
  judul?: string
  category: 'SK' | 'Proposal' | 'LPJ' | 'SOP' | 'Template' | 'Other'
  kategori?: string
  filename: string
  file_url: string
  url?: string
  download_url?: string
  file_size: number
  size?: number
  formatted_size?: string
  file_type: string
  mime_type?: string
  visibility: 'public' | 'internal'
  uploader_name: string
  created_at: string
}

export const documentService = {
  async list(params?: { category?: string; visibility?: string; search?: string }): Promise<OrgDocument[]> {
    const res = await api.get('/documents', { params })
    return res.data.data || []
  },

  async upload(formData: FormData): Promise<OrgDocument> {
    const res = await api.post('/documents', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return res.data.data
  },

  async remove(id: number): Promise<void> {
    await api.delete(`/documents/${id}`)
  }
}

