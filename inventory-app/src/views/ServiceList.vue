<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Service Contracts</h1>
        <p class="text-gray-600">Track and manage service contracts, rentals, and subscriptions</p>
      </div>
      <button @click="showCreateModal = true" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Service Contract
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ stats.total_active || 0 }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Expiring Soon</p>
            <p class="text-2xl font-bold text-yellow-600">{{ stats.expiring_soon || 0 }}</p>
          </div>
          <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Pending</p>
            <p class="text-2xl font-bold text-blue-600">{{ stats.total_pending || 0 }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Expired</p>
            <p class="text-2xl font-bold text-red-600">{{ stats.expired || 0 }}</p>
          </div>
          <div class="bg-red-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
          <input v-model="filters.search" type="text" placeholder="Contract number..."
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="ACTIVE">Active</option>
            <option value="PENDING">Pending</option>
            <option value="EXPIRED">Expired</option>
            <option value="TERMINATED">Terminated</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Contract Type</label>
          <select v-model="filters.contract_type"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">All Types</option>
            <option value="RENTAL">Rental</option>
            <option value="SUBSCRIPTION">Subscription</option>
            <option value="MAINTENANCE">Maintenance</option>
            <option value="CONSULTING">Consulting</option>
            <option value="UTILITY">Utility</option>
            <option value="OTHER">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Vendor</label>
          <select v-model="filters.vendor_id"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">All Vendors</option>
            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
              {{ vendor.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="flex justify-between items-center mt-4">
        <button @click="resetFilters" class="text-purple-600 hover:text-purple-800">
          Reset Filters
        </button>
        <button @click="loadContracts" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
          Apply Filters
        </button>
      </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product/Service</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start - End</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="contracts.length === 0">
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">No contracts found</td>
            </tr>
            <tr v-else v-for="contract in contracts" :key="contract.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ contract.contract_number }}</div>
                <div v-if="contract.is_expiring_soon" class="text-xs text-yellow-600 font-medium mt-1">
                  ⚠️ Expires in {{ contract.days_until_expiry }} days
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ contract.product?.name || 'N/A' }}</div>
                <div class="text-xs text-gray-500">{{ contract.product?.sku || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ contract.vendor?.name || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ contract.pic || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="badge badge-gray text-xs">{{ formatContractType(contract.contract_type) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(contract.start_date) }}</div>
                <div class="text-xs text-gray-500">{{ formatDate(contract.end_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ formatCurrency(contract.contract_value) }}</div>
                <div class="text-xs text-gray-500">{{ contract.billing_cycle }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(contract.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ contract.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="viewContract(contract.id)" class="text-purple-600 hover:text-purple-900">
                  View Details
                </button>
                <!-- Debug info -->
                <span class="text-xs text-gray-400 ml-2">(ID: {{ contract.id }})</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} contracts
        </div>
        <div class="flex space-x-2">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            Previous
          </button>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create Service Contract Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4">Create Service Contract</h3>
        <form @submit.prevent="createContract">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Product/Service *</label>
              <select v-model="createForm.product_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">-- Select Product --</option>
                <option v-for="product in serviceProducts" :key="product.id" :value="product.id">
                  {{ product.name }} ({{ product.sku }})
                </option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Vendor *</label>
              <select v-model="createForm.vendor_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">-- Select Vendor --</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
              <select v-model="createForm.location_id"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">-- Select Location (Optional) --</option>
                <option v-for="location in locations" :key="location.id" :value="location.id">
                  {{ location.name }}
                </option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">PIC (Person In Charge)</label>
              <input v-model="createForm.pic" type="text" placeholder="Name of person in charge..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contract Type *</label>
              <select v-model="createForm.contract_type" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">-- Select Type --</option>
                <option value="RENTAL">Rental (Sewa)</option>
                <option value="SUBSCRIPTION">Subscription (Langganan)</option>
                <option value="MAINTENANCE">Maintenance (Pemeliharaan)</option>
                <option value="CONSULTING">Consulting (Konsultasi)</option>
                <option value="UTILITY">Utility (Listrik, Air, Internet)</option>
                <option value="OTHER">Other (Lainnya)</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle *</label>
              <select v-model="createForm.billing_cycle" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="MONTHLY">Monthly</option>
                <option value="QUARTERLY">Quarterly</option>
                <option value="YEARLY">Yearly</option>
                <option value="ONE_TIME">One Time</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
              <input v-model="createForm.start_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
              <input v-model="createForm.end_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Contract Value *</label>
              <input v-model.number="createForm.contract_value" type="number" step="0.01" required min="0"
                placeholder="0.00"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea v-model="createForm.notes" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="creating" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 disabled:opacity-50">
              {{ creating ? 'Creating...' : 'Create Contract' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import serviceService from '@/services/serviceService'
import api from '@/services/api'

const router = useRouter()

const contracts = ref([])
const loading = ref(false)
const vendors = ref([])
const locations = ref([])
const serviceProducts = ref([])
const stats = ref({})
const showCreateModal = ref(false)
const creating = ref(false)

const filters = ref({
  search: '',
  status: '',
  contract_type: '',
  vendor_id: ''
})

const createForm = ref({
  product_id: '',
  vendor_id: '',
  location_id: '',
  pic: '',
  contract_type: '',
  billing_cycle: 'MONTHLY',
  start_date: new Date().toISOString().split('T')[0],
  end_date: '',
  contract_value: 0,
  notes: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
})

onMounted(async () => {
  await Promise.all([
    loadContracts(),
    loadVendors(),
    loadLocations(),
    loadServiceProducts(),
    loadStats()
  ])
})

watch(filters, () => {
  pagination.value.current_page = 1
}, { deep: true })

const loadContracts = async () => {
  loading.value = true
  try {
    // Clean up filters - remove empty values
    const cleanFilters = Object.entries(filters.value).reduce((acc, [key, value]) => {
      if (value !== '' && value !== null && value !== undefined) {
        acc[key] = value
      }
      return acc
    }, {})
    
    const params = {
      ...cleanFilters,
      page: pagination.value.current_page,
      per_page: 15
    }
    
    console.log('Request params:', params)
    const { data } = await serviceService.getAll(params)
    console.log('Service contracts response:', data)
    
    // Handle both paginated and direct array response
    if (data.data && Array.isArray(data.data)) {
      contracts.value = data.data
      pagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        from: data.from || 0,
        to: data.to || 0,
        total: data.total || 0
      }
    } else if (Array.isArray(data)) {
      contracts.value = data
      pagination.value = {
        current_page: 1,
        last_page: 1,
        from: 1,
        to: data.length,
        total: data.length
      }
    } else {
      contracts.value = []
      console.error('Unexpected data format:', data)
    }
    
    console.log('Contracts loaded:', contracts.value)
    // Debug: Log all contract IDs
    console.log('Contract IDs:', contracts.value.map(c => ({ id: c.id, contract_number: c.contract_number, status: c.status })))
  } catch (error) {
    console.error('Failed to load contracts:', error)
    console.error('Error response:', error.response?.data)
    alert('Failed to load service contracts: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const loadVendors = async () => {
  try {
    const { data } = await api.get('/vendors')
    vendors.value = Array.isArray(data) ? data : (data.data || [])
  } catch (error) {
    console.error('Failed to load vendors:', error)
  }
}

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = Array.isArray(data) ? data : (data.data || [])
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadServiceProducts = async () => {
  try {
    const { data } = await api.get('/products', { params: { type: 'SERVICE', per_page: 100 } })
    serviceProducts.value = data.data || []
  } catch (error) {
    console.error('Failed to load service products:', error)
  }
}

const loadStats = async () => {
  try {
    const { data } = await serviceService.getStats()
    stats.value = data
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    contract_type: '',
    vendor_id: ''
  }
  loadContracts()
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadContracts()
}

const viewContract = (id) => {
  console.log('Navigating to service contract ID:', id, 'Type:', typeof id)
  router.push(`/services/${id}`)
}

const createContract = async () => {
  // Validate end date is after start date
  if (new Date(createForm.value.end_date) <= new Date(createForm.value.start_date)) {
    alert('End date must be after start date')
    return
  }
  
  creating.value = true
  try {
    await serviceService.create(createForm.value)
    alert('Service contract created successfully!')
    closeCreateModal()
    await loadContracts()
    await loadStats()
  } catch (error) {
    console.error('Failed to create contract:', error)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || error.message
    alert('Failed to create contract: ' + errorMsg)
  } finally {
    creating.value = false
  }
}

const closeCreateModal = () => {
  showCreateModal.value = false
  // Reset form
  createForm.value = {
    product_id: '',
    vendor_id: '',
    location_id: '',
    pic: '',
    contract_type: '',
    billing_cycle: 'MONTHLY',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    contract_value: 0,
    notes: ''
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
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

const getStatusClass = (status) => {
  const classes = {
    'ACTIVE': 'bg-green-100 text-green-800',
    'PENDING': 'bg-blue-100 text-blue-800',
    'EXPIRED': 'bg-red-100 text-red-800',
    'TERMINATED': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>

<style scoped>
.badge {
  @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium;
}

.badge-gray {
  @apply bg-gray-100 text-gray-800;
}
</style>
