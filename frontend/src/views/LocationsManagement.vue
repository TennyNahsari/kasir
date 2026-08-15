<template>
  <div class="p-4 sm:p-6">
    <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ $t('locations.title') }}</h1>
        <p class="text-sm sm:text-base text-gray-600">{{ $t('locations.subtitle') }}</p>
      </div>
      <button @click="showCreateModal = true" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center" :disabled="loading">
        <span class="text-xl mr-1">+</span>
        {{ $t('locations.addLocation') }}
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="mb-4 sm:mb-6 bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-red-800">{{ $t('common.error') }}</h3>
          <p class="text-sm text-red-700 mt-1">{{ errorMessage }}</p>
        </div>
        <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-6 sm:p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-4 border-gray-200 border-t-blue-600"></div>
      <p class="text-sm sm:text-base text-gray-500 mt-4">{{ $t('locations.loadingLocations') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="locations.length === 0" class="bg-white rounded-lg shadow p-6 sm:p-12 text-center">
      <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <p class="text-sm sm:text-base text-gray-500 mb-2">{{ $t('locations.noLocations') }}</p>
      <p class="text-xs sm:text-sm text-gray-400 mb-4">{{ $t('locations.noLocationsHint') }}</p>
      <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
        <span class="text-xl mr-2">+</span>
        {{ $t('locations.addLocation') }}
      </button>
    </div>

    <!-- Locations Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <div v-for="location in locations" :key="location.id" class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex justify-between items-start mb-3 sm:mb-4">
          <div class="flex-1 min-w-0 mr-2">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">{{ location.name }}</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 flex items-center gap-1.5 flex-wrap">
              <span class="font-mono bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-xs border border-gray-200 font-medium">ID: {{ location.id }}</span>
              <span>•</span>
              <span>{{ location.code }}</span>
            </p>
            <span :class="getTypeClass(location.type)" class="inline-block px-2 py-1 text-xs font-semibold rounded-full mt-1.5">
              {{ location.type }}
            </span>
          </div>
          <div class="flex gap-2 flex-shrink-0">
            <button @click="editLocation(location)" class="text-blue-600 hover:text-blue-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button @click="deleteLocation(location)" class="text-red-600 hover:text-red-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="space-y-2 text-xs sm:text-sm text-gray-600">
          <div v-if="location.address">
            <p>{{ location.address }}</p>
          </div>
          <div v-if="location.phone" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            {{ location.phone }}
          </div>
          <div v-if="location.person_in_charge" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            {{ location.person_in_charge }}
          </div>
          <div>
            <span :class="location.is_active ? 'text-green-600' : 'text-red-600'" class="font-semibold">
              {{ location.is_active ? '● ' + $t('locations.status.active') : '● ' + $t('locations.status.inactive') }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <Pagination
        v-if="pagination.total > 0"
        :current-page="pagination.currentPage"
        :last-page="pagination.lastPage"
        :per-page="pagination.perPage"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @update:current-page="handlePageChange"
        @update:per-page="handlePerPageChange"
      />
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-base sm:text-lg font-semibold mb-4">{{ editingLocation ? $t('locations.modals.edit') : $t('locations.modals.add') }} {{ $t('locations.location') }}</h3>
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('locations.code') }} *</label>
            <input v-model="locationForm.code" type="text" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('locations.codePlaceholder')" required :disabled="loading">
            <p class="text-xs text-gray-500 mt-1">{{ $t('locations.codeHint') }}</p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('locations.name') }} *</label>
            <input v-model="locationForm.name" type="text" class="w-full border-gray-300 rounded-lg text-sm" required :disabled="loading">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('locations.type') }} *</label>
            <select v-model="locationForm.type" class="w-full border-gray-300 rounded-lg text-sm" required :disabled="loading">
              <option value="INVENTORY">{{ $t('locations.types.inventory') }}</option>
              <option value="OUTLET">{{ $t('locations.types.outlet') }}</option>
              <option value="FNB">{{ $t('locations.types.fnb') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('locations.address') }}</label>
            <textarea v-model="locationForm.address" rows="3" class="w-full border-gray-300 rounded-lg text-sm" :disabled="loading"></textarea>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('locations.phone') }}</label>
            <input v-model="locationForm.phone" type="text" class="w-full border-gray-300 rounded-lg text-sm" :disabled="loading">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('locations.personInCharge') }}</label>
            <input v-model="locationForm.person_in_charge" type="text" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('locations.personInChargePlaceholder')" :disabled="loading">
          </div>
          <div class="flex items-center">
            <input v-model="locationForm.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 mr-2" :disabled="loading">
            <label class="text-xs sm:text-sm font-medium text-gray-700">{{ $t('locations.status.active') }}</label>
          </div>
        </div>
        <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
          <button @click="closeModal" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm" :disabled="loading">{{ $t('common.cancel') }}</button>
          <button @click="saveLocation" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 text-sm" :disabled="loading">
            {{ loading ? $t('common.saving') : $t('common.save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'

const { t } = useI18n()
const locations = ref([])
const showCreateModal = ref(false)
const editingLocation = ref(null)
const loading = ref(false)
const errorMessage = ref('')

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 25,
  total: 0,
  from: 0,
  to: 0
})

const locationForm = ref({
  code: '',
  name: '',
  type: 'OUTLET',
  address: '',
  phone: '',
  person_in_charge: '',
  is_active: true
})

onMounted(async () => {
  await loadLocations()
})

const loadLocations = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    const { data } = await api.get('/locations', {
      params: { 
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage
      }
    })
    
    // Handle both paginated and non-paginated responses
    if (data.data && data.meta) {
      // Paginated response
      // Filter only INVENTORY, OUTLET, and FNB types
      locations.value = (data.data || []).filter(loc => 
        ['INVENTORY', 'WAREHOUSE', 'OUTLET', 'FNB'].includes(loc.type?.toUpperCase())
      )
      pagination.value = {
        currentPage: data.meta.current_page,
        lastPage: data.meta.last_page,
        perPage: data.meta.per_page,
        total: data.meta.total,
        from: data.meta.from || 0,
        to: data.meta.to || 0
      }
    } else {
      // Non-paginated response (array) or paginated without meta
      const allLocations = data.data || data
      
      if (!Array.isArray(allLocations)) {
        console.error('Locations data is not an array:', allLocations)
        locations.value = []
      } else {
        // Filter only INVENTORY, OUTLET, and FNB types
        locations.value = allLocations.filter(loc => 
          ['INVENTORY', 'WAREHOUSE', 'OUTLET', 'FNB'].includes(loc.type?.toUpperCase())
        )
      }
      
      // Set default pagination for non-paginated response
      pagination.value = {
        currentPage: 1,
        lastPage: 1,
        perPage: locations.value.length,
        total: locations.value.length,
        from: locations.value.length > 0 ? 1 : 0,
        to: locations.value.length
      }
    }
    
    console.log('Loaded locations:', locations.value)
  } catch (error) {
    console.error('Failed to load locations:', error)
    errorMessage.value = t('locations.alerts.loadError') + ': ' + (error.response?.data?.message || error.message)
    locations.value = []
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadLocations()
}

const handlePerPageChange = (perPage) => {
  pagination.value.perPage = perPage
  pagination.value.currentPage = 1
  loadLocations()
}

const getTypeClass = (type) => {
  if (type === 'WAREHOUSE' || type === 'INVENTORY') return 'bg-blue-100 text-blue-800'
  if (type === 'OUTLET') return 'bg-green-100 text-green-800'
  if (type === 'FNB') return 'bg-orange-100 text-orange-800'
  return 'bg-gray-100 text-gray-800'
}

const editLocation = (location) => {
  editingLocation.value = location
  locationForm.value = {
    code: location.code,
    name: location.name,
    type: location.type,
    address: location.address || '',
    phone: location.phone || '',
    person_in_charge: location.person_in_charge || '',
    is_active: location.is_active
  }
  showCreateModal.value = true
}

const saveLocation = async () => {
  try {
    if (!locationForm.value.code || !locationForm.value.name || !locationForm.value.type) {
      errorMessage.value = t('locations.alerts.fillRequired')
      return
    }

    loading.value = true
    errorMessage.value = ''

    if (editingLocation.value) {
      await api.put(`/locations/${editingLocation.value.id}`, locationForm.value)
      alert(t('locations.alerts.updateSuccess'))
    } else {
      await api.post('/locations', locationForm.value)
      alert(t('locations.alerts.createSuccess'))
    }

    closeModal()
    await loadLocations()
  } catch (error) {
    console.error('Save location error:', error)
    errorMessage.value = t('locations.alerts.saveError') + ': ' + (error.response?.data?.message || error.message)
    
    // Show errors object if available
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat().join(', ')
      errorMessage.value += ' - ' + errors
    }
  } finally {
    loading.value = false
  }
}

const deleteLocation = async (location) => {
  if (!confirm(t('locations.confirms.delete', { name: location.name }))) return
  
  try {
    loading.value = true
    errorMessage.value = ''
    
    await api.delete(`/locations/${location.id}`)
    alert(t('locations.alerts.deleteSuccess'))
    await loadLocations()
  } catch (error) {
    console.error('Delete location error:', error)
    errorMessage.value = t('locations.alerts.deleteError') + ': ' + (error.response?.data?.message || error.message)
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingLocation.value = null
  locationForm.value = {
    code: '',
    name: '',
    type: 'OUTLET',
    address: '',
    phone: '',
    person_in_charge: '',
    is_active: true
  }
}
</script>
