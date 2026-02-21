import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory('/kasir'),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/order/:locationId/:tableId',
      name: 'QROrder',
      component: () => import('@/views/QROrderView.vue'),
      meta: { public: true }
    },
    {
      path: '/',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('@/views/DashboardView.vue')
        },
        {
          path: 'pos',
          name: 'POS',
          component: () => import('@/views/POSView.vue')
        },
        {
          path: 'transactions',
          name: 'Transactions',
          component: () => import('@/views/TransactionsView.vue')
        },
        {
          path: 'reports',
          name: 'Reports',
          component: () => import('@/views/ReportsView.vue'),
          meta: { roles: ['owner', 'supervisor'] }
        },
        {
          path: 'settings/users',
          name: 'SettingsUsers',
          component: () => import('@/views/UserManagement.vue'),
          meta: { roles: ['owner'] }
        },
        {
          path: 'settings/products',
          name: 'SettingsProducts',
          component: () => import('@/views/ProductsView.vue'),
          meta: { roles: ['owner', 'supervisor'] }
        },
        {
          path: 'settings/stocks',
          name: 'SettingsStocks',
          component: () => import('@/views/StockManagement.vue'),
          meta: { roles: ['owner', 'supervisor'] }
        },
        {
          path: 'settings/locations',
          name: 'SettingsLocations',
          component: () => import('@/views/LocationsManagement.vue'),
          meta: { roles: ['owner'] }
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
  
  // Kasir App Access Control: Only owner and users with location assignment
  if (requiresAuth && isAuthenticated) {
    const user = authStore.user
    const hasLocation = user?.outlet_id || user?.location_id
    
    if (user?.role !== 'owner' && !hasLocation) {
      alert('Access Denied: Only Owner and users with location assignment can access this application')
      await authStore.logout()
      return next('/login')
    }
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
