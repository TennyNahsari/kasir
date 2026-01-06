<template>
  <div class="p-6">
    <div class="mb-6">
      <button @click="goBack" class="text-blue-600 hover:text-blue-800 mb-2">← Back to GRNs</button>
      
      <!-- Create Mode Form -->
      <div v-if="isCreateMode" class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Create Goods Receipt Note</h1>
        
        <form @submit.prevent="saveGRN" class="space-y-6">
          <div v-if="poId" class="bg-blue-50 p-4 rounded-lg mb-6">
            <h3 class="font-semibold mb-2">From Purchase Order</h3>
            <p class="text-sm">PO #{{ poData?.po_no }}</p>
            <p class="text-xs text-gray-600">Vendor: {{ poData?.vendor?.name }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Date *</label>
              <input v-model="grn.receipt_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Note Number</label>
              <input v-model="grn.delivery_note_no" type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea v-model="grn.notes" rows="3"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
          </div>

          <!-- Items Section -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Received Items</h3>
            
            <div v-if="grn.items.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
              <p class="text-yellow-800">No items to receive. Please select a Purchase Order first.</p>
              <button type="button" @click="goBack" class="mt-2 text-blue-600 hover:underline">
                Go back to select a Purchase Order
              </button>
            </div>
            
            <div v-for="(item, index) in grn.items" :key="index" class="bg-gray-50 p-4 rounded-lg mb-4">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                  <input :value="item.product_name" readonly
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Ordered</label>
                  <input :value="item.quantity_ordered" readonly
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Received Qty *</label>
                  <input v-model.number="item.quantity_received" type="number" step="0.01" required min="0" :max="item.quantity_ordered"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rejected Qty</label>
                  <input v-model.number="item.quantity_rejected" type="number" step="0.01" min="0"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <input v-model="item.notes" type="text"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div v-if="grn.items.length === 0" class="text-center py-8 text-gray-500">
              No items to receive. Please select a Purchase Order first.
            </div>
          </div>

          <div class="flex justify-end space-x-4 pt-6 border-t">
            <button type="button" @click="goBack" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Create Goods Receipt' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Detail Mode -->
      <div v-else>
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ grn.grn_number }}</h1>
            <p class="text-gray-600">PO: {{ grn.po?.po_no }} - {{ grn.po?.vendor?.name }}</p>
          </div>
          <span :class="getStatusClass(grn.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
            {{ grn.status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Rest of detail view -->
    <div v-if="!isCreateMode">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Receipt Information</h3>
          <dl class="space-y-2">
            <div class="flex justify-between">
              <dt class="text-gray-600">Receipt Date:</dt>
              <dd class="font-medium">{{ formatDate(grn.receipt_date) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-600">Delivery Note:</dt>
              <dd class="font-medium">{{ grn.delivery_note_no || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-600">Received By:</dt>
              <dd class="font-medium">{{ grn.received_by_name }}</dd>
            </div>
            <div class="flex justify-between" v-if="grn.inspected_by_name">
              <dt class="text-gray-600">Inspected By:</dt>
              <dd class="font-medium">{{ grn.inspected_by_name }}</dd>
            </div>
          </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Actions</h3>
          <div class="space-y-2">
            <button v-if="grn.status === 'DRAFT' && canSubmitQC" @click="submitGRN" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              Submit for Quality Check
            </button>
            <button v-if="grn.status === 'QUALITY_CHECK' && canApproveQC" @click="approveQC" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
              Approve Quality Check
            </button>
            <button v-if="grn.status === 'QUALITY_CHECK' && canApproveQC" @click="rejectQC" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
              Reject Quality Check
            </button>
            <button v-if="grn.status === 'APPROVED' && canPostGRN" @click="postToInventory" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
              Post to Inventory
            </button>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold">Received Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ordered</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Received</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rejected</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in grn.items" :key="item.id">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">SKU: {{ item.product?.sku || 'N/A' }}</div>
                  <div v-if="item.notes" class="text-xs text-gray-400 mt-1">{{ item.notes }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                  {{ item.quantity_ordered || item.ordered_quantity }} {{ item.product?.unit || '' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                  {{ item.quantity_received || item.received_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                  {{ item.quantity_rejected || item.rejected_quantity || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="item.quality_status === 'PASSED'" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Passed
                  </span>
                  <span v-else-if="item.quality_status === 'FAILED'" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    Failed
                  </span>
                  <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    Pending
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="grn.notes" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-2">Notes</h3>
        <p class="text-gray-700">{{ grn.notes }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { useProcurementPermissions } from '@/composables/useProcurementPermissions'

const router = useRouter()
const route = useRoute()

const { canPostGRN, canSubmitQC, canApproveQC } = useProcurementPermissions()

const grn = ref({
  items: []
})

const saving = ref(false)
const poId = ref(null)
const poData = ref(null)

const isCreateMode = computed(() => route.params.id === 'create')

onMounted(async () => {
  if (!isCreateMode.value) {
    await loadGRN()
  } else {
    // Initialize GRN for create mode
    grn.value = {
      grn_number: 'New GRN',
      po_id: null,
      receipt_date: new Date().toISOString().split('T')[0],
      delivery_note_no: '',
      status: 'DRAFT',
      notes: '',
      items: []
    }
    
    // Load PO data if po_id is provided (this will populate items)
    poId.value = route.query.po_id
    if (poId.value) {
      grn.value.po_id = poId.value
      await loadPOData()
    }
  }
})

const loadPOData = async () => {
  try {
    const { data } = await api.get(`/purchase-orders/${poId.value}`)
    poData.value = data
    
    // Pre-fill items from PO
    if (data.items && data.items.length > 0) {
      grn.value.items = data.items.map(item => ({
        po_item_id: item.id,
        product_id: item.product_id,
        product_name: item.product?.name || '',
        quantity_ordered: item.quantity,
        quantity_received: item.quantity, // Default to full quantity
        quantity_rejected: 0,
        notes: ''
      }))
    }
  } catch (error) {
    console.error('Failed to load PO data:', error)
    alert('Failed to load Purchase Order data')
    router.push('/procurement/goods-receipts')
  }
}

const loadGRN = async () => {
  try {
    const { data } = await api.get(`/goods-receipts/${route.params.id}`)
    grn.value = {
      ...data,
      items: data.items || []
    }
  } catch (error) {
    console.error('Failed to load GRN:', error)
    alert('GRN not found')
    router.push('/procurement/goods-receipts')
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'QUALITY_CHECK': 'bg-yellow-100 text-yellow-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'REJECTED': 'bg-red-100 text-red-800',
    'POSTED': 'bg-blue-100 text-blue-800'
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

const submitGRN = async () => {
  // Get GRN ID from route params or grn object
  const grnId = route.params.id || grn.value.id
  
  if (!grnId || grnId === 'create' || grnId === 'undefined') {
    alert('Cannot submit GRN: Please save the GRN first')
    return
  }
  
  if (!confirm('Submit this GRN for quality check?')) return
  
  try {
    console.log('GRN items before submit:', grn.value.items)
    
    // Mark all items as PENDING for quality check
    const items = grn.value.items.map(item => ({
      id: item.id,
      quality_status: 'PENDING',
      quality_notes: '',
      quantity_rejected: item.quantity_rejected || 0
    }))
    
    console.log('Mapped items for QC:', items)
    
    await api.post(`/goods-receipts/${grnId}/quality-check`, { 
      items: items,
      grn_quality_notes: 'Submitted for quality check'
    })
    alert('GRN submitted for quality check')
    await loadGRN()
  } catch (error) {
    console.error('Submit GRN error:', error)
    alert('Failed to submit GRN: ' + (error.response?.data?.message || error.message))
  }
}

const approveQC = async () => {
  // Get GRN ID from route params or grn object
  const grnId = route.params.id || grn.value.id
  
  if (!grnId || grnId === 'create' || grnId === 'undefined') {
    alert('Cannot approve QC: GRN not found')
    return
  }
  
  if (!confirm('Approve quality check for this GRN?')) return
  
  try {
    // First, mark all items as PASSED
    const items = grn.value.items.map(item => ({
      id: item.id,
      quality_status: 'PASSED',
      quality_notes: 'Quality check passed',
      quantity_rejected: item.quantity_rejected || 0
    }))
    
    await api.post(`/goods-receipts/${grnId}/quality-check`, { 
      items: items,
      grn_quality_notes: 'Quality check approved'
    })
    
    // Then approve the GRN
    await api.post(`/goods-receipts/${grnId}/approve`)
    
    alert('Quality check approved. GRN is ready to post to inventory.')
    await loadGRN()
  } catch (error) {
    alert('Failed to approve QC: ' + (error.response?.data?.message || error.message))
  }
}

const rejectQC = async () => {
  // Get GRN ID from route params or grn object
  const grnId = route.params.id || grn.value.id
  
  if (!grnId || grnId === 'create' || grnId === 'undefined') {
    alert('Cannot reject QC: GRN not found')
    return
  }
  
  const reason = prompt('Rejection reason:')
  if (!reason) return
  
  try {
    // Mark all items as FAILED
    const items = grn.value.items.map(item => ({
      id: item.id,
      quality_status: 'FAILED',
      quality_notes: reason,
      quantity_rejected: item.quantity_received // Reject all received
    }))
    
    await api.post(`/goods-receipts/${grnId}/quality-check`, { 
      items: items,
      grn_quality_notes: reason
    })
    alert('Quality check rejected')
    await loadGRN()
  } catch (error) {
    alert('Failed to reject QC: ' + (error.response?.data?.message || error.message))
  }
}

const postToInventory = async () => {
  // Get GRN ID from route params or grn object
  const grnId = route.params.id || grn.value.id
  
  if (!grnId || grnId === 'create' || grnId === 'undefined') {
    alert('Cannot post to inventory: GRN not found')
    return
  }
  
  if (!confirm('Post this GRN to inventory? This action cannot be undone.')) return
  
  try {
    await api.post(`/goods-receipts/${grnId}/post`)
    alert('GRN posted to inventory successfully!')
    await loadGRN()
  } catch (error) {
    alert('Failed to post GRN: ' + (error.response?.data?.message || error.message))
  }
}

const goBack = () => {
  router.push('/procurement/goods-receipts')
}

const saveGRN = async () => {
  if (grn.value.items.length === 0) {
    alert('No items to receive')
    return
  }

  // Validate received quantities
  const invalidItems = grn.value.items.filter(item => 
    item.quantity_received === null || 
    item.quantity_received === undefined ||
    item.quantity_received < 0 ||
    item.quantity_received > item.quantity_ordered
  )
  
  if (invalidItems.length > 0) {
    alert('Please enter valid received quantities (between 0 and ordered quantity)')
    return
  }

  saving.value = true
  try {
    const payload = {
      po_id: poId.value,
      receipt_date: grn.value.receipt_date,
      supplier_invoice_no: grn.value.delivery_note_no,
      notes: grn.value.notes,
      items: grn.value.items.map(item => ({
        po_item_id: item.po_item_id,
        quantity_received: item.quantity_received,
        quantity_rejected: item.quantity_rejected || 0,
        notes: item.notes
      }))
    }

    const { data } = await api.post('/goods-receipts', payload)
    alert('Goods Receipt created successfully!')
    router.push(`/procurement/goods-receipts/${data.id}`)
  } catch (error) {
    console.error('Failed to create GRN:', error)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || error.message
    alert('Failed to create GRN: ' + errorMsg)
  } finally {
    saving.value = false
  }
}
</script>
