<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="$emit('close')">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Dispose Asset</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
          <p class="text-sm text-red-800">
            <strong>{{ asset.product?.name }}</strong> ({{ asset.asset_code }})
          </p>
          <p class="text-xs text-gray-600 mt-1">
            Location: {{ asset.location?.name }}
          </p>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
              <div class="mt-2 text-sm text-yellow-700">
                <p>This action will permanently mark this asset as <strong>DISPOSED</strong>. This cannot be undone.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Disposal Reason -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Reason for Disposal <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.disposal_reason"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select Reason</option>
            <option value="BROKEN">Broken / Beyond Repair</option>
            <option value="OBSOLETE">Obsolete / Outdated</option>
            <option value="LOST">Lost</option>
            <option value="STOLEN">Stolen</option>
            <option value="SOLD">Sold</option>
            <option value="DONATED">Donated</option>
            <option value="OTHER">Other</option>
          </select>
        </div>

        <!-- Disposal Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Disposal Date <span class="text-red-500">*</span>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Notes <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            required
            placeholder="Provide details about the disposal..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Confirmation Checkbox -->
        <div class="flex items-start">
          <input
            v-model="form.confirmed"
            type="checkbox"
            required
            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1"
          />
          <label class="ml-2 block text-sm text-gray-700">
            I confirm that I want to dispose this asset. I understand this action is permanent.
          </label>
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
            :disabled="submitting || !form.confirmed"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:opacity-50"
          >
            {{ submitting ? 'Disposing...' : 'Dispose Asset' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import assetService from '@/services/assetService'

const props = defineProps({
  asset: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'disposed'])

const form = ref({
  disposal_reason: '',
  movement_date: new Date().toISOString().split('T')[0],
  notes: '',
  confirmed: false
})

const submitting = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    const payload = {
      movement_date: form.value.movement_date,
      notes: `[${form.value.disposal_reason}] ${form.value.notes}`
    }

    await assetService.disposeAsset(props.asset.id, payload)
    emit('disposed')
  } catch (err) {
    console.error('Error disposing asset:', err)
    error.value = err.response?.data?.message || 'Failed to dispose asset'
  } finally {
    submitting.value = false
  }
}
</script>
