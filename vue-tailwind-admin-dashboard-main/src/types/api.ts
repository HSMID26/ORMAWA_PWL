export interface Organization {
  id: number
  nama: string
  jenis: string
  subdomain: string
  warna_tema?: string
  modul_aktif?: Record<string, boolean>
  created_at?: string
  updated_at?: string
}

export interface UserProfile {
  id: number
  name: string
  email: string
  role: string
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
  cover_image?: string | null
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
