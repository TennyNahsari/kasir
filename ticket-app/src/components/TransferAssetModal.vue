<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="$emit('close')">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Transfer Asset</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
          <p class="text-sm text-blue-800">
            <strong>{{ asset.product?.name }}</strong> ({{ asset.asset_code }})
          </p>
          <p class="text-xs text-gray-600 mt-1">
            Current PIC: {{ asset.pic || '-' }} | Location: {{ asset.location?.name }}
          </p>
        </div>

        <!-- New Person in Charge -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Transfer To (New PIC) <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.pic"
            type="text"
            required
            placeholder="Enter person name"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- New Location -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            New Location <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.to_location_id"
            required
            :disabled="loadingLocations"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select Location</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>

        <!-- Movement Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Transfer Date <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.movement_date"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Reason for transfer..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-3">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ submitting ? 'Transferring...' : 'Transfer Asset' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import assetService from '@/services/assetService'
import axios from '@/utils/axios'

const props = defineProps({
  asset: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'transferred'])

const form = ref({
  pic: '',
  to_location_id: '',
  movement_date: new Date().toISOString().split('T')[0],
  notes: ''
})

const locations = ref([])
const loadingLocations = ref(false)
const submitting = ref(false)
const error = ref(null)

const loadLocations = async () => {
  try {
    loadingLocations.value = true
    const response = await axios.get('/api/locations')
    locations.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading locations:', err)
  } finally {
    loadingLocations.value = false
  }
}

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    await assetService.transferAsset(props.asset.id, form.value)
    emit('transferred')
  } catch (err) {
    console.error('Error transferring asset:', err)
    error.value = err.response?.data?.message || 'Failed to transfer asset'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadLocations()
})
</script>
