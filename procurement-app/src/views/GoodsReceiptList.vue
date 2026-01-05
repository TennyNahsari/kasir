<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Goods Receipt Notes</h1>
        <p class="text-gray-600">Receive goods from vendors</p>
      </div>
      <button @click="$router.push('/procurement/goods-receipts/create')" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
        Create GRN
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="loadGRNs" class="w-full border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option value="DRAFT">Draft</option>
            <option value="QC_PENDING">QC Pending</option>
            <option value="QC_APPROVED">QC Approved</option>
            <option value="POSTED">Posted</option>
            <option value="REJECTED">Rejected</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
          <input v-model="filters.po_number" @input="loadGRNs" type="text" placeholder="Search PO..." class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input v-model="filters.from_date" @change="loadGRNs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input v-model="filters.to_date" @change="loadGRNs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
      </div>
    </div>

    <!-- GRNs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="grn in grns" :key="grn.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ grn.grn_number }}</div>
                <div class="text-xs text-gray-500" v-if="grn.invoice_number">Invoice: {{ grn.invoice_number }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(grn.receipt_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grn.po_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grn.vendor_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(grn.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ grn.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="viewGRN(grn)" class="text-blue-600 hover:text-blue-900">View</button>
              </td>
            </tr>
            <tr v-if="grns.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                No goods receipts found
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

const grns = ref([])
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
  po_number: '',
  from_date: '',
  to_date: ''
})

onMounted(async () => {
  await loadGRNs()
})

const loadGRNs = async (page = 1) => {
  try {
    const params = { page }
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.po_number) params.po_number = filters.value.po_number
    if (filters.value.from_date) params.from_date = filters.value.from_date
    if (filters.value.to_date) params.to_date = filters.value.to_date

    const { data } = await api.get('/goods-receipts', { params })
    // Handle Laravel pagination response
    if (data.data) {
      grns.value = data.data
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
      grns.value = data || []
    }
  } catch (error) {
    console.error('Failed to load GRNs:', error)
    grns.value = []
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'QUALITY_CHECK': 'bg-yellow-100 text-yellow-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'POSTED': 'bg-blue-100 text-blue-800',
    'REJECTED': 'bg-red-100 text-red-800'
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

const viewGRN = (grn) => {
  router.push(`/procurement/goods-receipts/${grn.id}`)
}

const goToPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  loadGRNs(page)
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
