<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Stock Levels</h1>
        <p class="text-gray-600">View inventory across all locations</p>
      </div>
      <div class="flex space-x-3">
        <button @click="openAddModal" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
          + Add Stock
        </button>
        <button @click="showAdjustModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
          Adjust Stock
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <select v-model="filters.location_id" @change="loadStocks" class="w-full border-gray-300 rounded-lg">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
          <input v-model="filters.search" @input="loadStocks" type="text" placeholder="Name or SKU..." class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
          <select v-model="filters.showLowStock" @change="loadStocks" class="w-full border-gray-300 rounded-lg">
            <option :value="false">All Stock</option>
            <option :value="true">Low Stock Only</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Display</label>
          <select v-model="filters.hideZeroStock" @change="loadStocks" class="w-full border-gray-300 rounded-lg">
            <option :value="false">Show All (including 0)</option>
            <option :value="true">Hide Zero Stock</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reserved</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Available</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="stock in stocks" :key="stock.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ stock.product_name }}</div>
                <div class="text-sm text-gray-500">{{ stock.sku }} • {{ stock.category }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ stock.location_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                {{ stock.quantity }} {{ stock.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                {{ stock.reserved_quantity }} {{ stock.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                {{ stock.available_quantity }} {{ stock.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(stock)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStockStatus(stock) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="openLedger(stock)" class="text-blue-600 hover:text-blue-900 mr-3">History</button>
                <button @click="adjustStock(stock)" class="text-green-600 hover:text-green-900">Adjust</button>
              </td>
            </tr>
            <tr v-if="stocks.length === 0">
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                No stock data found
              </td>
            </tr>
          </tbody>
        </table>
      </div>      <Pagination :pagination="pagination" @page-change="changePage" />    </div>

    <!-- Adjust Stock Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Adjust Stock</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select v-model="adjustForm.location_id" class="w-full border-gray-300 rounded-lg" required>
              <option value="">Select Location</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
            <input v-model="adjustForm.product_search" @input="searchProducts" type="text" placeholder="Search product..." class="w-full border-gray-300 rounded-lg">
            <div v-if="productSearchResults.length > 0" class="mt-2 border rounded-lg max-h-40 overflow-y-auto">
              <div v-for="product in productSearchResults" :key="product.id" @click="selectProduct(product)" class="p-2 hover:bg-gray-100 cursor-pointer">
                {{ product.name }} ({{ product.sku }})
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Quantity</label>
            <input v-model.number="adjustForm.new_quantity" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="adjustForm.notes" rows="3" class="w-full border-gray-300 rounded-lg"></textarea>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeAdjustModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="submitAdjustment" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Adjust Stock</button>
        </div>
      </div>
    </div>

    <!-- Add Stock Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Add New Stock</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
            <select v-model="addForm.location_id" class="w-full border-gray-300 rounded-lg" required>
              <option value="">Select Location</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
            <input v-model="addForm.product_search" @input="searchProductsForAdd" type="text" placeholder="Search product..." class="w-full border-gray-300 rounded-lg">
            <div v-if="addProductSearchResults.length > 0" class="mt-2 border rounded-lg max-h-40 overflow-y-auto">
              <div v-for="product in addProductSearchResults" :key="product.id" @click="selectProductForAdd(product)" class="p-2 hover:bg-gray-100 cursor-pointer">
                {{ product.name }} ({{ product.sku }})
              </div>
            </div>
            <p v-if="addForm.product_id" class="mt-1 text-sm text-green-600">Selected: {{ addForm.product_search }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Initial Quantity *</label>
            <input v-model.number="addForm.quantity" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
            <input v-model.number="addForm.reorder_level" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg" placeholder="Optional">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="addForm.notes" rows="3" class="w-full border-gray-300 rounded-lg" placeholder="Optional notes..."></textarea>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeAddModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="submitAddStock" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Add Stock</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'

const router = useRouter()

const stocks = ref([])
const locations = ref([])
const showAdjustModal = ref(false)
const showAddModal = ref(false)
const productSearchResults = ref([])
const addProductSearchResults = ref([])

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  location_id: '',
  search: '',
  showLowStock: false,
  hideZeroStock: false
})

const adjustForm = ref({
  product_id: null,
  product_search: '',
  location_id: '',
  new_quantity: 0,
  notes: ''
})

const addForm = ref({
  product_id: null,
  product_search: '',
  location_id: '',
  quantity: 0,
  reorder_level: 0,
  notes: ''
})

onMounted(async () => {
  await loadLocations()
  await loadStocks()
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadStocks = async (page = 1) => {
  try {
    const params = { page, per_page: 20 }
    if (filters.value.location_id) params.location_id = filters.value.location_id
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.hideZeroStock) params.hide_zero_stock = true

    const endpoint = filters.value.showLowStock ? '/inventory-stocks/low-stock' : '/inventory-stocks'
    const { data } = await api.get(endpoint, { params })
    stocks.value = data.data || data
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      per_page: data.per_page || 20,
      total: data.total || stocks.value.length,
      from: data.from || 0,
      to: data.to || 0,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url
    }
  } catch (error) {
    console.error('Failed to load stocks:', error)
  }
}

const changePage = (page) => {
  loadStocks(page)
}

const getStockStatus = (stock) => {
  if (stock.available_quantity <= 0) return 'Out of Stock'
  if (stock.quantity <= stock.reorder_level) return 'Low Stock'
  return 'In Stock'
}

const getStatusClass = (stock) => {
  if (stock.available_quantity <= 0) return 'bg-red-100 text-red-800'
  if (stock.quantity <= stock.reorder_level) return 'bg-orange-100 text-orange-800'
  return 'bg-green-100 text-green-800'
}

const adjustStock = (stock) => {
  adjustForm.value = {
    product_id: stock.product_id,
    product_search: stock.product_name,
    location_id: stock.location_id,
    new_quantity: stock.quantity,
    notes: ''
  }
  showAdjustModal.value = true
}

const searchProducts = async () => {
  if (adjustForm.value.product_search.length < 2) {
    productSearchResults.value = []
    return
  }
  try {
    const { data } = await api.get('/products', { params: { search: adjustForm.value.product_search } })
    // Handle paginated response
    productSearchResults.value = (data.data || data).slice(0, 5)
  } catch (error) {
    console.error('Failed to search products:', error)
  }
}

const selectProduct = (product) => {
  adjustForm.value.product_id = product.id
  adjustForm.value.product_search = product.name
  productSearchResults.value = []
}

const submitAdjustment = async () => {
  try {
    await api.post('/inventory-stocks/adjust', {
      product_id: adjustForm.value.product_id,
      location_id: adjustForm.value.location_id,
      new_quantity: adjustForm.value.new_quantity,
      notes: adjustForm.value.notes
    })
    alert('Stock adjusted successfully')
    closeAdjustModal()
    await loadStocks()
  } catch (error) {
    alert('Failed to adjust stock: ' + (error.response?.data?.message || error.message))
  }
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  adjustForm.value = {
    product_id: null,
    product_search: '',
    location_id: '',
    new_quantity: 0,
    notes: ''
  }
}

const openLedger = (stock) => {
  router.push({
    path: '/inventory/ledger',
    query: {
      product_id: stock.product_id,
      location_id: stock.location_id
    }
  })
}

const openAddModal = () => {
  addForm.value = {
    product_id: null,
    product_search: '',
    location_id: '',
    quantity: 0,
    reorder_level: 0,
    notes: ''
  }
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  addProductSearchResults.value = []
}

const searchProductsForAdd = async () => {
  if (addForm.value.product_search.length < 2) {
    addProductSearchResults.value = []
    return
  }
  try {
    const { data } = await api.get('/products', { params: { search: addForm.value.product_search } })
    addProductSearchResults.value = (data.data || data).slice(0, 5)
  } catch (error) {
    console.error('Failed to search products:', error)
  }
}

const selectProductForAdd = (product) => {
  addForm.value.product_id = product.id
  addForm.value.product_search = product.name
  addProductSearchResults.value = []
}

const submitAddStock = async () => {
  if (!addForm.value.product_id) {
    alert('Please select a product')
    return
  }
  if (!addForm.value.location_id) {
    alert('Please select a location')
    return
  }
  if (addForm.value.quantity < 0) {
    alert('Quantity must be 0 or greater')
    return
  }

  try {
    await api.post('/inventory-stocks', {
      product_id: addForm.value.product_id,
      location_id: addForm.value.location_id,
      quantity: addForm.value.quantity,
      reorder_level: addForm.value.reorder_level || 0,
      notes: addForm.value.notes
    })
    alert('Stock added successfully')
    closeAddModal()
    await loadStocks()
  } catch (error) {
    alert('Failed to add stock: ' + (error.response?.data?.message || error.message))
  }
}
</script>
