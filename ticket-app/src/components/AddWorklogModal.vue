<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-lg w-full">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Add Worklog</h3>
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

        <!-- Time Spent -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Time Spent (minutes) <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.time_spent"
            type="number"
            required
            min="1"
            placeholder="e.g., 30"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p class="mt-1 text-xs text-gray-500">How much time did you spend on this work?</p>
        </div>

        <!-- Work Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Work Notes <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.notes"
            required
            rows="5"
            placeholder="Describe what work was done, findings, or next steps..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Update Status Option -->
        <div class="border-t border-gray-200 pt-4">
          <label class="flex items-center">
            <input
              v-model="form.update_status"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Also update ticket status</span>
          </label>
        </div>

        <!-- New Status (if updating) -->
        <div v-if="form.update_status">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            New Status <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.new_status"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select status...</option>
            <option v-for="status in availableStatuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
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
            {{ submitting ? 'Adding...' : 'Add Worklog' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ticketService from '@/services/ticketService'
import { TICKET_STATUSES } from '@/utils/ticketHelpers'

const props = defineProps({
  ticket: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'added'])
const authStore = useAuthStore()

const form = ref({
  time_spent: 30,
  notes: '',
  update_status: false,
  new_status: ''
})

const submitting = ref(false)
const error = ref(null)

// Define valid status transitions
const availableStatuses = computed(() => {
  const current = props.ticket.status
  const user = authStore.user
  const isOwnerOrSupervisor = user?.role === 'owner' || user?.role === 'supervisor'
  const isTechnician = user?.is_technician === true
  const isStaffOrKasir = ['staff', 'kasir'].includes(user?.role)

  // Owner, Supervisor, Technician, Staff, and Kasir can change to any status
  if (isOwnerOrSupervisor || isTechnician || isStaffOrKasir) {
    return TICKET_STATUSES.filter(s => s.value !== current)
  }

  return []
})

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    const payload = {
      time_spent: form.value.time_spent,
      notes: form.value.notes
    }

    if (form.value.update_status && form.value.new_status) {
      payload.new_status = form.value.new_status
    }

    await ticketService.addWorklog(props.ticket.id, payload)
    emit('added')
  } catch (err) {
    console.error('Error adding worklog:', err)
    console.error('Error details:', err.response?.data)
    error.value = err.response?.data?.error || err.response?.data?.message || 'Failed to add worklog'
  } finally {
    submitting.value = false
  }
}
</script>
