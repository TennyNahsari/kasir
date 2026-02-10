<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Tickets</h1>
          <p class="text-sm text-gray-600 mt-1">Manage and track all tickets</p>
        </div>
        <button 
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Ticket
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Ticket number, title..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadTickets"
            >
              <option value="">All Status</option>
              <option v-for="status in TICKET_STATUSES" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select
              v-model="filters.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadTickets"
            >
              <option value="">All Types</option>
              <option v-for="type in TICKET_TYPES" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
          </div>

          <!-- Priority -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select
              v-model="filters.priority"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadTickets"
            >
              <option value="">All Priorities</option>
              <option v-for="priority in PRIORITY_OPTIONS" :key="priority.value" :value="priority.value">
                {{ priority.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Additional Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4" v-if="canFilterByTechnician">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
            <select
              v-model="filters.assigned_to"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadTickets"
            >
              <option value="">All Technicians</option>
              <option v-for="tech in technicians" :key="tech.id" :value="tech.id">
                {{ tech.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="flex items-center mt-6">
              <input
                v-model="filters.overdue_only"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @change="loadTickets"
              />
              <span class="ml-2 text-sm text-gray-700">Show overdue only</span>
            </label>
          </div>

          <div class="flex items-end justify-end">
            <button
              @click="clearFilters"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
            <button @click="loadTickets" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
              Try again
            </button>
          </div>
        </div>
      </div>

      <!-- Tickets Table -->
      <div v-else-if="tickets.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ticket
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Asset
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Priority
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Assigned To
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  SLA Due
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ ticket.ticket_number }}</div>
                  <div class="text-sm text-gray-500 truncate max-w-xs">{{ ticket.title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ ticket.asset?.product?.name }}</div>
                  <div class="text-sm text-gray-500">{{ ticket.asset?.asset_code }}</div>
                  <div v-if="ticket.asset?.serial_number" class="text-xs text-gray-400">SN: {{ ticket.asset.serial_number }}</div>
                  <div v-if="ticket.asset?.pic" class="text-xs text-gray-400">PIC: {{ ticket.asset.pic }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTypeClass(ticket.type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ ticket.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPriorityClass(ticket.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ ticket.priority }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(ticket.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ formatStatus(ticket.status) }}
                  </span>
                  <span v-if="isOverdue(ticket)" class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    OVERDUE
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ ticket.assigned_user?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div>{{ formatDate(ticket.sla_due_date) }}</div>
                  <div v-if="!isOverdue(ticket) && ticket.sla_due_date" class="text-xs text-gray-500">
                    {{ getTimeRemaining(ticket.sla_due_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <router-link
                    :to="`/tickets/${ticket.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new ticket.</p>
        <div class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Ticket
          </button>
        </div>
      </div>
    </div>

    <!-- Create Ticket Modal -->
    <CreateTicketModal 
      v-if="showCreateModal" 
      @close="showCreateModal = false"
      @created="handleTicketCreated"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'
import CreateTicketModal from '@/components/CreateTicketModal.vue'
import ticketService from '@/services/ticketService'
import {
  getStatusClass,
  getPriorityClass,
  getTypeClass,
  formatStatus,
  formatDate,
  isOverdue,
  getTimeRemaining,
  TICKET_STATUSES,
  TICKET_TYPES,
  PRIORITY_OPTIONS
} from '@/utils/ticketHelpers'

const authStore = useAuthStore()

const tickets = ref([])
const technicians = ref([])
const loading = ref(false)
const error = ref(null)
const showCreateModal = ref(false)

const filters = ref({
  search: '',
  status: '',
  type: '',
  priority: '',
  assigned_to: '',
  overdue_only: false
})

const canFilterByTechnician = computed(() => {
  const role = authStore.user?.role
  return ['owner', 'supervisor'].includes(role)
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadTickets()
  }, 500)
}

const loadTickets = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.type) params.type = filters.value.type
    if (filters.value.priority) params.priority = filters.value.priority
    if (filters.value.assigned_to) params.assigned_to = filters.value.assigned_to
    if (filters.value.overdue_only) params.overdue_only = 1
    
    const response = await ticketService.getTickets(params)
    tickets.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading tickets:', err)
    error.value = err.response?.data?.message || 'Failed to load tickets'
  } finally {
    loading.value = false
  }
}

const loadTechnicians = async () => {
  try {
    // Get technicians from statistics endpoint which includes them
    const response = await ticketService.getStatistics()
    if (response.data.technicians) {
      technicians.value = response.data.technicians
    }
  } catch (err) {
    console.error('Error loading technicians:', err)
  }
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    type: '',
    priority: '',
    assigned_to: '',
    overdue_only: false
  }
  loadTickets()
}

const handleTicketCreated = () => {
  showCreateModal.value = false
  loadTickets()
}

onMounted(() => {
  loadTickets()
  if (canFilterByTechnician.value) {
    loadTechnicians()
  }
})
</script>
