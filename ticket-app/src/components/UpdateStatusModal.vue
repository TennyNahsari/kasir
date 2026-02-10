<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Update Ticket Status</h3>
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

        <!-- Current Status -->
        <div class="bg-gray-50 rounded-md p-4">
          <p class="text-sm text-gray-600">Current Status:</p>
          <p class="text-lg font-semibold text-gray-900">{{ formatStatus(ticket.status) }}</p>
        </div>

        <!-- New Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            New Status <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.status"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select status...</option>
            <option v-for="status in availableStatuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
        </div>

        <!-- Resolution Notes (for RESOLVED status) -->
        <div v-if="form.status === 'RESOLVED'">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Resolution Notes
          </label>
          <textarea
            v-model="form.resolution_notes"
            rows="3"
            placeholder="Describe how the issue was resolved..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Assign Technician (for ASSIGNED status) -->
        <div v-if="form.status === 'ASSIGNED' && canAssignTechnician">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Assign To <span class="text-red-500">*</span>
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
              required
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
            {{ submitting ? 'Updating...' : 'Update Status' }}
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
import { formatStatus, TICKET_STATUSES } from '@/utils/ticketHelpers'

const props = defineProps({
  ticket: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])
const authStore = useAuthStore()

const form = ref({
  status: '',
  resolution_notes: '',
  assigned_to: props.ticket.assigned_to
})

const technicians = ref([])
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

// Define valid status transitions
const availableStatuses = computed(() => {
  const current = props.ticket.status
  const user = authStore.user
  const isOwnerOrSupervisor = user?.role === 'owner' || user?.role === 'supervisor'
  const isTechnician = user?.is_technician === true

  // Owner, Supervisor, and Technician can change to any status
  if (isOwnerOrSupervisor || isTechnician) {
    return TICKET_STATUSES.filter(s => s.value !== current)
  }

  return []
})

const loadTechnicians = async () => {
  try {
    loadingTechnicians.value = true
    const response = await axios.get('/users', { params: { is_technician: 1 } })
    console.log('Loaded technicians:', response.data)
    technicians.value = response.data.data || response.data
    
    // If ticket already has assigned technician, set it as selected
    if (props.ticket.assigned_to) {
      const existingTech = technicians.value.find(t => t.id === props.ticket.assigned_to)
      if (existingTech) {
        selectedTechnician.value = existingTech
      }
    }
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

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    const payload = {
      status: form.value.status
    }

    if (form.value.resolution_notes) {
      payload.resolution_notes = form.value.resolution_notes
    }

    if (form.value.status === 'ASSIGNED' && form.value.assigned_to) {
      payload.assigned_to = form.value.assigned_to
    }

    await ticketService.updateTicket(props.ticket.id, payload)
    emit('updated')
  } catch (err) {
    console.error('Error updating ticket:', err)
    error.value = err.response?.data?.message || 'Failed to update ticket status'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  if (canAssignTechnician.value) {
    loadTechnicians()
  }
})
</script>
