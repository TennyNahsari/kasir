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
          component: () => import('@/views/ProcurementDashboard.vue')
        },
        {
          path: 'procurement/purchase-requests',
          name: 'PurchaseRequests',
          component: () => import('@/views/PurchaseRequestList.vue')
        },
        {
          path: 'procurement/purchase-requests/:id',
          name: 'PurchaseRequestDetail',
          component: () => import('@/views/PurchaseRequestDetail.vue')
        },
        {
          path: 'procurement/purchase-orders',
          name: 'PurchaseOrders',
          component: () => import('@/views/PurchaseOrderList.vue')
        },
        {
          path: 'procurement/purchase-orders/:id',
          name: 'PurchaseOrderDetail',
          component: () => import('@/views/PurchaseOrderDetail.vue')
        },
        {
          path: 'procurement/goods-receipts',
          name: 'GoodsReceipts',
          component: () => import('@/views/GoodsReceiptList.vue')
        },
        {
          path: 'procurement/goods-receipts/:id',
          name: 'GoodsReceiptDetail',
          component: () => import('@/views/GoodsReceiptDetail.vue')
        },
        {
          path: 'procurement/vendors',
          name: 'Vendors',
          component: () => import('@/views/VendorList.vue')
        },
        {
          path: 'master/products',
          name: 'Products',
          component: () => import('@/views/ProductList.vue')
        },
        {
          path: 'settings/users',
          name: 'Users',
          component: () => import('@/views/UserList.vue'),
          meta: { requiresOwner: true }
        },
        {
          path: 'settings/locations',
          name: 'Locations',
          component: () => import('@/views/LocationsView.vue'),
          meta: { requiresOwner: true }
        },
        {
          path: 'settings/company',
          name: 'CompanySettings',
          component: () => import('@/views/CompanySettings.vue'),
          meta: { requiresOwner: true }
        }
      ]
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth on first navigation only
  if (!authStore.initialized && !authStore.loading) {
    await authStore.initAuth()
  }
  
  const isAuthenticated = !!authStore.user
  const isGuestRoute = to.meta.guest === true
  const requiresAuth = to.meta.requiresAuth === true
  
  // Guest routes (login) - redirect to home if already authenticated
  if (isGuestRoute) {
    if (isAuthenticated) {
      return next('/')
    }
    return next()
  }
  
  // Protected routes - redirect to login if not authenticated
  if (requiresAuth && !isAuthenticated) {
    return next('/login')
  }
  
  // Procurement App Access Control: Only owner and users with location type DEPARTMENT or OUTLET
  if (requiresAuth && isAuthenticated) {
    const user = authStore.user
    const isOwner = user?.role === 'owner'
    const hasValidLocation = ['DEPARTMENT', 'OUTLET'].includes(user?.location?.type)
    
    if (!isOwner && !hasValidLocation) {
      alert('Access Denied: Only Owner, Department, and Outlet users can access Procurement application')
      await authStore.logout()
      return next('/login')
    }
  }
  
  // Owner-only routes check
  if (to.meta.requiresOwner && authStore.user?.role !== 'owner') {
    alert('Access Denied: Only Owner can access this page')
    return next('/')
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
