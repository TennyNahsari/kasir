<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Procurement Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Pending PR</p>
            <p class="text-3xl font-bold text-blue-600">{{ stats.pendingPR }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Active PO</p>
            <p class="text-3xl font-bold text-green-600">{{ stats.activePO }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Pending GRN</p>
            <p class="text-3xl font-bold text-purple-600">{{ stats.pendingGRN }}</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Active Vendors</p>
            <p class="text-3xl font-bold text-orange-600">{{ stats.activeVendors }}</p>
          </div>
          <div class="bg-orange-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link to="/procurement/purchase-requests/create" class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
          <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">New Purchase Request</span>
        </router-link>

        <router-link to="/procurement/purchase-orders" class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition">
          <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">Purchase Orders</span>
        </router-link>

        <router-link to="/procurement/goods-receipts" class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition">
          <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">Goods Receipts</span>
        </router-link>

        <router-link to="/procurement/vendors" class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition">
          <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">Vendors</span>
        </router-link>
      </div>
    </div>

    <!-- Recent Purchase Requests -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
      <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-xl font-semibold">Recent Purchase Requests</h2>
        <router-link to="/procurement/purchase-requests" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</router-link>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PR Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested By</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="pr in recentPRs" :key="pr.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ pr.pr_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(pr.request_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ pr.requested_by_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(pr.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ pr.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <router-link :to="`/procurement/purchase-requests/${pr.id}`" class="text-blue-600 hover:text-blue-900">View</router-link>
              </td>
            </tr>
            <tr v-if="recentPRs.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent purchase requests</td>
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
  pendingPR: 0,
  activePO: 0,
  pendingGRN: 0,
  activeVendors: 0
})

const recentPRs = ref([])

onMounted(async () => {
  await loadStats()
  await loadRecentPRs()
})

const loadStats = async () => {
  try {
    // Count pending PRs
    const { data: prs } = await api.get('/purchase-requests', { params: { status: 'SUBMITTED' } })
    stats.value.pendingPR = prs.length

    // Count active POs
    const { data: pos } = await api.get('/purchase-orders', { params: { status: 'APPROVED,SENT' } })
    stats.value.activePO = pos.length

    // Count pending GRNs
    const { data: grns } = await api.get('/goods-receipts', { params: { status: 'DRAFT,QC_PENDING' } })
    stats.value.pendingGRN = grns.length

    // Count active vendors
    const { data: vendors } = await api.get('/vendors')
    stats.value.activeVendors = vendors.filter(v => v.is_active).length
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
}

const loadRecentPRs = async () => {
  try {
    const { data } = await api.get('/purchase-requests', { params: { limit: 5 } })
    // Handle Laravel pagination response
    const prs = data.data || data || []
    recentPRs.value = Array.isArray(prs) ? prs.slice(0, 5) : []
  } catch (error) {
    console.error('Failed to load recent PRs:', error)
    recentPRs.value = []
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'SUBMITTED': 'bg-blue-100 text-blue-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'REJECTED': 'bg-red-100 text-red-800',
    'CANCELLED': 'bg-red-100 text-red-800',
    'PARTIALLY_ORDERED': 'bg-yellow-100 text-yellow-800',
    'FULLY_ORDERED': 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
</script>
