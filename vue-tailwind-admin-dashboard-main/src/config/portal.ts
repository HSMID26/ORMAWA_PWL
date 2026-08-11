import type { OrganizationDirectoryItem, PortalNewsItem, PortalEventItem } from '@/types/portal'

export const portalOrganizations: OrganizationDirectoryItem[] = [
  {
    id: 'hmif',
    name: 'HMIF ITI',
    slug: 'hmif',
    category: 'HMPS',
    shortDescription: 'Komunitas mahasiswa informatika yang fokus pada teknologi, inovasi, dan pembelajaran bersama.',
    logo: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=200&q=80',
    website: '/org/hmif',
    featured: true,
  },
  {
    id: 'hmsi',
    name: 'HMSI ITI',
    slug: 'hmsi',
    category: 'HMPS',
    shortDescription: 'Organisasi mahasiswa sistem informasi yang aktif mengembangkan soft skills dan kompetensi digital.',
    logo: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=200&q=80',
    website: '/org/hmsi',
  },
  {
    id: 'bem',
    name: 'BEM ITI',
    slug: 'bem',
    category: 'BEM',
    shortDescription: 'Badan eksekutif mahasiswa yang menggerakkan program strategis dan kolaborasi kampus.',
    logo: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=200&q=80',
    website: '/org/bem',
    featured: true,
  },
  {
    id: 'ukmbasket',
    name: 'UKM Basket',
    slug: 'ukmbasket',
    category: 'UKM',
    shortDescription: 'Unit kegiatan mahasiswa basket yang mengembangkan olahraga dan semangat kebersamaan.',
    logo: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=200&q=80',
    website: '/org/ukmbasket',
  },
]

export const portalNews: PortalNewsItem[] = [
  {
    id: 'n1',
    organizationName: 'HMIF ITI',
    organizationSlug: 'hmif',
    organizationLogo: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=120&q=80',
    category: 'Workshop',
    title: 'Pelatihan UI/UX untuk anggota baru',
    publishDate: '2026-08-01',
    link: '/news/hmif-uiux',
  },
  {
    id: 'n2',
    organizationName: 'BEM ITI',
    organizationSlug: 'bem',
    organizationLogo: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=120&q=80',
    category: 'Event',
    title: 'Festival kepemimpinan mahasiswa 2026',
    publishDate: '2026-07-28',
    link: '/news/bem-festival',
  },
]

export const portalEvents: PortalEventItem[] = [
  {
    id: 'e1',
    organizationName: 'HMIF ITI',
    title: 'Roadshow Teknologi & Inovasi',
    date: '2026-08-18',
    location: 'Auditorium ITI',
    link: '/events/hmif-roadshow',
  },
  {
    id: 'e2',
    organizationName: 'UKM Basket',
    title: 'Turnamen Basket Antar Organisasi',
    date: '2026-09-03',
    location: 'Lapangan ITI',
    link: '/events/ukm-basket',
  },
]
