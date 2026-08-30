<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-[#F9F6F0] border-r border-[#E5D9C5] shadow-md">
      <!-- Logo/Header -->
      <div class="h-16 flex items-center px-6 border-b border-[#E5D9C5]">
        <h1 class="text-xl font-bold font-display tracking-wide text-[#2C2C2C] hover:text-[#6B2E3E] transition-colors">Procurement</h1>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4">
        <router-link
          v-for="item in navigation"
          :key="item.name"
          :to="item.path"
          class="flex items-center px-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === item.path 
            ? 'bg-[#C9A96E]/20 text-[#6B2E3E] font-semibold border-l-4 border-[#C9A96E]' 
            : 'text-[#2C2C2C] hover:bg-[#E5D9C5]/40'"
        >
          {{ item.name }}
        </router-link>
        
        <!-- Settings Section -->
        <div v-if="hasSettingsAccess" class="mt-6 pt-4 border-t border-[#E5D9C5]">
          <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Settings</div>
          <router-link
            to="/settings/company"
            class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
            :class="$route.path === '/settings/company'
              ? 'bg-[#C9A96E]/20 text-[#6B2E3E] font-semibold border-l-4 border-[#C9A96E]' 
              : 'text-[#2C2C2C] hover:bg-[#E5D9C5]/40'"
          >
            Company
          </router-link>
          <router-link
            to="/settings/users"
            class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
            :class="$route.path === '/settings/users'
              ? 'bg-[#C9A96E]/20 text-[#6B2E3E] font-semibold border-l-4 border-[#C9A96E]' 
              : 'text-[#2C2C2C] hover:bg-[#E5D9C5]/40'"
          >
            Users
          </router-link>
          <router-link
            to="/settings/locations"
            class="flex items-center pl-8 pr-4 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
            :class="$route.path === '/settings/locations'
              ? 'bg-orange-50 text-orange-600' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            Locations
          </router-link>
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
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.path"
            @click="showMobileMenu = false"
            class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
            :class="$route.path === item.path 
              ? 'bg-orange-50 text-orange-600' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ item.name }}
          </router-link>
          
          <!-- Settings menu in mobile -->
          <div v-if="hasSettingsAccess" class="mt-4 border-t pt-2">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Settings</div>
            <router-link
              to="/settings/company"
              @click="showMobileMenu = false"
              class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
              :class="$route.path === '/settings/company'
                ? 'bg-orange-50 text-orange-600' 
                : 'text-gray-700 hover:bg-gray-100'"
            >
              Company
            </router-link>
            <router-link
              to="/settings/users"
              @click="showMobileMenu = false"
              class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
              :class="$route.path === '/settings/users'
                ? 'bg-orange-50 text-orange-600' 
                : 'text-gray-700 hover:bg-gray-100'"
            >
              Users
            </router-link>
            <router-link
              to="/settings/locations"
              @click="showMobileMenu = false"
              class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
              :class="$route.path === '/settings/locations'
                ? 'bg-orange-50 text-orange-600' 
                : 'text-gray-700 hover:bg-gray-100'"
            >
              Locations
            </router-link>
          </div>
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
          <h1 class="text-xl font-bold text-orange-600 lg:hidden">Procurement</h1>
          <span v-if="authStore.user?.outlet" class="hidden lg:inline text-sm text-gray-600">
            {{ authStore.user.outlet.name }}
          </span>
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

const hasSettingsAccess = computed(() => {
  return authStore.user?.role === 'owner'
})

const navigation = computed(() => {
  const nav = [
    { name: 'Dashboard', path: '/' },
    { name: 'Master Product', path: '/master/products' },
    { name: 'Purchase Requests', path: '/procurement/purchase-requests' },
    { name: 'Purchase Orders', path: '/procurement/purchase-orders' },
    { name: 'Goods Receipts', path: '/procurement/goods-receipts' },
    { name: 'Vendors', path: '/procurement/vendors' }
  ]

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
