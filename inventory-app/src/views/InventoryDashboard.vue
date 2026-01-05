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
            <p class="text-gray-600 text-sm">Total Products</p>
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
            <p class="text-gray-600 text-sm">Low Stock Items</p>
            <p class="text-2xl font-bold text-orange-600">{{ stats.lowStockCount }}</p>
          </div>
          <div class="bg-orange-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
            <p class="text-gray-600 text-sm">Pending Transfers</p>
            <p class="text-2xl font-bold text-purple-600">{{ stats.pendingTransfers }}</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <router-link to="/inventory/stocks" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow p-6 hover:from-blue-600 hover:to-blue-700 transition">
        <h3 class="text-lg font-semibold mb-2">Stock Levels</h3>
        <p class="text-blue-100 text-sm">View and manage inventory stock levels</p>
      </router-link>

      <router-link to="/inventory/transfers" class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow p-6 hover:from-green-600 hover:to-green-700 transition">
        <h3 class="text-lg font-semibold mb-2">Stock Transfers</h3>
        <p class="text-green-100 text-sm">Transfer stock between locations</p>
      </router-link>

      <router-link to="/inventory/adjustments" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg shadow p-6 hover:from-orange-600 hover:to-orange-700 transition">
        <h3 class="text-lg font-semibold mb-2">Stock Adjustments</h3>
        <p class="text-orange-100 text-sm">Make manual stock corrections</p>
      </router-link>
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
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const stats = ref({
  totalProducts: 0,
  lowStockCount: 0,
  totalLocations: 0,
  pendingTransfers: 0
})

const lowStockItems = ref([])

onMounted(async () => {
  await loadDashboardData()
})

const loadDashboardData = async () => {
  try {
    // Load low stock items
    const { data: lowStock } = await api.get('/inventory-stocks/low-stock')
    lowStockItems.value = lowStock.slice(0, 5) // Show top 5
    stats.value.lowStockCount = lowStock.length

    // Load locations
    const { data: locations } = await api.get('/locations')
    stats.value.totalLocations = locations.length

    // Load transfers
    const { data: transfers } = await api.get('/inventory-transfers?status=PENDING')
    stats.value.pendingTransfers = transfers.data?.length || 0

    // Load stocks for product count
    const { data: stocks } = await api.get('/inventory-stocks')
    const uniqueProducts = new Set(stocks.map(s => s.product_id))
    stats.value.totalProducts = uniqueProducts.size
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
}
</script>
