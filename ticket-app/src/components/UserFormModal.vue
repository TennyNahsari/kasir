<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="$emit('close')">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white mb-10">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditMode ? 'Edit User' : 'Add New User' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.name }"
          />
          <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.email }"
          />
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Password 
            <span v-if="!isEditMode" class="text-red-500">*</span>
            <span v-else class="text-gray-500 text-xs">(leave blank to keep current)</span>
          </label>
          <input
            v-model="form.password"
            type="password"
            :required="!isEditMode"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.password }"
          />
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
        </div>

        <!-- Confirm Password -->
        <div v-if="form.password">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Confirm Password <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.password_confirmation"
            type="password"
            :required="!!form.password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.password_confirmation }"
          />
          <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</p>
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Role <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.role"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.role }"
          >
            <option value="">Select Role</option>
            <option value="owner">Owner</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
          </select>
          <p v-if="errors.role" class="text-red-500 text-xs mt-1">{{ errors.role }}</p>
        </div>

        <!-- Location -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Location
          </label>
          <div class="relative">
            <input
              v-model="locationSearch"
              @focus="showLocationDropdown = true"
              @input="showLocationDropdown = true"
              @blur="handleLocationBlur"
              type="text"
              placeholder="Search location..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.location_id }"
            />
            <div v-if="showLocationDropdown && filteredLocations.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
              <button
                v-for="location in filteredLocations"
                :key="location.id"
                type="button"
                @click="selectLocation(location)"
                class="w-full px-3 py-2 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none border-b border-gray-100 last:border-b-0"
              >
                <div class="text-sm font-medium text-gray-900">{{ location.name }}</div>
                <div v-if="location.code" class="text-xs text-gray-500">Code: {{ location.code }}</div>
              </button>
            </div>
            <div v-else-if="showLocationDropdown && locations.length === 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-3">
              <p class="text-sm text-gray-500">No locations available</p>
            </div>
            <div v-else-if="showLocationDropdown && filteredLocations.length === 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-3">
              <p class="text-sm text-gray-500">No locations found</p>
            </div>
            <div v-if="selectedLocation" class="mt-2 inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
              <span>{{ selectedLocation.name }}</span>
              <button type="button" @click="clearLocation" class="ml-2 text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          <p v-if="errors.location_id" class="text-red-500 text-xs mt-1">{{ errors.location_id }}</p>
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

        <!-- Is Technician -->
        <div class="flex items-center">
          <input
            v-model="form.is_technician"
            type="checkbox"
            id="is_technician"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="is_technician" class="ml-2 block text-sm text-gray-900">
            Is Technician
            <span class="text-xs text-gray-500 ml-1">(can be assigned to tickets)</span>
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
import { ref, reactive, computed, onMounted } from 'vue'
import userService from '@/services/userService'
import locationService from '@/services/locationService'

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const isEditMode = computed(() => !!props.user)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  location_id: '',
  is_active: true,
  is_technician: false
})

const errors = ref({})
const generalError = ref(null)
const submitting = ref(false)
const locations = ref([])

// Location autocomplete
const locationSearch = ref('')
const showLocationDropdown = ref(false)
const selectedLocation = ref(null)

const filteredLocations = computed(() => {
  const search = locationSearch.value?.toLowerCase() || ''
  let result = []
  
  if (!search) {
    result = locations.value.slice(0, 10)
  } else {
    result = locations.value.filter(location => {
      const name = location.name?.toLowerCase() || ''
      const code = location.code?.toLowerCase() || ''
      return name.includes(search) || code.includes(search)
    }).slice(0, 10)
  }
  
  console.log('Search:', search, 'Results:', result.length, 'Show dropdown:', showLocationDropdown.value)
  return result
})

const selectLocation = (location) => {
  selectedLocation.value = location
  form.location_id = location.id
  locationSearch.value = ''
  showLocationDropdown.value = false
}

const clearLocation = () => {
  selectedLocation.value = null
  form.location_id = ''
  locationSearch.value = ''
}

const handleLocationBlur = () => {
  setTimeout(() => {
    showLocationDropdown.value = false
  }, 200)
}

// Populate form if editing
if (props.user) {
  form.name = props.user.name || ''
  form.email = props.user.email || ''
  form.role = props.user.role || ''
  form.location_id = props.user.location_id || ''
  form.is_active = props.user.is_active !== undefined ? props.user.is_active : true
  form.is_technician = props.user.is_technician !== undefined ? props.user.is_technician : false
}

const loadLocations = async () => {
  try {
    const response = await locationService.getLocations()
    locations.value = response.data || response
    
    console.log('Loaded locations:', locations.value)
    
    // Set selected location if editing and location_id exists
    if (props.user && form.location_id) {
      const location = locations.value.find(l => l.id === form.location_id)
      if (location) {
        selectedLocation.value = location
      }
    }
  } catch (err) {
    console.error('Error loading locations:', err)
  }
}

const validateForm = () => {
  errors.value = {}
  let isValid = true

  if (!form.name.trim()) {
    errors.value.name = 'Name is required'
    isValid = false
  }

  if (!form.email.trim()) {
    errors.value.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Invalid email format'
    isValid = false
  }

  if (!isEditMode.value && !form.password) {
    errors.value.password = 'Password is required'
    isValid = false
  }

  if (form.password) {
    if (form.password.length < 6) {
      errors.value.password = 'Password must be at least 6 characters'
      isValid = false
    }

    if (form.password !== form.password_confirmation) {
      errors.value.password_confirmation = 'Passwords do not match'
      isValid = false
    }
  }

  if (!form.role) {
    errors.value.role = 'Role is required'
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
      name: form.name,
      email: form.email,
      role: form.role,
      is_active: form.is_active ? 1 : 0,
      is_technician: form.is_technician ? 1 : 0
    }

    if (form.location_id) {
      payload.location_id = form.location_id
    }

    if (form.password) {
      payload.password = form.password
      payload.password_confirmation = form.password_confirmation
    }

    if (isEditMode.value) {
      await userService.updateUser(props.user.id, payload)
    } else {
      await userService.createUser(payload)
    }

    emit('saved')
  } catch (err) {
    console.error('Error saving user:', err)
    
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
    
    generalError.value = err.response?.data?.message || 'Failed to save user'
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await loadLocations()
})
</script>
