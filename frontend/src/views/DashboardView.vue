<template>
  <div class="space-y-4">
    <h2 class="text-xl sm:text-2xl font-bold">Dashboard</h2>
    
    <!-- Outlet Selector for Owner -->
    <OutletSelector @outlet-changed="handleOutletChange" />

    <!-- No Outlet Warning -->
    <div v-if="showNoOutletWarning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-yellow-800 text-sm">
        ⚠️ <strong>{{ isOwner ? 'Silakan pilih outlet untuk melihat statistik' : 'User tidak memiliki outlet' }}</strong>
      </p>
    </div>

    <!-- Stats Cards -->
    <div v-if="currentOutletId" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-6">
      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">Omzet Hari Ini</div>
        <div class="text-base sm:text-2xl font-bold text-primary-600">
          {{ formatCurrency(stats?.today?.total_revenue || 0) }}
        </div>
      </div>

      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">Jumlah Transaksi</div>
        <div class="text-base sm:text-2xl font-bold text-green-600">
          {{ stats?.today?.total_transactions || 0 }}
        </div>
      </div>

      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">Rata-rata Transaksi</div>
        <div class="text-base sm:text-2xl font-bold text-blue-600">
          {{ formatCurrency(stats?.today?.average_transaction || 0) }}
        </div>
      </div>

      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">Kas Hari Ini</div>
        <div class="text-base sm:text-2xl font-bold text-purple-600">
          {{ formatCurrency(stats?.today?.cash_in_hand || 0) }}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Top Products -->
      <div class="card">
        <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Produk Terlaris</h3>
        <div class="space-y-2 sm:space-y-3">
          <div 
            v-for="product in stats?.top_products?.slice(0, 5)" 
            :key="product.id"
            class="flex justify-between items-center p-2 sm:p-3 bg-gray-50 rounded"
          >
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm sm:text-base truncate">{{ product.name }}</div>
              <div class="text-xs sm:text-sm text-gray-600">{{ product.total_quantity }} terjual</div>
            </div>
            <div class="text-right flex-shrink-0 ml-2">
              <div class="font-semibold text-primary-600 text-sm sm:text-base">
                {{ formatCurrency(product.total_revenue) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock -->
      <div class="card">
        <div class="flex justify-between items-center mb-3 sm:mb-4">
          <h3 class="text-base sm:text-lg font-semibold">Stok Menipis</h3>
          <span v-if="isUserLocationFnb" class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded font-medium">
            🍽️ F&B Only
          </span>
        </div>
        <div class="space-y-2 sm:space-y-3">
          <div 
            v-for="product in stats?.low_stock_products" 
            :key="product.id"
            class="flex justify-between items-center p-2 sm:p-3 bg-red-50 rounded"
          >
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm sm:text-base truncate">{{ product.name }}</div>
              <div class="text-xs sm:text-sm text-gray-600">{{ product.category?.name }}</div>
            </div>
            <div class="text-right flex-shrink-0 ml-2">
              <div class="font-semibold text-red-600 text-sm sm:text-base">
                Stok: {{ product.stock }}
              </div>
              <div class="text-xs text-gray-600">Min: {{ product.min_stock }}</div>
            </div>
          </div>
          <div v-if="!stats?.low_stock_products?.length" class="text-center text-gray-500 py-4 text-sm">
            Semua produk stok aman
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'
import OutletSelector from '@/components/OutletSelector.vue'
import api from '@/services/api'

const dashboardStore = useDashboardStore()
const authStore = useAuthStore()
const stats = ref(null)
const currentOutletId = ref(null)
const userLocation = ref(null)

const isOwner = computed(() => {
  const role = authStore.user?.role
  return (role === 'owner' || role === 'inventory') && !authStore.user?.outlet_id
})
const showNoOutletWarning = computed(() => !currentOutletId.value && !stats.value)

// Check if user location is FNB type
const isUserLocationFnb = computed(() => {
  return userLocation.value?.type?.toUpperCase() === 'FNB'
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const loadDashboard = async (outletId) => {
  if (!outletId) return
  
  try {
    const params = { 
      outlet_id: outletId 
    }
    
    // Add location_id if user has one (for FNB filtering)
    const userLocationId = authStore.user?.location_id
    if (userLocationId) {
      params.location_id = userLocationId
      console.log('Loading dashboard with location_id:', userLocationId)
    }
    
    stats.value = await dashboardStore.fetchDashboard(params)
    console.log('Dashboard stats loaded:', stats.value)
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  }
}

const handleOutletChange = (outletId) => {
  currentOutletId.value = outletId
  if (outletId) {
    loadDashboard(outletId)
  }
}

onMounted(async () => {
  const userOutletId = authStore.user?.outlet_id
  const userLocationId = authStore.user?.location_id
  
  // Load user location info if available (to check if FNB type)
  if (userLocationId) {
    try {
      const response = await api.get(`/locations/${userLocationId}`)
      userLocation.value = response.data
      console.log('User location type:', userLocation.value?.type)
    } catch (error) {
      console.error('Failed to load user location:', error)
    }
  }
  
  if (userOutletId) {
    currentOutletId.value = userOutletId
    await loadDashboard(userOutletId)
  } else if (isOwner.value) {
    // Owner will select outlet via OutletSelector
    const savedOutlet = localStorage.getItem('owner_selected_outlet')
    if (savedOutlet) {
      currentOutletId.value = savedOutlet
      await loadDashboard(savedOutlet)
    }
  }
})
</script>
