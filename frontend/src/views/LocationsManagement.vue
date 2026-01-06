<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Locations</h1>
        <p class="text-gray-600">Manage warehouses, outlets, and F&B locations</p>
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
            <p class="text-sm text-gray-500 mt-1">{{ location.code }}</p>
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
            <button @click="deleteLocation(location)" class="text-red-600 hover:text-red-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
          <div>
            <span :class="location.is_active ? 'text-green-600' : 'text-red-600'" class="font-semibold">
              {{ location.is_active ? '● Active' : '● Inactive' }}
            </span>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
            <input v-model="locationForm.code" type="text" class="w-full border-gray-300 rounded-lg" placeholder="e.g. WH-001, OUT-001, FNB-001" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="locationForm.name" type="text" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
            <select v-model="locationForm.type" class="w-full border-gray-300 rounded-lg" required>
              <option value="WAREHOUSE">Warehouse</option>
              <option value="OUTLET">Outlet</option>
              <option value="FNB">F&B (Food & Beverage)</option>
              <option value="DEPARTMENT">Department</option>
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
import api from '@/services/api'

const locations = ref([])
const showCreateModal = ref(false)
const editingLocation = ref(null)

const locationForm = ref({
  code: '',
  name: '',
  type: 'OUTLET',
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
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const getTypeClass = (type) => {
  if (type === 'WAREHOUSE') return 'bg-blue-100 text-blue-800'
  if (type === 'OUTLET') return 'bg-green-100 text-green-800'
  if (type === 'FNB') return 'bg-orange-100 text-orange-800'
  if (type === 'DEPARTMENT') return 'bg-purple-100 text-purple-800'
  return 'bg-gray-100 text-gray-800'
}

const editLocation = (location) => {
  editingLocation.value = location
  locationForm.value = {
    code: location.code,
    name: location.name,
    type: location.type,
    address: location.address || '',
    phone: location.phone || '',
    is_active: location.is_active
  }
  showCreateModal.value = true
}

const saveLocation = async () => {
  try {
    if (!locationForm.value.code || !locationForm.value.name || !locationForm.value.type) {
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
    console.error('Save location error:', error)
    alert('Failed to save location: ' + (error.response?.data?.message || error.message))
  }
}

const deleteLocation = async (location) => {
  if (!confirm(`Delete location ${location.name}?`)) return
  
  try {
    await api.delete(`/locations/${location.id}`)
    alert('Location deleted successfully')
    await loadLocations()
  } catch (error) {
    console.error('Delete location error:', error)
    alert('Failed to delete location: ' + (error.response?.data?.message || error.message))
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingLocation.value = null
  locationForm.value = {
    code: '',
    name: '',
    type: 'OUTLET',
    address: '',
    phone: '',
    is_active: true
  }
}
</script>
