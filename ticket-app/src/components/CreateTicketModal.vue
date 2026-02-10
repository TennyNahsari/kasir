<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
        <h3 class="text-lg font-semibold text-gray-900">Create New Ticket</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>

        <!-- Ticket Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Ticket Type <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.type"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="onTypeChange"
          >
            <option value="">Select type...</option>
            <option value="INCIDENT">Incident</option>
            <option value="MAINTENANCE">Maintenance</option>
          </select>
        </div>

        <!-- Asset Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Asset <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.asset_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :disabled="loadingAssets"
            @change="onAssetChange"
          >
            <option value="">{{ loadingAssets ? 'Loading assets...' : 'Select asset...' }}</option>
            <option v-for="asset in assets" :key="asset.id" :value="asset.id">
              {{ asset.product?.name }} - {{ asset.asset_code }}{{ asset.serial_number ? ' | SN: ' + asset.serial_number : '' }}{{ asset.pic ? ' | PIC: ' + asset.pic : '' }} ({{ asset.location?.name }})
            </option>
          </select>
        </div>

        <!-- Title -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Title <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.title"
            type="text"
            required
            maxlength="255"
            placeholder="Brief description of the issue"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Description <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.description"
            required
            rows="4"
            placeholder="Detailed description of the issue or maintenance request"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Priority -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Priority <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.priority"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="NORMAL">Normal</option>
            <option value="HIGH">High</option>
          </select>
          <p class="mt-1 text-xs text-gray-500">High priority tickets will be escalated</p>
        </div>

        <!-- Maintenance Type (for Maintenance only) -->
        <div v-if="form.type === 'MAINTENANCE'">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Maintenance Type <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.maintenance_type"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select type...</option>
            <option value="PREVENTIVE">Preventive - Routine/scheduled maintenance</option>
            <option value="CORRECTIVE">Corrective - Fix after breakdown</option>
            <option value="PREDICTIVE">Predictive - Based on monitoring</option>
          </select>
        </div>

        <!-- Scheduled Date (for Maintenance only) -->
        <div v-if="form.type === 'MAINTENANCE'">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Scheduled Date
          </label>
          <input
            v-model="form.scheduled_date"
            type="datetime-local"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p class="mt-1 text-xs text-gray-500">When should this maintenance be performed</p>
        </div>

        <!-- Recurring Schedule (for Maintenance only) -->
        <div v-if="form.type === 'MAINTENANCE'" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
          <div class="flex items-center mb-3">
            <input
              v-model="form.create_schedule"
              type="checkbox"
              id="create_schedule"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="create_schedule" class="ml-2 block text-sm font-medium text-gray-700">
              Create Recurring Schedule
            </label>
          </div>
          
          <div v-if="form.create_schedule" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Repeat Every <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.frequency"
                :required="form.create_schedule"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="MONTHLY">Monthly (every 1 month)</option>
                <option value="QUARTERLY">Quarterly (every 3 months)</option>
                <option value="SEMI_ANNUAL">Semi-Annual (every 6 months)</option>
                <option value="ANNUAL">Annual (every 1 year)</option>
              </select>
              <p class="mt-1 text-xs text-gray-500">System will auto-create tickets based on this schedule</p>
            </div>
          </div>
        </div>

        <!-- Assigned To (only for supervisors) -->
        <div v-if="canAssignTechnician">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Assign To Technician
          </label>
          
          <!-- Selected Technician Badge -->
          <div v-if="selectedTechnician" class="mb-2 inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
            <span>{{ selectedTechnician.name }} ({{ selectedTechnician.email }})</span>
            <button
              type="button"
              @click="clearTechnician"
              class="ml-2 text-blue-600 hover:text-blue-800"
            >
              ×
            </button>
          </div>

          <!-- Search Input -->
          <div v-else class="relative">
            <input
              v-model="technicianSearch"
              type="text"
              placeholder="Search technician by name or email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @focus="showTechnicianDropdown = true"
              @input="showTechnicianDropdown = true"
              @blur="() => setTimeout(() => showTechnicianDropdown = false, 200)"
            />
            
            <!-- Dropdown -->
            <div
              v-if="showTechnicianDropdown && filteredTechnicians.length > 0"
              class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
            >
              <button
                v-for="tech in filteredTechnicians"
                :key="tech.id"
                type="button"
                @click="selectTechnician(tech)"
                class="w-full px-3 py-2 text-left hover:bg-gray-100 border-b border-gray-100 last:border-b-0"
              >
                <div class="font-medium text-sm">{{ tech.name }}</div>
                <div class="text-xs text-gray-500">{{ tech.email }}</div>
              </button>
            </div>

            <!-- Empty States -->
            <div
              v-else-if="showTechnicianDropdown && technicians.length === 0"
              class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-3 text-sm text-gray-500"
            >
              No technicians available
            </div>
            <div
              v-else-if="showTechnicianDropdown && filteredTechnicians.length === 0 && technicianSearch"
              class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-3 text-sm text-gray-500"
            >
              No technicians found
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            :disabled="submitting"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="submitting"
          >
            {{ submitting ? 'Creating...' : 'Create Ticket' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ticketService from '@/services/ticketService'
import axios from '@/utils/axios'

const emit = defineEmits(['close', 'created'])
const authStore = useAuthStore()

const form = ref({
  type: '',
  asset_id: '',
  title: '',
  description: '',
  priority: 'NORMAL',
  maintenance_type: '',
  scheduled_date: '',
  assigned_to: '',
  create_schedule: false,
  frequency: 'QUARTERLY'
})

const assets = ref([])
const technicians = ref([])
const loadingAssets = ref(false)
const loadingTechnicians = ref(false)
const submitting = ref(false)
const error = ref(null)

// Technician autocomplete
const technicianSearch = ref('')
const showTechnicianDropdown = ref(false)
const selectedTechnician = ref(null)

const canAssignTechnician = computed(() => {
  const user = authStore.user
  return user?.role === 'owner' || user?.is_technician === true
})

const filteredTechnicians = computed(() => {
  const search = technicianSearch.value?.toLowerCase() || ''
  let result = []
  if (!search) {
    result = technicians.value.slice(0, 10)
  } else {
    result = technicians.value.filter(tech => {
      const name = tech.name?.toLowerCase() || ''
      const email = tech.email?.toLowerCase() || ''
      return name.includes(search) || email.includes(search)
    }).slice(0, 10)
  }
  return result
})

const loadAssets = async () => {
  try {
    loadingAssets.value = true
    const response = await ticketService.getMyAssets()
    assets.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading assets:', err)
  } finally {
    loadingAssets.value = false
  }
}

const loadTechnicians = async () => {
  try {
    loadingTechnicians.value = true
    const response = await axios.get('/users', { params: { is_technician: 1 } })
    console.log('Loaded technicians:', response.data)
    technicians.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading technicians:', err)
  } finally {
    loadingTechnicians.value = false
  }
}

const selectTechnician = (tech) => {
  selectedTechnician.value = tech
  form.value.assigned_to = tech.id
  technicianSearch.value = ''
  showTechnicianDropdown.value = false
}

const clearTechnician = () => {
  selectedTechnician.value = null
  form.value.assigned_to = ''
  technicianSearch.value = ''
}

const onTypeChange = () => {
  if (form.value.type !== 'MAINTENANCE') {
    form.value.maintenance_type = ''
    form.value.scheduled_date = ''
    form.value.create_schedule = false
  }
}

const onAssetChange = () => {
  const selectedAsset = assets.value.find(a => a.id == form.value.asset_id)
  if (selectedAsset && !form.value.title) {
    // Auto-fill title based on type
    if (form.value.type === 'INCIDENT') {
      form.value.title = `Issue with ${selectedAsset.product?.name}`
    } else if (form.value.type === 'MAINTENANCE') {
      form.value.title = `Scheduled maintenance for ${selectedAsset.product?.name}`
    }
  }
}

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    const payload = {
      type: form.value.type,
      asset_id: form.value.asset_id,
      title: form.value.title,
      description: form.value.description,
      priority: form.value.priority
    }

    if (form.value.type === 'MAINTENANCE' && form.value.maintenance_type) {
      payload.maintenance_type = form.value.maintenance_type
    }

    if (form.value.scheduled_date) {
      payload.scheduled_date = form.value.scheduled_date
    }

    if (form.value.assigned_to) {
      payload.assigned_to = form.value.assigned_to
    }

    // Create recurring schedule if requested
    if (form.value.type === 'MAINTENANCE' && form.value.create_schedule) {
      payload.create_schedule = true
      payload.frequency = form.value.frequency
    }

    await ticketService.createTicket(payload)
    emit('created')
  } catch (err) {
    console.error('Error creating ticket:', err)
    error.value = err.response?.data?.message || 'Failed to create ticket'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadAssets()
  if (canAssignTechnician.value) {
    loadTechnicians()
  }
})
</script>
