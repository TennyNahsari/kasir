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

    <!-- Expected & Overdue Deliveries -->
    <div v-if="expectedDeliveries.length > 0" class="bg-white rounded-lg shadow overflow-hidden mb-6">
      <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-xl font-semibold">Expected & Overdue Deliveries</h2>
        <span class="text-sm text-gray-600">{{ expectedDeliveries.length }} PO(s)</span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="delivery in expectedDeliveries" :key="delivery.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="delivery.priority === 'overdue'" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                  🔴 {{ Math.abs(delivery.days_overdue) }}d late
                </span>
                <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                  🟡 Today
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ delivery.po_number }}</div>
                <div class="text-xs text-gray-500">{{ delivery.items_count }} items</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ delivery.vendor_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ delivery.location_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(delivery.expected_delivery_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="!delivery.has_grn" class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                  No GRN
                </span>
                <span v-else-if="delivery.grn_status === 'DRAFT'" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                  Draft
                </span>
                <span v-else-if="delivery.grn_status === 'QUALITY_CHECK'" class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                  QC Pending
                </span>
                <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                  {{ delivery.grn_status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                Rp {{ Number(delivery.total_amount || 0).toLocaleString('id-ID') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button v-if="!delivery.has_grn" @click="createGRNFromPO(delivery.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                  Create GRN
                </button>
                <button @click="viewPO(delivery.id)" class="text-blue-600 hover:text-blue-900">
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Purchase Requests -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
      <div class="px-6 py-4 border-b flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold">Recent Purchase Requests</h2>
          <p class="text-sm text-gray-600 mt-1">PRs pending supervisor approval</p>
        </div>
        <router-link to="/procurement/purchase-requests" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</router-link>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PR Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested By</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="pr in recentPRs" :key="pr.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ pr.pr_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(pr.request_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ pr.location?.name || '-' }}</td>
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
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">No pending purchase requests</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="prPagination.total > prPagination.per_page" class="px-6 py-4 border-t flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ prPagination.from }} to {{ prPagination.to }} of {{ prPagination.total }} PRs
        </div>
        <div class="flex gap-2">
          <button 
            @click="loadRecentPRs(prPagination.current_page - 1)"
            :disabled="prPagination.current_page === 1"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <span class="px-3 py-1 text-sm">
            Page {{ prPagination.current_page }} of {{ prPagination.last_page }}
          </span>
          <button 
            @click="loadRecentPRs(prPagination.current_page + 1)"
            :disabled="prPagination.current_page === prPagination.last_page"
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

const router = useRouter()

const stats = ref({
  pendingPR: 0,
  activePO: 0,
  pendingGRN: 0,
  activeVendors: 0
})

const recentPRs = ref([])
const expectedDeliveries = ref([])
const prPagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

onMounted(async () => {
  await loadStats()
  await loadRecentPRs()
  await loadExpectedDeliveries()
})

const loadStats = async () => {
  try {
    // Use dedicated procurement stats endpoint with role-based filtering
    const { data } = await api.get('/dashboard/procurement-stats')
    stats.value.pendingPR = data.pendingPR || 0
    stats.value.activePO = data.activePO || 0
    stats.value.pendingGRN = data.pendingGRN || 0

    // Count active vendors
    const { data: vendors } = await api.get('/vendors')
    stats.value.activeVendors = vendors.filter(v => v.is_active).length
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
}

const loadRecentPRs = async (page = 1) => {
  try {
    const { data } = await api.get('/dashboard/recent-purchase-requests', { 
      params: { 
        per_page: 10,
        page: page
      } 
    })
    
    // Handle Laravel pagination response
    recentPRs.value = data.data || []
    prPagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      per_page: data.per_page || 10,
      total: data.total || 0,
      from: data.from || 0,
      to: data.to || 0
    }
  } catch (error) {
    console.error('Failed to load recent PRs:', error)
    recentPRs.value = []
  }
}

const loadExpectedDeliveries = async () => {
  try {
    const { data } = await api.get('/dashboard/expected-deliveries')
    expectedDeliveries.value = data || []
  } catch (error) {
    console.error('Failed to load expected deliveries:', error)
    expectedDeliveries.value = []
  }
}

const viewPO = (poId) => {
  router.push(`/procurement/purchase-orders/${poId}`)
}

const createGRNFromPO = (poId) => {
  router.push(`/procurement/goods-receipts/create?po_id=${poId}`)
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
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
</script>
