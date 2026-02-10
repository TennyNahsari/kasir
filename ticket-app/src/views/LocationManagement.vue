<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Location Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage warehouses, outlets, and departments</p>
        </div>
        <button 
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Location
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Code or name..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>

          <!-- Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select
              v-model="filters.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="() => { pagination.currentPage = 1; loadLocations(); }"
            >
              <option value="">All Types</option>
              <option value="WAREHOUSE">Warehouse</option>
              <option value="OUTLET">Outlet</option>
              <option value="FNB">FNB</option>
              <option value="DEPARTMENT">Department</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.is_active"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="() => { pagination.currentPage = 1; loadLocations(); }"
            >
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
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
        <button @click="loadLocations" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
          Try again
        </button>
      </div>

      <!-- Locations Table -->
      <div v-else-if="locations.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="location in locations" :key="location.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ location.code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ location.name }}</div>
                  <div v-if="location.address" class="text-sm text-gray-500">{{ location.address }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTypeClass(location.type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ location.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.person_in_charge || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.phone || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ location.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="openEditModal(location)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(location)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No locations found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new location.</p>
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="!loading && !error && locations.length > 0 && pagination.total > 0"
        :current-page="pagination.currentPage"
        :total-pages="pagination.lastPage"
        :total="pagination.total"
        :per-page="pagination.perPage"
        :from-item="pagination.from"
        :to-item="pagination.to"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit Location Modal -->
    <LocationFormModal 
      v-if="showModal" 
      :location="selectedLocation"
      @close="closeModal"
      @saved="handleLocationSaved"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showDeleteConfirm = false">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Delete Location</h3>
          <div class="mt-2 px-4 py-3">
            <p class="text-sm text-gray-500 text-center">
              Are you sure you want to delete this location?
            </p>
            <p class="text-sm font-semibold text-gray-900 text-center mt-2">
              {{ locationToDelete?.name }}
            </p>
            <p class="text-xs text-red-600 text-center mt-2">
              This action cannot be undone.
            </p>
          </div>
          <div class="flex gap-3 px-4 py-3">
            <button
              @click="showDeleteConfirm = false"
              :disabled="deleting"
              class="flex-1 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 text-sm font-medium disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              @click="handleDelete"
              :disabled="deleting"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium disabled:opacity-50"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import LocationFormModal from '@/components/LocationFormModal.vue'
import Pagination from '@/components/Pagination.vue'
import locationService from '@/services/locationService'

const locations = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const selectedLocation = ref(null)
const showDeleteConfirm = ref(false)
const locationToDelete = ref(null)
const deleting = ref(false)

const filters = ref({
  search: '',
  type: '',
  is_active: ''
})

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 20,
  total: 0,
  from: 0,
  to: 0
})

const getTypeClass = (type) => {
  const classes = {
    'WAREHOUSE': 'bg-blue-100 text-blue-800',
    'OUTLET': 'bg-green-100 text-green-800',
    'FNB': 'bg-purple-100 text-purple-800',
    'DEPARTMENT': 'bg-yellow-100 text-yellow-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.currentPage = 1
    loadLocations()
  }, 500)
}

const loadLocations = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.type) params.type = filters.value.type
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active
    
    const response = await locationService.getLocations(params)
    
    // Handle paginated response
    if (response.data) {
      locations.value = response.data || []
      pagination.value.currentPage = response.current_page || 1
      pagination.value.lastPage = response.last_page || 1
      pagination.value.perPage = response.per_page || 20
      pagination.value.total = response.total || 0
      pagination.value.from = response.from || 0
      pagination.value.to = response.to || 0
    } else {
      locations.value = response || []
    }
  } catch (err) {
    console.error('Error loading locations:', err)
    error.value = err.response?.data?.message || 'Failed to load locations'
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  selectedLocation.value = null
  showModal.value = true
}

const openEditModal = (location) => {
  selectedLocation.value = location
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedLocation.value = null
}

const handleLocationSaved = () => {
  closeModal()
  loadLocations()
}

const confirmDelete = (location) => {
  locationToDelete.value = location
  showDeleteConfirm.value = true
}

const handleDelete = async () => {
  try {
    deleting.value = true
    await locationService.deleteLocation(locationToDelete.value.id)
    showDeleteConfirm.value = false
    locationToDelete.value = null
    loadLocations()
  } catch (err) {
    console.error('Error deleting location:', err)
    error.value = err.response?.data?.message || 'Failed to delete location'
    showDeleteConfirm.value = false
  } finally {
    deleting.value = false
  }
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadLocations()
}

onMounted(() => {
  loadLocations()
})
</script>
