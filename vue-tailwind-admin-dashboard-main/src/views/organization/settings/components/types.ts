export interface OrganizationSettingsForm {
  id: number
  nama: string
  subdomain: string
  jenis: string
  warna_tema: string
  logo: string | null
  hero_image: string | null
  slogan: string
  deskripsi: string
  deskripsi_lengkap: string
  hero_title: string
  hero_subtitle: string
  email: string
  telepon: string
  alamat: string
  copyright: string
  footer_description: string
  media_sosial: {
    instagram: string
    facebook: string
    youtube: string
    tiktok: string
    linkedin: string
    website: string
    whatsapp: string
  }
  label_menu: {
    posts: string
    agenda: string
    announcements: string
    galeri: string
    documents: string
    structure: string
  }
  seo_title: string
  seo_description: string
  ga_tracking_id: string
  public_website_enabled: boolean
  modul_aktif: {
    posts: boolean
    agenda: boolean
    announcements: boolean
    galeri: boolean
    documents: boolean
    structure: boolean
  }
}
