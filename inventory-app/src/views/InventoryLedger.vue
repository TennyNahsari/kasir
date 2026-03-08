<template>
  <div class="p-3 sm:p-4 lg:p-6">
    <div class="mb-3 sm:mb-4 lg:mb-6">
      <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-800">{{ $t('ledger.title') }}</h1>
      <p class="text-xs sm:text-sm text-gray-600">{{ $t('ledger.subtitle') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-3 sm:mb-4 lg:mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="relative">
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('ledger.productLabel') }}</label>
          <input v-model="filters.product_search" @input="searchProducts" type="text" :placeholder="$t('ledger.searchProductPlaceholder')" class="w-full border-gray-300 rounded-lg text-sm">
          <div v-if="productSearchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white border rounded-lg shadow-lg max-h-32 sm:max-h-40 overflow-y-auto">
            <div v-for="product in productSearchResults" :key="product.id" @click="selectProduct(product)" class="p-2 hover:bg-gray-100 cursor-pointer text-xs sm:text-sm">
              {{ product.name }} ({{ product.sku }})
            </div>
          </div>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('ledger.locationLabel') }}</label>
          <select v-model="filters.location_id" @change="loadLedger" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">{{ $t('ledger.allLocations') }}</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('ledger.movementTypeLabel') }}</label>
          <select v-model="filters.movement_type" @change="loadLedger" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">{{ $t('ledger.allTypes') }}</option>
            <option value="STOCK_IN">{{ $t('ledger.stockIn') }}</option>
            <option value="STOCK_OUT">{{ $t('ledger.stockOut') }}</option>
            <option value="TRANSFER_OUT">{{ $t('ledger.transferOut') }}</option>
            <option value="TRANSFER_IN">{{ $t('ledger.transferIn') }}</option>
            <option value="ADJUSTMENT">{{ $t('ledger.adjustment') }}</option>
            <option value="RESERVED">{{ $t('ledger.reserved') }}</option>
            <option value="RELEASED">{{ $t('ledger.released') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('ledger.fromDateLabel') }}</label>
          <input v-model="filters.from_date" @change="loadLedger" type="date" class="w-full border-gray-300 rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('ledger.toDateLabel') }}</label>
          <input v-model="filters.to_date" @change="loadLedger" type="date" class="w-full border-gray-300 rounded-lg text-sm">
        </div>
      </div>
    </div>

    <!-- Ledger Table (Desktop) -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.dateTime') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.product') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.location') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.movementType') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.quantity') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.balance') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.reference') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('ledger.notes') }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="entry in ledger" :key="entry.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDateTime(entry.created_at) }}
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ entry.product?.name || '-' }}</div>
                <div class="text-sm text-gray-500">{{ entry.product?.sku || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ entry.location?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getMovementTypeClass(entry.movement_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ formatMovementType(entry.movement_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <span :class="entry.quantity >= 0 ? 'text-green-600' : 'text-red-600'" class="font-medium">
                  {{ entry.quantity >= 0 ? '+' : '' }}{{ entry.quantity }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                {{ entry.balance_after }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ entry.reference_type || '-' }} {{ entry.reference_no ? '#' + entry.reference_no : '' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ entry.notes || '-' }}
              </td>
            </tr>
            <tr v-if="ledger.length === 0">
              <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                {{ $t('ledger.noEntries') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :pagination="pagination" @page-change="changePage" />
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-2 sm:space-y-3">
      <div v-for="entry in ledger" :key="entry.id" class="bg-white rounded-lg shadow p-3 sm:p-4">
        <div class="flex justify-between items-start mb-2">
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-sm sm:text-base text-gray-900 truncate">{{ entry.product?.name || '-' }}</h3>
            <p class="text-xs text-gray-500">{{ entry.product?.sku || '-' }}</p>
          </div>
          <span :class="getMovementTypeClass(entry.movement_type)" class="px-2 py-1 text-xs font-semibold rounded-full flex-shrink-0 ml-2">
            {{ formatMovementType(entry.movement_type) }}
          </span>
        </div>
        
        <div class="space-y-1 mb-3 text-xs">
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('ledger.dateTime') }}:</span>
            <span class="text-gray-900">{{ formatDateTime(entry.created_at) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('ledger.location') }}:</span>
            <span class="text-gray-900 truncate ml-2">{{ entry.location?.name || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('ledger.quantity') }}:</span>
            <span :class="entry.quantity >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
              {{ entry.quantity >= 0 ? '+' : '' }}{{ entry.quantity }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('ledger.balance') }}:</span>
            <span class="font-semibold text-gray-900">{{ entry.balance_after }}</span>
          </div>
          <div v-if="entry.reference_type || entry.reference_no" class="flex justify-between">
            <span class="text-gray-600">{{ $t('ledger.reference') }}:</span>
            <span class="text-gray-900 truncate ml-2">{{ entry.reference_type || '-' }} {{ entry.reference_no ? '#' + entry.reference_no : '' }}</span>
          </div>
          <div v-if="entry.notes" class="pt-1">
            <span class="text-gray-600">{{ $t('ledger.notes') }}:</span>
            <p class="text-gray-900 text-xs mt-0.5">{{ entry.notes }}</p>
          </div>
        </div>
      </div>
      
      <div v-if="ledger.length === 0" class="bg-white rounded-lg shadow p-6 sm:p-8 text-center text-gray-500 text-sm">
        {{ $t('ledger.noEntries') }}
      </div>

      <Pagination :pagination="pagination" @page-change="changePage" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'

const route = useRoute()
const { t } = useI18n()

const ledger = ref([])
const locations = ref([])
const productSearchResults = ref([])

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 50,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  product_id: route.query.product_id || '',
  product_search: '',
  location_id: route.query.location_id || '',
  movement_type: '',
  from_date: '',
  to_date: ''
})

onMounted(async () => {
  await loadLocations()
  await loadLedger()
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data.data || data  // Handle pagination response
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadLedger = async (page = 1) => {
  try {
    const params = { page, per_page: 50 }
    if (filters.value.product_id) params.product_id = filters.value.product_id
    if (filters.value.location_id) params.location_id = filters.value.location_id
    if (filters.value.movement_type) params.movement_type = filters.value.movement_type
    if (filters.value.from_date) params.date_from = filters.value.from_date
    if (filters.value.to_date) params.date_to = filters.value.to_date

    const { data } = await api.get('/inventory-stocks/ledger', { params })
    console.log('Ledger response:', data)
    // Handle paginated response
    ledger.value = Array.isArray(data) ? data : (data.data || [])
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      per_page: data.per_page || 50,
      total: data.total || ledger.value.length,
      from: data.from || 0,
      to: data.to || 0,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url
    }
  } catch (error) {
    console.error('Failed to load ledger:', error)
  }
}

const changePage = (page) => {
  loadLedger(page)
}

const searchProducts = async () => {
  if (filters.value.product_search.length < 2) {
    productSearchResults.value = []
    return
  }
  try {
    const { data } = await api.get('/products', { params: { search: filters.value.product_search } })
    productSearchResults.value = data.slice(0, 5)
  } catch (error) {
    console.error('Failed to search products:', error)
  }
}

const selectProduct = (product) => {
  filters.value.product_id = product.id
  filters.value.product_search = product.name
  productSearchResults.value = []
  loadLedger()
}

const getMovementTypeClass = (type) => {
  const classes = {
    'STOCK_IN': 'bg-green-100 text-green-800',
    'STOCK_OUT': 'bg-red-100 text-red-800',
    'TRANSFER_OUT': 'bg-orange-100 text-orange-800',
    'TRANSFER_IN': 'bg-blue-100 text-blue-800',
    'ADJUSTMENT': 'bg-purple-100 text-purple-800',
    'RESERVED': 'bg-yellow-100 text-yellow-800',
    'RELEASED': 'bg-teal-100 text-teal-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const formatMovementType = (type) => {
  const types = {
    'STOCK_IN': t('ledger.stockIn'),
    'STOCK_OUT': t('ledger.stockOut'),
    'TRANSFER_OUT': t('ledger.transferOut'),
    'TRANSFER_IN': t('ledger.transferIn'),
    'ADJUSTMENT': t('ledger.adjustment'),
    'RESERVED': t('ledger.reserved'),
    'RELEASED': t('ledger.released')
  }
  return types[type] || type
}

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
