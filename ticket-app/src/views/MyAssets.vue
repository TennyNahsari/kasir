<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900">My Assets</h1>
        <p class="text-sm text-gray-600 mt-1">Assets assigned to you or in your location</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <p class="text-red-800">{{ error }}</p>
        <button @click="loadMyAssets" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
          Try again
        </button>
      </div>

      <!-- Assets Grid -->
      <div v-else-if="assets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="asset in assets"
          :key="asset.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
          @click="viewAsset(asset.id)"
        >
          <div class="p-6">
            <!-- Status Badge -->
            <div class="flex justify-between items-start mb-3">
              <span :class="getAssetStatusClass(asset.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ asset.status }}
              </span>
              <span :class="getConditionClass(asset.condition)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ asset.condition }}
              </span>
            </div>

            <!-- Asset Info -->
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ asset.product?.name }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ asset.asset_code }}</p>

            <!-- Details -->
            <div class="space-y-2 text-sm">
              <div class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ asset.location?.name }}
              </div>
              
              <div v-if="asset.pic" class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ asset.pic }}
              </div>

              <div class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ formatCurrency(asset.purchase_price) }}
              </div>

              <div class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(asset.purchase_date) }}
              </div>
            </div>

            <!-- View Button -->
            <button
              @click.stop="viewAsset(asset.id)"
              class="mt-4 w-full px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
            >
              View Details
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No assets assigned</h3>
        <p class="mt-1 text-sm text-gray-500">You don't have any assets assigned to you yet.</p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import assetService from '@/services/assetService'
import {
  getAssetStatusClass,
  getConditionClass,
  formatCurrency,
  formatDate
} from '@/utils/assetHelpers'

const router = useRouter()

const assets = ref([])
const loading = ref(false)
const error = ref(null)

const loadMyAssets = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await assetService.getMyAssets()
    assets.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading my assets:', err)
    error.value = err.response?.data?.message || 'Failed to load your assets'
  } finally {
    loading.value = false
  }
}

const viewAsset = (id) => {
  router.push(`/assets/${id}`)
}

onMounted(() => {
  loadMyAssets()
})
</script>
