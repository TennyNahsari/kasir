<template>
  <div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <StatCard
        title="Assigned"
        :value="data.my_tickets.assigned"
        icon="📋"
        color="cyan"
      />
      <StatCard
        title="In Progress"
        :value="data.my_tickets.in_progress"
        icon="🔧"
        color="purple"
      />
      <StatCard
        title="On Hold"
        :value="data.my_tickets.on_hold"
        icon="⏸️"
        color="orange"
      />
      <StatCard
        title="Resolved This Week"
        :value="data.my_tickets.resolved_this_week"
        icon="✅"
        color="green"
      />
    </div>

    <!-- Overdue Tickets Alert -->
    <div v-if="data.overdue_tickets.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">
            {{ data.overdue_tickets.length }} Overdue Ticket{{ data.overdue_tickets.length > 1 ? 's' : '' }}
          </h3>
          <div class="mt-2 text-sm text-red-700">
            <p>You have overdue tickets that need immediate attention.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Assigned Tickets -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">My Assigned Tickets</h3>
      <div class="space-y-3">
        <div v-for="ticket in data.assigned_tickets" :key="ticket.id" 
             class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition cursor-pointer"
             @click="$router.push(`/tickets/${ticket.id}`)">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center space-x-2">
                <span class="font-medium text-blue-600">{{ ticket.ticket_number }}</span>
                <span :class="getPriorityClass(ticket.priority)" class="badge">
                  {{ ticket.priority }}
                </span>
                <span :class="getStatusClass(ticket.status)" class="badge">
                  {{ ticket.status }}
                </span>
              </div>
              <p class="mt-1 text-sm text-gray-900">{{ ticket.title }}</p>
              <div class="mt-2 flex items-center text-xs text-gray-500 space-x-4">
                <span>📦 {{ ticket.asset?.product?.name }}</span>
                <span>📍 {{ ticket.location?.name }}</span>
                <span>👤 {{ ticket.reporter?.name }}</span>
              </div>
            </div>
            <div class="text-xs text-gray-500">
              {{ formatDate(ticket.created_at) }}
            </div>
          </div>
        </div>

        <div v-if="data.assigned_tickets.length === 0" class="text-center py-8 text-gray-500">
          No tickets assigned to you at the moment.
        </div>
      </div>
    </div>

    <!-- Today's Maintenance -->
    <div v-if="data.today_maintenance.length > 0" class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Scheduled Maintenance</h3>
      <div class="space-y-3">
        <div v-for="ticket in data.today_maintenance" :key="ticket.id"
             class="border border-blue-200 bg-blue-50 rounded-lg p-4">
          <div class="flex items-center space-x-2">
            <span class="font-medium text-blue-900">{{ ticket.ticket_number }}</span>
            <span class="badge bg-blue-100 text-blue-800">MAINTENANCE</span>
          </div>
          <p class="mt-1 text-sm text-gray-900">{{ ticket.title }}</p>
          <p class="mt-1 text-xs text-gray-600">📦 {{ ticket.asset?.product?.name }} - 📍 {{ ticket.location?.name }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import StatCard from './StatCard.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

defineProps({
  data: {
    type: Object,
    required: true
  }
})

function getPriorityClass(priority) {
  return {
    'HIGH': 'bg-red-100 text-red-800',
    'NORMAL': 'bg-yellow-100 text-yellow-800'
  }[priority] || 'bg-gray-100 text-gray-800'
}

function getStatusClass(status) {
  return {
    'OPEN': 'bg-blue-100 text-blue-800',
    'ASSIGNED': 'bg-cyan-100 text-cyan-800',
    'IN_PROGRESS': 'bg-purple-100 text-purple-800',
    'ON_HOLD': 'bg-orange-100 text-orange-800',
    'RESOLVED': 'bg-green-100 text-green-800'
  }[status] || 'bg-gray-100 text-gray-800'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}
</script>
