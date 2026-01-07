<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Master Products</h1>
        <p class="text-gray-600">Manage product master data</p>
      </div>
      <div class="flex space-x-3">
        <button @click="showCategoryModal = true" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
          + Category
        </button>
        <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
          + Add Product
        </button>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @input="loadProducts" type="text" placeholder="Product name or SKU..." class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="filters.category_id" @change="loadProducts" class="w-full border-gray-300 rounded-lg">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select v-model="filters.type" @change="loadProducts" class="w-full border-gray-300 rounded-lg">
            <option value="">All Types</option>
            <option value="INVENTORY">Inventory</option>
            <option value="ASSET">Asset</option>
            <option value="SERVICE">Service</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.is_active" @change="loadProducts" class="w-full border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cost Price</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selling Price</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                {{ product.sku }}
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                <div class="text-xs text-gray-500">{{ product.uom }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ product.category?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span v-if="product.type === 'INVENTORY'" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Inventory</span>
                <span v-else-if="product.type === 'ASSET'" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Asset</span>
                <span v-else-if="product.type === 'SERVICE'" class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Service</span>
                <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ product.type || 'Inventory' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                {{ formatCurrency(product.cost_price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                {{ formatCurrency(product.selling_price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="openEditModal(product)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button @click="deleteProduct(product)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
            <tr v-if="products.length === 0">
              <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                No products found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">{{ editingProduct ? 'Edit' : 'Add' }} Product</h3>
          <form @submit.prevent="saveProduct" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                <input v-model="form.name" type="text" required class="w-full border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                <input v-model="form.sku" type="text" required class="w-full border-gray-300 rounded-lg">
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select v-model="form.category_id" required class="w-full border-gray-300 rounded-lg">
                  <option value="">Select Category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
                <select v-model="form.type" required class="w-full border-gray-300 rounded-lg">
                  <option value="INVENTORY">Inventory (Stok Barang)</option>
                  <option value="ASSET">Asset (Aset Tetap)</option>
                  <option value="SERVICE">Service (Jasa/Layanan)</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                  <span v-if="form.type === 'INVENTORY'">Barang dengan tracking stok</span>
                  <span v-else-if="form.type === 'ASSET'">Aset tetap dengan tracking individual</span>
                  <span v-else-if="form.type === 'SERVICE'">Jasa/layanan tanpa stok</span>
                </p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure *</label>
                <input v-model="form.uom" type="text" required placeholder="pcs, kg, liter, etc." class="w-full border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                <input v-model="form.barcode" type="text" class="w-full border-gray-300 rounded-lg">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price *</label>
                <input v-model.number="form.cost_price" type="number" step="0.01" min="0" required class="w-full border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                <input v-model.number="form.selling_price" type="number" step="0.01" min="0" required class="w-full border-gray-300 rounded-lg">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="3" class="w-full border-gray-300 rounded-lg"></textarea>
            </div>

            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600">
              <label class="ml-2 text-sm text-gray-700">Active</label>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ editingProduct ? 'Update' : 'Create' }} Product
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">Manage Categories</h3>
          
          <div class="mb-4">
            <div class="flex gap-2">
              <input v-model="newCategoryName" type="text" placeholder="New category name..." class="flex-1 border-gray-300 rounded-lg">
              <button @click="addCategory" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add</button>
            </div>
          </div>

          <div class="space-y-2 max-h-64 overflow-y-auto">
            <div v-for="cat in categories" :key="cat.id" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
              <span>{{ cat.name }}</span>
              <button @click="deleteCategory(cat)" class="text-red-600 hover:text-red-700 text-sm">Delete</button>
            </div>
          </div>

          <div class="flex justify-end mt-4 pt-4 border-t">
            <button @click="showCategoryModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const products = ref([])
const categories = ref([])
const showModal = ref(false)
const showCategoryModal = ref(false)
const editingProduct = ref(null)
const newCategoryName = ref('')

const filters = ref({
  search: '',
  category_id: '',
  type: '',
  is_active: ''
})

const form = ref({
  name: '',
  sku: '',
  category_id: '',
  type: 'INVENTORY',
  uom: 'pcs',
  cost_price: 0,
  selling_price: 0,
  barcode: '',
  description: '',
  is_active: true
})

onMounted(async () => {
  await loadCategories()
  await loadProducts()
})

const loadProducts = async () => {
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.category_id) params.category_id = filters.value.category_id
    if (filters.value.type) params.type = filters.value.type
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    const { data } = await api.get('/products', { params })
    products.value = data.data || data
  } catch (error) {
    console.error('Failed to load products:', error)
  }
}

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

const openAddModal = () => {
  editingProduct.value = null
  form.value = {
    name: '',
    sku: '',
    category_id: '',
    type: 'INVENTORY',
    uom: 'pcs',
    cost_price: 0,
    selling_price: 0,
    barcode: '',
    description: '',
    is_active: true
  }
  showModal.value = true
}

const openEditModal = (product) => {
  editingProduct.value = product
  form.value = {
    name: product.name,
    sku: product.sku,
    category_id: product.category_id,
    type: product.type || 'INVENTORY',
    uom: product.uom,
    cost_price: product.cost_price,
    selling_price: product.selling_price,
    barcode: product.barcode || '',
    description: product.description || '',
    is_active: product.is_active
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingProduct.value = null
}

const saveProduct = async () => {
  try {
    const data = { ...form.value }
    
    // Convert boolean to 1/0
    if (typeof data.is_active === 'boolean') {
      data.is_active = data.is_active ? 1 : 0
    }
    
    if (editingProduct.value) {
      await api.put(`/products/${editingProduct.value.id}`, data)
      alert('Product updated successfully')
    } else {
      await api.post('/products', data)
      alert('Product created successfully')
    }
    closeModal()
    await loadProducts()
  } catch (error) {
    console.error('Save product error:', error)
    alert('Failed to save product: ' + (error.response?.data?.message || error.message))
  }
}

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete ${product.name}?`)) return
  
  try {
    await api.delete(`/products/${product.id}`)
    alert('Product deleted successfully')
    await loadProducts()
  } catch (error) {
    alert('Failed to delete product: ' + (error.response?.data?.message || error.message))
  }
}

const addCategory = async () => {
  if (!newCategoryName.value.trim()) {
    alert('Please enter category name')
    return
  }
  
  try {
    await api.post('/categories', { name: newCategoryName.value })
    newCategoryName.value = ''
    await loadCategories()
    alert('Category added successfully')
  } catch (error) {
    alert('Failed to add category: ' + (error.response?.data?.message || error.message))
  }
}

const deleteCategory = async (category) => {
  if (!confirm(`Delete category "${category.name}"?`)) return
  
  try {
    await api.delete(`/categories/${category.id}`)
    await loadCategories()
    alert('Category deleted successfully')
  } catch (error) {
    alert('Failed to delete category: ' + (error.response?.data?.message || error.message))
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value || 0)
}
</script>
