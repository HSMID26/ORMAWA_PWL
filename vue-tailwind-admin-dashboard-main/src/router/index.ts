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
      path: '/org/:slug',
      component: () => import('@/components/public/OrganizationLayout.vue'),
      meta: { public: true },
      children: [
        {
          path: '',
          name: 'organization-home',
          component: () => import('@/views/public/OrganizationHome.vue'),
          meta: { title: 'Organisasi' }
        },
        {
          path: 'profil',
          name: 'organization-profile',
          component: () => import('@/views/public/organization/Profile.vue'),
          meta: { title: 'Profil Organisasi' }
        },
        {
          path: 'struktur',
          name: 'organization-structure',
          component: () => import('@/views/public/organization/Structure.vue'),
          meta: { title: 'Struktur Organisasi' }
        },
        {
          path: 'berita',
          name: 'organization-news-list',
          component: () => import('@/views/public/organization/NewsList.vue'),
          meta: { title: 'Berita & Artikel' }
        },
        {
          path: 'berita/:postSlug',
          name: 'organization-news-detail',
          component: () => import('@/views/public/organization/NewsDetail.vue'),
          meta: { title: 'Detail Berita' }
        },
        {
          path: 'kegiatan',
          name: 'organization-activity-list',
          component: () => import('@/views/public/organization/ActivityList.vue'),
          meta: { title: 'Kegiatan & Agenda' }
        },
        {
          path: 'kegiatan/:id',
          name: 'organization-activity-detail',
          component: () => import('@/views/public/organization/ActivityDetail.vue'),
          meta: { title: 'Detail Kegiatan' }
        }
      ]
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
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Dashboard' },
    },
    {
      path: '/super-admin/organizations',
      name: 'super-admin-organizations',
      component: () => import('@/views/super-admin/organizations/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Organizations' },
    },
    {
      path: '/super-admin/users',
      name: 'super-admin-users',
      component: () => import('@/views/super-admin/users/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Users' }
    },
    {
      path: '/super-admin/organization-registrations',
      name: 'super-admin-organization-registrations',
      component: () => import('@/views/super-admin/organization-registrations/List.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Pendaftaran Organisasi' }
    },
    {
      path: '/super-admin/activity-logs',
      name: 'activity-logs',
      component: () => import('@/views/super-admin/activity-logs/List.vue'),
      meta: { title: 'Activity Log', requiresAuth: true, roles: ['Super Admin'] }
    },
    {
      path: '/super-admin/approvals',
      redirect: '/dashboard',
    },
    {
      path: '/super-admin/settings',
      name: 'super-admin-settings',
      component: () => import('@/views/super-admin/settings/PlatformSettings.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Platform Settings' },
    },
    {
      path: '/super-admin/logs',
      name: 'super-admin-logs',
      component: () => import('@/views/super-admin/logs/ActivityLog.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Activity Logs' },
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
      redirect: to => ({ path: '/organization/content', query: { type: 'post' } }),
    },
    {
      path: '/organization/posts/create',
      redirect: to => ({ path: '/organization/content/create', query: { type: 'post' } }),
    },
    {
      path: '/organization/posts/edit/:id',
      redirect: to => ({ path: `/organization/content/edit/post/${to.params.id}` }),
    },
    {
      path: '/organization/agenda',
      redirect: to => ({ path: '/organization/content', query: { type: 'activity' } }),
    },
    {
      path: '/organization/agenda/create',
      redirect: to => ({ path: '/organization/content/create', query: { type: 'activity' } }),
    },
    {
      path: '/organization/agenda/edit/:id',
      redirect: to => ({ path: `/organization/content/edit/activity/${to.params.id}` }),
    },
    {
      path: '/organization/announcements',
      redirect: to => ({ path: '/organization/content', query: { type: 'announcement' } }),
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
      meta: { requiresAuth: true, roles: ['Admin Organisasi'], title: 'Documents' },
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

  // Initialize tenant dynamically on first load
  if (publicStore.currentTenantSlug === null && !publicStore.isLoading) {
    const slug = resolveTenantFromHostname()
    await publicStore.setTenant(slug)
  }

  if (to.meta.public) {
    // Note: Do NOT auto-redirect to dashboard if accessing public pages, 
    // unless they hit /login while logged in.
    if (authStore.token && (to.name === 'login' || to.name === 'register' || to.name === 'signup')) {
      if (authStore.role === 'Super Admin') return next({ name: 'super-admin-dashboard' })
      return next({ name: 'organization-dashboard' })
    }
    return next()
  }

  if (to.meta.requiresAuth) {
    if (!authStore.token) return next({ name: 'login', replace: true })

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
