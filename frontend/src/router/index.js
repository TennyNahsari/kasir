import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

// Track if we already validated access for current session
let accessValidated = false

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () => import('@/views/HomeView.vue'),
      meta: { public: true }
    },
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
      path: '/dashboard',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('@/views/DashboardView.vue')
        },
        {
          path: '/pos',
          name: 'POS',
          component: () => import('@/views/POSView.vue')
        },
        {
          path: '/transactions',
          name: 'Transactions',
          component: () => import('@/views/TransactionsView.vue')
        },
        {
          path: '/reports',
          name: 'Reports',
          component: () => import('@/views/ReportsView.vue'),
          meta: { roles: ['owner', 'inventory', 'supervisor'] }
        },
        {
          path: '/settings/users',
          name: 'SettingsUsers',
          component: () => import('@/views/UserManagement.vue'),
          meta: { roles: ['owner'] }
        },
        {
          path: '/settings/products',
          name: 'SettingsProducts',
          component: () => import('@/views/ProductsView.vue'),
          meta: { roles: ['owner', 'inventory'] }
        },
        {
          path: '/settings/stocks',
          name: 'SettingsStocks',
          component: () => import('@/views/StockManagement.vue'),
          meta: { roles: ['owner', 'inventory', 'supervisor'] }
        },
        {
          path: '/settings/locations',
          name: 'SettingsLocations',
          component: () => import('@/views/LocationsManagement.vue'),
          meta: { roles: ['owner', 'inventory'] }
        },
        {
          path: '/settings/whatsapp',
          name: 'SettingsWhatsapp',
          component: () => import('@/views/WhatsappSettings.vue'),
          meta: { roles: ['owner'] }
        },
        {
          path: '/settings/payment',
          name: 'SettingsPayment',
          component: () => import('@/views/PaymentSettings.vue'),
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
  const isPublicRoute = to.matched.some(r => r.meta.public === true) || to.meta.public === true
  const isGuestRoute = to.matched.some(r => r.meta.guest === true) || to.meta.guest === true
  const requiresAuth = !isPublicRoute && !isGuestRoute && (to.matched.some(r => r.meta.requiresAuth === true) || to.meta.requiresAuth === true)
  
  // Public routes - always allow
  if (isPublicRoute) {
    return next()
  }
  
  // Guest routes (login) - redirect to home if already authenticated
  if (isGuestRoute && isAuthenticated) {
    return next('/dashboard')
  }
  
  // Protected routes - redirect to login if not authenticated
  if (requiresAuth && !isAuthenticated) {
    return next('/login')
  }
  
  // Kasir App Access Control: Owner/Inventory or users with OUTLET/FNB location assignment
  // SKIP THIS CHECK for guest/login routes, public routes, or if already validated
  if (requiresAuth && isAuthenticated && authStore.user && !isGuestRoute && !isPublicRoute && !accessValidated) {
    const user = authStore.user
    const isAdminRole = user?.role === 'owner' || user?.role === 'inventory'
    
    // DEBUG: Log user data (only on first check)
    console.log('🔍 Router Guard - User Data:', {
      role: user?.role,
      roleType: typeof user?.role,
      isAdminRole: isAdminRole,
      location_id: user?.location_id,
      outlet_id: user?.outlet_id,
      fullUser: user
    })
    
    // Owner/Inventory have full access
    if (isAdminRole) {
      console.log('✅ Admin role detected - allowing access')
      accessValidated = true // Mark as validated
      // Continue to role check
    } else {
      console.log('⚠️ Non-admin user - checking location assignment')
      // Non-admin users must have location assignment with type OUTLET or FNB
      const hasLocation = user?.outlet_id || user?.location_id
      
      if (!hasLocation) {
        console.error('❌ No location assignment found')
        alert('Access Denied: Only Owner/Inventory and users with location assignment can access this application')
        accessValidated = false // Reset flag
        await authStore.logout()
        return next('/login')
      }
      
      // Validate location type (OUTLET or FNB only) - ONLY ONCE
      try {
        let isValidLocationType = false
        
        if (user.location_id) {
          // User assigned to specific location - check its type
          const locationResponse = await api.get(`/locations/${user.location_id}`)
          const locationType = locationResponse.data?.type?.toUpperCase()
          isValidLocationType = locationType === 'OUTLET' || locationType === 'FNB'
          
          if (!isValidLocationType) {
            alert(`Access Denied: This POS application is only for OUTLET and FNB locations.\n\nYour location type: ${locationType}\n\nPlease use the Inventory app instead.`)
            accessValidated = false
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
            accessValidated = false
            await authStore.logout()
            return next('/login')
          }
        }
        
        // Mark as validated after successful check
        accessValidated = true
        console.log('✅ Access validated successfully')
      } catch (error) {
        console.error('Failed to validate location type:', error)
        // Don't block on API errors, just log and continue
        // User might be offline temporarily
        console.warn('⚠️ Could not validate access, allowing access anyway')
        accessValidated = true // Allow access even if validation fails
      }
    }
  }
  
  // Role check for protected routes
  if (requiresAuth && to.meta.roles) {
    if (!to.meta.roles.includes(authStore.user?.role)) {
      return next('/dashboard')
    }
  }
  
  // Allow navigation
  next()
})

export default router
