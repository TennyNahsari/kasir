<template>
  <MainLayout>
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="error" class="max-w-2xl mx-auto">
      <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Failed to load dashboard</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error }}</p>
            </div>
            <div class="mt-4">
              <button
                @click="loadDashboard"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                Try Again
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-sm text-gray-600">Welcome back, {{ authStore.user?.name }}!</p>
      </div>

      <!-- Supervisor Dashboard -->
      <SupervisorDashboard v-if="dashboardData?.role === 'supervisor'" :data="dashboardData" />

      <!-- Technician Dashboard -->
      <TechnicianDashboard v-else-if="dashboardData?.role === 'technician'" :data="dashboardData" />

      <!-- User Dashboard -->
      <UserDashboard v-else :data="dashboardData" />
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'
import SupervisorDashboard from '@/components/SupervisorDashboard.vue'
import TechnicianDashboard from '@/components/TechnicianDashboard.vue'
import UserDashboard from '@/components/UserDashboard.vue'
import ticketService from '@/services/ticketService'

const authStore = useAuthStore()
const dashboardData = ref(null)
const loading = ref(true)
const error = ref(null)

const loadDashboard = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await ticketService.getDashboard()
    dashboardData.value = response.data
  } catch (err) {
    console.error('Error loading dashboard:', err)
    error.value = err.response?.data?.message || 'Failed to load dashboard data. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>
