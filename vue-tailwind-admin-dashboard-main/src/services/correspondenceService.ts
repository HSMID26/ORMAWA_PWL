import api from './api'

export type CorrespondenceType = 'incoming' | 'outgoing'
export type CorrespondenceStatus = 'draft' | 'submitted' | 'approved' | 'rejected' | 'revision_requested'
export interface CorrespondenceOrganization {
  id: number
  nama: string
}

export interface Correspondence {
  id: number
  reference_number: string
  type: CorrespondenceType
  sender: string
  recipient: string
  subject: string
  letter_date: string
  notes: string | null
  status: CorrespondenceStatus
  review_note: string | null
  attachment_name: string | null
  created_by: number | null
  organization?: CorrespondenceOrganization | null
  creator?: { id: number; name: string } | null
  reviewer?: { id: number; name: string } | null
}

export interface CorrespondenceInput {
  organization_id?: number
  reference_number: string
  type: CorrespondenceType
  sender: string
  recipient: string
  subject: string
  letter_date: string
  notes: string
  attachment: File | null
}

export interface CorrespondenceReviewInput {
  action: 'approve' | 'reject' | 'request_revision'
  review_note: string
}

interface CorrespondencePage {
  status: string
  data: Correspondence[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

const toFormData = (input: CorrespondenceInput) => {
  const formData = new FormData()
  if (input.organization_id) formData.append('organization_id', String(input.organization_id))
  formData.append('reference_number', input.reference_number)
  formData.append('type', input.type)
  formData.append('sender', input.sender)
  formData.append('recipient', input.recipient)
  formData.append('subject', input.subject)
  formData.append('letter_date', input.letter_date)
  formData.append('notes', input.notes)
  if (input.attachment) formData.append('attachment', input.attachment)
  return formData
}

export const correspondenceService = {
  async list(page = 1, filters: { type?: string; status?: string } = {}) {
    const params: { page: number; per_page: number; type?: string; status?: string } = {
      page,
      per_page: 20,
    }
    if (filters.type) params.type = filters.type
    if (filters.status) params.status = filters.status

    const { data } = await api.get<CorrespondencePage>('/correspondences', {
      params,
    })
    return data
  },

  async create(input: CorrespondenceInput) {
    const { data } = await api.post<{ data: Correspondence }>('/correspondences', toFormData(input), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data.data
  },

  async update(id: number, input: CorrespondenceInput) {
    const formData = toFormData(input)
    formData.append('_method', 'PUT')
    const { data } = await api.post<{ data: Correspondence }>(`/correspondences/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data.data
  },

  async submit(id: number) {
    const { data } = await api.post<{ data: Correspondence }>(`/correspondences/${id}/submit`)
    return data.data
  },

  async review(id: number, input: CorrespondenceReviewInput) {
    const { data } = await api.post<{ data: Correspondence }>(`/correspondences/${id}/review`, input)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/correspondences/${id}`)
  },

  async download(id: number, filename: string) {
    const response = await api.get(`/correspondences/${id}/attachment`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    link.remove()
    window.setTimeout(() => window.URL.revokeObjectURL(url), 1000)
  },
}
