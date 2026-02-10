<template>
  <div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <StatCard
        title="Total Tickets"
        :value="data.ticket_stats.total"
        icon="📊"
        color="blue"
      />
      <StatCard
        title="Open Tickets"
        :value="data.ticket_stats.open"
        icon="🎫"
        color="yellow"
      />
      <StatCard
        title="In Progress"
        :value="data.ticket_stats.in_progress"
        icon="⚙️"
        color="purple"
      />
      <StatCard
        title="High Priority"
        :value="data.ticket_stats.high_priority"
        icon="🔴"
        color="red"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Tickets by Status -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tickets by Status</h3>
        <div class="space-y-3">
          <StatusBar label="Open" :value="data.tickets_by_status.OPEN || 0" color="bg-yellow-500" />
          <StatusBar label="Assigned" :value="data.tickets_by_status.ASSIGNED || 0" color="bg-cyan-500" />
          <StatusBar label="In Progress" :value="data.tickets_by_status.IN_PROGRESS || 0" color="bg-purple-500" />
          <StatusBar label="Resolved" :value="data.tickets_by_status.RESOLVED || 0" color="bg-green-500" />
          <StatusBar label="Closed" :value="data.tickets_by_status.CLOSED || 0" color="bg-gray-500" />
        </div>
      </div>

      <!-- Asset Statistics -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Asset Overview</h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ data.asset_stats.total }}</div>
            <div class="text-sm text-gray-600">Total Assets</div>
          </div>
          <div class="text-center p-4 bg-green-50 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ data.asset_stats.available }}</div>
            <div class="text-sm text-gray-600">Available</div>
          </div>
          <div class="text-center p-4 bg-purple-50 rounded-lg">
            <div class="text-2xl font-bold text-purple-600">{{ data.asset_stats.in_use }}</div>
            <div class="text-sm text-gray-600">In Use</div>
          </div>
          <div class="text-center p-4 bg-orange-50 rounded-lg">
            <div class="text-2xl font-bold text-orange-600">{{ data.asset_stats.maintenance }}</div>
            <div class="text-sm text-gray-600">Maintenance</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Tickets -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Tickets</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="ticket in data.recent_tickets" :key="ticket.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                <router-link :to="`/tickets/${ticket.id}`">{{ ticket.ticket_number }}</router-link>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ ticket.title }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ ticket.asset?.product?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getPriorityClass(ticket.priority)" class="badge">
                  {{ ticket.priority }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(ticket.status)" class="badge">
                  {{ ticket.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ ticket.assigned_user?.name || 'Unassigned' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import StatCard from './StatCard.vue'
import StatusBar from './StatusBar.vue'

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
    'RESOLVED': 'bg-green-100 text-green-800',
    'CLOSED': 'bg-gray-100 text-gray-800'
  }[status] || 'bg-gray-100 text-gray-800'
}
</script>
