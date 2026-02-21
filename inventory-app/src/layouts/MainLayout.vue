<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-white border-r">
      <!-- Logo/Header -->
      <div class="h-16 flex items-center px-6 border-b">
        <h1 class="text-lg font-bold text-blue-600">Inventory</h1>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4">
        <!-- Main Navigation Items -->
        <router-link
          to="/"
          class="flex items-center px-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === '/' 
            ? 'bg-blue-50 text-blue-600' 
            : 'text-gray-700 hover:bg-gray-100'"
        >
          Dashboard
        </router-link>
        
        <router-link
          v-if="canAccessMasterProduct"
          to="/inventory/products"
          class="flex items-center px-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === '/inventory/products' 
            ? 'bg-blue-50 text-blue-600' 
            : 'text-gray-700 hover:bg-gray-100'"
        >
          Master Product
        </router-link>
        
        <!-- Inventory Section -->
        <div class="mt-4">
          <button 
            @click="inventoryExpanded = !inventoryExpanded"
            class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase hover:bg-gray-100 rounded-lg transition-colors"
          >
            <span>Inventory</span>
            <svg 
              class="w-4 h-4 transition-transform duration-200"
              :class="inventoryExpanded ? 'rotate-180' : ''"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div v-show="inventoryExpanded" class="overflow-hidden">
              <router-link
                to="/inventory/stocks"
                class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/inventory/stocks' 
                  ? 'bg-blue-50 text-blue-600' 
                  : 'text-gray-700 hover:bg-gray-100'"
              >
                Stock Levels
              </router-link>
              <router-link
                to="/inventory/transfers"
                class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/inventory/transfers' 
                  ? 'bg-blue-50 text-blue-600' 
                  : 'text-gray-700 hover:bg-gray-100'"
              >
                Transfers
              </router-link>
              <router-link
                to="/inventory/ledger"
                class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/inventory/ledger' 
                  ? 'bg-blue-50 text-blue-600' 
                  : 'text-gray-700 hover:bg-gray-100'"
              >
                Ledger
              </router-link>
            </div>
          </transition>
        </div>
        
        <router-link
          to="/assets"
          class="flex items-center px-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === '/assets' 
            ? 'bg-blue-50 text-blue-600' 
            : 'text-gray-700 hover:bg-gray-100'"
        >
          Assets
        </router-link>
        
        <router-link
          to="/services"
          class="flex items-center px-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === '/services' 
            ? 'bg-blue-50 text-blue-600' 
            : 'text-gray-700 hover:bg-gray-100'"
        >
          Services
        </router-link>
        
        <!-- Settings Section -->
        <div v-if="hasSettingsAccess" class="mt-6 pt-4 border-t">
          <button 
            @click="settingsExpanded = !settingsExpanded"
            class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase hover:bg-gray-100 rounded-lg transition-colors"
          >
            <span>Settings</span>
            <svg 
              class="w-4 h-4 transition-transform duration-200"
              :class="settingsExpanded ? 'rotate-180' : ''"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div v-show="settingsExpanded" class="overflow-hidden">
              <router-link
                to="/users"
                class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/users' 
                  ? 'bg-blue-50 text-blue-600' 
                  : 'text-gray-700 hover:bg-gray-100'"
              >
                Users
              </router-link>
              <router-link
                to="/inventory/locations"
                class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/inventory/locations' 
                  ? 'bg-blue-50 text-blue-600' 
                  : 'text-gray-700 hover:bg-gray-100'"
              >
                Locations
              </router-link>
            </div>
          </transition>
        </div>
      </nav>
      
      <!-- User Info -->
      <div class="p-4 border-t">
        <div class="text-sm text-gray-600 mb-2">
          {{ authStore.user?.name }}<br>
          <span class="text-xs text-gray-500">({{ authStore.user?.role }})</span>
        </div>
        <button @click="handleLogout" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">
          Logout
        </button>
      </div>
    </aside>

    <!-- Mobile Menu Overlay -->
    <div 
      v-if="showMobileMenu" 
      class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
      @click="showMobileMenu = false"
    >
      <div class="bg-white w-64 h-full" @click.stop>
        <div class="p-4 border-b">
          <div class="flex items-center justify-between">
            <span class="font-semibold text-gray-900">Menu</span>
            <button @click="showMobileMenu = false" class="p-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <nav class="p-2">
          <template v-for="item in navigation" :key="item.name">
            <div v-if="item.isHeader" class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">
              {{ item.name }}
            </div>
            <router-link
              v-else
              :to="item.path"
              @click="showMobileMenu = false"
              class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
              :class="$route.path === item.path 
                ? 'bg-blue-50 text-blue-600' 
                : 'text-gray-700 hover:bg-gray-100'"
            >
              {{ item.name }}
            </router-link>
          </template>
        </nav>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="h-16 bg-white border-b flex items-center justify-between px-4 lg:px-6">
        <div class="flex items-center gap-4">
          <button @click="showMobileMenu = !showMobileMenu" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <h1 class="text-xl font-bold text-blue-600 lg:hidden">Inventory</h1>
        </div>
        
        <div class="flex items-center gap-4">
          <span class="hidden lg:inline text-sm text-gray-600">
            {{ authStore.user?.name }} <span class="text-gray-500">({{ authStore.user?.role }})</span>
          </span>
          <button @click="handleLogout" class="lg:hidden bg-gray-600 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 text-sm">
            Logout
          </button>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 lg:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const showMobileMenu = ref(false)
const inventoryExpanded = ref(true)
const settingsExpanded = ref(true)

const mainNavigation = [
  { name: 'Dashboard', path: '/' },
  { name: 'Master Product', path: '/inventory/products' },
  { 
    name: 'Inventory', 
    isDropdown: true,
    children: [
      { name: 'Stock Levels', path: '/inventory/stocks' },
      { name: 'Transfers', path: '/inventory/transfers' },
      { name: 'Ledger', path: '/inventory/ledger' }
    ]
  },
  { name: 'Assets', path: '/assets' },
  { name: 'Services', path: '/services' }
]

const settingsNav = [
  { name: 'Users', path: '/users' },
  { name: 'Locations', path: '/inventory/locations' }
]

const canAccessMasterProduct = computed(() => {
  const user = authStore.user
  if (!user) return false
  return user.role === 'owner' || user.role === 'supervisor'
})

const hasSettingsAccess = computed(() => {
  const user = authStore.user
  if (!user) return false
  
  // Owner always has access
  if (user.role === 'owner') return true
  
  // Supervisor with inventory department has access
  if (user.role === 'supervisor' && 
      user.location?.type === 'DEPARTMENT' && 
      user.location?.name?.toLowerCase().includes('inventory')) {
    return true
  }
  
  return false
})

const navigation = computed(() => {
  const nav = []
  
  // Flatten mainNavigation for mobile menu
  mainNavigation.forEach(item => {
    // Skip Master Product if user doesn't have access
    if (item.path === '/inventory/products' && !canAccessMasterProduct.value) {
      return
    }
    
    if (item.isDropdown && item.children) {
      // Add parent as header (non-clickable)
      nav.push({ name: item.name, isHeader: true })
      // Add children
      nav.push(...item.children)
    } else {
      nav.push(item)
    }
  })

  // Add Users and Locations to mobile menu for users with settings access
  if (hasSettingsAccess.value) {
    nav.push({ name: 'Settings', isHeader: true })
    nav.push(...settingsNav)
  }

  return nav
})

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>
