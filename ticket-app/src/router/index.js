import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      name: 'Dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/tickets',
      name: 'Tickets',
      component: () => import('../views/TicketList.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/tickets/:id',
      name: 'TicketDetail',
      component: () => import('../views/TicketDetail.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/my-assets',
      name: 'MyAssets',
      component: () => import('../views/MyAssets.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/assets',
      name: 'Assets',
      component: () => import('../views/AssetList.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/assets/:id',
      name: 'AssetDetail',
      component: () => import('../views/AssetDetail.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users',
      name: 'UserManagement',
      component: () => import('../views/UserManagement.vue'),
      meta: { requiresAuth: true, requiresOwner: true }
    },
    {
      path: '/locations',
      name: 'LocationManagement',
      component: () => import('../views/LocationManagement.vue'),
      meta: { requiresAuth: true, requiresOwner: true }
    },
    {
      path: '/products',
      name: 'ProductManagement',
      component: () => import('../views/ProductManagement.vue'),
      meta: { requiresAuth: true, requiresOwner: true }
    },
    {
      path: '/categories',
      name: 'CategoryManagement',
      component: () => import('../views/CategoryManagement.vue'),
      meta: { requiresAuth: true, requiresOwner: true }
    },
  ]
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Wait for auth check if not done yet
  if (!authStore.authChecked) {
    await authStore.checkAuth()
  }

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  const requiresOwner = to.matched.some(record => record.meta.requiresOwner)

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (requiresGuest && authStore.isAuthenticated) {
    next('/')
  } else if (requiresOwner && authStore.user?.role !== 'owner') {
    // Redirect non-owners trying to access owner-only pages
    next('/')
  } else {
    next()
  }
})

export default router
