<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Inventory Transfers</h1>
        <p class="text-gray-600">Move stock between locations</p>
      </div>
      <button @click="showCreateModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Create Transfer
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="loadTransfers" class="w-full border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option value="DRAFT">Draft</option>
            <option value="PENDING">Pending</option>
            <option value="IN_TRANSIT">In Transit</option>
            <option value="RECEIVED">Received</option>
            <option value="CANCELLED">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Location</label>
          <select v-model="filters.from_location" @change="loadTransfers" class="w-full border-gray-300 rounded-lg">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Location</label>
          <select v-model="filters.to_location" @change="loadTransfers" class="w-full border-gray-300 rounded-lg">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @input="loadTransfers" type="text" placeholder="Transfer number..." class="w-full border-gray-300 rounded-lg">
        </div>
      </div>
    </div>

    <!-- Transfers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transfer #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From → To</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transfer in transfers" :key="transfer?.id || Math.random()" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ transfer?.transfer_no || '-' }}</div>
                <div class="text-xs text-gray-500">{{ transfer?.requested_by?.name || '-' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ transfer?.from_location?.name || '-' }}</div>
                <div class="text-sm text-gray-500">↓ {{ transfer?.to_location?.name || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transfer?.transfer_date ? formatDate(transfer.transfer_date) : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(transfer?.status || 'DRAFT')" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ transfer?.status || 'DRAFT' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transfer?.items?.length || 0 }} item(s)
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="viewTransfer(transfer)" class="text-blue-600 hover:text-blue-900">View</button>
              </td>
            </tr>
            <tr v-if="transfers.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                No transfers found
              </td>
            </tr>
          </tbody>
        </table>
      </div>      <Pagination :pagination="pagination" @page-change="changePage" />    </div>

    <!-- Create Transfer Modal -->
    <div v-if="showCreateModal" @click="closeCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col" @click.stop>
        <div class="flex justify-between items-center p-6 border-b">
          <h3 class="text-lg font-semibold">Create Transfer</h3>
          <button @click.stop="closeCreateModal" type="button" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Location</label>
                <select v-model="createForm.from_location_id" class="w-full border-gray-300 rounded-lg" required>
                  <option value="">Select Location</option>
                  <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Location</label>
                <select v-model="createForm.to_location_id" class="w-full border-gray-300 rounded-lg" required>
                  <option value="">Select Location</option>
                  <option v-for="loc in locations" :key="loc.id" :value="loc.id" :disabled="loc.id === createForm.from_location_id">{{ loc.name }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Date</label>
              <input v-model="createForm.transfer_date" type="date" class="w-full border-gray-300 rounded-lg" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="createForm.notes" rows="2" class="w-full border-gray-300 rounded-lg"></textarea>
            </div>

            <!-- Items -->
            <div class="border-t pt-4">
              <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium">Transfer Items</h4>
                <button 
                  @click.stop.prevent="addItem" 
                  type="button" 
                  class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                >
                  + Add Item
                </button>
              </div>
              <div v-if="createForm.items.length === 0" class="text-center py-4 text-gray-500 text-sm">
                No items added. Click "+ Add Item" to start.
              </div>
              <div v-for="(item, index) in createForm.items" :key="index" class="grid grid-cols-12 gap-2 mb-3 relative">
                <div class="col-span-6 relative">
                  <input v-model="item.product_search" @input="searchProductsForItem(index)" type="text" placeholder="Search product..." class="w-full border-gray-300 rounded-lg text-sm">
                  <div v-if="item.searchResults && item.searchResults.length > 0" class="absolute z-10 mt-1 left-0 right-0 bg-white border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                    <div v-for="product in item.searchResults.filter(p => p && p.id)" :key="product.id" @click="selectProductForItem(index, product)" class="p-2 hover:bg-gray-100 cursor-pointer text-sm">
                      {{ product.name }} ({{ product.sku }})
                    </div>
                  </div>
                </div>
                <div class="col-span-2">
                  <input v-model.number="item.quantity" type="number" step="0.01" min="0.01" placeholder="Qty" class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                <div class="col-span-3">
                  <input v-model="item.notes" type="text" placeholder="Notes (optional)" class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                <div class="col-span-1 flex items-center justify-center">
                  <button @click.stop="removeItem(index)" type="button" class="text-red-600 hover:text-red-700 text-xl font-bold cursor-pointer">×</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="border-t p-6 flex justify-end space-x-3">
          <button @click.stop="closeCreateModal" type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click.stop="submitTransfer(false)" type="button" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Save Draft</button>
          <button @click.stop="submitTransfer(true)" type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create & Submit</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'

const router = useRouter()

const transfers = ref([])
const locations = ref([])
const showModal = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  status: '',
  from_location: '',
  to_location: '',
  search: ''
})

const createForm = ref({
  from_location_id: '',
  to_location_id: '',
  transfer_date: new Date().toISOString().split('T')[0],
  notes: '',
  items: []
})

onMounted(async () => {
  await loadLocations()
  await loadTransfers()
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadTransfers = async () => {
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.from_location) params.from_location_id = filters.value.from_location
    if (filters.value.to_location) params.to_location_id = filters.value.to_location
    if (filters.value.search) params.search = filters.value.search

    console.log('Loading transfers with params:', params)
    const { data } = await api.get('/inventory-transfers', { params })
    console.log('Transfers response:', data)
    
    // Handle both array response and paginated response
    transfers.value = Array.isArray(data) ? data : (data.data || [])
    console.log('Transfers set to:', transfers.value)
  } catch (error) {
    console.error('Failed to load transfers:', error)
    transfers.value = []
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'SUBMITTED': 'bg-blue-100 text-blue-800',
    'APPROVED': 'bg-purple-100 text-purple-800',
    'COMPLETED': 'bg-green-100 text-green-800',
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

const addItem = () => {
  console.log('=== ADD ITEM CLICKED ===')
  console.log('Current items:', createForm.value.items)
  
  const newItem = {
    product_id: null,
    product_search: '',
    quantity: 1,
    notes: '',
    searchResults: []
  }
  
  createForm.value.items = [...createForm.value.items, newItem]
  
  console.log('After push, items count:', createForm.value.items.length)
  console.log('Items:', createForm.value.items)
}

const removeItem = (index) => {
  createForm.value.items.splice(index, 1)
}

const searchProductsForItem = async (index) => {
  const item = createForm.value.items[index]
  if (item.product_search.length < 2) {
    item.searchResults = []
    return
  }
  try {
    const { data } = await api.get('/products', { params: { search: item.product_search } })
    // Handle paginated response
    item.searchResults = (data.data || data).slice(0, 5)
  } catch (error) {
    console.error('Failed to search products:', error)
  }
}

const selectProductForItem = (index, product) => {
  const item = createForm.value.items[index]
  item.product_id = product.id
  item.product_search = product.name
  item.searchResults = []
}

const submitTransfer = async (shouldSubmit) => {
  try {
    if (!createForm.value.from_location_id || !createForm.value.to_location_id) {
      alert('Please select both locations')
      return
    }
    if (createForm.value.items.length === 0) {
      alert('Please add at least one item')
      return
    }

    // Validate all items have product selected
    const invalidItems = createForm.value.items.filter(item => !item.product_id)
    if (invalidItems.length > 0) {
      alert('Please select a product for all items')
      return
    }

    const payload = {
      from_location_id: createForm.value.from_location_id,
      to_location_id: createForm.value.to_location_id,
      transfer_date: createForm.value.transfer_date,
      notes: createForm.value.notes,
      items: createForm.value.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        notes: item.notes
      }))
    }

    console.log('Submit payload:', payload)

    const { data } = await api.post('/inventory-transfers', payload)
    console.log('Transfer created, response:', data)
    
    const transferId = data.id || data.data?.id
    if (!transferId) {
      throw new Error('Transfer ID not received from server')
    }
    
    if (shouldSubmit) {
      console.log('Submitting transfer:', transferId)
      await api.post(`/inventory-transfers/${transferId}/submit`)
    }

    alert('Transfer created successfully')
    closeCreateModal()
    await loadTransfers()
  } catch (error) {
    console.error('Submit error:', error.response?.data || error)
    alert('Failed to create transfer: ' + (error.response?.data?.message || error.message))
  }
}

const closeCreateModal = () => {
  console.log('Closing modal...')
  createForm.value = {
    from_location_id: '',
    to_location_id: '',
    transfer_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: []
  }
  showCreateModal.value = false
}

const viewTransfer = (transfer) => {
  router.push(`/inventory/transfers/${transfer.id}`)
}
</script>
