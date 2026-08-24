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
      name: 'public-home',
      component: () => import('@/views/public/Index.vue'),
      meta: { title: 'Beranda', public: true },
    },
    {
      path: '/organizations',
      name: 'public-organizations-directory',
      component: () => import('@/views/public/OrganizationsDirectory.vue'),
      meta: { title: 'Direktori Ormawa', public: true },
    },
    {
      path: '/organizations/:slug',
      component: () => import('@/components/public/OrganizationLayout.vue'),
      meta: { public: true },
      children: [
        {
          path: '',
          name: 'public-organization-home',
          component: () => import('@/views/public/OrganizationHome.vue'),
          meta: { title: 'Organisasi', public: true }
        },
        {
          path: 'profil',
          name: 'public-organization-profile',
          component: () => import('@/views/public/organization/Profile.vue'),
          meta: { title: 'Profil Organisasi', public: true }
        },
        {
          path: 'articles',
          alias: 'berita',
          name: 'public-organization-articles-list',
          component: () => import('@/views/public/organization/NewsList.vue'),
          meta: { title: 'Berita & Artikel', public: true }
        },
        {
          path: 'articles/:articleSlug',
          alias: 'berita/:articleSlug',
          name: 'public-organization-article-detail',
          component: () => import('@/views/public/organization/NewsDetail.vue'),
          meta: { title: 'Detail Berita', public: true }
        },
        {
          path: 'agenda',
          alias: 'kegiatan',
          name: 'public-organization-agenda-list',
          component: () => import('@/views/public/organization/ActivityList.vue'),
          meta: { title: 'Agenda Kegiatan', public: true }
        },
        {
          path: 'agenda/:id',
          alias: 'kegiatan/:id',
          name: 'public-organization-agenda-detail',
          component: () => import('@/views/public/organization/ActivityDetail.vue'),
          meta: { title: 'Detail Kegiatan', public: true }
        },
        {
          path: 'announcements',
          alias: 'pengumuman',
          name: 'public-organization-announcements',
          component: () => import('@/views/public/organization/AnnouncementsPage.vue'),
          meta: { title: 'Pengumuman', public: true }
        },
        {
          path: 'gallery',
          alias: 'galeri',
          name: 'public-organization-gallery',
          component: () => import('@/views/public/organization/GalleryPage.vue'),
          meta: { title: 'Galeri Foto', public: true }
        },
        {
          path: 'documents',
          alias: 'dokumen',
          name: 'public-organization-documents',
          component: () => import('@/views/public/organization/DocumentsPage.vue'),
          meta: { title: 'Dokumen', public: true }
        },
        {
          path: 'structure',
          alias: 'struktur',
          name: 'public-organization-structure',
          component: () => import('@/views/public/organization/Structure.vue'),
          meta: { title: 'Struktur Organisasi', public: true }
        },
      ]
    },
    // Compatibility alias: /org/:slug redirect to /organizations/:slug
    {
      path: '/org/:slug/:pathMatch(.*)*',
      redirect: to => {
        const path = Array.isArray(to.params.pathMatch) ? to.params.pathMatch.join('/') : (to.params.pathMatch || '')
        return `/organizations/${to.params.slug}${path ? '/' + path : ''}`
      },
    },
    {
      path: '/org/:slug',
      redirect: to => `/organizations/${to.params.slug}`,
    },
    // Keep other existing public routes as fallbacks if needed, but route / dynamically
    // ─── Auth ───────────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Auth/Login.vue'),
      meta: { title: 'Login', public: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/Auth/OrganizationRegistration.vue'),
      meta: {
        title: 'Pendaftaran Organisasi',
        public: true
      },
    },
    {
      path: '/register/success',
      name: 'register-success',
      component: () => import('@/views/Auth/RegistrationSuccess.vue'),
      meta: {
        title: 'Pendaftaran Berhasil',
        public: true
      },
    },

    // ─── Super Admin ──────────────────────────────────────────────
    {
      path: '/dashboard',
      name: 'super-admin-dashboard',
      component: () => import('@/views/dashboard/SuperAdminDashboard.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Dashboard Global' },
    },
    {
      path: '/super-admin/organizations',
      name: 'super-admin-organizations',
      component: () => import('@/views/super-admin/organizations/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Semua Organisasi' },
    },
    {
      path: '/super-admin/organization-registrations',
      name: 'super-admin-organization-registrations',
      component: () => import('@/views/super-admin/organization-registrations/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Pendaftaran Organisasi' }
    },
    {
      path: '/super-admin/organization-admins',
      name: 'super-admin-organization-admins',
      component: () => import('@/views/super-admin/organization-admins/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Admin Organisasi' }
    },
    {
      path: '/super-admin/periods',
      name: 'super-admin-periods',
      component: () => import('@/views/super-admin/periods/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Periode / Renewal' }
    },
    {
      path: '/super-admin/users',
      name: 'super-admin-users',
      component: () => import('@/views/super-admin/users/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Pengguna Global' }
    },
    {
      path: '/super-admin/content',
      name: 'super-admin-content',
      component: () => import('@/views/super-admin/content/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Monitoring Konten' }
    },
    {
      path: '/super-admin/activity-logs',
      name: 'super-admin-activity-logs',
      component: () => import('@/views/super-admin/activity-logs/List.vue'),
      meta: { title: 'Activity Log', requiresAuth: true, roles: ['Super Admin'] }
    },
    {
      path: '/super-admin/approvals',
      redirect: '/super-admin/periods',
    },
    {
      path: '/super-admin/settings',
      name: 'super-admin-settings',
      component: () => import('@/views/super-admin/settings/PlatformSettings.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Pengaturan Platform' },
    },
    {
      path: '/super-admin/logs',
      redirect: '/super-admin/activity-logs',
    },
    {
      path: '/super-admin/organizations/:id/periods',
      name: 'super-admin-organizations-periods',
      component: () => import('@/views/super-admin/organizations/Periods.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Manage Periods' },
    },

    // ─── Organization (Admin / Editor / Contributor) ─────────────
    {
      path: '/organization/dashboard',
      name: 'organization-dashboard',
      component: () => import('@/views/dashboard/OrganizationDashboard.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Dashboard' },
    },
    // ─── Unified Content Routes ──────────────────────────────
    {
      path: '/organization/content',
      name: 'organization-content',
      component: () => import('@/views/organization/content/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Konten' },
    },
    {
      path: '/organization/content/create',
      name: 'organization-content-create',
      component: () => import('@/views/organization/content/Create.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Buat Konten' },
    },
    {
      path: '/organization/content/edit/:type/:id',
      name: 'organization-content-edit',
      component: () => import('@/views/organization/content/Edit.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Edit Konten' },
    },

    // ─── Legacy Routes (Redirected) ─────────────────────────
    {
      path: '/organization/activity-logs',
      name: 'organization-activity-logs',
      component: () => import('@/views/organization/activity-logs/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi'], title: 'Activity Logs' },
    },
    {
      path: '/organization/posts',
      name: 'organization-posts',
      component: () => import('@/views/organization/posts/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Berita & Artikel' },
    },
    {
      path: '/organization/posts/create',
      name: 'organization-posts-create',
      component: () => import('@/views/organization/posts/Create.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Tulis Artikel' },
    },
    {
      path: '/organization/posts/edit/:id',
      name: 'organization-posts-edit',
      component: () => import('@/views/organization/posts/Edit.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Edit Artikel' },
    },
    {
      path: '/organization/agenda',
      name: 'organization-agenda',
      component: () => import('@/views/organization/agenda/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Agenda Acara' },
    },
    {
      path: '/organization/agenda/create',
      name: 'organization-agenda-create',
      component: () => import('@/views/organization/agenda/Create.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Tambah Agenda' },
    },
    {
      path: '/organization/agenda/edit/:id',
      name: 'organization-agenda-edit',
      component: () => import('@/views/organization/agenda/Edit.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Edit Agenda' },
    },
    {
      path: '/organization/announcements',
      name: 'organization-announcements',
      component: () => import('@/views/organization/announcements/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Pengumuman' },
    },
    {
      path: '/organization/gallery',
      name: 'organization-gallery',
      component: () => import('@/views/organization/gallery/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Gallery' },
    },
    {
      path: '/organization/documents',
      name: 'organization-documents',
      component: () => import('@/views/organization/documents/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Documents' },
    },
    {
      path: '/organization/users',
      name: 'organization-users',
      component: () => import('@/views/organization/users/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi'], title: 'Organization Users' },
    },
    {
      path: '/organization/settings',
      name: 'organization-settings',
      component: () => import('@/views/organization/settings/OrganizationSettings.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi'], title: 'Settings' },
    },
    {
      path: '/organization/period',
      name: 'organization-period',
      component: () => import('@/views/organization/period/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi'], title: 'Manage Period' },
    },
    {
      path: '/organization/committees',
      name: 'organization-committees',
      component: () => import('@/views/organization/committees/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Struktur Organisasi' },
    },

    // ─── Shared Admin Routes ──────────────────────────────────────
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/profile/Profile.vue'),
      meta: { requiresAuth: true, title: 'Profil' },
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/views/settings/Settings.vue'),
      meta: { requiresAuth: true, title: 'Pengaturan' },
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

import { usePublicStore } from '@/stores/public'
import { resolveTenantFromHostname } from '@/utils/tenantResolver'

router.beforeEach(async (to, _from, next) => {
  document.title = `CMS Ormawa | ${to.meta.title ?? 'App'}`

  const authStore = useAuthStore()
  const publicStore = usePublicStore()

  // 1. Ensure authentication session is fully restored/verified before evaluating routes
  if (!authStore.isInitialized) {
    await authStore.restoreSession()
  }

  // 2. Initialize tenant dynamically on first load
  if (publicStore.currentTenantSlug === null && !publicStore.isLoading) {
    const slug = resolveTenantFromHostname()
    await publicStore.setTenant(slug)
  }

  // 3. Public Routes Handling
  const isPublic = to.matched.some(record => record.meta.public) || to.meta.public
  if (isPublic) {
    // If authenticated user visits login or register pages, redirect to role-appropriate dashboard
    if (authStore.isAuthenticated && (to.name === 'login' || to.name === 'register' || to.name === 'signup')) {
      if (authStore.role === 'Super Admin') return next({ name: 'super-admin-dashboard' })
      return next({ name: 'organization-dashboard' })
    }
    return next()
  }

  // 4. Protected Routes Handling
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      return next({ name: 'login', query: { redirect: to.fullPath }, replace: true })
    }

    const allowedRoles = to.meta.roles as string[] | undefined
    if (allowedRoles && authStore.role && !allowedRoles.includes(authStore.role)) {
      if (authStore.role === 'Super Admin') return next({ name: 'super-admin-dashboard', replace: true })
      return next({ name: 'organization-dashboard', replace: true })
    }
    return next()
  }

  next()
})

export default router
