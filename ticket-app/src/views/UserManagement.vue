<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage system users and permissions</p>
        </div>
        <button 
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add User
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
              placeholder="Name or email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>

          <!-- Role Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select
              v-model="filters.role"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="() => { pagination.currentPage = 1; loadUsers(); }"
            >
              <option value="">All Roles</option>
              <option value="owner">Owner</option>
              <option value="supervisor">Supervisor</option>
              <option value="technician">Technician</option>
              <option value="staff">Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.is_active"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="() => { pagination.currentPage = 1; loadUsers(); }"
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
        <button @click="loadUsers" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
          Try again
        </button>
      </div>

      <!-- Users Table -->
      <div v-else-if="users.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                      <span class="text-white text-sm font-semibold">{{ user.name?.charAt(0) }}</span>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span :class="getRoleClass(user.role)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize inline-block w-fit">
                      {{ user.role }}
                    </span>
                    <span v-if="user.is_technician" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 inline-block w-fit">
                      🔧 Technician
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.location?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="openEditModal(user)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    v-if="user.id !== authStore.user?.id"
                    @click="confirmDelete(user)"
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new user.</p>
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="!loading && !error && users.length > 0 && pagination.total > 0"
        :current-page="pagination.currentPage"
        :total-pages="pagination.lastPage"
        :total="pagination.total"
        :per-page="pagination.perPage"
        :from-item="pagination.from"
        :to-item="pagination.to"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit User Modal -->
    <UserFormModal 
      v-if="showModal" 
      :user="selectedUser"
      @close="closeModal"
      @saved="handleUserSaved"
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
          <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Delete User</h3>
          <div class="mt-2 px-4 py-3">
            <p class="text-sm text-gray-500 text-center">
              Are you sure you want to delete this user?
            </p>
            <p class="text-sm font-semibold text-gray-900 text-center mt-2">
              {{ userToDelete?.name }}
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
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'
import UserFormModal from '@/components/UserFormModal.vue'
import Pagination from '@/components/Pagination.vue'
import userService from '@/services/userService'

const authStore = useAuthStore()

const users = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const selectedUser = ref(null)
const showDeleteConfirm = ref(false)
const userToDelete = ref(null)
const deleting = ref(false)

const filters = ref({
  search: '',
  role: '',
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

const getRoleClass = (role) => {
  const classes = {
    'owner': 'bg-purple-100 text-purple-800',
    'supervisor': 'bg-blue-100 text-blue-800',
    'technician': 'bg-green-100 text-green-800',
    'staff': 'bg-gray-100 text-gray-800',
    'admin': 'bg-red-100 text-red-800'
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.currentPage = 1
    loadUsers()
  }, 500)
}

const loadUsers = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active
    
    const response = await userService.getUsers(params)
    users.value = response.data.data || response.data
    
    // Update pagination info
    pagination.value.currentPage = response.data.current_page || 1
    pagination.value.lastPage = response.data.last_page || 1
    pagination.value.perPage = response.data.per_page || 20
    pagination.value.total = response.data.total || 0
    pagination.value.from = response.data.from || 0
    pagination.value.to = response.data.to || 0
  } catch (err) {
    console.error('Error loading users:', err)
    error.value = err.response?.data?.message || 'Failed to load users'
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  selectedUser.value = null
  showModal.value = true
}

const openEditModal = (user) => {
  selectedUser.value = user
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedUser.value = null
}

const handleUserSaved = () => {
  closeModal()
  loadUsers()
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteConfirm.value = true
}

const handleDelete = async () => {
  try {
    deleting.value = true
    await userService.deleteUser(userToDelete.value.id)
    showDeleteConfirm.value = false
    userToDelete.value = null
    loadUsers()
  } catch (err) {
    console.error('Error deleting user:', err)
    error.value = err.response?.data?.message || 'Failed to delete user'
    showDeleteConfirm.value = false
  } finally {
    deleting.value = false
  }
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadUsers()
}

onMounted(() => {
  loadUsers()
})
</script>
