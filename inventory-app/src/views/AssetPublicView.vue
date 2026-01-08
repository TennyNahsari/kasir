<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-blue-600 text-white p-6 shadow-lg">
      <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-2">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <h1 class="text-2xl font-bold">Asset Information</h1>
        </div>
        <p class="text-blue-100 text-sm">Scanned from QR Code</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-2xl mx-auto p-6">
      <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-4">Loading asset information...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-2xl mx-auto p-6">
      <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-semibold text-red-800 mb-2">Asset Not Found</h3>
        <p class="text-red-600">{{ error }}</p>
      </div>
    </div>

    <!-- Asset Content -->
    <div v-else-if="asset.id" class="max-w-2xl mx-auto p-4 pb-8">
      <!-- Asset Tag Card -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-4">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm mb-1">Asset Tag</p>
              <h2 class="text-3xl font-bold font-mono">{{ asset.asset_tag }}</h2>
            </div>
            <div class="text-right">
              <span :class="getStatusBadgeClass(asset.status)" class="inline-block px-3 py-1 rounded-full text-xs font-semibold">
                {{ asset.status }}
              </span>
              <span :class="getConditionBadgeClass(asset.condition)" class="inline-block px-3 py-1 rounded-full text-xs font-semibold mt-2">
                {{ asset.condition }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <h3 class="text-xl font-bold text-gray-800 mb-2">{{ asset.product?.name }}</h3>
          <p v-if="asset.serial_number" class="text-gray-600 text-sm font-mono">S/N: {{ asset.serial_number }}</p>
        </div>
      </div>

      <!-- Details Card -->
      <div class="bg-white rounded-lg shadow-lg p-6 mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Details
        </h3>
        
        <div class="space-y-4">
          <div class="flex items-start">
            <div class="flex-shrink-0 w-32 text-sm text-gray-600 font-medium">Location</div>
            <div class="flex-1 text-gray-900">{{ asset.location?.name || '-' }}</div>
          </div>
          
          <div class="flex items-start">
            <div class="flex-shrink-0 w-32 text-sm text-gray-600 font-medium">PIC</div>
            <div class="flex-1 text-gray-900">{{ asset.pic || 'Not Assigned' }}</div>
          </div>
          
          <div class="flex items-start">
            <div class="flex-shrink-0 w-32 text-sm text-gray-600 font-medium">Purchase Date</div>
            <div class="flex-1 text-gray-900">{{ formatDate(asset.purchase_date) }}</div>
          </div>
          
          <div v-if="asset.warranty_until" class="flex items-start">
            <div class="flex-shrink-0 w-32 text-sm text-gray-600 font-medium">Warranty</div>
            <div class="flex-1">
              <span :class="isUnderWarranty ? 'text-green-600' : 'text-red-600'" class="font-medium">
                {{ formatDate(asset.warranty_until) }}
              </span>
              <span v-if="isUnderWarranty" class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
              <span v-else class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Expired</span>
            </div>
          </div>
          
          <div v-if="asset.assigned_date" class="flex items-start">
            <div class="flex-shrink-0 w-32 text-sm text-gray-600 font-medium">Assigned Date</div>
            <div class="flex-1 text-gray-900">{{ formatDate(asset.assigned_date) }}</div>
          </div>
          
          <div v-if="asset.notes" class="flex items-start">
            <div class="flex-shrink-0 w-32 text-sm text-gray-600 font-medium">Notes</div>
            <div class="flex-1 text-gray-900">{{ asset.notes }}</div>
          </div>
        </div>
      </div>

      <!-- History Card -->
      <div v-if="history.length > 0" class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          History
        </h3>
        
        <div class="space-y-3">
          <div v-for="(item, index) in history" :key="index" class="flex gap-3 pb-3 border-b last:border-b-0">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ item.action }}</p>
              <p class="text-xs text-gray-600 mt-1">{{ formatDateTime(item.created_at) }}</p>
              <p v-if="item.notes" class="text-xs text-gray-500 mt-1">{{ item.notes }}</p>
              <p class="text-xs text-gray-500 mt-1">By: {{ item.user?.name || 'System' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-6 text-center text-sm text-gray-500">
        <p>Last updated: {{ formatDateTime(asset.updated_at) }}</p>
        <p class="mt-2">Powered by Asset Management System</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const asset = ref({})
const history = ref([])
const loading = ref(true)
const error = ref(null)

const isUnderWarranty = computed(() => {
  if (!asset.value.warranty_until) return false
  return new Date(asset.value.warranty_until) > new Date()
})

const loadAsset = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await api.get(`/public/assets/${route.params.id}`)
    asset.value = response.data
    
    // Load history
    try {
      const historyResponse = await api.get(`/public/assets/${route.params.id}/history`)
      history.value = historyResponse.data
    } catch (err) {
      console.log('History not available')
    }
  } catch (err) {
    console.error('Failed to load asset:', err)
    error.value = err.response?.data?.message || 'Failed to load asset information. This asset may not exist or the QR code is invalid.'
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (value) => {
  if (!value) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'AVAILABLE': 'bg-green-500 text-white',
    'ASSIGNED': 'bg-blue-500 text-white',
    'IN_USE': 'bg-indigo-500 text-white',
    'MAINTENANCE': 'bg-yellow-500 text-white',
    'DAMAGED': 'bg-red-500 text-white',
    'DISPOSED': 'bg-gray-500 text-white'
  }
  return classes[status] || 'bg-gray-500 text-white'
}

const getConditionBadgeClass = (condition) => {
  const classes = {
    'NEW': 'bg-green-500 text-white',
    'GOOD': 'bg-blue-500 text-white',
    'FAIR': 'bg-yellow-500 text-white',
    'POOR': 'bg-orange-500 text-white',
    'BROKEN': 'bg-red-500 text-white'
  }
  return classes[condition] || 'bg-gray-500 text-white'
}

onMounted(() => {
  loadAsset()
})
</script>

<style scoped>
/* Mobile-optimized styles */
@media (max-width: 640px) {
  .flex-shrink-0.w-32 {
    width: 100px;
  }
}
</style>
