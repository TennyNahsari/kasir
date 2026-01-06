<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
      <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + Add User
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @input="loadUsers" type="text" class="w-full border-gray-300 rounded-lg" placeholder="Name or email...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="filters.role" @change="loadUsers" class="w-full border-gray-300 rounded-lg">
            <option value="">All Roles</option>
            <option value="owner">Owner</option>
            <option value="supervisor">Supervisor</option>
            <option value="kasir">Kasir</option>
            <option value="kitchen">Kitchen</option>
            <option value="staff">Staff</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.is_active" @change="loadUsers" class="w-full border-gray-300 rounded-lg">
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
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-500">{{ user.email }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-purple-100 text-purple-800': user.role === 'owner',
                    'bg-blue-100 text-blue-800': user.role === 'supervisor',
                    'bg-green-100 text-green-800': user.role === 'kasir',
                    'bg-orange-100 text-orange-800': user.role === 'kitchen',
                    'bg-gray-100 text-gray-800': user.role === 'staff'
                  }">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ user.location?.name || '-' }}</div>
              </td>
              <td class="px-6 py-4">
                <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                  class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm space-x-2">
                <button @click="openEditModal(user)" class="text-blue-600 hover:text-blue-900">Edit</button>
                <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingUser ? 'Edit' : 'Add' }} User</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="userForm.name" type="text" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input v-model="userForm.email" type="email" class="w-full border-gray-300 rounded-lg" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ editingUser ? '(leave blank to keep)' : '*' }}</label>
            <input v-model="userForm.password" type="password" class="w-full border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <select v-model="userForm.role" class="w-full border-gray-300 rounded-lg" required>
              <option value="owner">Owner</option>
              <option value="supervisor">Supervisor</option>
              <option value="kasir">Kasir</option>
              <option value="kitchen">Kitchen</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <div v-if="userForm.role !== 'owner'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select v-model="userForm.location_id" class="w-full border-gray-300 rounded-lg">
              <option value="">Select Location</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }} ({{ loc.type }})
              </option>
            </select>
          </div>
          <div class="flex items-center">
            <input v-model="userForm.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 mr-2">
            <label class="text-sm font-medium text-gray-700">Active</label>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="saveUser" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
        </div>
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
  is_active: ''
})

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'kasir',
  location_id: '',
  is_active: true
})

onMounted(async () => {
  await Promise.all([loadUsers(), loadLocations()])
})

const loadUsers = async () => {
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active
    
    const { data } = await api.get('/users', { params })
    console.log('Users loaded:', data)
    users.value = Array.isArray(data) ? data : (data.data || [])
  } catch (error) {
    console.error('Failed to load users:', error)
    alert('Failed to load users: ' + (error.response?.data?.message || error.message))
  }
}

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const openAddModal = () => {
  editingUser.value = null
  userForm.value = {
    name: '',
    email: '',
    password: '',
    role: 'kasir',
    location_id: '',
    is_active: true
  }
  showModal.value = true
}

const openEditModal = (user) => {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    location_id: user.location_id || '',
    is_active: user.is_active
  }
  showModal.value = true
}

const saveUser = async () => {
  try {
    const payload = { ...userForm.value }
    if (editingUser.value && !payload.password) {
      delete payload.password
    }
    
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, payload)
      alert('User updated successfully')
    } else {
      await api.post('/users', payload)
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
  if (!confirm(`Delete user ${user.name}?`)) return
  
  try {
    await api.delete(`/users/${user.id}`)
    alert('User deleted successfully')
    await loadUsers()
  } catch (error) {
    console.error('Delete user error:', error)
    alert('Failed to delete user: ' + (error.response?.data?.message || error.message))
  }
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
}
</script>
