<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <p class="text-red-800">{{ error }}</p>
        <router-link to="/assets" class="mt-2 inline-block text-sm text-red-600 hover:text-red-800 underline">
          Back to Assets
        </router-link>
      </div>

      <!-- Asset Detail -->
      <template v-else-if="asset">
        <!-- Header -->
        <div class="flex justify-between items-start">
          <div>
            <div class="flex items-center space-x-2">
              <router-link to="/assets" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
              </router-link>
              <h1 class="text-3xl font-bold text-gray-900">{{ asset.product?.name }}</h1>
            </div>
            <p class="text-sm text-gray-600 mt-1">{{ asset.asset_code }}</p>
          </div>
          
          <!-- Actions -->
          <div class="flex space-x-2">
            <button
              v-if="canEditAsset && asset.status !== 'DISPOSED'"
              @click="showEditModal = true"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </button>

            <!-- Movement Actions Dropdown -->
            <div v-if="canManageAsset && asset.status !== 'DISPOSED'" class="relative">
              <button
                @click="showActionsMenu = !showActionsMenu"
                class="inline-flex items-center px-3 py-2 border border-blue-600 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
              >
                Actions
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="showActionsMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                <button
                  v-if="asset.status === 'AVAILABLE'"
                  @click="openAssignModal"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Assign Asset
                </button>
                <button
                  v-if="asset.status === 'IN_USE'"
                  @click="openTransferModal"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Transfer Asset
                </button>
                <button
                  v-if="asset.status === 'IN_USE'"
                  @click="openReturnModal"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Return Asset
                </button>
                <button
                  @click="openDisposeModal"
                  class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                >
                  Dispose Asset
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Asset Information -->
          <div class="bg-white rounded-lg shadow p-6 col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Asset Information</h2>
            <div class="grid grid-cols-2 gap-y-4">
              <div>
                <p class="text-sm text-gray-500">Asset Code</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.asset_code }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Serial Number</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.serial_number || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Status</p>
                <span :class="getAssetStatusClass(asset.status)" class="inline-block px-2 py-1 text-xs font-semibold rounded-full">
                  {{ asset.status }}
                </span>
              </div>
              <div>
                <p class="text-sm text-gray-500">Condition</p>
                <span :class="getConditionClass(asset.condition)" class="inline-block px-2 py-1 text-xs font-semibold rounded-full">
                  {{ asset.condition }}
                </span>
              </div>
              <div>
                <p class="text-sm text-gray-500">Location</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.location?.name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Person in Charge</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.pic || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Category</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.product?.category?.name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Supplier</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.supplier?.name || '-' }}</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-gray-500">Description</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.description || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Financial Information -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Financial Info</h2>
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-500">Purchase Price</p>
                <p class="text-lg font-bold text-gray-900">{{ formatCurrency(asset.purchase_price) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Purchase Date</p>
                <p class="text-sm font-medium text-gray-900">{{ formatDate(asset.purchase_date) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Useful Life</p>
                <p class="text-sm font-medium text-gray-900">{{ asset.useful_life_years }} years</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Residual Value</p>
                <p class="text-sm font-medium text-gray-900">{{ formatCurrency(asset.residual_value) }}</p>
              </div>
              <div class="pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">Current Value</p>
                <p class="text-lg font-bold text-blue-600">{{ formatCurrency(depreciation.currentValue) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ depreciation.ageYears.toFixed(1) }} years old</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Annual Depreciation</p>
                <p class="text-sm font-medium text-gray-900">{{ formatCurrency(depreciation.annualDepreciation) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Movement History -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Movement History</h2>
          
          <div v-if="loadingHistory" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          </div>

          <div v-else-if="history.length === 0" class="text-center py-8 text-gray-500">
            No movement history yet
          </div>

          <div v-else class="flow-root">
            <ul class="-mb-8">
              <li v-for="(item, idx) in history" :key="item.id">
                <div class="relative pb-8">
                  <span v-if="idx !== history.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                  <div class="relative flex space-x-3">
                    <div>
                      <span :class="getMovementIconClass(item.movement_type)" class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="item.movement_type === 'ASSIGN'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                          <path v-else-if="item.movement_type === 'TRANSFER'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                          <path v-else-if="item.movement_type === 'RETURN'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div>
                        <div class="text-sm">
                          <span class="font-medium text-gray-900">{{ item.movement_type }}</span>
                          <span class="text-gray-500"> by {{ item.user?.name }}</span>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500">{{ formatDateTime(item.movement_date) }}</p>
                      </div>
                      <div class="mt-2 text-sm text-gray-700">
                        <p v-if="item.from_location">From: {{ item.from_location?.name }}</p>
                        <p v-if="item.to_location">To: {{ item.to_location?.name }}</p>
                        <p v-if="item.notes" class="mt-1 text-gray-600">{{ item.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </template>
    </div>

    <!-- Modals -->
    <CreateAssetModal 
      v-if="showEditModal" 
      :asset="asset"
      @close="showEditModal = false"
      @saved="handleAssetUpdated"
    />

    <AssignAssetModal
      v-if="showAssignModal"
      :asset="asset"
      @close="showAssignModal = false"
      @assigned="handleMovement"
    />

    <TransferAssetModal
      v-if="showTransferModal"
      :asset="asset"
      @close="showTransferModal = false"
      @transferred="handleMovement"
    />

    <ReturnAssetModal
      v-if="showReturnModal"
      :asset="asset"
      @close="showReturnModal = false"
      @returned="handleMovement"
    />

    <DisposeAssetModal
      v-if="showDisposeModal"
      :asset="asset"
      @close="showDisposeModal = false"
      @disposed="handleMovement"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'
import CreateAssetModal from '@/components/CreateAssetModal.vue'
import AssignAssetModal from '@/components/AssignAssetModal.vue'
import TransferAssetModal from '@/components/TransferAssetModal.vue'
import ReturnAssetModal from '@/components/ReturnAssetModal.vue'
import DisposeAssetModal from '@/components/DisposeAssetModal.vue'
import assetService from '@/services/assetService'
import {
  getAssetStatusClass,
  getConditionClass,
  formatCurrency,
  formatDate,
  formatDateTime,
  calculateDepreciation
} from '@/utils/assetHelpers'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const asset = ref(null)
const history = ref([])
const loading = ref(false)
const loadingHistory = ref(false)
const error = ref(null)

const showActionsMenu = ref(false)
const showEditModal = ref(false)
const showAssignModal = ref(false)
const showTransferModal = ref(false)
const showReturnModal = ref(false)
const showDisposeModal = ref(false)

const depreciation = computed(() => {
  if (!asset.value) return { currentValue: 0, annualDepreciation: 0, ageYears: 0 }
  return calculateDepreciation(
    asset.value.purchase_price,
    asset.value.residual_value,
    asset.value.useful_life_years,
    asset.value.purchase_date
  )
})

const canEditAsset = computed(() => {
  const role = authStore.user?.role
  return ['owner', 'supervisor', 'admin'].includes(role)
})

const canManageAsset = computed(() => {
  const role = authStore.user?.role
  return ['owner', 'supervisor', 'admin'].includes(role)
})

const getMovementIconClass = (type) => {
  const classes = {
    'ASSIGN': 'bg-green-500',
    'TRANSFER': 'bg-blue-500',
    'RETURN': 'bg-yellow-500',
    'DISPOSE': 'bg-red-500',
    'MAINTENANCE': 'bg-purple-500'
  }
  return classes[type] || 'bg-gray-500'
}

const loadAsset = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await assetService.getAsset(route.params.id)
    asset.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading asset:', err)
    error.value = err.response?.data?.message || 'Failed to load asset details'
  } finally {
    loading.value = false
  }
}

const loadHistory = async () => {
  try {
    loadingHistory.value = true
    const response = await assetService.getAssetHistory(route.params.id)
    history.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading history:', err)
  } finally {
    loadingHistory.value = false
  }
}

const openAssignModal = () => {
  showActionsMenu.value = false
  showAssignModal.value = true
}

const openTransferModal = () => {
  showActionsMenu.value = false
  showTransferModal.value = true
}

const openReturnModal = () => {
  showActionsMenu.value = false
  showReturnModal.value = true
}

const openDisposeModal = () => {
  showActionsMenu.value = false
  showDisposeModal.value = true
}

const handleAssetUpdated = () => {
  showEditModal.value = false
  loadAsset()
}

const handleMovement = () => {
  showAssignModal.value = false
  showTransferModal.value = false
  showReturnModal.value = false
  showDisposeModal.value = false
  loadAsset()
  loadHistory()
}

onMounted(() => {
  loadAsset()
  loadHistory()
})
</script>
