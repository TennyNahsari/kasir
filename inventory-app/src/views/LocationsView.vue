<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Locations</h1>
        <p class="text-gray-600">Manage warehouses and outlets</p>
      </div>
      <button @click="showCreateModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Add Location
      </button>
    </div>

    <!-- Locations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="location in locations" :key="location.id" class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ location.name }}</h3>
            <span :class="getTypeClass(location.type)" class="inline-block px-2 py-1 text-xs font-semibold rounded-full mt-1">
              {{ location.type }}
            </span>
          </div>
          <div class="flex space-x-2">
            <button @click="editLocation(location)" class="text-blue-600 hover:text-blue-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button @click="viewStock(location)" class="text-green-600 hover:text-green-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="space-y-2 text-sm text-gray-600">
          <div v-if="location.address">
            <p>{{ location.address }}</p>
          </div>
          <div v-if="location.phone" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            {{ location.phone }}
          </div>
        </div>

        <div v-if="location.stock_summary" class="mt-4 pt-4 border-t">
          <div class="grid grid-cols-2 gap-2 text-sm">
            <div>
              <p class="text-gray-500">Total SKUs</p>
              <p class="text-lg font-semibold">{{ location.stock_summary.total_products }}</p>
            </div>
            <div>
              <p class="text-gray-500">Low Stock Items</p>
              <p class="text-lg font-semibold text-orange-600">{{ location.stock_summary.low_stock_count }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingLocation ? 'Edit' : 'Add' }} Location</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="locationForm.name" type="text" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
            <select v-model="locationForm.type" class="w-full border-gray-300 rounded-lg" required>
              <option value="WAREHOUSE">Warehouse</option>
              <option value="OUTLET">Outlet</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea v-model="locationForm.address" rows="3" class="w-full border-gray-300 rounded-lg"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="locationForm.phone" type="text" class="w-full border-gray-300 rounded-lg">
          </div>
          <div class="flex items-center">
            <input v-model="locationForm.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 mr-2">
            <label class="text-sm font-medium text-gray-700">Active</label>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="saveLocation" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const locations = ref([])
const showCreateModal = ref(false)
const editingLocation = ref(null)

const locationForm = ref({
  name: '',
  type: 'WAREHOUSE',
  address: '',
  phone: '',
  is_active: true
})

onMounted(async () => {
  await loadLocations()
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    // Load stock summary for each location
    for (const location of data) {
      try {
        const { data: summary } = await api.get(`/locations/${location.id}/stock-summary`)
        location.stock_summary = summary
      } catch (error) {
        console.error(`Failed to load stock summary for location ${location.id}:`, error)
      }
    }
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const getTypeClass = (type) => {
  return type === 'WAREHOUSE' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
}

const editLocation = (location) => {
  editingLocation.value = location
  locationForm.value = {
    name: location.name,
    type: location.type,
    address: location.address || '',
    phone: location.phone || '',
    is_active: location.is_active
  }
  showCreateModal.value = true
}

const viewStock = (location) => {
  router.push({
    path: '/inventory/stocks',
    query: { location_id: location.id }
  })
}

const saveLocation = async () => {
  try {
    if (!locationForm.value.name || !locationForm.value.type) {
      alert('Please fill in all required fields')
      return
    }

    if (editingLocation.value) {
      await api.put(`/locations/${editingLocation.value.id}`, locationForm.value)
      alert('Location updated successfully')
    } else {
      await api.post('/locations', locationForm.value)
      alert('Location created successfully')
    }

    closeModal()
    await loadLocations()
  } catch (error) {
    alert('Failed to save location: ' + (error.response?.data?.message || error.message))
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingLocation.value = null
  locationForm.value = {
    name: '',
    type: 'WAREHOUSE',
    address: '',
    phone: '',
    is_active: true
  }
}
</script>
