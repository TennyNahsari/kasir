<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Category Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage product categories</p>
        </div>
        <button 
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Category
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <p class="text-red-800">{{ error }}</p>
        <button @click="loadCategories" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
          Try again
        </button>
      </div>

      <!-- Categories Table -->
      <div v-else-if="categories.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div 
                      v-if="category.color" 
                      class="w-4 h-4 rounded-full mr-3" 
                      :style="{ backgroundColor: category.color }"
                    ></div>
                    <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-500">{{ category.description || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    {{ category.products_count || 0 }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ category.sort_order || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="openEditModal(category)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(category)"
                    class="text-red-600 hover:text-red-900"
                    :disabled="category.products_count > 0"
                    :class="category.products_count > 0 ? 'opacity-50 cursor-not-allowed' : ''"
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No categories found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new category.</p>
        <div class="mt-6">
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Category
          </button>
        </div>
      </div>
    </div>

    <!-- Category Form Modal -->
    <CategoryFormModal
      v-if="showModal"
      :category="selectedCategory"
      @close="closeModal"
      @saved="handleSaved"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Delete Category</h3>
          <div class="mt-2 px-4 py-3">
            <p class="text-sm text-gray-500 text-center">
              Are you sure you want to delete "{{ categoryToDelete?.name }}"? This action cannot be undone.
            </p>
          </div>
          <div class="flex gap-4 px-4 py-3">
            <button
              @click="showDeleteConfirm = false"
              class="flex-1 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
              :disabled="deleting"
            >
              Cancel
            </button>
            <button
              @click="deleteCategory"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
              :disabled="deleting"
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
import CategoryFormModal from '@/components/CategoryFormModal.vue'
import axios from '@/utils/axios'

const categories = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const selectedCategory = ref(null)
const showDeleteConfirm = ref(false)
const categoryToDelete = ref(null)
const deleting = ref(false)

const loadCategories = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await axios.get('/categories')
    categories.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading categories:', err)
    error.value = err.response?.data?.message || 'Failed to load categories'
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  selectedCategory.value = null
  showModal.value = true
}

const openEditModal = (category) => {
  selectedCategory.value = category
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedCategory.value = null
}

const handleSaved = () => {
  closeModal()
  loadCategories()
}

const confirmDelete = (category) => {
  categoryToDelete.value = category
  showDeleteConfirm.value = true
}

const deleteCategory = async () => {
  try {
    deleting.value = true
    await axios.delete(`/categories/${categoryToDelete.value.id}`)
    showDeleteConfirm.value = false
    categoryToDelete.value = null
    loadCategories()
  } catch (err) {
    console.error('Error deleting category:', err)
    error.value = err.response?.data?.message || 'Failed to delete category'
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadCategories()
})
</script>
