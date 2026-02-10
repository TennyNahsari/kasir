<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage inventory stock levels across all locations</p>
      </div>
      <button v-if="selectedLocationId" @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
        <span class="text-xl">+</span>
        Add Stock
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <p class="text-sm text-red-700 mt-1">{{ errorMessage }}</p>
        </div>
        <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
          <select v-model="selectedLocationId" @change="loadStocks" class="w-full border-gray-300 rounded-lg" :disabled="loading">
            <option value="">Select Location</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }} ({{ loc.type }})
            </option>
          </select>
          <p v-if="locations.length === 0 && !loading" class="text-xs text-red-600 mt-1">
            No locations found. Please add locations first.
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @input="loadStocks" type="text" class="w-full border-gray-300 rounded-lg" placeholder="Product name or SKU..." :disabled="!selectedLocationId || loading">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="filters.category_id" @change="loadStocks" class="w-full border-gray-300 rounded-lg" :disabled="!selectedLocationId || loading">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
      <p class="text-gray-500 mt-4">Loading...</p>
    </div>

    <!-- Stock Table -->
    <div v-if="selectedLocationId && !loading" class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="stocks.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-gray-500 mb-2">No stock data for this location</p>
        <p class="text-sm text-gray-400">Click "Add Stock" to add your first product to this location</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reserved</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Available</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="stock in stocks" :key="stock.id">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ stock.product_name }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-500">{{ stock.sku || '-' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-500">{{ stock.category }}</div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="text-sm text-gray-900">{{ stock.quantity }}</div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="text-sm text-orange-600">{{ stock.reserved_quantity || 0 }}</div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="text-sm font-semibold text-green-600">{{ stock.available_quantity || (stock.quantity - (stock.reserved_quantity || 0)) }}</div>
              </td>
              <td class="px-6 py-4 text-center">
                <button @click="openAdjustModal(stock)" class="text-blue-600 hover:text-blue-900 text-sm">
                  Adjust
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else-if="!selectedLocationId && !loading" class="bg-white rounded-lg shadow p-12 text-center">
      <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      <p class="text-gray-500">Please select a location to view stocks</p>
    </div>

    <!-- Adjust Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Adjust Stock - {{ adjustingStock?.product_name }}</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Quantity</label>
            <div class="text-2xl font-bold text-gray-900">{{ adjustingStock?.quantity }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type *</label>
            <select v-model="adjustForm.type" class="w-full border-gray-300 rounded-lg" required>
              <option value="add">Add Stock</option>
              <option value="subtract">Reduce Stock</option>
              <option value="set">Set to Specific Amount</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ adjustForm.type === 'set' ? 'New Quantity' : 'Amount' }} *
            </label>
            <input v-model.number="adjustForm.quantity" type="number" min="0" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
            <textarea v-model="adjustForm.reason" rows="2" class="w-full border-gray-300 rounded-lg" placeholder="Optional notes..."></textarea>
          </div>
          <div v-if="adjustForm.type !== 'set'" class="bg-blue-50 p-3 rounded-lg">
            <div class="text-sm text-gray-700">
              New quantity will be: 
              <span class="font-bold text-blue-600">
                {{ calculateNewQuantity() }}
              </span>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeAdjustModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" :disabled="loading">Cancel</button>
          <button @click="saveAdjustment" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Stock Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Add Stock</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
            <select v-model="addForm.product_id" class="w-full border-gray-300 rounded-lg" required>
              <option value="">Select Product</option>
              <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                {{ product.name }} ({{ product.sku }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Initial Quantity *</label>
            <input v-model.number="addForm.quantity" type="number" min="0" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
            <input v-model.number="addForm.reorder_level" type="number" min="0" class="w-full border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="addForm.notes" class="w-full border-gray-300 rounded-lg" rows="3" placeholder="Optional notes..."></textarea>
          </div>
          <div class="flex gap-2 justify-end pt-4">
            <button @click="closeAddModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" :disabled="loading">Cancel</button>
            <button @click="saveNewStock" :disabled="!addForm.product_id || addForm.quantity == null || loading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
              {{ loading ? 'Adding...' : 'Add Stock' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const locations = ref([])
const categories = ref([])
const stocks = ref([])
const selectedLocationId = ref('')
const showAdjustModal = ref(false)
const adjustingStock = ref(null)
const showAddModal = ref(false)
const availableProducts = ref([])
const errorMessage = ref('')
const loading = ref(false)

const filters = ref({
  search: '',
  category_id: ''
})

const adjustForm = ref({
  type: 'add',
  quantity: 0,
  reason: ''
})

const addForm = ref({
  product_id: '',
  quantity: 0,
  reorder_level: 0,
  notes: ''
})

onMounted(async () => {
  await Promise.all([loadLocations(), loadCategories()])
  console.log('Loaded locations:', locations.value)
  console.log('Loaded categories:', categories.value)
})

const loadLocations = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    const { data } = await api.get('/locations', {
      params: { per_page: 100, is_active: 1 }
    })
    // Handle pagination - extract data array from paginated response
    locations.value = data.data || data
    
    if (!Array.isArray(locations.value)) {
      console.error('Locations data is not an array:', locations.value)
      locations.value = []
    }
    
    if (locations.value.length === 0) {
      errorMessage.value = 'No locations found. Please add locations first in Settings > Locations.'
    }
  } catch (error) {
    console.error('Failed to load locations:', error)
    errorMessage.value = 'Failed to load locations: ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    errorMessage.value = ''
    const { data } = await api.get('/categories')
    categories.value = Array.isArray(data) ? data : []
    
    if (categories.value.length === 0) {
      console.warn('No categories found')
      // Don't show error for categories as it's optional filter
    }
  } catch (error) {
    console.error('Failed to load categories:', error)
    // Categories are optional, so just log the error
    categories.value = []
  }
}

const loadStocks = async () => {
  if (!selectedLocationId.value) return
  
  try {
    loading.value = true
    errorMessage.value = ''
    const params = { location_id: selectedLocationId.value }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.category_id) params.category_id = filters.value.category_id
    
    const { data } = await api.get('/inventory-stocks', { params })
    console.log('Stocks loaded:', data)
    stocks.value = Array.isArray(data) ? data : (data.data || [])
  } catch (error) {
    console.error('Failed to load stocks:', error)
    errorMessage.value = 'Failed to load stocks: ' + (error.response?.data?.message || error.message)
    stocks.value = []
  } finally {
    loading.value = false
  }
}

const openAdjustModal = (stock) => {
  adjustingStock.value = stock
  adjustForm.value = {
    type: 'add',
    quantity: 0,
    reason: ''
  }
  showAdjustModal.value = true
}

const calculateNewQuantity = () => {
  if (!adjustingStock.value) return 0
  const current = adjustingStock.value.quantity
  const amount = adjustForm.value.quantity || 0
  
  if (adjustForm.value.type === 'add') {
    return current + amount
  } else if (adjustForm.value.type === 'subtract') {
    return Math.max(0, current - amount)
  }
  return 0
}

const saveAdjustment = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    
    let newQuantity
    if (adjustForm.value.type === 'set') {
      newQuantity = adjustForm.value.quantity
    } else {
      newQuantity = calculateNewQuantity()
    }
    
    await api.post('/inventory-stocks/adjust', {
      location_id: selectedLocationId.value,
      product_id: adjustingStock.value.product_id,
      new_quantity: newQuantity,
      notes: adjustForm.value.reason || 'Manual adjustment'
    })
    
    closeAdjustModal()
    await loadStocks()
    
    // Show success message briefly
    const successMsg = 'Stock adjusted successfully'
    alert(successMsg)
  } catch (error) {
    console.error('Adjust stock error:', error)
    errorMessage.value = 'Failed to adjust stock: ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  adjustingStock.value = null
}

const openAddModal = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    
    // Load all products
    const { data } = await api.get('/products')
    // Handle both array and object with data property
    const products = Array.isArray(data) ? data : (data.data || [])
    // Filter out products that already have stock in this location
    const existingProductIds = stocks.value.map(s => s.product_id)
    availableProducts.value = products.filter(p => !existingProductIds.includes(p.id))
    
    if (availableProducts.value.length === 0) {
      errorMessage.value = 'No available products to add. All products already have stock in this location or no products exist.'
      return
    }
    
    addForm.value = {
      product_id: '',
      quantity: 0,
      reorder_level: 0,
      notes: ''
    }
    showAddModal.value = true
  } catch (error) {
    console.error('Failed to load products:', error)
    errorMessage.value = 'Failed to load products: ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const saveNewStock = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    
    await api.post('/inventory-stocks', {
      product_id: addForm.value.product_id,
      location_id: selectedLocationId.value,
      quantity: addForm.value.quantity,
      reorder_level: addForm.value.reorder_level || 0,
      notes: addForm.value.notes || 'Initial stock entry'
    })
    
    closeAddModal()
    await loadStocks()
    
    alert('Stock added successfully')
  } catch (error) {
    console.error('Add stock error:', error)
    errorMessage.value = 'Failed to add stock: ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const closeAddModal = () => {
  showAddModal.value = false
  availableProducts.value = []
}
</script>
