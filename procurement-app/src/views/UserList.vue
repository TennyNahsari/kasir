<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
      <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        + Add User
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @input="loadUsers" type="text" class="w-full border-gray-300 rounded" placeholder="Name or email...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="filters.role" @change="loadUsers" class="w-full border-gray-300 rounded">
            <option value="">All Roles</option>
            <option value="owner">Owner</option>
            <option value="supervisor">Supervisor</option>
            <option value="procurement">Procurement</option>
            <option value="warehouse">Warehouse</option>
            <option value="staff">Staff</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <select v-model="filters.location_id" @change="loadUsers" class="w-full border-gray-300 rounded">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.is_active" @change="loadUsers" class="w-full border-gray-300 rounded">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
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
                    'bg-blue-100 text-blue-800': user.role === 'supervisor',
                    'bg-green-100 text-green-800': user.role === 'procurement',
                    'bg-orange-100 text-orange-800': user.role === 'warehouse',
                    'bg-gray-100 text-gray-800': user.role === 'staff'
                  }">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ user.location?.name || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                  class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="openEditModal(user)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-xl font-bold mb-4">{{ editingUser ? 'Edit User' : 'Add User' }}</h2>

        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="form.name" type="text" class="w-full border-gray-300 rounded" required>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input v-model="form.email" type="email" class="w-full border-gray-300 rounded" required>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ editingUser ? '(leave blank to keep current)' : '*' }}</label>
            <input v-model="form.password" type="password" class="w-full border-gray-300 rounded" :required="!editingUser" minlength="6">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <select v-model="form.role" class="w-full border-gray-300 rounded" required>
              <option value="">-- Select Role --</option>
              <option value="owner">Owner</option>
              <option value="supervisor">Supervisor</option>
              <option value="procurement">Procurement</option>
              <option value="warehouse">Warehouse</option>
              <option value="staff">Staff</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select v-model="form.location_id" class="w-full border-gray-300 rounded">
              <option :value="null">-- No Location (for Owner) --</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }} ({{ loc.type }})
              </option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
              Set location untuk user. Owner tidak wajib pilih location.
            </p>
          </div>

          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600">
            <label class="ml-2 text-sm text-gray-700">Active</label>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ editingUser ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const users = ref([])
const locations = ref([])
const showModal = ref(false)
const editingUser = ref(null)

const filters = ref({
  search: '',
  role: '',
  location_id: '',
  is_active: ''
})

const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
  location_id: null,
  is_active: true
})

const loadUsers = async () => {
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.location_id) params.location_id = filters.value.location_id
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    const response = await api.get('/users', { params })
    users.value = response.data.data || response.data
  } catch (error) {
    alert('Failed to load users: ' + (error.response?.data?.message || error.message))
  }
}

const loadLocations = async () => {
  try {
    const response = await api.get('/locations', { params: { is_active: true } })
    locations.value = response.data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const openAddModal = () => {
  editingUser.value = null
  form.value = {
    name: '',
    email: '',
    password: '',
    role: '',
    location_id: null,
    is_active: true
  }
  showModal.value = true
}

const openEditModal = (user) => {
  editingUser.value = user
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
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
    
    if (typeof data.is_active === 'boolean') {
      data.is_active = data.is_active ? 1 : 0
    }
    
    if (data.location_id !== null && data.location_id !== '') {
      data.location_id = parseInt(data.location_id)
    } else {
      data.location_id = null
    }
    
    if (editingUser.value && !data.password) {
      delete data.password
    }

    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, data)
      alert('User updated successfully')
    } else {
      await api.post('/users', data)
      alert('User created successfully')
    }
    
    closeModal()
    await loadUsers()
  } catch (error) {
    console.error('Save user error:', error)
    alert('Failed to save user: ' + (error.response?.data?.message || error.message))
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) return

  try {
    await api.delete(`/users/${user.id}`)
    alert('User deleted successfully')
    await loadUsers()
  } catch (error) {
    alert('Failed to delete user: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(() => {
  loadUsers()
  loadLocations()
})
</script>
