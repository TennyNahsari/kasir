<template>
  <div class="p-6">
    <div class="mb-6">
      <button @click="goBack" class="text-blue-600 hover:text-blue-800 mb-2">← Back to PRs</button>
      
      <!-- Create Mode Form -->
      <div v-if="isCreateMode" class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Create Purchase Request</h1>
        
        <form @submit.prevent="savePR" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
              <select v-model="pr.location_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option :value="null">Select Location</option>
                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                  {{ loc.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Required Date *</label>
              <input v-model="pr.required_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea v-model="pr.notes" rows="3"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
          </div>

          <!-- Items Section -->
          <div>
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Items</h3>
              <button type="button" @click="addItem" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                + Add Item
              </button>
            </div>

            <div v-for="(item, index) in pr.items" :key="index" class="bg-gray-50 p-4 rounded-lg mb-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Product *</label>
                  <input 
                    v-model="item.productSearch" 
                    @input="filterProducts(item)"
                    @focus="item.showDropdown = true"
                    type="text" 
                    placeholder="Type to search product..."
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <div v-if="item.showDropdown && item.filteredProducts && item.filteredProducts.length > 0" 
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <div 
                      v-for="product in item.filteredProducts" 
                      :key="product.id"
                      @click="selectProduct(item, product)"
                      class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b last:border-b-0">
                      <div class="font-medium">{{ product.name }}</div>
                      <div class="text-xs text-gray-500">SKU: {{ product.sku }}</div>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                  <input v-model.number="item.quantity" type="number" step="0.01" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Est. Price</label>
                  <input v-model.number="item.estimated_price" type="number" step="0.01"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                  <input v-model="item.notes" type="text"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                  <button type="button" @click="removeItem(index)" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <div v-if="pr.items.length === 0" class="text-center py-8 text-gray-500">
              No items added. Click "Add Item" to add products to this request.
            </div>
          </div>

          <div class="flex justify-end space-x-4 pt-6 border-t">
            <button type="button" @click="goBack" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Create Purchase Request' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Detail Mode (existing view) -->
      <div v-else>
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ pr.pr_number }}</h1>
            <p class="text-gray-600">{{ pr.department }}</p>
          </div>
          <span :class="getStatusClass(pr.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
            {{ pr.status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Rest of detail view (only shown when not in create mode) -->
    <div v-if="!isCreateMode">
      <!-- PR Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Request Information</h3>
        <dl class="space-y-2">
          <div class="flex justify-between">
            <dt class="text-gray-600">Request Date:</dt>
            <dd class="font-medium">{{ formatDate(pr.request_date) }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-600">Required Date:</dt>
            <dd class="font-medium">{{ formatDate(pr.required_date) }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-600">Location:</dt>
            <dd class="font-medium">{{ pr.location?.name || '-' }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-600">Requested By:</dt>
            <dd class="font-medium">{{ pr.requested_by_name }}</dd>
          </div>
          <div class="flex justify-between" v-if="pr.approved_by_name">
            <dt class="text-gray-600">Approved By:</dt>
            <dd class="font-medium">{{ pr.approved_by_name }}</dd>
          </div>
        </dl>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Actions</h3>
        <div class="space-y-2">
          <button v-if="pr.status === 'DRAFT'" @click="submitPR" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Submit for Approval
          </button>
          <button v-if="pr.status === 'PENDING_APPROVAL' && canApprovePR(pr)" @click="approvePR" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Approve PR
          </button>
          <button v-if="pr.status === 'PENDING_APPROVAL' && canApprovePR(pr)" @click="rejectPR" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
            Reject PR
          </button>
          <button v-if="pr.status === 'APPROVED' && canCreatePO" @click="createPO" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            Create Purchase Order
          </button>
          <button v-if="['DRAFT', 'PENDING_APPROVAL'].includes(pr.status)" @click="cancelPR" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            Cancel PR
          </button>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Request Items</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Estimated Price</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in pr.items" :key="item.id">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ item.product?.name || item.product_name || 'N/A' }}</div>
                <div class="text-sm text-gray-500">SKU: {{ item.product?.sku || 'N/A' }}</div>
                <div v-if="item.notes" class="text-xs text-gray-400 mt-1">{{ item.notes }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                {{ item.quantity }} {{ item.product?.unit || '' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                Rp {{ Number(item.estimated_price || 0).toLocaleString('id-ID') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                Rp {{ Number(item.quantity * (item.estimated_price || 0)).toLocaleString('id-ID') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="item.ordered_quantity >= item.quantity" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                  Ordered
                </span>
                <span v-else-if="item.ordered_quantity > 0" class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                  Partial
                </span>
                <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                  Pending
                </span>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="3" class="px-6 py-4 text-right font-semibold text-gray-900">Total Estimated:</td>
              <td class="px-6 py-4 text-right font-bold text-gray-900">
                Rp {{ calculateTotal().toLocaleString('id-ID') }}
              </td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="pr.notes" class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold mb-2">Notes</h3>
      <p class="text-gray-700">{{ pr.notes }}</p>
    </div>
    </div><!-- End of v-if="!isCreateMode" -->
  </div><!-- End of p-6 -->
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { useProcurementPermissions } from '@/composables/useProcurementPermissions'

const router = useRouter()
const route = useRoute()
const { canApprovePR, canCreatePO } = useProcurementPermissions()

const pr = ref({
  items: []
})

const saving = ref(false)
const locations = ref([])
const products = ref([])

const isCreateMode = computed(() => route.params.id === 'create')

onMounted(async () => {
  await loadLocations()
  
  if (!isCreateMode.value) {
    await loadPR()
  } else {
    await loadProducts()
    // Initialize empty PR for create mode
    pr.value = {
      pr_number: 'New PR',
      location_id: null,
      request_date: new Date().toISOString().split('T')[0],
      required_date: '',
      status: 'DRAFT',
      notes: '',
      items: []
    }
  }
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadProducts = async () => {
  try {
    const { data } = await api.get('/products', { params: { per_page: 1000 } })
    products.value = data.data || data || []
  } catch (error) {
    console.error('Failed to load products:', error)
  }
}

const loadPR = async () => {
  try {
    const { data } = await api.get(`/purchase-requests/${route.params.id}`)
    pr.value = data
    console.log('PR Data:', data)
    console.log('Requested By:', data.requestedBy)
    console.log('Can Approve:', canApprovePR(data))
  } catch (error) {
    console.error('Failed to load PR:', error)
    alert('PR not found')
    router.push('/procurement/purchase-requests')
  }
}

const calculateTotal = () => {
  return pr.value.items.reduce((sum, item) => {
    const total = item.quantity * (item.estimated_price || 0)
    return sum + total
  }, 0)
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'PENDING_APPROVAL': 'bg-blue-100 text-blue-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'REJECTED': 'bg-red-100 text-red-800',
    'CANCELLED': 'bg-red-100 text-red-800',
    'PARTIALLY_ORDERED': 'bg-yellow-100 text-yellow-800',
    'FULLY_ORDERED': 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const submitPR = async () => {
  if (!pr.value.id) {
    alert('PR data not loaded yet. Please wait...')
    return
  }
  
  if (!confirm('Submit this PR for approval?')) return
  try {
    await api.post(`/purchase-requests/${pr.value.id}/submit`)
    alert('PR submitted successfully')
    await loadPR()
  } catch (error) {
    alert('Failed to submit PR: ' + (error.response?.data?.message || error.message))
  }
}

const approvePR = async () => {
  if (!confirm('Approve this PR?')) return
  try {
    await api.post(`/purchase-requests/${pr.value.id}/approve`)
    alert('PR approved successfully')
    await loadPR()
  } catch (error) {
    alert('Failed to approve PR: ' + (error.response?.data?.message || error.message))
  }
}

const rejectPR = async () => {
  const reason = prompt('Rejection reason:')
  if (!reason) return
  try {
    await api.post(`/purchase-requests/${pr.value.id}/reject`, { reason })
    alert('PR rejected')
    await loadPR()
  } catch (error) {
    alert('Failed to reject PR: ' + (error.response?.data?.message || error.message))
  }
}

const cancelPR = async () => {
  if (!confirm('Cancel this PR?')) return
  try {
    await api.post(`/purchase-requests/${pr.value.id}/cancel`)
    alert('PR cancelled')
    await loadPR()
  } catch (error) {
    alert('Failed to cancel PR: ' + (error.response?.data?.message || error.message))
  }
}

const createPO = () => {
  router.push(`/procurement/purchase-orders/create?pr_id=${pr.value.id}`)
}

const goBack = () => {
  router.push('/procurement/purchase-requests')
}

// Create mode functions
const addItem = () => {
  pr.value.items.push({
    product_id: null,
    product_name: '',
    productSearch: '',
    filteredProducts: [],
    showDropdown: false,
    quantity: 1,
    estimated_price: 0,
    notes: ''
  })
}

const removeItem = (index) => {
  pr.value.items.splice(index, 1)
}

const filterProducts = (item) => {
  const search = item.productSearch.toLowerCase()
  if (search.length === 0) {
    item.filteredProducts = []
    item.showDropdown = false
    return
  }
  
  item.filteredProducts = products.value.filter(p => 
    p.name.toLowerCase().includes(search) || 
    p.sku.toLowerCase().includes(search)
  ).slice(0, 10) // Limit to 10 results
  
  item.showDropdown = item.filteredProducts.length > 0
}

const selectProduct = (item, product) => {
  item.product_id = product.id
  item.product_name = product.name
  item.productSearch = product.name
  item.showDropdown = false
  item.filteredProducts = []
}

const savePR = async () => {
  if (pr.value.items.length === 0) {
    alert('Please add at least one item to the purchase request')
    return
  }

  if (!pr.value.location_id) {
    alert('Please select a location')
    return
  }

  // Validate all items have product selected
  const invalidItems = pr.value.items.filter(item => !item.product_id)
  if (invalidItems.length > 0) {
    alert('Please select a product for all items')
    return
  }

  saving.value = true
  try {
    const payload = {
      location_id: pr.value.location_id,
      request_date: pr.value.request_date,
      required_date: pr.value.required_date,
      notes: pr.value.notes,
      items: pr.value.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        estimated_price: item.estimated_price || 0,
        notes: item.notes
      }))
    }

    const { data } = await api.post('/purchase-requests', payload)
    alert('Purchase Request created successfully!')
    router.push(`/procurement/purchase-requests/${data.id}`)
  } catch (error) {
    console.error('Failed to create PR:', error)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || error.message
    alert('Failed to create PR: ' + errorMsg)
  } finally {
    saving.value = false
  }
}
</script>
