<template>
  <div v-if="showSelector" class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
      <span class="text-primary-600">👤 Owner Mode:</span> Pilih Outlet
    </label>
    <select 
      v-model="selectedLocationId" 
      @change="handleOutletChange"
      class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
    >
      <option value="">-- Pilih Outlet --</option>
      <option v-for="outlet in outlets" :key="outlet.location_id" :value="outlet.location_id">
        {{ outlet.location_name }} ({{ outlet.outlet_name }})
      </option>
    </select>
    <p v-if="!selectedLocationId" class="mt-1 text-xs text-gray-500">
      Silakan pilih outlet untuk melihat data
    </p>
    <p v-if="outlets.length === 0 && !selectedLocationId" class="mt-1 text-xs text-orange-600">
      ⚠️ Tidak ada outlet yang terdaftar di inventory. Silakan daftarkan outlet di aplikasi inventory terlebih dahulu.
    </p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const emit = defineEmits(['outlet-changed'])

const authStore = useAuthStore()
const outlets = ref([])
const selectedLocationId = ref('')

// Show selector only for owner/inventory without outlet_id
const showSelector = computed(() => {
  const role = authStore.user?.role
  return (role === 'owner' || role === 'inventory') && !authStore.user?.outlet_id
})

const handleOutletChange = () => {
  console.log('Location changed to:', selectedLocationId.value, 'Type:', typeof selectedLocationId.value)
  
  // Convert to integer for consistency, handle empty string as null
  const locationId = selectedLocationId.value && selectedLocationId.value !== '' 
    ? parseInt(selectedLocationId.value) 
    : null
  
  // Validate that the selected location exists in outlets list
  if (locationId) {
    const isValid = outlets.value.find(o => o.location_id === locationId)
    if (!isValid) {
      console.error('❌ Invalid location selected:', locationId)
      alert('Location tidak valid. Silakan pilih location lain.')
      selectedLocationId.value = ''
      localStorage.removeItem('owner_selected_location')
      return
    }
    console.log('✅ Valid location selected:', locationId, 'Outlet:', isValid.outlet_name)
  }
  
  // Save to localStorage for persistence
  if (locationId) {
    localStorage.setItem('owner_selected_location', locationId)
  } else {
    localStorage.removeItem('owner_selected_location')
  }
  
  console.log('Emitting location_id:', locationId, 'Type:', typeof locationId)
  
  // Emit event to parent (now sending location_id as integer or null)
  emit('outlet-changed', locationId)
}

const loadOutlets = async () => {
  try {
    console.log('Loading outlets from locations...')
    // Load outlets that are registered in inventory locations
    // Include both OUTLET and FNB type locations for POS
    const response = await api.get('/locations', { 
      params: { 
        is_active: true,
        per_page: 100  // Get all locations in one request
      } 
    })
    
    console.log('Locations response:', response.data)
    
    // Handle paginated response (response.data.data) or direct array
    const locationsData = response.data.data || response.data
    
    console.log('Locations data:', locationsData)
    
    // Map locations to outlet format (with outlet info)
    // Filter: only locations with outlet_id (not null) AND type is OUTLET or FNB
    const allLocations = locationsData.map(loc => ({
      location_id: loc.id,
      location_name: loc.name,
      outlet_id: loc.outlet_id,
      outlet_name: loc.outlet?.name || loc.name,
      business_type: loc.outlet?.business_type || 'retail',
      location_type: loc.type,
      hasOutletId: loc.outlet_id != null && loc.outlet_id > 0,
      isValidType: loc.type === 'OUTLET' || loc.type === 'FNB'
    }))
    
    console.log('All locations before filtering:', allLocations)
    
    outlets.value = allLocations.filter(loc => loc.hasOutletId && loc.isValidType)
    
    console.log('Mapped outlets:', outlets.value)
    console.log('Valid location IDs:', outlets.value.map(o => o.location_id))
    
    // Load saved location from localStorage and validate it
    const savedLocation = localStorage.getItem('owner_selected_location')
    if (savedLocation) {
      const savedLocationId = parseInt(savedLocation)
      const isValidLocation = outlets.value.find(o => o.location_id === savedLocationId)
      
      if (isValidLocation) {
        console.log('✅ Restored saved location:', savedLocationId)
        selectedLocationId.value = savedLocation
        emit('outlet-changed', savedLocationId)
      } else {
        console.warn('❌ Saved location', savedLocationId, 'is not valid anymore. Clearing...')
        localStorage.removeItem('owner_selected_location')
      }
    }
  } catch (error) {
    console.error('Failed to load outlets:', error)
    console.error('Error response:', error.response?.data)
    // Fallback to direct outlets endpoint if locations API fails
    try {
      console.log('Trying fallback to /outlets endpoint...')
      const response = await api.get('/outlets')
      outlets.value = response.data.map(outlet => ({
        location_id: outlet.id, // Fallback: use outlet_id as location_id
        location_name: outlet.name,
        outlet_id: outlet.id,
        outlet_name: outlet.name,
        business_type: outlet.business_type
      }))
      
      const savedLocation = localStorage.getItem('owner_selected_location')
      if (savedLocation && outlets.value.find(o => o.location_id === parseInt(savedLocation))) {
        selectedLocationId.value = savedLocation
        emit('outlet-changed', savedLocation)
      }
    } catch (fallbackError) {
      console.error('Failed to load outlets (fallback):', fallbackError)
    }
  }
}

onMounted(() => {
  loadOutlets()
})

// Watch for auth changes
watch(() => authStore.user, (newUser) => {
  const newRole = newUser?.role
  if ((newRole === 'owner' || newRole === 'inventory') && !newUser?.outlet_id) {
    loadOutlets()
  }
}, { immediate: true })
</script>
