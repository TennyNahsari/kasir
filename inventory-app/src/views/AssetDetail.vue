<template>
  <div class="p-4 sm:p-6">
    <div class="mb-6">
      <button @click="goBack" class="text-blue-600 hover:text-blue-800 mb-2">← Back to Assets</button>
      
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 font-mono">{{ asset.asset_tag }}</h1>
          <p class="text-gray-600">{{ asset.product?.name }}</p>
        </div>
        <div class="flex flex-col gap-2 items-end">
          <span :class="getStatusBadgeClass(asset.status)" class="badge">
            {{ asset.status }}
          </span>
          <span :class="getConditionBadgeClass(asset.condition)" class="badge">
            {{ asset.condition }}
          </span>
        </div>
      </div>
    </div>

    <!-- Asset Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 card">
        <h3 class="text-lg font-semibold mb-4">Asset Information</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-gray-600">Serial Number</dt>
            <dd class="font-medium font-mono">{{ asset.serial_number || '-' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">Product</dt>
            <dd class="font-medium">{{ asset.product?.name }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">Location</dt>
            <dd class="font-medium">{{ asset.location?.name }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">PIC</dt>
            <dd class="font-medium">{{ asset.pic || 'Not Assigned' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">Purchase Date</dt>
            <dd class="font-medium">{{ formatDate(asset.purchase_date) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">Purchase Price</dt>
            <dd class="font-medium">{{ formatCurrency(asset.purchase_price) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">Current Value</dt>
            <dd class="font-medium text-blue-600">{{ formatCurrency(asset.current_value) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600">Useful Life</dt>
            <dd class="font-medium">{{ asset.useful_life_months }} months</dd>
          </div>
          <div v-if="asset.warranty_until">
            <dt class="text-sm text-gray-600">Warranty Until</dt>
            <dd class="font-medium" :class="isUnderWarranty ? 'text-green-600' : 'text-red-600'">
              {{ formatDate(asset.warranty_until) }}
              <span v-if="isUnderWarranty" class="text-xs">(Active)</span>
            </dd>
          </div>
          <div v-if="asset.assigned_date">
            <dt class="text-sm text-gray-600">Assigned Date</dt>
            <dd class="font-medium">{{ formatDate(asset.assigned_date) }}</dd>
          </div>
        </dl>
      </div>

      <!-- Actions -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-4">Actions</h3>
        <div class="space-y-2">
          <button
            v-if="asset.status === 'AVAILABLE'"
            @click="showAssignModal = true"
            class="w-full btn btn-primary text-sm"
          >
            Assign to User
          </button>
          
          <button
            v-if="asset.status === 'ASSIGNED' || asset.status === 'IN_USE'"
            @click="showReturnModal = true"
            class="w-full btn btn-success text-sm"
          >
            Return Asset
          </button>
          
          <button
            v-if="asset.status !== 'DISPOSED'"
            @click="showTransferModal = true"
            class="w-full btn btn-secondary text-sm"
          >
            Transfer Location
          </button>
          
          <button
            v-if="asset.status !== 'DISPOSED'"
            @click="showEditModal = true"
            class="w-full btn btn-secondary text-sm"
          >
            Edit Details
          </button>
          
          <button
            v-if="asset.status !== 'DISPOSED'"
            @click="showDisposeModal = true"
            class="w-full btn btn-danger text-sm"
          >
            Dispose Asset
          </button>
        </div>
      </div>
    </div>

    <!-- Movement History -->
    <div class="card">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Movement History</h3>
        <button @click="showHistoryModal = true" class="bg-blue-600 text-white px-3 py-1.5 text-sm rounded-lg hover:bg-blue-700">
          + Add History
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Type</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">From</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">To</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Condition</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Notes</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">By</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="movement in movements" :key="movement.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm">{{ formatDateTime(movement.moved_at) }}</td>
              <td class="px-4 py-3 text-sm">
                <span :class="getMovementTypeBadgeClass(movement.movement_type)" class="badge">
                  {{ movement.movement_type }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm">
                {{ movement.from_user?.name || movement.from_location?.name || '-' }}
              </td>
              <td class="px-4 py-3 text-sm">
                {{ movement.to_user?.name || movement.to_location?.name || '-' }}
              </td>
              <td class="px-4 py-3 text-sm">
                <span v-if="movement.condition_after" :class="getConditionBadgeClass(movement.condition_after)" class="badge text-xs">
                  {{ movement.condition_after }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ movement.notes || '-' }}</td>
              <td class="px-4 py-3 text-sm">{{ movement.moved_by_user?.name }}</td>
            </tr>
          </tbody>
        </table>
        
        <div v-if="movements.length === 0" class="text-center py-8 text-gray-500">
          No movement history
        </div>
      </div>
    </div>

    <!-- Assign Modal -->
    <div v-if="showAssignModal" class="modal-overlay" @click.self="closeAssignModal">
      <div class="modal-content max-w-lg">
        <h3 class="text-xl font-bold mb-4">Assign Asset</h3>
        <form @submit.prevent="assignAsset">
          <div class="mb-4">
            <label class="label">PIC (Person In Charge) *</label>
            <input v-model="assignForm.pic" type="text" class="input" placeholder="Enter PIC name" required>
          </div>
          <div class="mb-4">
            <label class="label">Location</label>
            <select v-model.number="assignForm.location_id" class="input">
              <option value="">Keep Current Location</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }}
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="label">Notes</label>
            <textarea v-model="assignForm.notes" class="input" rows="3"></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary flex-1">Assign</button>
            <button type="button" @click="closeAssignModal" class="btn btn-secondary flex-1">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Return Modal -->
    <div v-if="showReturnModal" class="modal-overlay" @click.self="closeReturnModal">
      <div class="modal-content max-w-lg">
        <h3 class="text-xl font-bold mb-4">Return Asset</h3>
        <form @submit.prevent="returnAsset">
          <div class="mb-4">
            <label class="label">Condition *</label>
            <select v-model="returnForm.condition" class="input" required>
              <option value="NEW">NEW</option>
              <option value="GOOD">GOOD</option>
              <option value="FAIR">FAIR</option>
              <option value="POOR">POOR</option>
              <option value="BROKEN">BROKEN</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="label">Notes</label>
            <textarea v-model="returnForm.notes" class="input" rows="3" placeholder="Return notes, any issues, etc."></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-success flex-1">Return</button>
            <button type="button" @click="closeReturnModal" class="btn btn-secondary flex-1">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Transfer Modal -->
    <div v-if="showTransferModal" class="modal-overlay" @click.self="closeTransferModal">
      <div class="modal-content max-w-lg">
        <h3 class="text-xl font-bold mb-4">Transfer Asset</h3>
        <form @submit.prevent="transferAsset">
          <div class="mb-4">
            <label class="label">Current Location</label>
            <input :value="asset.location?.name" class="input bg-gray-100" readonly>
          </div>
          <div class="mb-4">
            <label class="label">New Location *</label>
            <select v-model.number="transferForm.location_id" class="input" required>
              <option value="">Select Location</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id" :disabled="loc.id === asset.location_id">
                {{ loc.name }}
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="label">Notes</label>
            <textarea v-model="transferForm.notes" class="input" rows="3"></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary flex-1">Transfer</button>
            <button type="button" @click="closeTransferModal" class="btn btn-secondary flex-1">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Edit Asset Details</h3>
        <form @submit.prevent="updateAsset">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="label">Status</label>
              <select v-model="editForm.status" class="input">
                <option value="AVAILABLE">Available</option>
                <option value="ASSIGNED">Assigned</option>
                <option value="IN_USE">In Use</option>
                <option value="MAINTENANCE">Maintenance</option>
                <option value="DAMAGED">Damaged</option>
              </select>
            </div>
            <div>
              <label class="label">Condition</label>
              <select v-model="editForm.condition" class="input">
                <option value="NEW">NEW</option>
                <option value="GOOD">GOOD</option>
                <option value="FAIR">FAIR</option>
                <option value="POOR">POOR</option>
                <option value="BROKEN">BROKEN</option>
              </select>
            </div>
            <div>
              <label class="label">Serial Number</label>
              <input v-model="editForm.serial_number" type="text" class="input">
            </div>
            <div>
              <label class="label">Useful Life (months)</label>
              <input v-model.number="editForm.useful_life_months" type="number" class="input" min="1">
            </div>
            <div>
              <label class="label">Warranty Until</label>
              <input v-model="editForm.warranty_until" type="date" class="input">
            </div>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary flex-1">Save Changes</button>
            <button type="button" @click="closeEditModal" class="btn btn-secondary flex-1">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Dispose Modal -->
    <div v-if="showDisposeModal" class="modal-overlay" @click.self="closeDisposeModal">
      <div class="modal-content max-w-lg">
        <h3 class="text-xl font-bold mb-4 text-red-600">Dispose Asset</h3>
        <p class="text-gray-600 mb-4">
          This action will mark the asset as disposed and remove it from active inventory. 
          This action cannot be undone.
        </p>
        <form @submit.prevent="disposeAsset">
          <div class="mb-4">
            <label class="label">Disposal Reason *</label>
            <textarea v-model="disposeForm.notes" class="input" rows="3" required placeholder="Enter disposal reason..."></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-danger flex-1">Dispose Asset</button>
            <button type="button" @click="closeDisposeModal" class="btn btn-secondary flex-1">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add History Modal -->
    <div v-if="showHistoryModal" class="modal-overlay" @click.self="closeHistoryModal">
      <div class="modal-content max-w-lg">
        <h3 class="text-xl font-bold mb-4">Add Movement History</h3>
        <form @submit.prevent="addHistory">
          <div class="mb-4">
            <label class="label">Movement Type *</label>
            <select v-model="historyForm.movement_type" class="input" required>
              <option value="">Select Type</option>
              <option value="ASSIGNED">Assigned</option>
              <option value="RETURNED">Returned</option>
              <option value="TRANSFERRED">Transferred</option>
              <option value="MAINTENANCE">Maintenance</option>
              <option value="REPAIRED">Repaired</option>
              <option value="DAMAGED">Damaged</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="label">Date & Time *</label>
            <input v-model="historyForm.moved_at" type="datetime-local" class="input" required>
          </div>
          <div class="mb-4">
            <label class="label">Condition</label>
            <select v-model="historyForm.condition_after" class="input">
              <option value="">No Change</option>
              <option value="NEW">NEW</option>
              <option value="GOOD">GOOD</option>
              <option value="FAIR">FAIR</option>
              <option value="POOR">POOR</option>
              <option value="BROKEN">BROKEN</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="label">Notes *</label>
            <textarea v-model="historyForm.notes" class="input" rows="3" placeholder="Describe the movement or event..." required></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary flex-1">Add Entry</button>
            <button type="button" @click="closeHistoryModal" class="btn btn-secondary flex-1">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import assetService from '@/services/assetService'
import locationService from '@/services/locationService'
import userService from '@/services/userService'

const router = useRouter()
const route = useRoute()

const asset = ref({})
const movements = ref([])
const locations = ref([])
const users = ref([])

const showAssignModal = ref(false)
const showReturnModal = ref(false)
const showTransferModal = ref(false)
const showEditModal = ref(false)
const showDisposeModal = ref(false)
const showHistoryModal = ref(false)

const assignForm = ref({
  pic: '',
  location_id: '',
  notes: ''
})

const returnForm = ref({
  condition: 'GOOD',
  notes: ''
})

const transferForm = ref({
  location_id: '',
  notes: ''
})

const editForm = ref({
  status: '',
  condition: '',
  serial_number: '',
  useful_life_months: 36,
  warranty_until: ''
})

const disposeForm = ref({
  notes: ''
})

const historyForm = ref({
  movement_type: '',
  moved_at: new Date().toISOString().slice(0, 16),
  condition_after: '',
  notes: ''
})

const isUnderWarranty = computed(() => {
  if (!asset.value.warranty_until) return false
  return new Date(asset.value.warranty_until) > new Date()
})

onMounted(async () => {
  await Promise.all([
    loadAsset(),
    loadMovements(),
    loadLocations(),
    loadUsers()
  ])
})

const loadAsset = async () => {
  try {
    asset.value = await assetService.getAsset(route.params.id)
    
    // Pre-fill edit form
    editForm.value = {
      status: asset.value.status,
      condition: asset.value.condition,
      serial_number: asset.value.serial_number,
      useful_life_months: asset.value.useful_life_months || 36,
      warranty_until: asset.value.warranty_until ? asset.value.warranty_until.split('T')[0] : ''
    }
  } catch (error) {
    console.error('Failed to load asset:', error)
    alert('Failed to load asset')
    router.push('/assets')
  }
}

const loadMovements = async () => {
  try {
    movements.value = await assetService.getAssetHistory(route.params.id)
  } catch (error) {
    console.error('Failed to load movements:', error)
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

const assignAsset = async () => {
  try {
    await assetService.assignAsset(asset.value.id, assignForm.value)
    alert('Asset assigned successfully')
    closeAssignModal()
    await loadAsset()
    await loadMovements()
  } catch (error) {
    console.error('Failed to assign asset:', error)
    alert('Failed to assign asset: ' + (error.response?.data?.message || error.message))
  }
}

const returnAsset = async () => {
  try {
    await assetService.returnAsset(asset.value.id, returnForm.value)
    alert('Asset returned successfully')
    closeReturnModal()
    await loadAsset()
    await loadMovements()
  } catch (error) {
    console.error('Failed to return asset:', error)
    alert('Failed to return asset: ' + (error.response?.data?.message || error.message))
  }
}

const transferAsset = async () => {
  try {
    await assetService.transferAsset(asset.value.id, transferForm.value)
    alert('Asset transferred successfully')
    closeTransferModal()
    await loadAsset()
    await loadMovements()
  } catch (error) {
    console.error('Failed to transfer asset:', error)
    alert('Failed to transfer asset: ' + (error.response?.data?.message || error.message))
  }
}

const updateAsset = async () => {
  try {
    await assetService.updateAsset(asset.value.id, editForm.value)
    alert('Asset updated successfully')
    closeEditModal()
    await loadAsset()
  } catch (error) {
    console.error('Failed to update asset:', error)
    alert('Failed to update asset: ' + (error.response?.data?.message || error.message))
  }
}

const disposeAsset = async () => {
  if (!confirm('Are you sure you want to dispose this asset? This action cannot be undone.')) {
    return
  }
  
  try {
    await assetService.disposeAsset(asset.value.id, disposeForm.value)
    alert('Asset disposed successfully')
    router.push('/assets')
  } catch (error) {
    console.error('Failed to dispose asset:', error)
    alert('Failed to dispose asset: ' + (error.response?.data?.message || error.message))
  }
}

const goBack = () => {
  router.push('/assets')
}

const closeAssignModal = () => {
  showAssignModal.value = false
  assignForm.value = { pic: '', location_id: '', notes: '' }
}

const closeReturnModal = () => {
  showReturnModal.value = false
  returnForm.value = { condition: 'GOOD', notes: '' }
}

const closeTransferModal = () => {
  showTransferModal.value = false
  transferForm.value = { location_id: '', notes: '' }
}

const closeEditModal = () => {
  showEditModal.value = false
}

const closeDisposeModal = () => {
  showDisposeModal.value = false
  disposeForm.value = { notes: '' }
}

const closeHistoryModal = () => {
  showHistoryModal.value = false
  historyForm.value = {
    movement_type: '',
    moved_at: new Date().toISOString().slice(0, 16),
    condition_after: '',
    notes: ''
  }
}

const addHistory = async () => {
  try {
    // Prepare data and remove empty strings
    const payload = {
      movement_type: historyForm.value.movement_type,
      notes: historyForm.value.notes
    }
    
    // Add moved_at only if it has a value
    if (historyForm.value.moved_at) {
      payload.moved_at = historyForm.value.moved_at.replace('T', ' ') + ':00'
    }
    
    // Add condition_after only if selected
    if (historyForm.value.condition_after) {
      payload.condition_after = historyForm.value.condition_after
    }
    
    await assetService.addMovementHistory(asset.value.id, payload)
    alert('History entry added successfully')
    closeHistoryModal()
    await loadAsset()
    await loadMovements()
  } catch (error) {
    console.error('Failed to add history:', error)
    alert('Failed to add history: ' + (error.response?.data?.message || error.message))
  }
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

const getMovementTypeBadgeClass = (type) => {
  const classes = {
    'PURCHASED': 'badge-green',
    'ASSIGNED': 'badge-blue',
    'RETURNED': 'badge-purple',
    'TRANSFERRED': 'badge-blue',
    'MAINTENANCE': 'badge-yellow',
    'REPAIRED': 'badge-green',
    'DAMAGED': 'badge-red',
    'DISPOSED': 'badge-gray'
  }
  return classes[type] || 'badge-gray'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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
