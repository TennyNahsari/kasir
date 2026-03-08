<template>
  <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $t('stocks.title') }}</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $t('stocks.subtitle') }}</p>
      </div>
      <button v-if="selectedLocationId" @click="openAddModal" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 text-sm sm:text-base">
        <span class="text-xl">+</span>
        {{ $t('stocks.addStock') }}
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-red-800">{{ $t('common.error') }}</h3>
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
    <div class="bg-white rounded-lg shadow p-3 sm:p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="sm:col-span-2 lg:col-span-1">
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.location') }} *</label>
          <select v-model="selectedLocationId" @change="loadStocks" class="w-full border-gray-300 rounded-lg text-sm" :disabled="loading">
            <option value="">{{ $t('stocks.selectLocation') }}</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }} ({{ loc.type }})
            </option>
          </select>
          <p v-if="locations.length === 0 && !loading" class="text-xs text-red-600 mt-1">
            {{ $t('stocks.noLocationsFound') }}
          </p>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('common.search') }}</label>
          <input v-model="filters.search" @input="loadStocks" type="text" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('stocks.searchPlaceholder')" :disabled="!selectedLocationId || loading">
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('common.category') }}</label>
          <select v-model="filters.category_id" @change="loadStocks" class="w-full border-gray-300 rounded-lg text-sm" :disabled="!selectedLocationId || loading">
            <option value="">{{ $t('stocks.allCategories') }}</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-6 sm:p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-4 border-gray-200 border-t-blue-600"></div>
      <p class="text-sm sm:text-base text-gray-500 mt-4">{{ $t('common.loading') }}</p>
    </div>

    <!-- Stock Table -->
    <div v-if="selectedLocationId && !loading" class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="stocks.length === 0" class="p-6 sm:p-12 text-center">
        <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-sm sm:text-base text-gray-500 mb-2">{{ $t('stocks.noStocksForLocation') }}</p>
        <p class="text-xs sm:text-sm text-gray-400">{{ $t('stocks.noStocksHint') }}</p>
      </div>
      <div v-else class="overflow-x-auto -mx-4 sm:mx-0">
        <div class="inline-block min-w-full align-middle">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $t('stocks.tableHeaders.product') }}</th>
              <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap hidden sm:table-cell">{{ $t('stocks.tableHeaders.sku') }}</th>
              <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap hidden md:table-cell">{{ $t('stocks.tableHeaders.category') }}</th>
              <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $t('stocks.tableHeaders.quantity') }}</th>
              <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap hidden lg:table-cell">{{ $t('stocks.tableHeaders.reorderLevel') }}</th>
              <th class="px-3 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $t('stocks.tableHeaders.actions') }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="stock in stocks" :key="stock.id">
              <td class="px-3 sm:px-6 py-3 sm:py-4">
                <div class="text-xs sm:text-sm font-medium text-gray-900">{{ stock.product_name }}</div>
                <div class="text-xs text-gray-500 mt-1 sm:hidden">{{ stock.sku || '-' }}</div>
              </td>
              <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">
                <div class="text-sm text-gray-500">{{ stock.sku || '-' }}</div>
              </td>
              <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">
                <div class="text-sm text-gray-500">{{ stock.category }}</div>
              </td>
              <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                <div class="text-sm font-semibold text-gray-900">{{ stock.quantity }}</div>
                <div class="text-xs text-gray-500 mt-1 lg:hidden" :class="stock.quantity <= stock.reorder_level ? 'text-red-600 font-medium' : ''">
                  Min: {{ stock.reorder_level || 0 }}
                </div>
              </td>
              <td class="px-3 sm:px-6 py-3 sm:py-4 text-right hidden lg:table-cell">
                <div class="text-sm font-medium" :class="stock.quantity <= stock.reorder_level ? 'text-red-600' : 'text-gray-900'">
                  {{ stock.reorder_level || 0 }}
                </div>
              </td>
              <td class="px-3 sm:px-6 py-3 sm:py-4 text-center">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-3">
                  <button @click="openAdjustModal(stock)" class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm font-medium whitespace-nowrap">
                    {{ $t('stocks.adjustStock') }}
                  </button>
                  <button @click="deleteStock(stock)" class="text-red-600 hover:text-red-900 text-xs sm:text-sm font-medium">
                    {{ $t('common.delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
      
      <!-- Pagination -->
      <Pagination
        v-if="pagination.total > 0"
        :current-page="pagination.currentPage"
        :last-page="pagination.lastPage"
        :per-page="pagination.perPage"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @update:current-page="handlePageChange"
        @update:per-page="handlePerPageChange"
      />
    </div>

    <div v-else-if="!selectedLocationId && !loading" class="bg-white rounded-lg shadow p-6 sm:p-12 text-center">
      <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      <p class="text-sm sm:text-base text-gray-500">{{ $t('stocks.selectLocationPrompt') }}</p>
    </div>

    <!-- Adjust Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-base sm:text-lg font-semibold mb-4">{{ $t('stocks.modals.adjustStock.title') }} - {{ adjustingStock?.product_name }}</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.adjustStock.currentQuantity') }}</label>
            <div class="text-xl sm:text-2xl font-bold text-gray-900">{{ adjustingStock?.quantity }}</div>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.adjustStock.adjustmentType') }} *</label>
            <select v-model="adjustForm.type" class="w-full border-gray-300 rounded-lg text-sm" required>
              <option value="add">{{ $t('stocks.modals.adjustStock.addStock') }}</option>
              <option value="subtract">{{ $t('stocks.modals.adjustStock.reduceStock') }}</option>
              <option value="set">{{ $t('stocks.modals.adjustStock.setAmount') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
              {{ adjustForm.type === 'set' ? $t('stocks.modals.adjustStock.newQuantity') : $t('stocks.modals.adjustStock.amount') }} *
            </label>
            <input v-model.number="adjustForm.quantity" type="number" min="0" class="w-full border-gray-300 rounded-lg text-sm" required>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.adjustStock.reorderLevel') }}</label>
            <input v-model.number="adjustForm.reorder_level" type="number" min="0" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('stocks.modals.adjustStock.reorderLevelPlaceholder')">
            <p class="text-xs text-gray-500 mt-1">{{ $t('stocks.modals.adjustStock.current') }}: {{ adjustingStock?.reorder_level || 0 }}</p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.adjustStock.reason') }}</label>
            <textarea v-model="adjustForm.reason" rows="2" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('stocks.modals.adjustStock.reasonPlaceholder')"></textarea>
          </div>
          <div v-if="adjustForm.type !== 'set'" class="bg-blue-50 p-3 rounded-lg">
            <div class="text-sm text-gray-700">
              {{ $t('stocks.modals.adjustStock.newQuantityWillBe') }}: 
              <span class="font-bold text-blue-600">
                {{ calculateNewQuantity() }}
              </span>
            </div>
          </div>
        </div>
        <div class="mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
          <button @click="closeAdjustModal" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm" :disabled="loading">{{ $t('common.cancel') }}</button>
          <button @click="saveAdjustment" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 text-sm" :disabled="loading">
            {{ loading ? $t('common.saving') : $t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Stock Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-base sm:text-lg font-semibold mb-4">{{ $t('stocks.modals.addStock.title') }}</h3>
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.addStock.product') }} *</label>
            <div class="relative">
              <input 
                v-model="productSearch" 
                @focus="showProductDropdown = true"
                @input="showProductDropdown = true"
                @blur="() => { setTimeout(() => showProductDropdown = false, 200) }"
                type="text" 
                :placeholder="$t('stocks.modals.addStock.searchPlaceholder')"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm border focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                required
              >
              
              <!-- Dropdown list -->
              <div 
                v-if="showProductDropdown && filteredProducts.length > 0" 
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 sm:max-h-60 overflow-y-auto"
              >
                <div 
                  v-for="product in filteredProducts" 
                  :key="product.id"
                  @click="selectProduct(product)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                  <div class="font-medium text-gray-900 text-sm">{{ product.name }}</div>
                  <div class="text-xs text-gray-500">SKU: {{ product.sku }}</div>
                </div>
              </div>
              
              <!-- No results message -->
              <div 
                v-if="showProductDropdown && productSearch && filteredProducts.length === 0" 
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg px-3 py-2 text-gray-500 text-sm"
              >
                {{ $t('stocks.modals.addStock.noProductsFound') }}
              </div>
              
              <!-- Selected product display -->
              <div v-if="selectedProduct" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm">
                <span class="font-medium">{{ $t('stocks.modals.addStock.selected') }}:</span> {{ selectedProduct.name }} ({{ selectedProduct.sku }})
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.addStock.initialQuantity') }} *</label>
            <input v-model.number="addForm.quantity" type="number" min="0" class="w-full border-gray-300 rounded-lg text-sm" required>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.addStock.reorderLevel') }}</label>
            <input v-model.number="addForm.reorder_level" type="number" min="0" class="w-full border-gray-300 rounded-lg text-sm">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.modals.addStock.notes') }}</label>
            <textarea v-model="addForm.notes" class="w-full border-gray-300 rounded-lg text-sm" rows="3" :placeholder="$t('stocks.modals.addStock.notesPlaceholder')"></textarea>
          </div>
          <div class="flex flex-col sm:flex-row gap-2 justify-end pt-4">
            <button @click="closeAddModal" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm" :disabled="loading">{{ $t('common.cancel') }}</button>
            <button @click="saveNewStock" :disabled="!addForm.product_id || addForm.quantity == null || loading" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm">
              {{ loading ? $t('common.adding') : $t('stocks.addStock') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'

const { t } = useI18n()
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

// Autocomplete for product selection
const productSearch = ref('')
const showProductDropdown = ref(false)
const selectedProduct = ref(null)

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 25,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  search: '',
  category_id: ''
})

const adjustForm = ref({
  type: 'add',
  quantity: 0,
  reorder_level: 0,
  reason: ''
})

const addForm = ref({
  product_id: '',
  quantity: 0,
  reorder_level: 0,
  notes: ''
})

// Computed property for filtered products based on search
const filteredProducts = computed(() => {
  if (!productSearch.value) {
    return availableProducts.value
  }
  
  const search = productSearch.value.toLowerCase()
  return availableProducts.value.filter(product => 
    product.name.toLowerCase().includes(search) || 
    product.sku.toLowerCase().includes(search)
  )
})

// Select product from dropdown
const selectProduct = (product) => {
  selectedProduct.value = product
  addForm.value.product_id = product.id
  productSearch.value = product.name
  showProductDropdown.value = false
}

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
    const allLocations = data.data || data
    
    if (!Array.isArray(allLocations)) {
      console.error('Locations data is not an array:', allLocations)
      locations.value = []
    } else {
      // Filter only INVENTORY, OUTLET, and FNB types
      locations.value = allLocations.filter(loc => 
        ['INVENTORY', 'WAREHOUSE', 'OUTLET', 'FNB'].includes(loc.type?.toUpperCase())
      )
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
    const params = { 
      location_id: selectedLocationId.value,
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.category_id) params.category_id = filters.value.category_id
    
    const { data } = await api.get('/inventory-stocks', { params })
    console.log('Stocks loaded:', data)
    
    // Handle both paginated and non-paginated responses
    if (data.data && data.meta) {
      // Paginated response
      stocks.value = data.data
      pagination.value = {
        currentPage: data.meta.current_page,
        lastPage: data.meta.last_page,
        perPage: data.meta.per_page,
        total: data.meta.total,
        from: data.meta.from || 0,
        to: data.meta.to || 0
      }
    } else {
      // Non-paginated response (array)
      stocks.value = Array.isArray(data) ? data : (data.data || [])
      pagination.value = {
        currentPage: 1,
        lastPage: 1,
        perPage: stocks.value.length,
        total: stocks.value.length,
        from: stocks.value.length > 0 ? 1 : 0,
        to: stocks.value.length
      }
    }
  } catch (error) {
    console.error('Failed to load stocks:', error)
    errorMessage.value = 'Failed to load stocks: ' + (error.response?.data?.message || error.message)
    stocks.value = []
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadStocks()
}

const handlePerPageChange = (perPage) => {
  pagination.value.perPage = perPage
  pagination.value.currentPage = 1
  loadStocks()
}

const openAdjustModal = (stock) => {
  adjustingStock.value = stock
  adjustForm.value = {
    type: 'add',
    quantity: 0,
    reorder_level: stock.reorder_level || 0,
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
      reorder_level: adjustForm.value.reorder_level,
      notes: adjustForm.value.reason || 'Manual adjustment'
    })
    
    closeAdjustModal()
    await loadStocks()
    
    // Show success message briefly
    alert(t('stocks.alerts.adjustSuccess'))
  } catch (error) {
    console.error('Adjust stock error:', error)
    errorMessage.value = t('stocks.alerts.adjustError') + ': ' + (error.response?.data?.message || error.message)
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
    
    // Reset autocomplete state
    productSearch.value = ''
    selectedProduct.value = null
    showProductDropdown.value = false
    
    // Load all products
    const { data } = await api.get('/products')
    // Handle both array and object with data property
    const products = Array.isArray(data) ? data : (data.data || [])
    // Filter out products that already have stock in this location
    const existingProductIds = stocks.value.map(s => s.product_id)
    availableProducts.value = products.filter(p => !existingProductIds.includes(p.id))
    
    if (availableProducts.value.length === 0) {
      errorMessage.value = t('stocks.alerts.noAvailableProducts')
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
    errorMessage.value = t('stocks.alerts.loadProductsError') + ': ' + (error.response?.data?.message || error.message)
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
    
    alert(t('stocks.alerts.addSuccess'))
  } catch (error) {
    console.error('Add stock error:', error)
    errorMessage.value = t('stocks.alerts.addError') + ': ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const deleteStock = async (stock) => {
  // Confirm before deleting
  const confirmMessage = stock.quantity > 0 
    ? t('stocks.confirms.deleteWithQuantity', { quantity: stock.quantity })
    : t('stocks.confirms.delete', { product: stock.product_name })
  
  if (!confirm(confirmMessage)) {
    return
  }
  
  // If quantity > 0, show adjustment modal instead
  if (stock.quantity > 0) {
    openAdjustModal(stock)
    return
  }
  
  try {
    loading.value = true
    errorMessage.value = ''
    
    await api.delete(`/inventory-stocks/${stock.id}`)
    
    alert(t('stocks.alerts.deleteSuccess'))
    await loadStocks()
  } catch (error) {
    console.error('Delete stock error:', error)
    errorMessage.value = t('stocks.alerts.deleteError') + ': ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const closeAddModal = () => {
  showAddModal.value = false
  availableProducts.value = []
  productSearch.value = ''
  selectedProduct.value = null
  showProductDropdown.value = false
}
</script>
