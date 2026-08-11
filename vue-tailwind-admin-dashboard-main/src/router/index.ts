import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior() {
    return { top: 0 }
  },
  routes: [
    // ─── Public Website ─────────────────────────────────────────
    {
      path: '/',
      name: 'portal-home',
      component: () => import('@/views/portal/PortalHomePage.vue'),
      alias: ['/portal'],
      meta: { title: 'Portal Ormawa', public: true },
    },
    {
      path: '/org/:slug',
      name: 'organization-landing',
      component: () => import('@/views/public/HomePage.vue'),
      meta: { title: 'Website Organisasi', public: true },
    },
    {
      path: '/org/:slug/profile',
      name: 'organization-profile',
      component: () => import('@/views/public/ProfilePage.vue'),
      meta: { title: 'Profil', public: true },
    },
    {
      path: '/org/:slug/structure',
      name: 'organization-structure',
      component: () => import('@/views/public/StructurePage.vue'),
      meta: { title: 'Struktur', public: true },
    },
    {
      path: '/news',
      name: 'news-list',
      component: () => import('@/views/public/NewsListPage.vue'),
      meta: { title: 'Berita', public: true },
    },
    {
      path: '/org/:slug/news',
      name: 'organization-news-list',
      component: () => import('@/views/public/NewsListPage.vue'),
      meta: { title: 'Berita Organisasi', public: true },
    },
    {
      path: '/news/:slug',
      name: 'news-detail',
      component: () => import('@/views/public/NewsDetailPage.vue'),
      meta: { title: 'Detail Berita', public: true },
    },
    {
      path: '/org/:slug/news/:slug',
      name: 'organization-news-detail',
      component: () => import('@/views/public/NewsDetailPage.vue'),
      meta: { title: 'Detail Berita Organisasi', public: true },
    },
    {
      path: '/agenda',
      name: 'agenda',
      component: () => import('@/views/public/AgendaPage.vue'),
      meta: { title: 'Agenda', public: true },
    },
    {
      path: '/org/:slug/agenda',
      name: 'organization-agenda',
      component: () => import('@/views/public/AgendaPage.vue'),
      meta: { title: 'Agenda Organisasi', public: true },
    },
    {
      path: '/gallery',
      name: 'gallery',
      component: () => import('@/views/public/GalleryPage.vue'),
      meta: { title: 'Galeri', public: true },
    },
    {
      path: '/org/:slug/gallery',
      name: 'organization-gallery',
      component: () => import('@/views/public/GalleryPage.vue'),
      meta: { title: 'Galeri Organisasi', public: true },
    },
    {
      path: '/documents',
      name: 'documents',
      component: () => import('@/views/public/DocumentsPage.vue'),
      meta: { title: 'Dokumen', public: true },
    },
    {
      path: '/org/:slug/documents',
      name: 'organization-documents',
      component: () => import('@/views/public/DocumentsPage.vue'),
      meta: { title: 'Dokumen Organisasi', public: true },
    },
    {
      path: '/contact',
      name: 'contact',
      component: () => import('@/views/public/ContactPage.vue'),
      meta: { title: 'Kontak', public: true },
    },
    {
      path: '/org/:slug/contact',
      name: 'organization-contact',
      component: () => import('@/views/public/ContactPage.vue'),
      meta: { title: 'Kontak Organisasi', public: true },
    },
    {
      path: '/search',
      name: 'search',
      component: () => import('@/views/public/SearchPage.vue'),
      meta: { title: 'Pencarian', public: true },
    },
    {
      path: '/org/:slug/search',
      name: 'organization-search',
      component: () => import('@/views/public/SearchPage.vue'),
      meta: { title: 'Pencarian Organisasi', public: true },
    },

    // ─── Main Portal ────────────────────────────────────────────
    {
      path: '/portal/organizations',
      name: 'portal-organizations',
      component: () => import('@/views/portal/OrganizationsPage.vue'),
      meta: { title: 'Organisasi', public: true },
    },
    {
      path: '/portal/news',
      name: 'portal-news',
      component: () => import('@/views/portal/NewsPage.vue'),
      meta: { title: 'Berita Portal', public: true },
    },
    {
      path: '/portal/events',
      name: 'portal-events',
      component: () => import('@/views/portal/EventsPage.vue'),
      meta: { title: 'Agenda Portal', public: true },
    },
    {
      path: '/portal/about',
      name: 'portal-about',
      component: () => import('@/views/portal/AboutPage.vue'),
      meta: { title: 'Tentang Portal', public: true },
    },
    {
      path: '/portal/contact',
      name: 'portal-contact',
      component: () => import('@/views/portal/ContactPage.vue'),
      meta: { title: 'Kontak Portal', public: true },
    },

    // ─── Auth ───────────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Auth/Login.vue'),
      meta: { title: 'Login', public: true },
    },
    {
      path: '/signup',
      name: 'signup',
      component: () => import('@/views/Auth/Signup.vue'),
      meta: { title: 'Sign Up', public: true },
    },

    // ─── Super Admin ──────────────────────────────────────────────
    {
      path: '/dashboard',
      name: 'super-admin-dashboard',
      component: () => import('@/views/dashboard/SuperAdminDashboard.vue'),
      meta: { requiresAuth: true, roles: ['super_admin'], title: 'Dashboard' },
    },
    {
      path: '/super-admin/organizations',
      name: 'super-admin-organizations',
      component: () => import('@/views/super-admin/organizations/List.vue'),
      meta: { requiresAuth: true, roles: ['super_admin'], title: 'Organizations' },
    },
    {
      path: '/super-admin/users',
      name: 'super-admin-users',
      component: () => import('@/views/super-admin/users/List.vue'),
      meta: { requiresAuth: true, roles: ['super_admin'], title: 'Users' },
    },
    {
      path: '/super-admin/approvals',
      name: 'super-admin-approvals',
      component: () => import('@/views/super-admin/approvals/OrganizationApproval.vue'),
      meta: { requiresAuth: true, roles: ['super_admin'], title: 'Approvals' },
    },
    {
      path: '/super-admin/settings',
      name: 'super-admin-settings',
      component: () => import('@/views/super-admin/settings/PlatformSettings.vue'),
      meta: { requiresAuth: true, roles: ['super_admin'], title: 'Platform Settings' },
    },
    {
      path: '/super-admin/logs',
      name: 'super-admin-logs',
      component: () => import('@/views/super-admin/logs/ActivityLog.vue'),
      meta: { requiresAuth: true, roles: ['super_admin'], title: 'Activity Logs' },
    },

    // ─── Organization (Admin / Editor / Contributor) ─────────────
    {
      path: '/organization/dashboard',
      name: 'organization-dashboard',
      component: () => import('@/views/dashboard/OrganizationDashboard.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'editor', 'contributor'], title: 'Dashboard' },
    },
    {
      path: '/organization/posts',
      name: 'organization-posts',
      component: () => import('@/views/organization/posts/List.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'editor', 'contributor'], title: 'Posts' },
    },
    {
      path: '/organization/agenda',
      name: 'organization-agenda',
      component: () => import('@/views/organization/agenda/List.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'editor'], title: 'Agenda' },
    },
    {
      path: '/organization/announcements',
      name: 'organization-announcements',
      component: () => import('@/views/organization/announcements/List.vue'),
      meta: { requiresAuth: true, roles: ['admin'], title: 'Announcements' },
    },
    {
      path: '/organization/gallery',
      name: 'organization-gallery',
      component: () => import('@/views/organization/gallery/List.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'editor'], title: 'Gallery' },
    },
    {
      path: '/organization/documents',
      name: 'organization-documents',
      component: () => import('@/views/organization/documents/List.vue'),
      meta: { requiresAuth: true, roles: ['admin'], title: 'Documents' },
    },
    {
      path: '/organization/users',
      name: 'organization-users',
      component: () => import('@/views/organization/users/List.vue'),
      meta: { requiresAuth: true, roles: ['admin'], title: 'Organization Users' },
    },
    {
      path: '/organization/settings',
      name: 'organization-settings',
      component: () => import('@/views/organization/settings/OrganizationSettings.vue'),
      meta: { requiresAuth: true, roles: ['admin'], title: 'Settings' },
    },

    // ─── Errors ──────────────────────────────────────────────────
    {
      path: '/404',
      name: 'not-found',
      component: () => import('@/views/public/NotFoundPage.vue'),
      meta: { title: 'Page Not Found', public: true },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/404',
    },
  ],
})

router.beforeEach((to, _from, next) => {
  document.title = `CMS Ormawa | ${to.meta.title ?? 'App'}`

  const authStore = useAuthStore()

  if (to.meta.public) {
    // Already logged in → redirect to appropriate dashboard
    if (authStore.token) {
      if (authStore.role === 'super_admin') return next({ name: 'super-admin-dashboard' })
      return next({ name: 'organization-dashboard' })
    }
    return next()
  }

  if (to.meta.requiresAuth) {
    if (!authStore.token) return next({ name: 'login' })

    const allowedRoles = to.meta.roles as string[] | undefined
    if (allowedRoles && authStore.role && !allowedRoles.includes(authStore.role)) {
      // Unauthorized role → send to their dashboard
      if (authStore.role === 'super_admin') return next({ name: 'super-admin-dashboard' })
      return next({ name: 'organization-dashboard' })
    }
    return next()
  }

  next()
})

export default router
