<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Products</h1>
        <p class="text-gray-600">Manage product master data</p>
      </div>
      <div class="flex space-x-3">
        <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export Excel
        </button>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
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
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="product.image" class="w-12 h-12 flex-shrink-0">
                  <img :src="`http://localhost:8000/storage/${product.image}`" :alt="product.name" class="w-full h-full object-cover rounded">
                </div>
                <div v-else class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                  <span class="text-2xl">📦</span>
                </div>
              </td>
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
                <span v-if="product.type === 'INVENTORY'" class="badge badge-blue">Inventory</span>
                <span v-else-if="product.type === 'ASSET'" class="badge badge-green">Asset</span>
                <span v-else-if="product.type === 'SERVICE'" class="badge badge-purple">Service</span>
                <span v-else class="badge badge-gray">{{ product.type || 'Inventory' }}</span>
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
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                No products found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :pagination="pagination" @page-change="changePage" />
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

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
              <input type="file" @change="handleImageUpload" accept="image/*" class="w-full border-gray-300 rounded-lg">
              <p class="text-xs text-gray-500 mt-1">Max 2MB. Supported formats: JPG, PNG, GIF</p>
              
              <!-- Image Preview -->
              <div v-if="imagePreview" class="mt-2">
                <p class="text-sm text-gray-600 mb-1">Preview:</p>
                <div class="relative inline-block">
                  <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-lg border">
                  <button type="button" @click="removeImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                    ×
                  </button>
                </div>
              </div>
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
import Pagination from '@/components/Pagination.vue'
import * as XLSX from 'xlsx'

const products = ref([])
const categories = ref([])
const showModal = ref(false)
const showCategoryModal = ref(false)
const editingProduct = ref(null)
const newCategoryName = ref('')
const imageFile = ref(null)
const imagePreview = ref(null)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

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

const loadProducts = async (page = 1) => {
  try {
    const params = { page, per_page: 20 }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.category_id) params.category_id = filters.value.category_id
    if (filters.value.type) params.type = filters.value.type
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    const { data } = await api.get('/products', { params })
    products.value = data.data || data
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      per_page: data.per_page || 20,
      total: data.total || products.value.length,
      from: data.from || 0,
      to: data.to || 0,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url
    }
  } catch (error) {
    console.error('Failed to load products:', error)
  }
}

const changePage = (page) => {
  loadProducts(page)
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
  imageFile.value = null
  imagePreview.value = null
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
  imageFile.value = null
  imagePreview.value = product.image ? `http://localhost:8000/storage/${product.image}` : null
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

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('File size must be less than 2MB')
    event.target.value = ''
    return
  }
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Please select an image file')
    event.target.value = ''
    return
  }
  
  imageFile.value = file
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  imageFile.value = null
  imagePreview.value = null
  // Clear file input
  const fileInput = document.querySelector('input[type="file"]')
  if (fileInput) fileInput.value = ''
}

const closeModal = () => {
  showModal.value = false
  editingProduct.value = null
}

const exportToExcel = () => {
  try {
    const exportData = products.value.map(product => ({
      'SKU': product.sku,
      'Product Name': product.name,
      'Category': product.category?.name || '',
      'Type': product.type || 'INVENTORY',
      'UOM': product.uom,
      'Cost Price': product.cost_price,
      'Selling Price': product.selling_price,
      'Barcode': product.barcode || '',
      'Description': product.description || '',
      'Status': product.is_active ? 'Active' : 'Inactive'
    }))

    if (exportData.length === 0) {
      alert('No data to export')
      return
    }

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    ws['!cols'] = [
      { wch: 15 },  // SKU
      { wch: 30 },  // Product Name
      { wch: 20 },  // Category
      { wch: 12 },  // Type
      { wch: 10 },  // UOM
      { wch: 15 },  // Cost Price
      { wch: 15 },  // Selling Price
      { wch: 20 },  // Barcode
      { wch: 40 },  // Description
      { wch: 10 }   // Status
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Products')

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Products_${timestamp}.xlsx`

    XLSX.writeFile(wb, filename)
    alert(`Excel file exported successfully: ${filename} (${exportData.length} records)`)
  } catch (error) {
    console.error('Export error:', error)
    alert('Failed to export Excel file: ' + error.message)
  }
}

const saveProduct = async () => {
  try {
    // Prepare FormData for file upload
    const formData = new FormData()
    
    // Append form fields with proper type conversion
    Object.keys(form.value).forEach(key => {
      let value = form.value[key]
      
      // Convert boolean to 1/0 for Laravel
      if (key === 'is_active' || key === 'track_stock') {
        value = value ? 1 : 0
      }
      
      formData.append(key, value)
    })
    
    // Append image if exists
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }
    
    const config = {
      headers: { 'Content-Type': 'multipart/form-data' }
    }
    
    if (editingProduct.value) {
      // Laravel doesn't support PUT with FormData, so use POST with _method
      formData.append('_method', 'PUT')
      await api.post(`/products/${editingProduct.value.id}`, formData, config)
      alert('Product updated successfully')
    } else {
      await api.post('/products', formData, config)
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
