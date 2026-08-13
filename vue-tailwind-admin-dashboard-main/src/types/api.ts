export interface Organization {
  id: number
  nama: string
  jenis: 'HMPS' | 'UKM' | 'BEM'
  subdomain: string
  logo: string | null
  warna_tema: string
  modul_aktif: Record<string, boolean>
  status: 'active' | 'inactive'
  current_period?: OrganizationPeriod
  created_at: string
  updated_at: string
}

export interface OrganizationPeriod {
  id: number
  organization_id: number
  period_name: string
  start_date: string
  end_date: string
  status: 'pending' | 'active' | 'expired' | 'rejected'
  notes?: string
  rejection_reason?: string
  approved_by?: number
  approved_at?: string
  created_at: string
  updated_at: string
}

export interface UserProfile {
  id: number
  name: string
  email: string
  role: string
  status?: string
  organization?: Organization | null
}

export interface AuthResponse {
  message: string
  access_token: string
  token_type: string
  user: UserProfile
}

export interface ApiListResponse<T> {
  status: string
  data: T[]
  message?: string
}

export interface ApiSingleResponse<T> {
  status: string
  data: T
  message?: string
}

export interface Post {
  id: number
  judul: string
  slug?: string
  konten: string
  excerpt?: string | null
  cover_image?: string | null
  meta_title?: string | null
  meta_description?: string | null
  status: string
  user_id?: number
  organization_id?: number
  created_at?: string
  updated_at?: string
  user?: { id: number; name: string }
}

export interface Activity {
  id: number
  judul: string
  deskripsi: string
  tanggal_pelaksanaan: string
  status: string
  user_id?: number
  organization_id?: number
  created_at?: string
  updated_at?: string
  user?: { id: number; name: string }
  organization?: { id: number; nama: string }
}
