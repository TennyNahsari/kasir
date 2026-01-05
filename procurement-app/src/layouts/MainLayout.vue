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
            <h1 class="text-lg sm:text-2xl font-bold text-orange-600">Procurement Management</h1>
            <span v-if="authStore.user?.outlet" class="hidden md:inline text-xs sm:text-sm text-gray-600">
              {{ authStore.user.outlet.name }}
            </span>
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
              ? 'bg-orange-50 text-orange-600' 
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
        <div class="flex overflow-x-auto">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.path"
            class="py-3 sm:py-4 px-3 text-xs sm:text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
            :class="$route.path === item.path 
              ? 'border-orange-600 text-orange-600' 
              : 'border-transparent text-gray-600 hover:text-gray-900'"
          >
            {{ item.name }}
          </router-link>
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
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const showMobileMenu = ref(false)

const navigation = computed(() => {
  const nav = [
    { name: 'Dashboard', path: '/' },
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
