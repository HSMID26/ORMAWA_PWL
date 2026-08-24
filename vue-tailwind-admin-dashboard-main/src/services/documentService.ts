import api from './api'

export interface OrgDocument {
  id: number
  name: string
  category: 'SK' | 'Proposal' | 'LPJ' | 'SOP' | 'Template' | 'Other'
  filename: string
  file_url: string
  file_size: number
  file_type: string
  visibility: 'public' | 'internal'
  uploader_name: string
  created_at: string
}

// Local storage key for persistent document metadata across sessions (scoped to organization tenant)
const STORAGE_KEY = 'cms_org_documents_v2'

export const documentService = {
  async list(orgId: number): Promise<OrgDocument[]> {
    const raw = localStorage.getItem(`${STORAGE_KEY}_${orgId}`)
    if (!raw) return []
    try {
      return JSON.parse(raw)
    } catch {
      return []
    }
  },

  async create(orgId: number, doc: Omit<OrgDocument, 'id' | 'created_at'>): Promise<OrgDocument> {
    const list = await this.list(orgId)
    const newDoc: OrgDocument = {
      ...doc,
      id: Date.now(),
      created_at: new Date().toISOString(),
    }
    list.unshift(newDoc)
    localStorage.setItem(`${STORAGE_KEY}_${orgId}`, JSON.stringify(list))
    return newDoc
  },

  async remove(orgId: number, id: number): Promise<void> {
    const list = await this.list(orgId)
    const filtered = list.filter(d => d.id !== id)
    localStorage.setItem(`${STORAGE_KEY}_${orgId}`, JSON.stringify(filtered))
  }
}
