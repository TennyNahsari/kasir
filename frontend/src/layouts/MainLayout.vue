<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Mobile Menu Overlay -->
    <div 
      v-if="showMobileMenu" 
      class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
      @click="showMobileMenu = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="fixed lg:static inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-50 lg:translate-x-0 w-64 bg-white shadow-lg flex flex-col"
      :class="showMobileMenu ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Sidebar Header -->
      <div class="p-4 border-b">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold text-primary-600">Unified POS</h1>
            <span v-if="authStore.user?.outlet" class="text-xs text-gray-600">
              {{ authStore.user.outlet.name }}
            </span>
          </div>
          <button @click="showMobileMenu = false" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Sidebar Navigation -->
      <nav class="flex-1 overflow-y-auto p-3">
        <!-- Main Menu -->
        <div class="space-y-1">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.path"
            @click="showMobileMenu = false"
            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-colors"
            :class="$route.path === item.path 
              ? 'bg-primary-50 text-primary-600' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.name }}</span>
          </router-link>
        </div>
        
        <!-- Settings Section (Owner & Supervisor) -->
        <div v-if="canAccessSettings" class="mt-6 pt-6 border-t">
          <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            {{ $t('nav.settings') }}
          </div>
          <div class="space-y-1 mt-2">
            <router-link
              v-for="item in filteredSettingsMenu"
              :key="item.name"
              :to="item.path"
              @click="showMobileMenu = false"
              class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path === item.path 
                ? 'bg-primary-50 text-primary-600' 
                : 'text-gray-700 hover:bg-gray-100'"
            >
              <component :is="item.icon" class="w-5 h-5" />
              <span>{{ item.name }}</span>
            </router-link>
          </div>
        </div>
      </nav>

      <!-- Sidebar Footer (User Info) -->
      <div class="p-4 border-t">
        <!-- Language Switcher Desktop -->
        <div class="mb-3 relative">
          <button @click="showLangMenu = !showLangMenu" class="w-full flex items-center justify-between p-2 hover:bg-gray-100 rounded-lg">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
              </svg>
              <span class="text-sm text-gray-700">{{ currentLocale === 'id' ? '🇮🇩 ID' : '🇬🇧 EN' }}</span>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="showLangMenu" class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-lg border z-50">
            <button @click="changeLocale('id')" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2 rounded-t-lg">
              <span class="text-sm">🇮🇩</span>
              <span class="text-sm">{{ $t('language.indonesian') }}</span>
            </button>
            <button @click="changeLocale('en')" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2 rounded-b-lg">
              <span class="text-sm">🇬🇧</span>
              <span class="text-sm">{{ $t('language.english') }}</span>
            </button>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
            <span class="text-primary-600 font-semibold text-sm">
              {{ authStore.user?.name?.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ authStore.user?.name }}
            </p>
            <p class="text-xs text-gray-500">{{ authStore.user?.role }}</p>
          </div>
        </div>
        <button 
          @click="handleLogout" 
          class="mt-3 w-full btn btn-secondary text-sm py-2"
        >
          {{ $t('nav.logout') }}
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Top Header (Mobile) -->
      <header class="lg:hidden bg-white shadow-sm sticky top-0 z-30">
        <div class="px-4 py-3">
          <div class="flex items-center justify-between">
            <button @click="showMobileMenu = !showMobileMenu" class="p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <h1 class="text-lg font-bold text-primary-600">Unified POS</h1>
            <!-- Language Switcher Mobile -->
            <div class="relative">
              <button @click="showLangMenu = !showLangMenu" class="p-2 hover:bg-gray-100 rounded-lg flex items-center gap-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
                <span class="text-xs font-medium">{{ currentLocale.toUpperCase() }}</span>
              </button>
              <div v-if="showLangMenu" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border z-50">
                <button @click="changeLocale('id')" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                  <span class="text-sm">🇮🇩</span>
                  <span class="text-sm">{{ $t('language.indonesian') }}</span>
                </button>
                <button @click="changeLocale('en')" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                  <span class="text-sm">🇬🇧</span>
                  <span class="text-sm">{{ $t('language.english') }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, h } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const authStore = useAuthStore()
const showMobileMenu = ref(false)
const showLangMenu = ref(false)
const { t, locale } = useI18n()

const currentLocale = computed(() => locale.value)

const changeLocale = (newLocale) => {
  locale.value = newLocale
  localStorage.setItem('locale', newLocale)
  showLangMenu.value = false
}

const navigation = computed(() => {
  const items = [
    { 
      name: t('nav.dashboard'), 
      path: '/',
      icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
      ])
    },
    { 
      name: t('nav.pos'), 
      path: '/pos',
      icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' })
      ])
    },
    { 
      name: t('nav.transactions'), 
      path: '/transactions',
      icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })
      ])
    }
  ]

  return items
})

const settingsMenu = computed(() => [
  { 
    name: t('nav.users'), 
    path: '/settings/users',
    roles: ['owner', 'inventory'],
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
    ])
  },
  { 
    name: t('nav.products'), 
    path: '/settings/products',
    roles: ['owner', 'inventory'],
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })
    ])
  },
  { 
    name: t('nav.stocks'), 
    path: '/settings/stocks',
    roles: ['owner', 'inventory', 'supervisor'],
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })
    ])
  },
  { 
    name: t('nav.locations'), 
    path: '/settings/locations',
    roles: ['owner', 'inventory'],
    icon: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' }),
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 11a3 3 0 11-6 0 3 3 0 016 0z' })
    ])
  }
])

const canAccessSettings = computed(() => {
  const userRole = authStore.user?.role
  return userRole === 'owner' || userRole === 'inventory' || userRole === 'supervisor'
})

const filteredSettingsMenu = computed(() => {
  const userRole = authStore.user?.role
  return settingsMenu.value.filter(item => item.roles.includes(userRole))
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
