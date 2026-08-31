export interface PublicOrganization {
  id: number
  nama: string
  jenis: 'HMPS' | 'UKM' | 'BEM' | 'Senat' | 'Lainnya' | string
  subdomain: string
  logo?: string | null
  warna_tema?: string
  status?: string
  slogan?: string | null
  deskripsi?: string | null
  deskripsi_lengkap?: string | null
  hero_title?: string | null
  hero_subtitle?: string | null
  email_publik?: string | null
  instagram?: string | null
  website_eksternal?: string | null
  seo_title?: string | null
  seo_description?: string | null
  og_image?: string | null
  modules?: {
    posts?: boolean
    agenda?: boolean
    announcements?: boolean
    galeri?: boolean
    gallery?: boolean
    documents?: boolean
    structure?: boolean
    public_website_enabled?: boolean
    [key: string]: any
  }
  modul_aktif?: Record<string, any>
  current_period?: {
    id: number
    period_name: string
    start_date: string
    end_date: string
    status: string
  } | null
  stats?: {
    posts_count: number
    activities_count: number
    announcements_count: number
    media_count: number
    committees_count: number
  }
  posts_count?: number
  activities_count?: number
  committees_count?: number
}

export interface PublicArticle {
  id: number
  judul: string
  slug: string
  excerpt: string
  konten?: string
  cover_image?: string | null
  published_at: string
  author: {
    id?: number
    name: string
  }
  category?: {
    id: number
    name: string
    slug: string
  } | null
  tags: Array<{
    id: number
    name: string
    slug: string
  }>
  organization?: {
    id: number
    nama: string
    subdomain: string
    logo?: string | null
    warna_tema?: string
  } | null
  seo?: {
    meta_title: string
    meta_description: string
    og_image?: string | null
  }
  related_articles?: PublicArticle[]
}

export interface PublicAgenda {
  id: number
  judul: string
  deskripsi: string
  tanggal_pelaksanaan?: string | null
  tempat?: string
  published_at?: string
  organization?: {
    id: number
    nama: string
    subdomain: string
    logo?: string | null
    warna_tema?: string
  } | null
}

export interface PublicAnnouncement {
  id: number
  title: string
  slug: string
  content: string
  priority: 'urgent' | 'high' | 'normal' | 'low'
  effective_date?: string | null
  expires_at?: string | null
  published_at?: string
  organization?: {
    id: number
    nama: string
    subdomain: string
    logo?: string | null
    warna_tema?: string
  } | null
}

export interface PublicMedia {
  id: number
  title?: string
  name?: string
  judul?: string
  caption?: string | null
  deskripsi?: string | null
  alt_text?: string | null
  category?: string | null
  kategori?: string | null
  taken_at?: string | null
  tanggal?: string | null
  image_url: string
  url: string
  filename: string
  mime_type?: string
  size?: number
  created_at?: string
}

export interface PublicDocument {
  id: number
  name: string
  judul?: string
  filename: string
  category?: string
  kategori?: string
  mime_type: string
  file_type?: string
  size?: number
  file_size?: number
  formatted_size?: string
  url?: string
  file_url?: string
  download_url: string
  created_at?: string
}

export interface PublicCommittee {
  id: number
  name: string
  position: string
  department: string
  period?: string | null
  photo?: string | null
  photo_url?: string | null
}

export interface PublicHomeSummary {
  organizations: PublicOrganization[]
  featured_articles: PublicArticle[]
  upcoming_agenda: PublicAgenda[]
  active_announcements: PublicAnnouncement[]
  stats: {
    total_organizations: number
    total_articles: number
    total_agenda: number
  }
}

export interface PublicPaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ─── Legacy compatibility types ──────────────────────────────────────────
export interface OrganizationConfig {
  organizationName: string
  shortName: string
  domain: string
  tagline: string
  logo: string
  accentColor: string
  heroImage: string
  intro: string
  address: string
  email: string
  instagram: string
  whatsapp: string
  footerNote: string
  modules: Record<string, boolean>
  navigation: Array<{ label: string; to: string; enabled: boolean }>
  stats: Array<{ label: string; value: string }>
  partners?: Array<any>
  [key: string]: any
}

export interface NewsItem {
  id?: number
  title: string
  slug: string
  excerpt: string
  category: string
  publishedAt: string
  author: string
  image: string
  featured?: boolean
  content?: string
  tags?: string[]
  readingTime?: string
  [key: string]: any
}

export interface EventItem {
  id?: number
  title: string
  slug?: string
  date: string
  time?: string
  location: string
  category: string
  description: string
  status?: string
  image: string
  featured?: boolean
  [key: string]: any
}

export interface GalleryItem {
  id?: number
  title: string
  slug?: string
  category?: string
  date?: string
  image: string
  description: string
  album: string
  [key: string]: any
}

export interface DocumentItem {
  id?: number
  title: string
  slug?: string
  category: string
  updatedAt: string
  fileSize?: string
  downloadUrl?: string
  description: string
  size: string
  preview?: string
  [key: string]: any
}

export interface LeadershipMember {
  id?: number
  name: string
  role: string
  division: string
  period?: string
  photo?: string
  image?: string
  [key: string]: any
}
