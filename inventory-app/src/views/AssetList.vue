<template>
  <div class="p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
      <h2 class="text-2xl font-bold">Asset Management</h2>
      <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        + Add Asset
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="label text-sm">Search</label>
          <input
            v-model="filters.search"
            type="text"
            class="input"
            placeholder="Asset tag, serial, product..."
            @input="loadAssets"
          >
        </div>
        <div>
          <label class="label text-sm">Status</label>
          <select v-model="filters.status" class="input" @change="loadAssets">
            <option value="">All Status</option>
            <option value="AVAILABLE">Available</option>
            <option value="ASSIGNED">Assigned</option>
            <option value="IN_USE">In Use</option>
            <option value="MAINTENANCE">Maintenance</option>
            <option value="DAMAGED">Damaged</option>
            <option value="DISPOSED">Disposed</option>
          </select>
        </div>
        <div>
          <label class="label text-sm">Location</label>
          <select v-model="filters.location_id" class="input" @change="loadAssets">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="label text-sm">PIC</label>
          <input v-model="filters.pic" type="text" class="input" placeholder="Search by PIC..." @input="loadAssets">
        </div>
      </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block card overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold">Asset Tag</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Product</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Serial Number</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Location</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">PIC</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Condition</th>
            <th class="px-4 py-3 text-center text-sm font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="asset in assets" :key="asset.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-mono font-semibold text-blue-600">
              {{ asset.asset_tag }}
            </td>
            <td class="px-4 py-3 text-sm">{{ asset.product?.name }}</td>
            <td class="px-4 py-3 text-sm font-mono">{{ asset.serial_number || '-' }}</td>
            <td class="px-4 py-3 text-sm">{{ asset.location?.name }}</td>
            <td class="px-4 py-3 text-sm">{{ asset.pic || '-' }}</td>
            <td class="px-4 py-3 text-sm">
              <span :class="getStatusBadgeClass(asset.status)" class="badge">
                {{ asset.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm">
              <span :class="getConditionBadgeClass(asset.condition)" class="badge">
                {{ asset.condition }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-center">
              <button @click="viewAsset(asset)" class="text-blue-600 hover:text-blue-700 font-medium">
                View
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!loading && assets.length === 0" class="text-center py-8 text-gray-500">
        No assets found
      </div>

      <div v-if="loading" class="text-center py-8 text-gray-500">
        Loading assets...
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-3">
      <div v-for="asset in assets" :key="asset.id" class="card p-4">
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-semibold text-blue-600 font-mono">{{ asset.asset_tag }}</h3>
            <p class="text-sm text-gray-700 font-medium">{{ asset.product?.name }}</p>
            <p class="text-xs text-gray-500">SN: {{ asset.serial_number || '-' }}</p>
          </div>
          <div class="flex flex-col gap-1 items-end">
            <span :class="getStatusBadgeClass(asset.status)" class="badge text-xs">
              {{ asset.status }}
            </span>
            <span :class="getConditionBadgeClass(asset.condition)" class="badge text-xs">
              {{ asset.condition }}
            </span>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
          <div>
            <span class="text-gray-600">Location:</span>
            <span class="font-medium ml-1">{{ asset.location?.name }}</span>
          </div>
          <div>
            <span class="text-gray-600">PIC:</span>
            <span class="font-medium ml-1">{{ asset.pic || '-' }}</span>
          </div>
          <div>
            <span class="text-gray-600">Purchase Date:</span>
            <span class="font-medium ml-1">{{ formatDate(asset.purchase_date) }}</span>
          </div>
          <div>
            <span class="text-gray-600">Value:</span>
            <span class="font-medium ml-1">{{ formatCurrency(asset.current_value) }}</span>
          </div>
        </div>
        
        <button @click="viewAsset(asset)" class="w-full py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
          View Details
        </button>
      </div>

      <div v-if="!loading && assets.length === 0" class="card p-8 text-center text-gray-500">
        No assets found
      </div>

      <div v-if="loading" class="card p-8 text-center text-gray-500">
        Loading assets...
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="flex justify-between items-center mt-6">
      <div class="text-sm text-gray-600">
        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} assets
      </div>
      <div class="flex gap-2">
        <button
          @click="loadAssets(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-secondary text-sm disabled:opacity-50"
        >
          Previous
        </button>
        <button
          @click="loadAssets(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-secondary text-sm disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Add Asset Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Add New Asset</h3>
            <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="createAsset" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                <select v-model="assetForm.product_id" required class="w-full border-gray-300 rounded-lg">
                  <option value="">Select Product</option>
                  <option v-for="product in assetProducts" :key="product.id" :value="product.id">
                    {{ product.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number *</label>
                <input v-model="assetForm.serial_number" type="text" required class="w-full border-gray-300 rounded-lg" placeholder="Enter serial number">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                <select v-model="assetForm.location_id" required class="w-full border-gray-300 rounded-lg">
                  <option value="">Select Location</option>
                  <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                    {{ loc.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condition *</label>
                <select v-model="assetForm.condition" required class="w-full border-gray-300 rounded-lg">
                  <option value="NEW">New</option>
                  <option value="GOOD">Good</option>
                  <option value="FAIR">Fair</option>
                  <option value="POOR">Poor</option>
                  <option value="BROKEN">Broken</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
                <input v-model="assetForm.purchase_date" type="date" required class="w-full border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price *</label>
                <input v-model="assetForm.purchase_price" type="number" step="0.01" required class="w-full border-gray-300 rounded-lg" placeholder="0">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Useful Life (years)</label>
                <input v-model="assetForm.useful_life_years" type="number" step="1" class="w-full border-gray-300 rounded-lg" placeholder="5">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Warranty Expiry</label>
                <input v-model="assetForm.warranty_expiry" type="date" class="w-full border-gray-300 rounded-lg">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="assetForm.notes" rows="3" class="w-full border-gray-300 rounded-lg" placeholder="Additional notes..."></textarea>
            </div>

            <div class="flex gap-3 justify-end pt-4">
              <button type="button" @click="closeAddModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Create Asset
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
import { useRouter } from 'vue-router'
import assetService from '@/services/assetService'
import locationService from '@/services/locationService'
import userService from '@/services/userService'
import api from '@/services/api'

const router = useRouter()

const assets = ref([])
const locations = ref([])
const users = ref([])
const assetProducts = ref([])
const loading = ref(false)
const showAddModal = ref(false)

const filters = ref({
  search: '',
  status: '',
  location_id: '',
  pic: ''
})

const assetForm = ref({
  product_id: '',
  serial_number: '',
  location_id: '',
  condition: 'NEW',
  purchase_date: new Date().toISOString().split('T')[0],
  purchase_price: '',
  useful_life_years: 5,
  warranty_expiry: '',
  notes: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

onMounted(async () => {
  await Promise.all([
    loadAssets(),
    loadLocations(),
    loadUsers(),
    loadAssetProducts()
  ])
})

const loadAssets = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      ...filters.value
    }
    
    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })

    const response = await assetService.getAssets(params)
    
    if (response.data) {
      assets.value = response.data
      pagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        per_page: response.per_page || 20,
        total: response.total || 0,
        from: response.from || 0,
        to: response.to || 0
      }
    } else {
      assets.value = response
    }
  } catch (error) {
    console.error('Failed to load assets:', error)
    alert('Failed to load assets: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const loadLocations = async () => {
  try {
    const response = await locationService.getLocations()
    locations.value = response.data || response
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await userService.getUsers()
    users.value = response.data || response
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const loadAssetProducts = async () => {
  try {
    const response = await api.get('/products', {
      params: { type: 'ASSET' }
    })
    assetProducts.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load asset products:', error)
  }
}

const createAsset = async () => {
  try {
    loading.value = true
    await assetService.createAsset(assetForm.value)
    alert('Asset created successfully!')
    closeAddModal()
    await loadAssets()
  } catch (error) {
    console.error('Failed to create asset:', error)
    alert('Failed to create asset: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const closeAddModal = () => {
  showAddModal.value = false
  assetForm.value = {
    product_id: '',
    serial_number: '',
    location_id: '',
    condition: 'NEW',
    purchase_date: new Date().toISOString().split('T')[0],
    purchase_price: '',
    useful_life_years: 5,
    warranty_expiry: '',
    notes: ''
  }
}

const viewAsset = (asset) => {
  router.push(`/assets/${asset.id}`)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'AVAILABLE': 'badge-green',
    'ASSIGNED': 'badge-blue',
    'IN_USE': 'badge-blue',
    'MAINTENANCE': 'badge-yellow',
    'DAMAGED': 'badge-red',
    'DISPOSED': 'badge-gray'
  }
  return classes[status] || 'badge-gray'
}

const getConditionBadgeClass = (condition) => {
  const classes = {
    'NEW': 'badge-green',
    'GOOD': 'badge-blue',
    'FAIR': 'badge-yellow',
    'POOR': 'badge-orange',
    'BROKEN': 'badge-red'
  }
  return classes[condition] || 'badge-gray'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}
</script>
