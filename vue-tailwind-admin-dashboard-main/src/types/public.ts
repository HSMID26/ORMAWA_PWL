export interface OrganizationModuleConfig {
  profile: boolean
  structure: boolean
  news: boolean
  agenda: boolean
  gallery: boolean
  documents: boolean
  contact: boolean
  search: boolean
}

export interface NavigationItem {
  label: string
  to: string
  enabled: boolean
}

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
  modules: OrganizationModuleConfig
  navigation: NavigationItem[]
  stats: Array<{ label: string; value: string }>
  partners: string[]
}

export interface NewsItem {
  slug: string
  title: string
  excerpt: string
  content: string
  category: string
  tags: string[]
  author: string
  publishedAt: string
  readingTime: string
  image: string
  featured?: boolean
}

export interface EventItem {
  slug: string
  title: string
  description: string
  date: string
  location: string
  category: string
  image: string
  featured?: boolean
}

export interface GalleryItem {
  slug: string
  title: string
  description: string
  image: string
  album: string
}

export interface DocumentItem {
  slug: string
  title: string
  description: string
  category: string
  size: string
  updatedAt: string
  preview?: string
}

export interface LeadershipMember {
  name: string
  role: string
  division: string
  image: string
}
