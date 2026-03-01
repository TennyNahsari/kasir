<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">{{ $t('users.title') }}</h1>
      <button @click="openAddModal" class="btn btn-primary">
        + {{ $t('users.addUser') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="label">{{ $t('users.searchLabel') }}</label>
          <input v-model="filters.search" @input="loadUsers" type="text" class="input" :placeholder="$t('users.searchPlaceholder')">
        </div>
        <div>
          <label class="label">{{ $t('users.roleLabel') }}</label>
          <select v-model="filters.role" @change="loadUsers" class="input">
            <option value="">{{ $t('users.allRoles') }}</option>
            <option value="owner">{{ $t('users.owner') }}</option>
            <option value="inventory">{{ $t('users.inventory') }}</option>
            <option value="supervisor">{{ $t('users.supervisor') }}</option>
            <option value="staff">{{ $t('users.staff') }}</option>
          </select>
        </div>
        <div>
          <label class="label">{{ $t('users.outletLabel') }}</label>
          <select v-model="filters.outlet_id" @change="loadUsers" class="input">
            <option value="">{{ $t('users.allOutlets') }}</option>
            <option v-for="outlet in outlets" :key="outlet.outlet_id" :value="outlet.outlet_id">
              {{ outlet.location_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="label">{{ $t('users.statusLabel') }}</label>
          <select v-model="filters.is_active" @change="loadUsers" class="input">
            <option value="">{{ $t('users.allStatus') }}</option>
            <option value="1">{{ $t('users.active') }}</option>
            <option value="0">{{ $t('users.inactive') }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.name') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.email') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.role') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.outlet') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.status') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ user.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-purple-100 text-purple-800': user.role === 'owner',
                    'bg-indigo-100 text-indigo-800': user.role === 'inventory',
                    'bg-blue-100 text-blue-800': user.role === 'supervisor',
                    'bg-gray-100 text-gray-800': user.role === 'staff'
                  }">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ user.location?.name || user.outlet?.location?.name || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                  class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ user.is_active ? $t('users.active') : $t('users.inactive') }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="openEditModal(user)" class="text-blue-600 hover:text-blue-900 mr-3">{{ $t('common.edit') }}</button>
                <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">{{ $t('common.delete') }}</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-if="pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @page-changed="loadUsers"
      />
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-xl font-bold mb-4">{{ editingUser ? $t('users.modalEditTitle') : $t('users.modalAddTitle') }}</h2>

        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="label">{{ $t('users.name') }} *</label>
            <input v-model="form.name" type="text" class="input" required>
          </div>

          <div>
            <label class="label">{{ $t('users.email') }} *</label>
            <input v-model="form.email" type="email" class="input" required>
          </div>

          <div>
            <label class="label">{{ $t('users.password') }} {{ editingUser ? $t('users.passwordHintEdit') : $t('users.passwordRequired') }}</label>
            <input v-model="form.password" type="password" class="input" :required="!editingUser" minlength="6">
          </div>

          <div>
            <label class="label">{{ $t('users.role') }} *</label>
            <select v-model="form.role" class="input" required>
              <option value="">{{ $t('users.selectRole') }}</option>
              <option value="owner">{{ $t('users.owner') }}</option>
              <option value="inventory">{{ $t('users.inventory') }}</option>
              <option value="supervisor">{{ $t('users.supervisor') }}</option>
              <option value="staff">{{ $t('users.staff') }}</option>
            </select>
          </div>

          <div class="relative">
            <label class="label">{{ $t('users.location') }}</label>
            <input 
              v-model="locationSearch" 
              @focus="showLocationDropdown = true"
              @input="filterLocations"
              @blur="handleLocationBlur"
              type="text" 
              class="input"
              :placeholder="$t('users.locationPlaceholder')"
              autocomplete="off"
            >
            <div 
              v-if="showLocationDropdown && filteredLocations.length > 0" 
              class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
            >
              <div 
                v-for="location in filteredLocations" 
                :key="location.location_id"
                @click="selectLocation(location)"
                class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0"
              >
                <div class="font-medium text-gray-900">{{ location.location_name }}</div>
                <div class="text-sm text-gray-500">Type: {{ location.location_type }}</div>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              {{ $t('users.locationHelper') }}
            </p>
          </div>

          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600">
            <label class="ml-2 text-sm text-gray-700">{{ $t('users.active') }}</label>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="closeModal" class="btn btn-secondary">{{ $t('common.cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ editingUser ? $t('users.update') : $t('users.create') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'

const { t } = useI18n()
const users = ref([])
const outlets = ref([])
const locationSearch = ref('')
const showLocationDropdown = ref(false)
const filteredLocations = ref([])
const showModal = ref(false)
const editingUser = ref(null)

const filters = ref({
  search: '',
  role: '',
  outlet_id: '',
  is_active: ''
})

const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
  outlet_id: null,
  location_id: null,
  is_active: true
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

const loadUsers = async (page = 1) => {
  try {
    const params = { page }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.outlet_id) params.outlet_id = filters.value.outlet_id
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    const response = await api.get('/users', { params })
    console.log('loadUsers response:', response.data.data)
    users.value = response.data.data
    
    // Check first user's outlet data
    if (users.value.length > 0) {
      console.log('First user outlet:', users.value[0].outlet)
      console.log('First user outlet.location:', users.value[0].outlet?.location)
    }
    
    // Find user id=3 (Kasir Retail) to verify update
    const user3 = users.value.find(u => u.id === 3)
    if (user3) {
      console.log('User id=3 (Kasir Retail):', user3)
      console.log('User id=3 outlet_id:', user3.outlet_id)
      console.log('User id=3 outlet:', user3.outlet)
      console.log('User id=3 outlet.location:', user3.outlet?.location)
    }
    
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    }
  } catch (error) {
    alert(t('users.loadFailed') + ': ' + (error.response?.data?.message || error.message))
  }
}

const loadOutlets = async () => {
  try {
    // Load all active locations from inventory
    const response = await api.get('/locations?is_active=true&per_page=100')
    
    console.log('Raw locations response:', response.data)
    
    // Extract locations array from paginated response
    const locationsList = response.data.data || response.data
    
    // Map to outlet format with location info
    outlets.value = locationsList.map(loc => ({
      location_id: loc.id,
      location_name: loc.name,
      location_type: loc.type,
      outlet_id: loc.outlet?.id || null,
      outlet_name: loc.outlet?.name || loc.name
    }))
    
    filteredLocations.value = outlets.value
    
    console.log('Loaded locations:', outlets.value)
    console.table(outlets.value)
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const filterLocations = () => {
  const search = locationSearch.value.toLowerCase()
  if (!search) {
    filteredLocations.value = outlets.value
  } else {
    filteredLocations.value = outlets.value.filter(location => 
      location.location_name.toLowerCase().includes(search) ||
      location.location_type.toLowerCase().includes(search)
    )
  }
  showLocationDropdown.value = true
}

const selectLocation = (location) => {
  form.value.location_id = location.location_id
  locationSearch.value = location.location_name
  showLocationDropdown.value = false
}

const handleLocationBlur = () => {
  setTimeout(() => {
    showLocationDropdown.value = false
  }, 200)
}

const openAddModal = () => {
  editingUser.value = null
  locationSearch.value = ''
  showLocationDropdown.value = false
  filteredLocations.value = outlets.value
  form.value = {
    name: '',
    email: '',
    password: '',
    role: '',
    outlet_id: null,
    location_id: null,
    is_active: true
  }
  showModal.value = true
}

const openEditModal = (user) => {
  editingUser.value = user
  // Set location search with current location name
  const currentLocation = outlets.value.find(o => o.location_id === user.location_id)
  locationSearch.value = currentLocation ? currentLocation.location_name : ''
  showLocationDropdown.value = false
  filteredLocations.value = outlets.value
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    outlet_id: user.outlet_id,
    location_id: user.location_id,
    is_active: user.is_active
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
}

const saveUser = async () => {
  try {
    const data = { ...form.value }
    
    // Convert boolean to 1/0 for backend
    if (typeof data.is_active === 'boolean') {
      data.is_active = data.is_active ? 1 : 0
    }
    
    // Convert outlet_id to number (in case it's string from select)
    if (data.outlet_id !== null && data.outlet_id !== '') {
      data.outlet_id = parseInt(data.outlet_id)
    }
    
    // Convert location_id to number
    if (data.location_id !== null && data.location_id !== '') {
      data.location_id = parseInt(data.location_id)
    } else {
      data.location_id = null
    }
    
    console.log('Saving user with data:', data)
    console.log('Selected outlet from outlets list:', outlets.value.find(o => o.location_id === data.location_id))
    
    // Remove password if empty (for edit)
    if (editingUser.value && !data.password) {
      delete data.password
    }

    if (editingUser.value) {
      const response = await api.put(`/users/${editingUser.value.id}`, data)
      console.log('Update response:', response.data)
      console.log('Updated user outlet_id:', response.data.outlet_id)
      console.log('Updated user outlet:', response.data.outlet)
      console.log('Response full JSON:', JSON.stringify(response.data, null, 2))
      alert(t('users.updateSuccess'))
    } else {
      const response = await api.post('/users', data)
      console.log('Create response:', response.data)
      alert(t('users.createSuccess'))
    }
    
    closeModal()
    await loadUsers()
  } catch (error) {
    console.error('Save user error:', error)
    alert(t('users.saveFailed') + ': ' + (error.response?.data?.message || error.message))
  }
}

const deleteUser = async (user) => {
  if (!confirm(t('users.deleteConfirm', { name: user.name }))) return

  try {
    await api.delete(`/users/${user.id}`)
    alert(t('users.deleteSuccess'))
    await loadUsers()
  } catch (error) {
    alert(t('users.deleteFailed') + ': ' + (error.response?.data?.message || error.message))
  }
}

onMounted(() => {
  loadUsers()
  loadOutlets()
})
</script>
