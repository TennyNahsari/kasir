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
            <h1 class="text-lg sm:text-2xl font-bold text-primary-600">Unified POS</h1>
            <span v-if="authStore.user?.outlet" class="hidden md:inline text-xs sm:text-sm text-gray-600">
              {{ authStore.user.outlet.name }}
            </span>
          </div>
          
          <div class="flex items-center gap-2 sm:gap-4">
            <span class="hidden sm:inline text-xs sm:text-sm text-gray-600">
              {{ authStore.user?.name }} <span class="hidden md:inline">({{ authStore.user?.role }})</span>
            </span>
            <button @click="handleLogout" class="btn btn-secondary text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2">
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
              ? 'bg-primary-50 text-primary-600' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ item.name }}
          </router-link>
          
          <!-- Settings Section (Owner only) -->
          <div v-if="authStore.isOwner()" class="mt-4 pt-4 border-t">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Settings</div>
            <router-link
              v-for="item in settingsMenu"
              :key="item.name"
              :to="item.path"
              @click="showMobileMenu = false"
              class="block px-4 py-3 text-sm font-medium rounded-lg transition-colors mb-1"
              :class="$route.path === item.path 
                ? 'bg-primary-50 text-primary-600' 
                : 'text-gray-700 hover:bg-gray-100'"
            >
              {{ item.name }}
            </router-link>
          </div>
        </nav>
      </div>
    </div>

    <!-- Desktop Navigation -->
    <nav class="hidden sm:block bg-white border-b relative">
      <div class="px-3 sm:px-6">
        <div class="flex items-center">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.path"
            class="py-3 sm:py-4 px-3 text-xs sm:text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
            :class="$route.path === item.path 
              ? 'border-primary-600 text-primary-600' 
              : 'border-transparent text-gray-600 hover:text-gray-900'"
          >
            {{ item.name }}
          </router-link>
          
          <!-- Settings Dropdown (Owner only) -->
          <div v-if="authStore.isOwner()" class="relative">
            <button 
              @click="showSettingsMenu = !showSettingsMenu"
              class="py-3 sm:py-4 px-4 text-xs sm:text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 group"
              :class="$route.path.startsWith('/settings') 
                ? 'border-blue-600 text-blue-600' 
                : 'border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Settings
              <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': showSettingsMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div 
                v-show="showSettingsMenu"
                class="absolute top-full left-0 mt-1 w-56 bg-white rounded-lg shadow-2xl border border-gray-200 py-2"
                style="z-index: 9999;"
              >
                <div class="px-3 py-2 border-b border-gray-100">
                  <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">System Settings</p>
                </div>
                <router-link
                  v-for="item in settingsMenu"
                  :key="item.name"
                  :to="item.path"
                  @click="showSettingsMenu = false"
                  class="flex items-center gap-3 px-4 py-2.5 text-sm transition-all group"
                  :class="$route.path === item.path 
                    ? 'bg-blue-50 text-blue-600 font-medium' 
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
                >
                  <component :is="item.icon" class="w-5 h-5" :class="$route.path === item.path ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" />
                  <span>{{ item.name }}</span>
                </router-link>
              </div>
            </transition>
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
import { computed, ref, onMounted, onUnmounted, h } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const showMobileMenu = ref(false)
const showSettingsMenu = ref(false)

// Close settings menu when clicking outside
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.relative')
  if (!dropdown && showSettingsMenu.value) {
    showSettingsMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const navigation = computed(() => {
  const items = [
    { name: 'Dashboard', path: '/' },
    { name: 'POS Kasir', path: '/pos' },
    { name: 'Transaksi', path: '/transactions' }
  ]


  return items
})

const settingsMenu = [
  { 
    name: 'Users', 
    path: '/settings/users',
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
    ])
  },
  { 
    name: 'Products', 
    path: '/settings/products',
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })
    ])
  },
  { 
    name: 'Stock', 
    path: '/settings/stocks',
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })
    ])
  },
  { 
    name: 'Locations', 
    path: '/settings/locations',
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' }),
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 11a3 3 0 11-6 0 3 3 0 016 0z' })
    ])
  }
]

const closeSettingsMenu = () => {
  showSettingsMenu.value = false
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
