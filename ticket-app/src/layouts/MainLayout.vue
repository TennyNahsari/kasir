<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-200 ease-in-out" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
      <!-- Logo -->
      <div class="flex items-center h-16 px-6 bg-gray-800">
        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
          </svg>
        </div>
        <span class="text-xl font-bold text-white">Ticket System</span>
      </div>

      <!-- Navigation -->
      <nav class="mt-6 px-3">
        <router-link
          to="/"
          class="flex items-center px-3 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === '/' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </router-link>

        <router-link
          to="/tickets"
          class="flex items-center px-3 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path.startsWith('/tickets') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
          </svg>
          Tickets
        </router-link>

        <router-link
          to="/my-assets"
          class="flex items-center px-3 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
          :class="$route.path === '/my-assets' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          My Assets
        </router-link>

        <!-- Owner Only: Settings Menu -->
        <div v-if="canManageUsers" class="mt-2">
          <button
            @click="settingsOpen = !settingsOpen"
            class="w-full flex items-center justify-between px-3 py-3 mb-1 text-sm font-medium rounded-lg transition-colors"
            :class="isInSettingsRoute ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Settings
            </div>
            <svg 
              class="w-4 h-4 transition-transform"
              :class="settingsOpen ? 'rotate-180' : ''"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Submenu -->
          <div v-show="settingsOpen" class="ml-6 space-y-1">
            <router-link
              to="/users"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path === '/users' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              Users
            </router-link>

            <router-link
              to="/locations"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path === '/locations' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Locations
            </router-link>

            <router-link
              to="/products"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path === '/products' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              Products
            </router-link>

            <router-link
              to="/categories"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path === '/categories' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              Categories
            </router-link>

            <router-link
              to="/assets"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path.startsWith('/assets') && $route.path !== '/my-assets' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              Assets
            </router-link>
          </div>
        </div>
      </nav>

      <!-- User Info at bottom -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center min-w-0">
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-2 flex-shrink-0">
              <span class="text-white text-sm font-semibold">{{ authStore.user?.name?.charAt(0) }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-white truncate">{{ authStore.user?.name }}</p>
              <p class="text-xs text-gray-400 capitalize">{{ authStore.user?.role }}</p>
            </div>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Logout
        </button>
      </div>
    </div>

    <!-- Mobile sidebar overlay -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden"></div>

    <!-- Main Content Area -->
    <div class="lg:pl-64">
      <!-- Top Header (Mobile) -->
      <div class="sticky top-0 z-30 flex h-16 bg-white border-b border-gray-200 lg:hidden">
        <button @click="sidebarOpen = true" class="px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="flex items-center flex-1 px-4">
          <span class="text-lg font-bold text-gray-900">Ticket System</span>
        </div>
      </div>

      <!-- Main Content -->
      <main class="px-4 py-8 sm:px-6 lg:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const sidebarOpen = ref(false)
const settingsOpen = ref(false)

const canManageUsers = computed(() => authStore.user?.role === 'owner')

const isInSettingsRoute = computed(() => {
  return route.path.startsWith('/assets') || 
         route.path === '/users' || 
         route.path === '/locations'
})

// Auto-open settings menu if navigating to settings route
watch(isInSettingsRoute, (newVal) => {
  if (newVal) {
    settingsOpen.value = true
  }
}, { immediate: true })

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>
