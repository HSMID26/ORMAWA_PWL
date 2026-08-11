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
      name: 'organization-home',
      component: () => import('@/views/public/OrganizationHome.vue'),
      meta: { title: 'Organisasi', public: true },
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
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Users' },
    },
    {
      path: '/super-admin/approvals',
      name: 'super-admin-approvals',
      component: () => import('@/views/super-admin/approvals/OrganizationApproval.vue'),
      meta: { requiresAuth: true, roles: ['Super Admin'], title: 'Approvals' },
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

    // ─── Organization (Admin / Editor / Contributor) ─────────────
    {
      path: '/organization/dashboard',
      name: 'organization-dashboard',
      component: () => import('@/views/dashboard/OrganizationDashboard.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Dashboard' },
    },
    {
      path: '/organization/posts',
      name: 'organization-posts',
      component: () => import('@/views/organization/posts/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor', 'Kontributor'], title: 'Posts' },
    },
    {
      path: '/organization/agenda',
      name: 'organization-agenda',
      component: () => import('@/views/organization/agenda/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi', 'Editor'], title: 'Agenda' },
    },
    {
      path: '/organization/announcements',
      name: 'organization-announcements',
      component: () => import('@/views/organization/announcements/List.vue'),
      meta: { requiresAuth: true, roles: ['Admin Organisasi'], title: 'Announcements' },
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
    if (authStore.token && (to.name === 'login' || to.name === 'signup')) {
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
