<template>
  <div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <StatCard
        title="Open"
        :value="data.ticket_stats.open"
        icon="🎫"
        color="blue"
      />
      <StatCard
        title="In Progress"
        :value="data.ticket_stats.in_progress"
        icon="⚙️"
        color="purple"
      />
      <StatCard
        title="Resolved"
        :value="data.ticket_stats.resolved"
        icon="✅"
        color="green"
      />
      <StatCard
        title="Closed"
        :value="data.ticket_stats.closed"
        icon="📦"
        color="gray"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- My Tickets -->
      <div class="card">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">My Tickets</h3>
          <router-link to="/tickets?my_tickets=true" class="text-sm text-blue-600 hover:text-blue-700">
            View All →
          </router-link>
        </div>
        <div class="space-y-3">
          <div v-for="ticket in data.my_tickets.slice(0, 5)" :key="ticket.id"
               class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition cursor-pointer"
               @click="$router.push(`/tickets/${ticket.id}`)">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-blue-600">{{ ticket.ticket_number }}</span>
                <span :class="getStatusClass(ticket.status)" class="badge">
                  {{ ticket.status }}
                </span>
              </div>
              <span :class="getPriorityClass(ticket.priority)" class="badge">
                {{ ticket.priority }}
              </span>
            </div>
            <p class="mt-1 text-sm text-gray-900 truncate">{{ ticket.title }}</p>
            <div class="mt-1 text-xs text-gray-500">
              📦 {{ ticket.asset?.product?.name }}
            </div>
          </div>

          <div v-if="!data.my_tickets || data.my_tickets.length === 0" 
               class="text-center py-8 text-gray-500">
            No tickets found.
          </div>
        </div>
      </div>

      <!-- My Assets -->
      <div class="card">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">My Assets</h3>
          <router-link to="/my-assets" class="text-sm text-blue-600 hover:text-blue-700">
            View All →
          </router-link>
        </div>
        <div class="grid grid-cols-1 gap-3">
          <div v-for="asset in data.my_assets.slice(0, 5)" :key="asset.id"
               class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ asset.product?.name }}</p>
                <p class="text-xs text-gray-500">{{ asset.asset_tag }}</p>
              </div>
              <div class="text-right">
                <span :class="getAssetStatusClass(asset.status)" class="badge text-xs">
                  {{ asset.status }}
                </span>
                <p class="text-xs text-gray-500 mt-1">{{ asset.location?.name }}</p>
              </div>
            </div>
          </div>

          <div v-if="!data.my_assets || data.my_assets.length === 0"
               class="text-center py-8 text-gray-500">
            No assets assigned to you.
          </div>
        </div>

        <div class="mt-4">
          <router-link to="/tickets/create" class="btn btn-primary w-full">
            + Report Issue
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import StatCard from './StatCard.vue'

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

function getAssetStatusClass(status) {
  return {
    'AVAILABLE': 'bg-green-100 text-green-800',
    'ASSIGNED': 'bg-blue-100 text-blue-800',
    'IN_USE': 'bg-purple-100 text-purple-800',
    'MAINTENANCE': 'bg-orange-100 text-orange-800',
    'DAMAGED': 'bg-red-100 text-red-800'
  }[status] || 'bg-gray-100 text-gray-800'
}
</script>
