<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Departments</h1>
      <button @click="showForm = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + New Department
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <input v-model="filters.search" @input="loadDepartments" type="text" placeholder="Search departments..." 
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
          <select v-model="filters.is_active" @change="loadDepartments"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Departments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Budget Limit</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="font-mono text-sm text-gray-900">{{ dept.code }}</span>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-gray-900">{{ dept.name }}</div>
              <div class="text-sm text-gray-500">{{ dept.cost_center }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-900">{{ dept.manager_name }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-900">{{ dept.phone }}</div>
              <div class="text-sm text-gray-500">{{ dept.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
              <span v-if="dept.budget_limit">Rp {{ Number(dept.budget_limit).toLocaleString('id-ID') }}</span>
              <span v-else class="text-gray-400">-</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span :class="dept.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ dept.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
              <button @click="editDepartment(dept)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
              <button @click="deleteDepartment(dept)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="departments.length === 0" class="text-center py-12">
        <p class="text-gray-500">No departments found</p>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ editMode ? 'Edit' : 'New' }} Department</h2>
            <button @click="closeForm" class="text-gray-500 hover:text-gray-700">✕</button>
          </div>

          <form @submit.prevent="saveDepartment" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Code *</label>
                <input v-model="form.code" type="text" required
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input v-model="form.name" type="text" required
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Manager Name</label>
                <input v-model="form.manager_name" type="text"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input v-model="form.phone" type="text"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input v-model="form.email" type="email"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                <input v-model="form.cost_center" type="text"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Budget Limit</label>
              <input v-model.number="form.budget_limit" type="number" step="0.01"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea v-model="form.description" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="mr-2">
              <label class="text-sm font-medium text-gray-700">Active</label>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t">
              <button type="button" @click="closeForm" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const departments = ref([])
const showForm = ref(false)
const editMode = ref(false)
const saving = ref(false)

const filters = ref({
  search: '',
  is_active: ''
})

const form = ref({
  code: '',
  name: '',
  manager_name: '',
  phone: '',
  email: '',
  cost_center: '',
  budget_limit: null,
  is_active: true,
  description: ''
})

onMounted(() => {
  loadDepartments()
})

const loadDepartments = async () => {
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    const { data } = await api.get('/departments', { params })
    departments.value = data
  } catch (error) {
    console.error('Failed to load departments:', error)
    alert('Failed to load departments')
  }
}

const editDepartment = (dept) => {
  form.value = { ...dept }
  editMode.value = true
  showForm.value = true
}

const deleteDepartment = async (dept) => {
  if (!confirm(`Delete department "${dept.name}"?`)) return
  
  try {
    await api.delete(`/departments/${dept.id}`)
    alert('Department deleted successfully')
    await loadDepartments()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete department')
  }
}

const saveDepartment = async () => {
  saving.value = true
  try {
    if (editMode.value) {
      await api.put(`/departments/${form.value.id}`, form.value)
      alert('Department updated successfully')
    } else {
      await api.post('/departments', form.value)
      alert('Department created successfully')
    }
    closeForm()
    await loadDepartments()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save department')
  } finally {
    saving.value = false
  }
}

const closeForm = () => {
  showForm.value = false
  editMode.value = false
  form.value = {
    code: '',
    name: '',
    manager_name: '',
    phone: '',
    email: '',
    cost_center: '',
    budget_limit: null,
    is_active: true,
    description: ''
  }
}
</script>
