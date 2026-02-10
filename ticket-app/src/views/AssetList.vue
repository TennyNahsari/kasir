<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Asset Management</h1>
          <p class="text-sm text-gray-600 mt-1">Track and manage all company assets</p>
        </div>
        <button 
          v-if="canCreateAsset"
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Asset
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Asset code, name..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>

          <!-- Status  -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="() => { pagination.currentPage = 1; loadAssets(); }"
            >
              <option value="">All Status</option>
              <option v-for="status in ASSET_STATUSES" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Location -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select
              v-model="filters.location_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="() => { pagination.currentPage = 1; loadAssets(); }"
              :disabled="loadingLocations"
            >
              <option value="">All Locations</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }}
              </option>
            </select>
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <p class="text-red-800">{{ error }}</p>
        <button @click="loadAssets" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
          Try again
        </button>
      </div>

      <!-- Assets Table -->
      <div v-else-if="assets.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Asset
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Location
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Condition
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Purchase Info
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="asset in assets" :key="asset.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ asset.product?.name }}</div>
                      <div class="text-sm text-gray-500">{{ asset.asset_code }}</div>
                      <div v-if="asset.serial_number" class="text-xs text-gray-400">SN: {{ asset.serial_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ asset.location?.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getAssetStatusClass(asset.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ asset.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getConditionClass(asset.condition)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ asset.condition }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatCurrency(asset.purchase_price) }}</div>
                  <div class="text-xs text-gray-500">{{ formatDate(asset.purchase_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <router-link
                    :to="`/assets/${asset.id}`"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    View
                  </router-link>
                  <button
                    v-if="canEditAsset"
                    @click="editAsset(asset)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No assets found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new asset.</p>
        <div v-if="canCreateAsset" class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Asset
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="!loading && !error && assets.length > 0 && pagination.total > 0"
        :current-page="pagination.currentPage"
        :total-pages="pagination.lastPage"
        :total="pagination.total"
        :per-page="pagination.perPage"
        :from-item="pagination.from"
        :to-item="pagination.to"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit Asset Modal -->
    <CreateAssetModal 
      v-if="showCreateModal" 
      :asset="selectedAsset"
      @close="closeModal"
      @saved="handleAssetSaved"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'
import CreateAssetModal from '@/components/CreateAssetModal.vue'
import Pagination from '@/components/Pagination.vue'
import assetService from '@/services/assetService'
import axios from '@/utils/axios'
import {
  getAssetStatusClass,
  getConditionClass,
  formatCurrency,
  formatDate,
  ASSET_STATUSES
} from '@/utils/assetHelpers'

const authStore = useAuthStore()

const assets = ref([])
const locations = ref([])
const loading = ref(false)
const loadingLocations = ref(false)
const error = ref(null)
const showCreateModal = ref(false)
const selectedAsset = ref(null)

const filters = ref({
  search: '',
  status: '',
  location_id: ''
})

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 20,
  total: 0,
  from: 0,
  to: 0
})

const canCreateAsset = computed(() => {
  const role = authStore.user?.role
  return ['owner', 'supervisor', 'admin'].includes(role)
})

const canEditAsset = computed(() => {
  const role = authStore.user?.role
  return ['owner', 'supervisor', 'admin'].includes(role)
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.currentPage = 1
    loadAssets()
  }, 500)
}

const loadAssets = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.location_id) params.location_id = filters.value.location_id
    
    const response = await assetService.getAssets(params)
    assets.value = response.data.data || response.data
    
    // Update pagination info
    pagination.value.currentPage = response.data.current_page || 1
    pagination.value.lastPage = response.data.last_page || 1
    pagination.value.perPage = response.data.per_page || 20
    pagination.value.total = response.data.total || 0
    pagination.value.from = response.data.from || 0
    pagination.value.to = response.data.to || 0
  } catch (err) {
    console.error('Error loading assets:', err)
    error.value = err.response?.data?.message || 'Failed to load assets'
  } finally {
    loading.value = false
  }
}

const loadLocations = async () => {
  try {
    loadingLocations.value = true
    const response = await axios.get('/api/locations')
    locations.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading locations:', err)
  } finally {
    loadingLocations.value = false
  }
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    location_id: ''
  }
  loadAssets()
}

const editAsset = (asset) => {
  selectedAsset.value = asset
  showCreateModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  selectedAsset.value = null
}

const handleAssetSaved = () => {
  closeModal()
  loadAssets()
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadAssets()
}

onMounted(() => {
  loadAssets()
  loadLocations()
})
</script>
