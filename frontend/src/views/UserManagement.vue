<template>
  <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $t('users.title') }}</h1>
      <button @click="openAddModal" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm sm:text-base">
        {{ $t('users.addUser') }}
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 sm:px-4 sm:py-3 rounded relative text-sm">
      <span class="block sm:inline">{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="absolute top-0 bottom-0 right-0 px-3 py-2 sm:px-4 sm:py-3">
        <span class="text-red-700">&times;</span>
      </button>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 sm:px-4 sm:py-3 rounded relative text-sm">
      <span class="block sm:inline">{{ successMessage }}</span>
      <button @click="successMessage = ''" class="absolute top-0 bottom-0 right-0 px-3 py-2 sm:px-4 sm:py-3">
        <span class="text-green-700">&times;</span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-3 sm:p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.searchLabel') }}</label>
          <input v-model="filters.search" @input="loadUsers" type="text" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('users.searchPlaceholder')">
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.roleLabel') }}</label>
          <select v-model="filters.role" @change="loadUsers" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">{{ $t('users.allRoles') }}</option>
            <option value="owner">{{ $t('users.owner') }}</option>
            <option value="inventory">{{ $t('users.inventory') }}</option>
            <option value="supervisor">{{ $t('users.supervisor') }}</option>
            <option value="staff">{{ $t('users.staff') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.statusLabel') }}</label>
          <select v-model="filters.is_active" @change="loadUsers" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">{{ $t('users.allStatus') }}</option>
            <option value="1">{{ $t('users.active') }}</option>
            <option value="0">{{ $t('users.inactive') }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Users Table - Desktop Only -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('users.tableHeaderName') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('users.tableHeaderEmail') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('users.tableHeaderRole') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('users.tableHeaderLocation') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('users.tableHeaderStatus') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('users.tableHeaderActions') }}</th>
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
                    'bg-indigo-100 text-indigo-800': user.role === 'inventory',
                    'bg-blue-100 text-blue-800': user.role === 'supervisor',
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
                  {{ user.is_active ? $t('users.active') : $t('users.inactive') }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm space-x-2">
                <button @click="openEditModal(user)" class="text-blue-600 hover:text-blue-900">{{ $t('users.edit') }}</button>
                <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">{{ $t('users.delete') }}</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <Pagination
        v-if="pagination.total > 0"
        :current-page="pagination.currentPage"
        :last-page="pagination.lastPage"
        :per-page="pagination.perPage"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @update:current-page="handlePageChange"
        @update:per-page="handlePerPageChange"
      />
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-3">
      <div v-for="user in users" :key="user.id" class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-start mb-3">
          <div class="flex-1">
            <h3 class="font-semibold text-sm text-gray-900">{{ user.name }}</h3>
            <p class="text-xs text-gray-600 mt-0.5">{{ user.email }}</p>
          </div>
          <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
            class="px-2 py-1 text-xs font-semibold rounded-full">
            {{ user.is_active ? $t('users.active') : $t('users.inactive') }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
          <div>
            <span class="text-gray-600">{{ $t('users.roleLabel') }}:</span>
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold inline-block mt-1"
              :class="{
                'bg-purple-100 text-purple-800': user.role === 'owner',
                'bg-indigo-100 text-indigo-800': user.role === 'inventory',
                'bg-blue-100 text-blue-800': user.role === 'supervisor',
                'bg-gray-100 text-gray-800': user.role === 'staff'
              }">
              {{ user.role }}
            </span>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('users.locationLabel') }}:</span>
            <span class="font-medium ml-1">{{ user.location?.name || '-' }}</span>
          </div>
        </div>

        <div class="flex gap-2 pt-3 border-t">
          <button @click="openEditModal(user)" class="flex-1 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
            {{ $t('users.edit') }}
          </button>
          <button @click="deleteUser(user)" class="flex-1 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
            {{ $t('users.delete') }}
          </button>
        </div>
      </div>

      <!-- Pagination (Mobile) -->
      <Pagination
        v-if="pagination.total > 0"
        :current-page="pagination.currentPage"
        :last-page="pagination.lastPage"
        :per-page="pagination.perPage"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @update:current-page="handlePageChange"
        @update:per-page="handlePerPageChange"
      />
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">{{ editingUser ? $t('users.modalTitleEdit') : $t('users.modalTitleAdd') }}</h3>
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.nameLabel') }}</label>
            <input v-model="userForm.name" type="text" class="w-full border-gray-300 rounded-lg text-sm" required>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.emailLabel') }}</label>
            <input v-model="userForm.email" type="email" class="w-full border-gray-300 rounded-lg text-sm" required>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.passwordLabel') }} {{ editingUser ? $t('users.passwordLeaveBlank') : $t('users.passwordRequired') }}</label>
            <input v-model="userForm.password" type="password" class="w-full border-gray-300 rounded-lg text-sm">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.roleLabel') }} *</label>
            <select v-model="userForm.role" class="w-full border-gray-300 rounded-lg text-sm" required>
              <option value="owner">{{ $t('users.owner') }}</option>
              <option value="inventory">{{ $t('users.inventory') }}</option>
              <option value="supervisor">{{ $t('users.supervisor') }}</option>
              <option value="kasir">Kasir (Cashier)</option>
              <option value="staff">{{ $t('users.staff') }}</option>
              <option value="kitchen">Kitchen / Dapur (F&B)</option>
            </select>
          </div>
          <div v-if="!['owner', 'inventory'].includes(userForm.role)">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.locationLabel') }}</label>
            <select v-model="userForm.location_id" class="w-full border-gray-300 rounded-lg text-sm">
              <option value="">{{ $t('users.selectLocation') }}</option>
              <option v-for="loc in filteredLocationOptions" :key="loc.id" :value="loc.id">
                {{ loc.name }} ({{ loc.type }})
              </option>
            </select>
            <p v-if="userForm.role === 'kitchen'" class="text-[11px] text-blue-600 mt-1">
              ℹ️ Role Kitchen hanya dapat di-assign ke lokasi bertipe F&B.
            </p>
            <p v-else-if="['kasir', 'staff', 'supervisor'].includes(userForm.role)" class="text-[11px] text-gray-500 mt-1">
              ℹ️ Pilih lokasi Outlet atau F&B untuk Kasir / Supervisor.
            </p>
          </div>
          <div class="flex items-center">
            <input v-model="userForm.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 mr-2">
            <label class="text-xs sm:text-sm font-medium text-gray-700">{{ $t('users.active') }}</label>
          </div>
        </div>
        <div class="mt-4 sm:mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
          <button @click="closeModal" :disabled="loading" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm">{{ $t('users.cancel') }}</button>
          <button @click="saveUser" :disabled="loading" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center text-sm">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? $t('common.loading') : $t('users.save') }}
          </button>
        </div>
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
const locations = ref([])
const showModal = ref(false)

const filteredLocationOptions = computed(() => {
  if (userForm.value.role === 'kitchen') {
    return locations.value.filter(loc => loc.type === 'FNB')
  }
  if (['kasir', 'staff', 'supervisor'].includes(userForm.value.role)) {
    return locations.value.filter(loc => ['OUTLET', 'FNB'].includes(loc.type))
  }
  return locations.value
})
const editingUser = ref(null)
const errorMessage = ref('')
const successMessage = ref('')
const loading = ref(false)

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 25,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  search: '',
  role: '',
  is_active: ''
})

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'staff',
  location_id: '',
  is_active: true
})

onMounted(async () => {
  await Promise.all([loadUsers(), loadLocations()])
})

const loadUsers = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active
    
    const { data } = await api.get('/users', { params })
    console.log('Users loaded:', data)
    
    // Handle both paginated and non-paginated responses
    if (data.data && data.meta) {
      // Paginated response
      users.value = data.data
      pagination.value = {
        currentPage: data.meta.current_page,
        lastPage: data.meta.last_page,
        perPage: data.meta.per_page,
        total: data.meta.total,
        from: data.meta.from || 0,
        to: data.meta.to || 0
      }
    } else {
      // Non-paginated response
      users.value = Array.isArray(data) ? data : (data.data || [])
      pagination.value = {
        currentPage: 1,
        lastPage: 1,
        perPage: users.value.length,
        total: users.value.length,
        from: users.value.length > 0 ? 1 : 0,
        to: users.value.length
      }
    }
  } catch (error) {
    console.error('Failed to load users:', error)
    errorMessage.value = 'Failed to load users: ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadUsers()
}

const handlePerPageChange = (perPage) => {
  pagination.value.perPage = perPage
  pagination.value.currentPage = 1
  loadUsers()
}

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations', { params: { per_page: 1000 } })
    // Handle both paginated and non-paginated responses
    locations.value = data.data ? data.data : (Array.isArray(data) ? data : [])
    console.log('Locations loaded:', locations.value.length)
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
    role: 'staff',
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
    errorMessage.value = ''
    successMessage.value = ''
    loading.value = true
    
    const payload = { ...userForm.value }
    if (editingUser.value && !payload.password) {
      delete payload.password
    }
    
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, payload)
      successMessage.value = t('users.updateSuccess')
    } else {
      await api.post('/users', payload)
      successMessage.value = t('users.createSuccess')
    }
    
    closeModal()
    await loadUsers()
    
    // Auto-hide success message after 3 seconds
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Save user error:', error)
    errorMessage.value = t('users.' + (editingUser.value ? 'updateFailed' : 'createFailed')) + ': ' + (error.response?.data?.message || error.message)
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorList = Object.values(errors).flat().join(', ')
      errorMessage.value += ' - ' + errorList
    }
  } finally {
    loading.value = false
  }
}

const deleteUser = async (user) => {
  if (!confirm(t('users.deleteConfirm', { name: user.name }))) return
  
  try {
    errorMessage.value = ''
    successMessage.value = ''
    loading.value = true
    
    await api.delete(`/users/${user.id}`)
    successMessage.value = t('users.deleteSuccess')
    await loadUsers()
    
    // Auto-hide success message after 3 seconds
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Delete user error:', error)
    errorMessage.value = t('users.deleteFailed') + ': ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
}
</script>
