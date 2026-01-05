<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Locations</h1>
        <p class="text-gray-600">Manage warehouses and outlets</p>
      </div>
      <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + Add Location
      </button>
    </div>

    <!-- Locations Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="location in locations" :key="location.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ location.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span :class="getTypeClass(location.type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ location.type }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ location.address || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" 
                  class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ location.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="editLocation(location)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button @click="deleteLocation(location)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
            <tr v-if="locations.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                No locations found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">{{ showEditModal ? 'Edit Location' : 'Add Location' }}</h2>
        <form @submit.prevent="showEditModal ? updateLocation() : createLocation()">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input v-model="form.name" type="text" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
              <select v-model="form.type" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="WAREHOUSE">Warehouse</option>
                <option value="OUTLET">Outlet</option>
                <option value="STORE">Store</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <textarea v-model="form.address" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input v-model="form.phone" type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2">
              <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              {{ showEditModal ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const locations = ref([])
const showAddModal = ref(false)
const showEditModal = ref(false)
const form = ref({
  name: '',
  type: 'WAREHOUSE',
  address: '',
  phone: '',
  is_active: true
})

onMounted(() => {
  loadLocations()
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const createLocation = async () => {
  try {
    await api.post('/locations', form.value)
    alert('Location created successfully')
    closeModal()
    await loadLocations()
  } catch (error) {
    alert('Failed to create location: ' + (error.response?.data?.message || error.message))
  }
}

const editLocation = (location) => {
  form.value = { ...location }
  showEditModal.value = true
}

const updateLocation = async () => {
  try {
    await api.put(`/locations/${form.value.id}`, form.value)
    alert('Location updated successfully')
    closeModal()
    await loadLocations()
  } catch (error) {
    alert('Failed to update location: ' + (error.response?.data?.message || error.message))
  }
}

const deleteLocation = async (location) => {
  if (!confirm(`Are you sure you want to delete ${location.name}?`)) return
  
  try {
    await api.delete(`/locations/${location.id}`)
    alert('Location deleted successfully')
    await loadLocations()
  } catch (error) {
    alert('Failed to delete location: ' + (error.response?.data?.message || error.message))
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    name: '',
    type: 'WAREHOUSE',
    address: '',
    phone: '',
    is_active: true
  }
}

const getTypeClass = (type) => {
  const classes = {
    'WAREHOUSE': 'bg-blue-100 text-blue-800',
    'OUTLET': 'bg-green-100 text-green-800',
    'STORE': 'bg-purple-100 text-purple-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}
</script>
