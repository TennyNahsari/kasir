<template>
  <div class="p-6">
    <div class="mb-6">
      <button @click="goBack" class="text-blue-600 hover:text-blue-800 mb-2">← Back to POs</button>
      
      <!-- Create Mode Form -->
      <div v-if="isCreateMode" class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Create Purchase Order</h1>
        
        <form @submit.prevent="savePO" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Vendor *</label>
              <select v-model="po.vendor_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option :value="null">Select Vendor</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
              <select v-model="po.location_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option :value="null">Select Location</option>
                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                  {{ loc.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Expected Delivery *</label>
              <input v-model="po.expected_delivery_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>

          <div v-if="prId">
            <label class="block text-sm font-medium text-gray-700 mb-2">From Purchase Request</label>
            <div class="bg-blue-50 p-4 rounded-lg">
              <p class="text-sm">PR #{{ prData?.pr_no }}</p>
              <p class="text-xs text-gray-600">{{ prData?.department }} - {{ prData?.notes }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea v-model="po.notes" rows="3"
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

            <div v-for="(item, index) in po.items" :key="index" class="bg-gray-50 p-4 rounded-lg mb-4">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                      <div class="text-xs text-gray-500">SKU: {{ product.sku }} | Stock: {{ product.stock }}</div>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                  <input v-model.number="item.quantity" type="number" step="0.01" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price *</label>
                  <input v-model.number="item.unit_price" type="number" step="0.01" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                  <input :value="(item.quantity * item.unit_price).toFixed(2)" readonly
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100">
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

            <div v-if="po.items.length === 0" class="text-center py-8 text-gray-500">
              No items added. Click "Add Item" to add products to this order.
            </div>
          </div>

          <div class="flex justify-end space-x-4 pt-6 border-t">
            <button type="button" @click="goBack" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Create Purchase Order' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Detail Mode (existing view) -->
      <div v-else>
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ po.po_number }}</h1>
            <p class="text-gray-600">Vendor: {{ po.vendor?.name }}</p>
          </div>
          <span :class="getStatusClass(po.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
            {{ po.status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Rest of detail view (only shown when not in create mode) -->
    <div v-if="!isCreateMode">
      <!-- PO Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Order Information</h3>
          <dl class="space-y-2">
            <div class="flex justify-between">
              <dt class="text-gray-600">Order Date:</dt>
              <dd class="font-medium">{{ formatDate(po.order_date) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-600">Expected Delivery:</dt>
              <dd class="font-medium">{{ formatDate(po.expected_delivery_date) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-600">Vendor:</dt>
              <dd class="font-medium">{{ po.vendor?.name }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-600">Total Amount:</dt>
              <dd class="font-bold text-lg">Rp {{ Number(po.total_amount || 0).toLocaleString('id-ID') }}</dd>
            </div>
          </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Actions</h3>
          <div class="space-y-2">
            <button v-if="po.status === 'DRAFT'" @click="submitPO" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              Submit for Approval
            </button>
            <button v-if="po.status === 'PENDING_APPROVAL'" @click="approvePO" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
              Approve PO
            </button>
            <button v-if="po.status === 'APPROVED'" @click="sendPO" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
              Send to Vendor
            </button>
            <button v-if="['APPROVED', 'SENT', 'RECEIVED'].includes(po.status)" @click="downloadPDF" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 flex items-center justify-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Download PDF
            </button>
            <button v-if="po.status === 'SENT'" @click="createGRN" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
              Create Goods Receipt
            </button>
            <button v-if="['DRAFT', 'PENDING_APPROVAL', 'APPROVED'].includes(po.status)" @click="cancelPO" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
              Cancel PO
            </button>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold">Order Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in po.items" :key="item.id">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">SKU: {{ item.product?.sku || 'N/A' }}</div>
                  <div v-if="item.notes" class="text-xs text-gray-400 mt-1">{{ item.notes }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                  {{ item.quantity }} {{ item.product?.unit || '' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                  Rp {{ Number(item.unit_price || 0).toLocaleString('id-ID') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                  Rp {{ Number(item.quantity * (item.unit_price || 0)).toLocaleString('id-ID') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="item.received_quantity >= item.quantity" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Received
                  </span>
                  <span v-else-if="item.received_quantity > 0" class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
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
                <td colspan="3" class="px-6 py-4 text-right font-semibold text-gray-900">Total Amount:</td>
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
      <div v-if="po.notes" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-2">Notes</h3>
        <p class="text-gray-700">{{ po.notes }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()

const po = ref({
  items: []
})

const saving = ref(false)
const vendors = ref([])
const locations = ref([])
const products = ref([])
const prId = ref(null)
const prData = ref(null)

const isCreateMode = computed(() => route.params.id === 'create')

onMounted(async () => {
  await loadVendors()
  await loadLocations()
  
  if (!isCreateMode.value) {
    await loadPO()
  } else {
    await loadProducts()
    
    // Initialize empty PO for create mode first
    po.value = {
      po_number: 'New PO',
      vendor_id: null,
      location_id: null,
      order_date: new Date().toISOString().split('T')[0],
      expected_delivery_date: '',
      status: 'DRAFT',
      notes: '',
      items: []
    }
    
    // Then load PR data if pr_id exists (this will override location and items)
    prId.value = route.query.pr_id
    if (prId.value) {
      await loadPRData()
    }
  }
})

const loadVendors = async () => {
  try {
    const { data } = await api.get('/vendors', { params: { is_active: 1 } })
    vendors.value = data
  } catch (error) {
    console.error('Failed to load vendors:', error)
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

const loadProducts = async () => {
  try {
    const { data } = await api.get('/products', { params: { per_page: 1000 } })
    products.value = data.data || data || []
  } catch (error) {
    console.error('Failed to load products:', error)
  }
}

const loadPRData = async () => {
  try {
    const { data } = await api.get(`/purchase-requests/${prId.value}`)
    prData.value = data
    
    // Pre-fill location from PR
    if (data.location_id) {
      po.value.location_id = data.location_id
    }
    
    // Pre-fill items from PR
    if (data.items && data.items.length > 0) {
      po.value.items = data.items.map(item => ({
        product_id: item.product_id,
        product_name: item.product?.name || '',
        productSearch: item.product?.name || '',
        quantity: item.quantity,
        unit_price: item.estimated_price || 0,
        notes: item.notes || '',
        filteredProducts: [],
        showDropdown: false
      }))
    }
  } catch (error) {
    console.error('Failed to load PR data:', error)
  }
}

const loadPO = async () => {
  try {
    const { data } = await api.get(`/purchase-orders/${route.params.id}`)
    po.value = {
      ...data,
      items: data.items || []
    }
  } catch (error) {
    console.error('Failed to load PO:', error)
    alert('PO not found')
    router.push('/procurement/purchase-orders')
  }
}

const calculateTotal = () => {
  if (!po.value.items || !Array.isArray(po.value.items)) return 0
  return po.value.items.reduce((sum, item) => {
    const total = item.quantity * (item.unit_price || 0)
    return sum + total
  }, 0)
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'PENDING_APPROVAL': 'bg-blue-100 text-blue-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'SENT': 'bg-purple-100 text-purple-800',
    'PARTIALLY_RECEIVED': 'bg-yellow-100 text-yellow-800',
    'FULLY_RECEIVED': 'bg-indigo-100 text-indigo-800',
    'CANCELLED': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const submitPO = async () => {
  if (!po.value.id) {
    alert('PO data not loaded yet. Please wait...')
    return
  }
  
  if (!confirm('Submit this PO for approval?')) return
  try {
    await api.post(`/purchase-orders/${po.value.id}/submit`)
    alert('PO submitted successfully')
    await loadPO()
  } catch (error) {
    alert('Failed to submit PO: ' + (error.response?.data?.message || error.message))
  }
}

const approvePO = async () => {
  if (!confirm('Approve this PO?')) return
  try {
    await api.post(`/purchase-orders/${po.value.id}/approve`)
    alert('PO approved successfully')
    await loadPO()
  } catch (error) {
    alert('Failed to approve PO: ' + (error.response?.data?.message || error.message))
  }
}

const sendPO = async () => {
  if (!confirm('Send this PO to vendor?')) return
  try {
    await api.post(`/purchase-orders/${po.value.id}/send`)
    alert('PO sent to vendor')
    await loadPO()
  } catch (error) {
    alert('Failed to send PO: ' + (error.response?.data?.message || error.message))
  }
}

const cancelPO = async () => {
  if (!confirm('Cancel this PO?')) return
  try {
    await api.post(`/purchase-orders/${po.value.id}/cancel`)
    alert('PO cancelled')
    await loadPO()
  } catch (error) {
    alert('Failed to cancel PO: ' + (error.response?.data?.message || error.message))
  }
}

const createGRN = () => {
  router.push(`/procurement/goods-receipts/create?po_id=${po.value.id}`)
}

const downloadPDF = async () => {
  try {
    // Get the PO ID from route params or po object
    const poId = route.params.id || po.value.id
    
    if (!poId || poId === 'create' || poId === 'undefined') {
      alert('Cannot download PDF: PO not saved yet')
      return
    }
    
    const response = await api.get(`/purchase-orders/${poId}/pdf`, {
      responseType: 'blob'
    })
    
    // Create a blob URL for the PDF
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    
    // Create a temporary anchor element to trigger download
    const link = document.createElement('a')
    link.href = url
    link.download = `PO-${po.value.po_number || 'document'}.pdf`
    document.body.appendChild(link)
    link.click()
    
    // Clean up
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('PDF download error:', error)
    alert('Failed to download PDF: ' + (error.response?.data?.message || error.message))
  }
}

const goBack = () => {
  router.push('/procurement/purchase-orders')
}

// Create mode functions
const addItem = () => {
  po.value.items.push({
    product_id: null,
    product_name: '',
    productSearch: '',
    filteredProducts: [],
    showDropdown: false,
    quantity: 1,
    unit_price: 0,
    notes: ''
  })
}

const removeItem = (index) => {
  po.value.items.splice(index, 1)
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
  ).slice(0, 10)
  
  item.showDropdown = item.filteredProducts.length > 0
}

const selectProduct = (item, product) => {
  item.product_id = product.id
  item.product_name = product.name
  item.productSearch = product.name
  item.showDropdown = false
  item.filteredProducts = []
}

const savePO = async () => {
  if (po.value.items.length === 0) {
    alert('Please add at least one item to the purchase order')
    return
  }

  if (!po.value.vendor_id) {
    alert('Please select a vendor')
    return
  }

  if (!po.value.location_id) {
    alert('Please select a department')
    return
  }

  // Validate all items have product selected
  const invalidItems = po.value.items.filter(item => !item.product_id)
  if (invalidItems.length > 0) {
    alert('Please select a product for all items')
    return
  }

  saving.value = true
  try {
    const payload = {
      vendor_id: po.value.vendor_id,
      location_id: po.value.location_id,
      pr_id: prId.value || null,
      order_date: po.value.order_date,
      expected_delivery_date: po.value.expected_delivery_date,
      notes: po.value.notes,
      items: po.value.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        unit_price: item.unit_price || 0,
        notes: item.notes
      }))
    }

    const { data } = await api.post('/purchase-orders', payload)
    alert('Purchase Order created successfully!')
    router.push(`/procurement/purchase-orders/${data.id}`)
  } catch (error) {
    console.error('Failed to create PO:', error)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || error.message
    alert('Failed to create PO: ' + errorMsg)
  } finally {
    saving.value = false
  }
}
</script>
