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

// Show selector only for owner without outlet_id
const showSelector = computed(() => {
  return authStore.user?.role === 'owner' && !authStore.user?.outlet_id
})

const handleOutletChange = () => {
  console.log('Location changed to:', selectedLocationId.value)
  
  // Save to localStorage for persistence
  if (selectedLocationId.value) {
    localStorage.setItem('owner_selected_location', selectedLocationId.value)
  } else {
    localStorage.removeItem('owner_selected_location')
  }
  
  // Emit event to parent (now sending location_id)
  emit('outlet-changed', selectedLocationId.value)
}

const loadOutlets = async () => {
  try {
    console.log('Loading outlets from locations...')
    // Load outlets that are registered in inventory locations
    const response = await api.get('/locations', { 
      params: { 
        type: 'OUTLET',
        is_active: true 
      } 
    })
    
    console.log('Locations response:', response.data)
    
    // Map locations to outlet format (with outlet info)
    outlets.value = response.data
      .filter(loc => loc.outlet) // Only locations that have outlet linked
      .map(loc => ({
        location_id: loc.id,
        location_name: loc.name,
        outlet_id: loc.outlet.id,
        outlet_name: loc.outlet.name,
        business_type: loc.outlet.business_type
      }))
    
    console.log('Mapped outlets:', outlets.value)
    
    // Load saved location from localStorage
    const savedLocation = localStorage.getItem('owner_selected_location')
    if (savedLocation && outlets.value.find(o => o.location_id === parseInt(savedLocation))) {
      selectedLocationId.value = savedLocation
      emit('outlet-changed', savedLocation)
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
  if (newUser?.role === 'owner' && !newUser?.outlet_id) {
    loadOutlets()
  }
}, { immediate: true })
</script>
