<template>
  <div class="p-6">
    <div class="mb-6">
      <button @click="goBack" class="text-purple-600 hover:text-purple-800 mb-2">← Back to Service Contracts</button>
      
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">{{ contract.contract_number }}</h1>
          <p class="text-gray-600">{{ contract.product?.name }}</p>
        </div>
        <span :class="getStatusClass(contract.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
          {{ contract.status }}
        </span>
      </div>
    </div>

    <!-- Warning for expiring soon -->
    <div v-if="contract.is_expiring_soon" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-yellow-700">
            This contract expires in <strong>{{ contract.days_until_expiry }} days</strong> ({{ formatDate(contract.end_date) }})
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Contract Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Contract Details</h3>
          <dl class="grid grid-cols-2 gap-4">
            <div>
              <dt class="text-sm text-gray-600">Contract Number</dt>
              <dd class="font-medium">{{ contract.contract_number }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Contract Type</dt>
              <dd class="font-medium">{{ formatContractType(contract.contract_type) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Start Date</dt>
              <dd class="font-medium">{{ formatDate(contract.start_date) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">End Date</dt>
              <dd class="font-medium">{{ formatDate(contract.end_date) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Contract Value</dt>
              <dd class="font-medium text-purple-600">{{ formatCurrency(contract.contract_value) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Billing Cycle</dt>
              <dd class="font-medium">{{ contract.billing_cycle }}</dd>
            </div>
          </dl>
        </div>

        <!-- Product & Vendor Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Product & Vendor Information</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <dt class="text-sm text-gray-600 mb-1">Product/Service</dt>
              <dd class="font-medium">{{ contract.product?.name || 'N/A' }}</dd>
              <dd class="text-xs text-gray-500">SKU: {{ contract.product?.sku || 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600 mb-1">Vendor</dt>
              <dd class="font-medium">{{ contract.vendor?.name || 'N/A' }}</dd>
              <dd class="text-xs text-gray-500">{{ contract.vendor?.email || 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600 mb-1">Location</dt>
              <dd class="font-medium">{{ contract.location?.name || 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600 mb-1">PIC</dt>
              <dd class="font-medium">{{ contract.pic || '-' }}</dd>
            </div>
            <div v-if="contract.goods_receipt">
              <dt class="text-sm text-gray-600 mb-1">From GRN</dt>
              <dd class="font-medium text-blue-600 cursor-pointer hover:underline" @click="goToGRN">
                {{ contract.goods_receipt?.grn_no || 'N/A' }}
              </dd>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="contract.notes" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Notes</h3>
          <p class="text-gray-700 whitespace-pre-line">{{ contract.notes }}</p>
        </div>

        <!-- Contract Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Contract Timeline</h3>
          <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-300"></div>
            
            <!-- Created -->
            <div class="relative flex items-start mb-6 pl-12">
              <div class="absolute left-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium">Contract Created</p>
                <p class="text-sm text-gray-500">{{ formatDate(contract.created_at) }}</p>
              </div>
            </div>

            <!-- Start Date -->
            <div class="relative flex items-start mb-6 pl-12">
              <div class="absolute left-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium">Contract Start</p>
                <p class="text-sm text-gray-500">{{ formatDate(contract.start_date) }}</p>
              </div>
            </div>

            <!-- Today marker (if active) -->
            <div v-if="contract.status === 'ACTIVE'" class="relative flex items-start mb-6 pl-12">
              <div class="absolute left-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center animate-pulse">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium text-blue-600">Currently Active</p>
                <p class="text-sm text-gray-500">{{ formatDate(new Date()) }}</p>
              </div>
            </div>

            <!-- End Date -->
            <div class="relative flex items-start pl-12">
              <div :class="contract.status === 'EXPIRED' ? 'bg-red-100' : 'bg-gray-100'" class="absolute left-0 w-8 h-8 rounded-full flex items-center justify-center">
                <svg :class="contract.status === 'EXPIRED' ? 'text-red-600' : 'text-gray-400'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p :class="contract.status === 'EXPIRED' ? 'font-medium text-red-600' : 'font-medium'">Contract End</p>
                <p class="text-sm text-gray-500">{{ formatDate(contract.end_date) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions Sidebar -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Actions</h3>
          <div class="space-y-3">
            <button @click="showEditModal = true"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              Edit Contract
            </button>
            <button v-if="contract.status === 'ACTIVE' || contract.status === 'EXPIRED'" 
              @click="showRenewModal = true"
              class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
              Renew Contract
            </button>
            <button v-if="contract.status === 'ACTIVE'" 
              @click="showTerminateModal = true"
              class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
              Terminate Contract
            </button>
            <button v-if="contract.purchase_order" 
              @click="goToPO"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              View Purchase Order
            </button>
          </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-purple-50 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4 text-purple-900">Contract Summary</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-purple-700">Duration</dt>
              <dd class="text-lg font-bold text-purple-900">{{ calculateDuration() }} days</dd>
            </div>
            <div v-if="contract.status === 'ACTIVE'">
              <dt class="text-sm text-purple-700">Days Remaining</dt>
              <dd class="text-lg font-bold text-purple-900">{{ contract.days_until_expiry }} days</dd>
            </div>
            <div v-if="contract.billing_cycle === 'MONTHLY'">
              <dt class="text-sm text-purple-700">Monthly Cost</dt>
              <dd class="text-lg font-bold text-purple-900">{{ formatCurrency(contract.contract_value) }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4">Edit Service Contract</h3>
        <form @submit.prevent="updateContract">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Product/Service</label>
              <input :value="contract.product?.name" readonly
                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100">
              <p class="text-xs text-gray-500 mt-1">Product cannot be changed</p>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Vendor</label>
              <input :value="contract.vendor?.name" readonly
                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100">
              <p class="text-xs text-gray-500 mt-1">Vendor cannot be changed</p>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">PIC (Person In Charge)</label>
              <input v-model="editForm.pic" type="text" placeholder="Name of person in charge..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contract Type</label>
              <select v-model="editForm.contract_type"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="RENTAL">Rental (Sewa)</option>
                <option value="SUBSCRIPTION">Subscription (Langganan)</option>
                <option value="MAINTENANCE">Maintenance (Pemeliharaan)</option>
                <option value="CONSULTING">Consulting (Konsultasi)</option>
                <option value="UTILITY">Utility (Listrik, Air, Internet)</option>
                <option value="OTHER">Other (Lainnya)</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle</label>
              <select v-model="editForm.billing_cycle"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="MONTHLY">Monthly</option>
                <option value="QUARTERLY">Quarterly</option>
                <option value="YEARLY">Yearly</option>
                <option value="ONE_TIME">One Time</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
              <input v-model="editForm.start_date" type="date"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
              <input v-model="editForm.end_date" type="date"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Contract Value</label>
              <input v-model.number="editForm.contract_value" type="number" step="0.01" min="0"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea v-model="editForm.notes" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
              Update Contract
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Renew Modal -->
    <div v-if="showRenewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Renew Contract</h3>
        <form @submit.prevent="renewContract">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">New Start Date *</label>
              <input v-model="renewForm.start_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">New End Date *</label>
              <input v-model="renewForm.end_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contract Value</label>
              <input v-model.number="renewForm.contract_value" type="number" step="0.01" 
                :placeholder="contract.contract_value"
                class="w-full border border-gray-300 rounded-lg px-4 py-2">
              <p class="text-xs text-gray-500 mt-1">Leave empty to keep current value</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea v-model="renewForm.notes" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="showRenewModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
              Renew Contract
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Terminate Modal -->
    <div v-if="showTerminateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Terminate Contract</h3>
        <form @submit.prevent="terminateContract">
          <div class="space-y-4">
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
              <p class="text-sm text-red-700">
                This action will terminate the contract immediately. This cannot be undone.
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Termination Reason *</label>
              <textarea v-model="terminateForm.reason" rows="4" required
                placeholder="Enter reason for terminating this contract..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="showTerminateModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
              Terminate Contract
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import serviceService from '@/services/serviceService'

const router = useRouter()
const route = useRoute()

const contract = ref({})
const showEditModal = ref(false)
const showRenewModal = ref(false)
const showTerminateModal = ref(false)

const editForm = ref({
  pic: '',
  contract_type: '',
  billing_cycle: '',
  start_date: '',
  end_date: '',
  contract_value: 0,
  notes: ''
})

const renewForm = ref({
  start_date: '',
  end_date: '',
  contract_value: null,
  notes: ''
})

const terminateForm = ref({
  reason: ''
})

onMounted(async () => {
  await loadContract()
})

const loadContract = async () => {
  try {
    console.log('Loading service contract, route.params.id:', route.params.id, 'Type:', typeof route.params.id)
    const { data } = await serviceService.get(route.params.id)
    console.log('Contract loaded successfully:', data)
    contract.value = data
    
    // Pre-fill edit form
    editForm.value = {
      pic: data.pic || '',
      contract_type: data.contract_type,
      billing_cycle: data.billing_cycle,
      start_date: data.start_date,
      end_date: data.end_date,
      contract_value: data.contract_value,
      notes: data.notes || ''
    }
    
    // Pre-fill renew form with suggested dates (extend by same duration)
    if (data.end_date) {
      const endDate = new Date(data.end_date)
      const startDate = new Date(data.start_date)
      const duration = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24))
      
      const newStartDate = new Date(endDate)
      newStartDate.setDate(newStartDate.getDate() + 1)
      
      const newEndDate = new Date(newStartDate)
      newEndDate.setDate(newEndDate.getDate() + duration)
      
      renewForm.value.start_date = newStartDate.toISOString().split('T')[0]
      renewForm.value.end_date = newEndDate.toISOString().split('T')[0]
    }
  } catch (error) {
    console.error('Failed to load contract:', error)
    console.error('Requested ID:', route.params.id)
    console.error('Error response:', error.response?.data)
    alert(`Contract ID ${route.params.id} not found. Please access contracts from the list page.`)
    router.push('/services')
  }
}

const updateContract = async () => {
  if (!confirm('Update this service contract?')) return
  
  try {
    await serviceService.update(contract.value.id, editForm.value)
    alert('Service contract updated successfully!')
    await loadContract()
    showEditModal.value = false
  } catch (error) {
    console.error('Failed to update contract:', error)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || error.message
    alert('Failed to update contract: ' + errorMsg)
  }
}

const renewContract = async () => {
  if (!confirm('Confirm renew this contract? A new contract will be created.')) return
  
  try {
    await serviceService.renew(contract.value.id, renewForm.value)
    alert('Contract renewed successfully! Redirecting to new contract...')
    
    // Reload to get the new contract
    await loadContract()
    showRenewModal.value = false
  } catch (error) {
    console.error('Failed to renew contract:', error)
    alert('Failed to renew contract: ' + (error.response?.data?.message || error.message))
  }
}

const terminateContract = async () => {
  if (!confirm('Are you sure you want to terminate this contract? This cannot be undone.')) return
  
  try {
    await serviceService.terminate(contract.value.id, terminateForm.value.reason)
    alert('Contract terminated successfully')
    await loadContract()
    showTerminateModal.value = false
  } catch (error) {
    console.error('Failed to terminate contract:', error)
    alert('Failed to terminate contract: ' + (error.response?.data?.message || error.message))
  }
}

const goBack = () => {
  router.push('/services')
}

const goToGRN = () => {
  if (contract.value.grn_id) {
    window.open(`http://localhost:5176/procurement/goods-receipts/${contract.value.grn_id}`, '_blank')
  }
}

const goToPO = () => {
  if (contract.value.po_id) {
    window.open(`http://localhost:5176/procurement/purchase-orders/${contract.value.po_id}`, '_blank')
  }
}

const calculateDuration = () => {
  if (!contract.value.start_date || !contract.value.end_date) return 0
  const start = new Date(contract.value.start_date)
  const end = new Date(contract.value.end_date)
  return Math.ceil((end - start) / (1000 * 60 * 60 * 24))
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatContractType = (type) => {
  const types = {
    'RENTAL': 'Rental',
    'SUBSCRIPTION': 'Subscription',
    'MAINTENANCE': 'Maintenance',
    'CONSULTING': 'Consulting',
    'UTILITY': 'Utility',
    'OTHER': 'Other'
  }
  return types[type] || type
}

const getStatusClass = (status) => {
  const classes = {
    'ACTIVE': 'bg-green-100 text-green-800',
    'PENDING': 'bg-blue-100 text-blue-800',
    'EXPIRED': 'bg-red-100 text-red-800',
    'TERMINATED': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
