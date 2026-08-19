import type { OrganizationConfig, NewsItem, EventItem, GalleryItem, DocumentItem, LeadershipMember } from '@/types/public'

export const organizationConfig: OrganizationConfig = {
  organizationName: 'HMIF ITI',
  shortName: 'HMIF',
  domain: 'hmif.iti.ac.id',
  tagline: 'Membangun ekosistem teknologi, literasi, dan kolaborasi mahasiswa.',
  logo: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=200&q=80',
  accentColor: '#465FFF',
  heroImage: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1600&q=80',
  intro:
    'HMIF ITI adalah organisasi mahasiswa yang fokus pada pengembangan kompetensi teknologi, komunikasi, dan kontribusi nyata untuk kampus.',
  address: 'Institut Teknologi Indonesia, Kota Tangerang Selatan',
  email: 'hmif@iti.ac.id',
  instagram: 'https://instagram.com/hmifiti',
  whatsapp: 'https://wa.me/6281234567890',
  footerNote: 'Platform CMS Ormawa yang dapat digunakan berbagai organisasi mahasiswa dengan konfigurasi yang fleksibel.',
  modules: {
    profile: true,
    structure: true,
    news: true,
    agenda: true,
    gallery: true,
    documents: true,
    contact: true,
    search: true,
  },
  navigation: [
    { label: 'Beranda', to: '/', enabled: true },
    { label: 'Profil', to: '/profile', enabled: true },
    { label: 'Struktur', to: '/structure', enabled: true },
    { label: 'Berita', to: '/news', enabled: true },
    { label: 'Agenda', to: '/agenda', enabled: true },
    { label: 'Galeri', to: '/gallery', enabled: true },
    { label: 'Dokumen', to: '/documents', enabled: true },
    { label: 'Kontak', to: '/contact', enabled: true },
  ],
  stats: [
    { label: 'Anggota', value: '320+' },
    { label: 'Kegiatan', value: '45+' },
    { label: 'Prestasi', value: '18' },
    { label: 'Program', value: '12' },
  ],
  partners: ['Google Developer Group', 'Techno Park', 'Kampus Merdeka'],
}

export const organizationConfigPresets: Record<string, Partial<OrganizationConfig>> = {
  hmif: {
    organizationName: 'HMIF ITI',
    shortName: 'HMIF',
    domain: 'hmif.iti.ac.id',
    tagline: 'Membangun ekosistem teknologi, literasi, dan kolaborasi mahasiswa.',
    accentColor: '#465FFF',
  },
  hmsi: {
    organizationName: 'HMSI ITI',
    shortName: 'HMSI',
    domain: 'hmsi.iti.ac.id',
    tagline: 'Membangun komunitas sistem informasi yang aktif, adaptif, dan kolaboratif.',
    accentColor: '#0F766E',
  },
  bem: {
    organizationName: 'BEM ITI',
    shortName: 'BEM',
    domain: 'bem.iti.ac.id',
    tagline: 'Menggerakkan semangat kepemimpinan dan kontribusi kampus.',
    accentColor: '#7C3AED',
  },
  ukmbasket: {
    organizationName: 'UKM Basket ITI',
    shortName: 'UKM Basket',
    domain: 'ukmbasket.iti.ac.id',
    tagline: 'Menjadi wadah olahraga basket yang sehat, kompetitif, dan menyenangkan.',
    accentColor: '#DC2626',
  },
}

export function resolveOrganizationConfig(slug = 'hmif'): OrganizationConfig {
  return {
    ...organizationConfig,
    ...organizationConfigPresets[slug],
  }
}

export const publicNews: NewsItem[] = [
  {
    slug: 'pelatihan-uiux-2026',
    title: 'Pelatihan UI/UX untuk Pengembangan Produk Digital',
    excerpt: 'Program intensif yang memperkenalkan prinsip desain dan prototyping digital.',
    content: 'HMIF ITI mengadakan pelatihan UI/UX dengan fokus pada desain pengalaman pengguna dan prototyping.',
    category: 'Workshop',
    tags: ['UI/UX', 'Workshop'],
    author: 'Alya Rahma',
    publishedAt: '2026-08-01',
    readingTime: '4 menit',
    image: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80',
    featured: true,
  },
  {
    slug: 'seminar-ai-untuk-mahasiswa',
    title: 'Seminar AI untuk Mahasiswa dan Tantangan Masa Depan',
    excerpt: 'Membahas pemanfaatan AI dalam industri dan pengembangan karier.',
    content: 'Seminar ini menghadirkan praktisi industri yang berbagi insight tentang AI.',
    category: 'Seminar',
    tags: ['AI', 'Seminar'],
    author: 'Rizki Pratama',
    publishedAt: '2026-07-22',
    readingTime: '3 menit',
    image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
  },
]

export const publicEvents: EventItem[] = [
  {
    slug: 'roadshow-kampus-merdeka',
    title: 'Roadshow Kampus Merdeka',
    description: 'Kegiatan sharing session dan networking bersama mitra industri.',
    date: '2026-08-18',
    location: 'Auditorium ITI',
    category: 'Workshop',
    image: 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
    featured: true,
  },
  {
    slug: 'hackathon-2026',
    title: 'Hackathon 2026',
    description: 'Kompetisi inovasi teknologi yang diselenggarakan selama 24 jam.',
    date: '2026-09-20',
    location: 'Lab Komputer',
    category: 'Competition',
    image: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80',
  },
]

export const publicGallery: GalleryItem[] = [
  {
    slug: 'workshop-kreatif',
    title: 'Workshop Kreatif',
    description: 'Dokumentasi workshop desain dan ideation.',
    image: 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
    album: 'Kegiatan',
  },
  {
    slug: 'sharing-session',
    title: 'Sharing Session',
    description: 'Kegiatan diskusi bersama alumni dan praktisi.',
    image: 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80',
    album: 'Kegiatan',
  },
  {
    slug: 'mading-inovasi',
    title: 'Mading Inovasi',
    description: 'Eksposisi hasil karya anggota.',
    image: 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=900&q=80',
    album: 'Program',
  },
]

export const publicDocuments: DocumentItem[] = [
  {
    slug: 'panduan-organisasi',
    title: 'Panduan Organisasi 2026',
    description: 'Dokumen utama mengenai struktur organisasi dan mekanisme kerja.',
    category: 'Panduan',
    size: '2.4 MB',
    updatedAt: '2026-07-20',
    preview: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
  },
  {
    slug: 'form-pendaftaran',
    title: 'Form Pendaftaran Anggota',
    description: 'Template pendaftaran anggota baru.',
    category: 'Form',
    size: '780 KB',
    updatedAt: '2026-07-12',
  },
]

export const publicLeadership: LeadershipMember[] = [
  {
    name: 'Raka Pradipta',
    role: 'Ketua Umum',
    division: 'Eksekutif',
    image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
  },
  {
    name: 'Nadia Salsabila',
    role: 'Wakil Ketua',
    division: 'Program',
    image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
  },
  {
    name: 'Damar Putra',
    role: 'Sekretaris',
    division: 'Administrasi',
    image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
  },
]
