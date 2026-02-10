<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage master product catalog</p>
        </div>
        <button 
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Product
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
              placeholder="SKU, name, or barcode..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>

          <!-- Category Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="filters.category_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadProducts"
            >
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.is_active"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadProducts"
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
        <button @click="loadProducts" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
          Try again
        </button>
      </div>

      <!-- Products Table -->
      <div v-else-if="products.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ product.sku }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                  <div v-if="product.description" class="text-sm text-gray-500">{{ product.description }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ product.category?.name || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(product.selling_price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ product.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="openEditModal(product)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(product)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination
          :current-page="pagination.currentPage"
          :total-pages="pagination.lastPage"
          :total="pagination.total"
          :per-page="pagination.perPage"
          :from-item="pagination.from"
          :to-item="pagination.to"
          @page-change="handlePageChange"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new product.</p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <ProductFormModal
      v-if="showModal"
      :product="selectedProduct"
      :categories="categories"
      @close="closeModal"
      @saved="handleSaved"
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
          <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Delete Product</h3>
          <div class="mt-2 px-4 py-3">
            <p class="text-sm text-gray-500 text-center">
              Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
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
              @click="deleteProduct"
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
import Pagination from '@/components/Pagination.vue'
import ProductFormModal from '@/components/ProductFormModal.vue'
import axios from '@/utils/axios'

// Simple debounce implementation
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

const products = ref([])
const categories = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const selectedProduct = ref(null)
const showDeleteConfirm = ref(false)
const productToDelete = ref(null)
const deleting = ref(false)

const filters = ref({
  search: '',
  category_id: '',
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

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value || 0)
}

const loadProducts = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Only include non-empty filter values
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }
    
    // Add filters only if they have values
    if (filters.value.search) {
      params.search = filters.value.search
    }
    if (filters.value.category_id) {
      params.category_id = filters.value.category_id
    }
    if (filters.value.is_active !== '') {
      params.is_active = filters.value.is_active
    }
    
    const response = await axios.get('/products', { params })
    
    // Laravel paginate returns { data: [...], current_page, last_page, etc }
    products.value = response.data.data || []
    
    pagination.value = {
      currentPage: response.data.current_page || 1,
      lastPage: response.data.last_page || 1,
      perPage: response.data.per_page || 20,
      total: response.data.total || 0,
      from: response.data.from || 0,
      to: response.data.to || 0
    }
  } catch (err) {
    console.error('Error loading products:', err)
    error.value = err.response?.data?.message || 'Failed to load products'
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await axios.get('/categories')
    categories.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading categories:', err)
  }
}

const debouncedSearch = debounce(() => {
  pagination.value.currentPage = 1
  loadProducts()
}, 500)

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadProducts()
}

const openCreateModal = () => {
  selectedProduct.value = null
  showModal.value = true
}

const openEditModal = (product) => {
  selectedProduct.value = product
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedProduct.value = null
}

const handleSaved = () => {
  closeModal()
  loadProducts()
}

const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteConfirm.value = true
}

const deleteProduct = async () => {
  try {
    deleting.value = true
    await axios.delete(`/products/${productToDelete.value.id}`)
    showDeleteConfirm.value = false
    productToDelete.value = null
    loadProducts()
  } catch (err) {
    console.error('Error deleting product:', err)
    error.value = err.response?.data?.message || 'Failed to delete product'
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>
