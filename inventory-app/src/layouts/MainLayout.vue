<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-30">
      <div class="px-3 sm:px-6 py-3 sm:py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 sm:gap-4">
            <button @click="showMobileMenu = !showMobileMenu" class="sm:hidden p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <h1 class="text-lg sm:text-2xl font-bold text-blue-600">Inventory Management</h1>
          </div>
          
          <div class="flex items-center gap-2 sm:gap-4">
            <span class="hidden sm:inline text-xs sm:text-sm text-gray-600">
              {{ authStore.user?.name }} <span class="hidden md:inline">({{ authStore.user?.role }})</span>
            </span>
            <button @click="handleLogout" class="bg-gray-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg hover:bg-gray-700 text-xs sm:text-sm">
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation -->
    <div 
      v-if="showMobileMenu" 
      class="sm:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
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
          <div class="mt-2 text-sm text-gray-600">
            {{ authStore.user?.name }}<br>
            <span class="text-xs">({{ authStore.user?.role }})</span>
          </div>
        </div>
        <nav class="p-2">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.path"
            @click="showMobileMenu = false"
            class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
            :class="$route.path === item.path 
              ? 'bg-blue-50 text-blue-600' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ item.name }}
          </router-link>
        </nav>
      </div>
    </div>

    <!-- Desktop Navigation -->
    <nav class="hidden sm:block bg-white border-b">
      <div class="px-3 sm:px-6">
        <div class="flex gap-1">
          <router-link
            v-for="item in mainNavigation"
            :key="item.name"
            :to="item.path"
            class="py-3 sm:py-4 px-3 text-xs sm:text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
            :class="$route.path === item.path 
              ? 'border-blue-600 text-blue-600' 
              : 'border-transparent text-gray-600 hover:text-gray-900'"
          >
            {{ item.name }}
          </router-link>
          
          <!-- Settings Dropdown -->
          <div v-if="hasSettingsAccess" class="relative" ref="settingsDropdown">
            <button
              @click="toggleSettings"
              class="py-3 sm:py-4 px-3 text-xs sm:text-sm font-medium border-b-2 transition-colors whitespace-nowrap flex items-center gap-1"
              :class="isSettingsActive 
                ? 'border-blue-600 text-blue-600' 
                : 'border-transparent text-gray-600 hover:text-gray-900'"
            >
              Settings
              <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showSettings }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div
              v-if="showSettings"
              class="absolute top-full left-0 mt-0 w-48 bg-white rounded-b-lg shadow-lg border border-gray-200 py-1 z-50"
            >
              <router-link
                v-for="item in settingsNav"
                :key="item.name"
                :to="item.path"
                @click="showSettings = false"
                class="block px-4 py-2 text-sm transition-colors"
                :class="$route.path === item.path 
                  ? 'bg-blue-50 text-blue-600' 
                  : 'text-gray-700 hover:bg-gray-100'"
              >
                {{ item.name }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="p-3 sm:p-6">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const showMobileMenu = ref(false)
const showSettings = ref(false)
const settingsDropdown = ref(null)

const mainNavigation = [
  { name: 'Dashboard', path: '/' },
  { name: 'Products', path: '/inventory/products' },
  { name: 'Stock Levels', path: '/inventory/stocks' },
  { name: 'Transfers', path: '/inventory/transfers' },
  { name: 'Ledger', path: '/inventory/ledger' }
]

const settingsNav = [
  { name: 'Users', path: '/users' },
  { name: 'Locations', path: '/inventory/locations' }
]

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
  const nav = [...mainNavigation]

  // Add Users and Locations to mobile menu for users with settings access
  if (hasSettingsAccess.value) {
    nav.push(...settingsNav)
  }

  return nav
})

const isSettingsActive = computed(() => {
  return settingsNav.some(item => route.path === item.path)
})

const toggleSettings = () => {
  showSettings.value = !showSettings.value
}

const handleClickOutside = (event) => {
  if (settingsDropdown.value && !settingsDropdown.value.contains(event.target)) {
    showSettings.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
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
