import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

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
          meta: { roles: ['owner', 'inventory', 'supervisor'] }
        },
        {
          path: 'settings/users',
          name: 'SettingsUsers',
          component: () => import('@/views/UserManagement.vue'),
          meta: { roles: ['owner', 'inventory'] }
        },
        {
          path: 'settings/products',
          name: 'SettingsProducts',
          component: () => import('@/views/ProductsView.vue'),
          meta: { roles: ['owner', 'inventory', 'supervisor'] }
        },
        {
          path: 'settings/stocks',
          name: 'SettingsStocks',
          component: () => import('@/views/StockManagement.vue'),
          meta: { roles: ['owner', 'inventory', 'supervisor'] }
        },
        {
          path: 'settings/locations',
          name: 'SettingsLocations',
          component: () => import('@/views/LocationsManagement.vue'),
          meta: { roles: ['owner', 'inventory'] }
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
  
  // Kasir App Access Control: Owner/Inventory or users with OUTLET/FNB location assignment
  if (requiresAuth && isAuthenticated) {
    const user = authStore.user
    const isAdminRole = user?.role === 'owner' || user?.role === 'inventory'
    
    // Owner/Inventory have full access
    if (isAdminRole) {
      // Continue to role check
    } else {
      // Non-admin users must have location assignment with type OUTLET or FNB
      const hasLocation = user?.outlet_id || user?.location_id
      
      if (!hasLocation) {
        alert('Access Denied: Only Owner/Inventory and users with location assignment can access this application')
        await authStore.logout()
        return next('/login')
      }
      
      // Validate location type (OUTLET or FNB only)
      try {
        let isValidLocationType = false
        
        if (user.location_id) {
          // User assigned to specific location - check its type
          const locationResponse = await api.get(`/locations/${user.location_id}`)
          const locationType = locationResponse.data?.type?.toUpperCase()
          isValidLocationType = locationType === 'OUTLET' || locationType === 'FNB'
          
          if (!isValidLocationType) {
            alert(`Access Denied: This POS application is only for OUTLET and FNB locations.\n\nYour location type: ${locationType}\n\nPlease use the Inventory app instead.`)
            await authStore.logout()
            return next('/login')
          }
        } else if (user.outlet_id) {
          // User assigned to outlet - check if outlet has OUTLET or FNB type locations
          const locationsResponse = await api.get('/locations', {
            params: { outlet_id: user.outlet_id, is_active: true }
          })
          
          const validLocations = locationsResponse.data?.filter(loc => 
            loc.type === 'OUTLET' || loc.type === 'FNB'
          ) || []
          
          if (validLocations.length === 0) {
            alert('Access Denied: This POS application is only for OUTLET and FNB locations.\n\nYour outlet has no valid POS locations.\n\nPlease use the Inventory app instead.')
            await authStore.logout()
            return next('/login')
          }
        }
      } catch (error) {
        console.error('Failed to validate location type:', error)
        alert('Failed to validate location access. Please try again.')
        return next('/login')
      }
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
