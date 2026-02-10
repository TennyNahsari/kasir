<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="$emit('close')">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white mb-10">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditMode ? 'Edit Location' : 'Add New Location' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Code <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.code"
            type="text"
            required
            maxlength="50"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.code }"
          />
          <p v-if="errors.code" class="text-red-500 text-xs mt-1">{{ errors.code }}</p>
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            maxlength="255"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.name }"
          />
          <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
        </div>

        <!-- Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Type <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.type"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.type }"
          >
            <option value="">Select Type</option>
            <option value="WAREHOUSE">Warehouse</option>
            <option value="OUTLET">Outlet</option>
            <option value="FNB">FNB (Food & Beverage)</option>
            <option value="DEPARTMENT">Department</option>
          </select>
          <p v-if="errors.type" class="text-red-500 text-xs mt-1">{{ errors.type }}</p>
        </div>

        <!-- Address -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Address
          </label>
          <textarea
            v-model="form.address"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.address }"
          ></textarea>
          <p v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address }}</p>
        </div>

        <!-- Person in Charge -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Person in Charge
          </label>
          <input
            v-model="form.person_in_charge"
            type="text"
            maxlength="255"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.person_in_charge }"
          />
          <p v-if="errors.person_in_charge" class="text-red-500 text-xs mt-1">{{ errors.person_in_charge }}</p>
        </div>

        <!-- Phone -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Phone
          </label>
          <input
            v-model="form.phone"
            type="text"
            maxlength="50"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.phone }"
          />
          <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
        </div>

        <!-- Status -->
        <div class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="is_active"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="is_active" class="ml-2 block text-sm text-gray-900">
            Active
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="generalError" class="bg-red-50 border border-red-200 rounded-md p-3">
          <p class="text-sm text-red-800">{{ generalError }}</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            :disabled="submitting"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
          >
            {{ submitting ? 'Saving...' : (isEditMode ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import locationService from '@/services/locationService'

const props = defineProps({
  location: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const isEditMode = computed(() => !!props.location)

const form = reactive({
  code: '',
  name: '',
  type: '',
  address: '',
  person_in_charge: '',
  phone: '',
  is_active: true
})

const errors = ref({})
const generalError = ref(null)
const submitting = ref(false)

// Populate form if editing
if (props.location) {
  form.code = props.location.code || ''
  form.name = props.location.name || ''
  form.type = props.location.type || ''
  form.address = props.location.address || ''
  form.person_in_charge = props.location.person_in_charge || ''
  form.phone = props.location.phone || ''
  form.is_active = props.location.is_active !== undefined ? props.location.is_active : true
}

const validateForm = () => {
  errors.value = {}
  let isValid = true

  if (!form.code.trim()) {
    errors.value.code = 'Code is required'
    isValid = false
  }

  if (!form.name.trim()) {
    errors.value.name = 'Name is required'
    isValid = false
  }

  if (!form.type) {
    errors.value.type = 'Type is required'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  try {
    submitting.value = true
    generalError.value = null

    const payload = {
      code: form.code,
      name: form.name,
      type: form.type,
      address: form.address || null,
      person_in_charge: form.person_in_charge || null,
      phone: form.phone || null,
      is_active: form.is_active
    }

    if (isEditMode.value) {
      await locationService.updateLocation(props.location.id, payload)
    } else {
      await locationService.createLocation(payload)
    }

    emit('saved')
  } catch (err) {
    console.error('Error saving location:', err)
    
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
    
    generalError.value = err.response?.data?.message || 'Failed to save location'
  } finally {
    submitting.value = false
  }
}
</script>
