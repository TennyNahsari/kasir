<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Inventory Dashboard</h1>
      <p class="text-gray-600">Manage your stock levels across all locations</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-1">
            <p class="text-gray-600 text-sm">Total Master Product</p>
            <p class="text-2xl font-bold text-gray-800">{{ stats.totalProducts }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-1">
            <p class="text-gray-600 text-sm">Total Asset</p>
            <p class="text-2xl font-bold text-orange-600">{{ stats.totalAssets }}</p>
          </div>
          <div class="bg-orange-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-1">
            <p class="text-gray-600 text-sm">Total Locations</p>
            <p class="text-2xl font-bold text-gray-800">{{ stats.totalLocations }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-1">
            <p class="text-gray-600 text-sm">Total Services</p>
            <p class="text-2xl font-bold text-purple-600">{{ stats.totalServices }}</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Expiring Service Contracts -->
    <div v-if="expiringServices.length > 0" class="bg-white rounded-lg shadow mb-8">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-800">Service Contracts Expiring Soon</h2>
          <router-link to="/services" class="text-sm text-purple-600 hover:text-purple-800">
            View All →
          </router-link>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contract</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product/Service</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Left</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="contract in expiringServices" :key="contract.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ contract.contract_number }}</div>
                <div class="text-xs text-gray-500">{{ formatContractType(contract.contract_type) }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ contract.product?.name || 'N/A' }}</div>
                <div class="text-xs text-gray-500">{{ contract.product?.sku || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ contract.vendor?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ contract.pic || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(contract.end_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getDaysLeftClass(contract.days_until_expiry)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ contract.days_until_expiry }} days
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <router-link :to="`/services/${contract.id}`" class="text-purple-600 hover:text-purple-900">
                  View Details
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Low Stock Alerts -->
    <div v-if="lowStockItems.length > 0" class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Low Stock Alerts</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in lowStockItems" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                <div class="text-sm text-gray-500">{{ item.sku }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.location_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.quantity }} {{ item.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.reorder_level }} {{ item.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                  Low Stock
                </span>
              </td>
            </tr>
            <tr v-if="lowStockItems.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">No low stock items</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="lowStockPagination.total > lowStockPagination.per_page" class="px-6 py-4 border-t flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ lowStockPagination.from }} to {{ lowStockPagination.to }} of {{ lowStockPagination.total }} items
        </div>
        <div class="flex gap-2">
          <button 
            @click="loadLowStockItems(lowStockPagination.current_page - 1)"
            :disabled="lowStockPagination.current_page === 1"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <span class="px-3 py-1 text-sm">
            Page {{ lowStockPagination.current_page }} of {{ lowStockPagination.last_page }}
          </span>
          <button 
            @click="loadLowStockItems(lowStockPagination.current_page + 1)"
            :disabled="lowStockPagination.current_page === lowStockPagination.last_page"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import serviceService from '@/services/serviceService'

const router = useRouter()

const stats = ref({
  totalProducts: 0,
  totalAssets: 0,
  totalLocations: 0,
  totalServices: 0
})

const lowStockItems = ref([])
const expiringServices = ref([])
const lowStockPagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

onMounted(async () => {
  await loadDashboardData()
  await loadLowStockItems()
  await loadExpiringServices()
})

const loadDashboardData = async () => {
  try {
    // Load locations
    const { data: locations } = await api.get('/locations')
    stats.value.totalLocations = locations.length

    // Load products directly from products endpoint
    const { data: productsResponse } = await api.get('/products')
    // Handle both array and paginated response
    const products = productsResponse.data || productsResponse || []
    stats.value.totalProducts = Array.isArray(products) ? products.length : (productsResponse.total || 0)
    
    // Load assets
    const { data: assetsResponse } = await api.get('/assets')
    const assets = assetsResponse.data || assetsResponse || []
    stats.value.totalAssets = Array.isArray(assets) ? assets.length : (assetsResponse.total || 0)

    // Load services
    const { data: servicesResponse } = await serviceService.getAll()
    stats.value.totalServices = servicesResponse.total || 0
    
    console.log('Dashboard stats loaded:', stats.value)
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
}

const loadLowStockItems = async (page = 1) => {
  try {
    const { data } = await api.get('/inventory-stocks/low-stock', { 
      params: { 
        page: page,
        per_page: 10
      } 
    })
    
    // Handle both array and paginated response
    if (Array.isArray(data)) {
      // If response is plain array, paginate manually
      const perPage = 10
      const start = (page - 1) * perPage
      const end = start + perPage
      lowStockItems.value = data.slice(start, end)
      lowStockPagination.value = {
        current_page: page,
        last_page: Math.ceil(data.length / perPage),
        per_page: perPage,
        total: data.length,
        from: start + 1,
        to: Math.min(end, data.length)
      }
    } else {
      // If response is paginated
      lowStockItems.value = data.data || []
      lowStockPagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        per_page: data.per_page || 10,
        total: data.total || 0,
        from: data.from || 0,
        to: data.to || 0
      }
    }
  } catch (error) {
    console.error('Failed to load low stock items:', error)
    lowStockItems.value = []
  }
}

const loadExpiringServices = async () => {
  try {
    const { data } = await serviceService.getAll({ 
      status: 'ACTIVE',
      expiring_days: 30,
      per_page: 10
    })
    expiringServices.value = data.data || []
  } catch (error) {
    console.error('Failed to load expiring services:', error)
    expiringServices.value = []
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatContractType = (type) => {
  const types = {
    'RENTAL': 'Rental',
    'SUBSCRIPTION': 'Subscription',
    'MAINTENANCE': 'Maintenance',
    'CONSULTING': 'Consulting',
    'UTILITY': 'Utility',
    'OTHER': 'Other'
  }
  return types[type] || type
}

const getDaysLeftClass = (days) => {
  if (days <= 7) return 'bg-red-100 text-red-800'
  if (days <= 14) return 'bg-orange-100 text-orange-800'
  return 'bg-yellow-100 text-yellow-800'
}
</script>
