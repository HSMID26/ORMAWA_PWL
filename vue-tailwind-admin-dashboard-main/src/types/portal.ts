export interface OrganizationDirectoryItem {
  id: string
  name: string
  slug: string
  category: 'HMPS' | 'UKM' | 'BEM' | 'Faculty' | 'Other'
  shortDescription: string
  logo: string
  website: string
  featured?: boolean
}

export interface PortalNewsItem {
  id: string
  organizationName: string
  organizationSlug: string
  organizationLogo: string
  category: string
  title: string
  publishDate: string
  link: string
}

export interface PortalEventItem {
  id: string
  organizationName: string
  title: string
  date: string
  location: string
  link: string
}
