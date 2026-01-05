<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Purchase Orders</h1>
        <p class="text-gray-600">Manage purchase orders to vendors</p>
      </div>
      <button @click="$router.push('/procurement/purchase-orders/create')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
        Create PO
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="loadPOs" class="w-full border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option value="DRAFT">Draft</option>
            <option value="SUBMITTED">Submitted</option>
            <option value="APPROVED">Approved</option>
            <option value="SENT">Sent to Vendor</option>
            <option value="CANCELLED">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
          <select v-model="filters.vendor_id" @change="loadPOs" class="w-full border-gray-300 rounded-lg">
            <option value="">All Vendors</option>
            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">{{ vendor.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input v-model="filters.from_date" @change="loadPOs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input v-model="filters.to_date" @change="loadPOs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
      </div>
    </div>

    <!-- POs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="po in pos" :key="po.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ po.po_number }}</div>
                <div class="text-xs text-gray-500" v-if="po.pr_number">PR: {{ po.pr_number }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(po.po_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ po.vendor_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(po.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ po.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                Rp {{ Number(po.total_amount).toLocaleString('id-ID') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="viewPO(po)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                <button v-if="po.status === 'DRAFT'" @click="deletePO(po)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
            <tr v-if="pos.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                No purchase orders found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200">
        <div class="flex-1 flex justify-between sm:hidden">
          <button @click="goToPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" :class="{'opacity-50 cursor-not-allowed': !pagination.prev_page_url}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</button>
          <button @click="goToPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url" :class="{'opacity-50 cursor-not-allowed': !pagination.next_page_url}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ pagination.from || 0 }}</span> to <span class="font-medium">{{ pagination.to || 0 }}</span> of <span class="font-medium">{{ pagination.total || 0 }}</span> results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button @click="goToPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" :class="{'opacity-50 cursor-not-allowed': !pagination.prev_page_url}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>
              <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="page === pagination.current_page ? 'bg-blue-50 border-blue-500 text-blue-600 z-10' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">{{ page }}</button>
              <button @click="goToPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url" :class="{'opacity-50 cursor-not-allowed': !pagination.next_page_url}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const pos = ref([])
const vendors = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null
})

const filters = ref({
  status: '',
  vendor_id: '',
  from_date: '',
  to_date: ''
})

onMounted(async () => {
  await loadVendors()
  await loadPOs()
})

const loadVendors = async () => {
  try {
    const { data } = await api.get('/vendors')
    vendors.value = data
  } catch (error) {
    console.error('Failed to load vendors:', error)
  }
}

const loadPOs = async (page = 1) => {
  try {
    const params = { page }
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.vendor_id) params.vendor_id = filters.value.vendor_id
    if (filters.value.from_date) params.from_date = filters.value.from_date
    if (filters.value.to_date) params.to_date = filters.value.to_date

    const { data } = await api.get('/purchase-orders', { params })
    // Handle Laravel pagination response
    if (data.data) {
      pos.value = data.data
      pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total,
        from: data.from,
        to: data.to,
        prev_page_url: data.prev_page_url,
        next_page_url: data.next_page_url
      }
    } else {
      pos.value = data || []
    }
  } catch (error) {
    console.error('Failed to load POs:', error)
    pos.value = []
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'SUBMITTED': 'bg-blue-100 text-blue-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'SENT': 'bg-purple-100 text-purple-800',
    'CANCELLED': 'bg-red-100 text-red-800'
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

const viewPO = (po) => {
  router.push(`/procurement/purchase-orders/${po.id}`)
}

const deletePO = async (po) => {
  if (!confirm(`Are you sure you want to delete PO ${po.po_number}?`)) return
  
  try {
    await api.delete(`/purchase-orders/${po.id}`)
    alert('Purchase Order deleted successfully')
    await loadPOs(pagination.value.current_page)
  } catch (error) {
    alert('Failed to delete PO: ' + (error.response?.data?.message || error.message))
  }
}

const goToPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  loadPOs(page)
}

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i)
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    }
  }
  
  return pages.filter(p => p !== '...' || typeof p === 'string')
})
</script>
