import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('@/views/InventoryDashboard.vue')
        },
        {
          path: 'inventory/stocks',
          name: 'StockList',
          component: () => import('@/views/StockList.vue')
        },
        {
          path: 'inventory/transfers',
          name: 'TransferList',
          component: () => import('@/views/TransferList.vue')
        },
        {
          path: 'inventory/transfers/:id',
          name: 'TransferDetail',
          component: () => import('@/views/TransferDetail.vue')
        },
        {
          path: 'inventory/ledger',
          name: 'InventoryLedger',
          component: () => import('@/views/InventoryLedger.vue')
        },
        {
          path: 'inventory/locations',
          name: 'Locations',
          component: () => import('@/views/LocationsView.vue')
        },
        {
          path: 'inventory/products',
          name: 'Products',
          component: () => import('@/views/ProductList.vue')
        }
      ]
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth on first navigation only
  if (!authStore.initialized) {
    await authStore.initAuth()
  }
  
  const isAuthenticated = !!authStore.user
  const isPublicRoute = to.meta.public === true
  const isGuestRoute = to.meta.guest === true
  const requiresAuth = to.meta.requiresAuth === true
  
  // Public routes - always allow
  if (isPublicRoute) {
    return next()
  }
  
  // Guest routes (login) - redirect to home if already authenticated
  if (isGuestRoute && isAuthenticated) {
    return next('/')
  }
  
  // Protected routes - redirect to login if not authenticated
  if (requiresAuth && !isAuthenticated) {
    return next('/login')
  }
  
  // Role check for protected routes
  if (requiresAuth && to.meta.roles) {
    if (!to.meta.roles.includes(authStore.user?.role)) {
      return next('/')
    }
  }
  
  // Allow navigation
  next()
})

export default router
