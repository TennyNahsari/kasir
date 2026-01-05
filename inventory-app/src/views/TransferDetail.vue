<template>
  <div class="p-6">
    <div class="mb-6">
      <button @click="goBack" class="text-blue-600 hover:text-blue-800 mb-2">← Back to Transfers</button>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">{{ transfer.transfer_no || 'Transfer' }}</h1>
          <p class="text-gray-600">{{ transfer.from_location?.name || '-' }} → {{ transfer.to_location?.name || '-' }}</p>
        </div>
        <span :class="getStatusClass(transfer.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
          {{ transfer.status }}
        </span>
      </div>
    </div>

    <!-- Transfer Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Transfer Information</h3>
        <dl class="space-y-2">
          <div class="flex justify-between">
            <dt class="text-gray-600">Transfer Date:</dt>
            <dd class="font-medium">{{ formatDate(transfer.transfer_date) }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-600">Created By:</dt>
            <dd class="font-medium">{{ transfer.requested_by?.name || '-' }}</dd>
          </div>
          <div class="flex justify-between" v-if="transfer.approved_by">
            <dt class="text-gray-600">Approved By:</dt>
            <dd class="font-medium">{{ transfer.approved_by?.name || '-' }}</dd>
          </div>
          <div class="flex justify-between" v-if="transfer.received_by">
            <dt class="text-gray-600">Received By:</dt>
            <dd class="font-medium">{{ transfer.received_by?.name || '-' }}</dd>
          </div>
        </dl>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Actions</h3>
        <div class="space-y-2">
          <button v-if="transfer.status === 'DRAFT'" @click="submitTransfer" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Submit for Approval
          </button>
          <button v-if="transfer.status === 'PENDING'" @click="approveTransfer" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Approve Transfer
          </button>
          <button v-if="transfer.status === 'IN_TRANSIT'" @click="receiveTransfer" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            Receive Transfer
          </button>
          <button v-if="['DRAFT', 'PENDING', 'IN_TRANSIT'].includes(transfer.status)" @click="cancelTransfer" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
            Cancel Transfer
          </button>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Transfer Items</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in transfer.items" :key="item.id">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ item.product?.name || '-' }}</div>
                <div class="text-sm text-gray-500">{{ item.product?.sku || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                {{ item.quantity_requested || 0 }} {{ item.product?.unit || '' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ item.notes || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="transfer.notes" class="bg-white rounded-lg shadow p-6 mt-6">
      <h3 class="text-lg font-semibold mb-2">Notes</h3>
      <p class="text-gray-700">{{ transfer.notes }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()

const transfer = ref({
  items: []
})

onMounted(async () => {
  await loadTransfer()
})

const loadTransfer = async () => {
  try {
    const { data } = await api.get(`/inventory-transfers/${route.params.id}`)
    transfer.value = data
  } catch (error) {
    console.error('Failed to load transfer:', error)
    alert('Transfer not found')
    router.push('/inventory/transfers')
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'PENDING': 'bg-blue-100 text-blue-800',
    'IN_TRANSIT': 'bg-purple-100 text-purple-800',
    'RECEIVED': 'bg-green-100 text-green-800',
    'CANCELLED': 'bg-red-100 text-red-800'
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

const submitTransfer = async () => {
  if (!confirm('Submit this transfer for approval?')) return
  try {
    await api.post(`/inventory-transfers/${transfer.value.id}/submit`)
    alert('Transfer submitted successfully')
    await loadTransfer()
  } catch (error) {
    alert('Failed to submit transfer: ' + (error.response?.data?.message || error.message))
  }
}

const approveTransfer = async () => {
  if (!confirm('Approve this transfer?')) return
  try {
    await api.post(`/inventory-transfers/${transfer.value.id}/approve`)
    alert('Transfer approved successfully')
    await loadTransfer()
  } catch (error) {
    alert('Failed to approve transfer: ' + (error.response?.data?.message || error.message))
  }
}

const receiveTransfer = async () => {
  if (!confirm('Confirm receipt of this transfer? Stock will be updated.')) return
  try {
    // Prepare items data for receiving
    const items = transfer.value.items.map(item => ({
      id: item.id,
      quantity_received: item.quantity_requested,
      quantity_rejected: 0,
      notes: ''
    }))
    
    console.log('Transfer items:', transfer.value.items)
    console.log('Sending receive request with items:', items)
    console.log('Payload:', { items })
    
    await api.post(`/inventory-transfers/${transfer.value.id}/receive`, { items })
    alert('Transfer received successfully. Stock has been updated.')
    await loadTransfer()
  } catch (error) {
    console.error('Receive error details:', error.response?.data)
    console.error('Validation errors:', error.response?.data?.errors)
    
    // Format validation errors for display
    let errorMsg = error.response?.data?.message || 'Validation failed'
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      errorMsg += ':\n' + Object.entries(errors).map(([field, msgs]) => 
        `- ${field}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`
      ).join('\n')
    }
    
    alert('Failed to receive transfer: ' + errorMsg)
  }
}

const cancelTransfer = async () => {
  if (!confirm('Cancel this transfer? This action cannot be undone.')) return
  try {
    await api.post(`/inventory-transfers/${transfer.value.id}/cancel`)
    alert('Transfer cancelled successfully')
    await loadTransfer()
  } catch (error) {
    alert('Failed to cancel transfer: ' + (error.response?.data?.message || error.message))
  }
}

const goBack = () => {
  router.push('/inventory/transfers')
}
</script>
