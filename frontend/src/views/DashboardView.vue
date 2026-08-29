<template>
  <div class="space-y-4">
    <h2 class="text-xl sm:text-2xl font-bold">{{ $t('dashboard.title') }}</h2>
    
    <!-- Outlet Selector for Owner -->
    <OutletSelector :allowed-types="['OUTLET', 'FNB']" @outlet-changed="handleOutletChange" />

    <!-- No Outlet Warning -->
    <div v-if="showNoOutletWarning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-yellow-800 text-sm">
        ⚠️ <strong>{{ isOwner ? $t('dashboard.selectOutlet') : $t('dashboard.noOutlet') }}</strong>
      </p>
    </div>

    <!-- Stats Cards -->
    <div v-if="currentLocationId" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-6">
      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">{{ $t('dashboard.todayRevenue') }}</div>
        <div class="text-base sm:text-2xl font-bold text-primary-600">
          {{ formatCurrency(stats?.today?.total_revenue || 0) }}
        </div>
      </div>

      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">{{ $t('dashboard.totalTransactions') }}</div>
        <div class="text-base sm:text-2xl font-bold text-green-600">
          {{ stats?.today?.total_transactions || 0 }}
        </div>
      </div>

      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">{{ $t('dashboard.averageTransaction') }}</div>
        <div class="text-base sm:text-2xl font-bold text-blue-600">
          {{ formatCurrency(stats?.today?.average_transaction || 0) }}
        </div>
      </div>

      <div class="card p-3 sm:p-6">
        <div class="text-xs sm:text-sm text-gray-600 mb-1">{{ $t('dashboard.cashInHand') }}</div>
        <div class="text-base sm:text-2xl font-bold text-purple-600">
          {{ formatCurrency(stats?.today?.cash_in_hand || 0) }}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Top Products -->
      <div class="card">
        <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">{{ $t('dashboard.topProducts') }}</h3>
        <div class="space-y-2 sm:space-y-3">
          <div 
            v-for="product in stats?.top_products?.slice(0, 5)" 
            :key="product.id"
            class="flex justify-between items-center p-2 sm:p-3 bg-gray-50 rounded"
          >
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm sm:text-base truncate">{{ product.name }}</div>
              <div class="text-xs sm:text-sm text-gray-600">{{ product.total_quantity }} {{ $t('dashboard.sold') }}</div>
            </div>
            <div class="text-right flex-shrink-0 ml-2">
              <div class="font-semibold text-primary-600 text-sm sm:text-base">
                {{ formatCurrency(product.total_revenue) }}
              </div>
            </div>
          </div>
          <div v-if="!stats?.top_products?.length" class="text-center text-gray-500 py-4 text-sm">
            Belum ada data produk terlaris
          </div>
        </div>
      </div>

      <!-- Low Stock -->
      <div class="card">
        <div class="flex justify-between items-center mb-3 sm:mb-4">
          <h3 class="text-base sm:text-lg font-semibold">{{ $t('dashboard.lowStock') }}</h3>
          <span v-if="isUserLocationFnb" class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded font-medium">
            🍽️ {{ $t('dashboard.fnbOnly') }}
          </span>
        </div>
        <div class="space-y-2 sm:space-y-3">
          <div 
            v-for="product in stats?.low_stock_products" 
            :key="product.id"
            class="flex justify-between items-center p-2 sm:p-3 bg-red-50 rounded"
          >
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm sm:text-base truncate">{{ product.name }}</div>
              <div class="text-xs sm:text-sm text-gray-600">{{ product.category?.name }}</div>
            </div>
            <div class="text-right flex-shrink-0 ml-2">
              <div class="font-semibold text-red-600 text-sm sm:text-base">
                {{ $t('dashboard.stock') }}: {{ product.stock }}
              </div>
              <div class="text-xs text-gray-600">{{ $t('dashboard.min') }}: {{ product.min_stock }}</div>
            </div>
          </div>
          <div v-if="!stats?.low_stock_products?.length" class="text-center text-gray-500 py-4 text-sm">
            {{ $t('dashboard.allProductsSafe') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Table Bookings Widget Section -->
    <div class="card mt-4 sm:mt-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3 sm:mb-4">
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="text-base sm:text-lg font-semibold">📅 {{ $t('dashboard.tableBookings') }}</h3>
          <span v-if="pendingBookingsCount > 0" class="px-2.5 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full animate-pulse">
            {{ pendingBookingsCount }} Pending
          </span>
        </div>

        <div class="flex items-center gap-2 flex-wrap text-xs font-medium">
          <!-- Filter toggle -->
          <div class="bg-gray-100 p-0.5 rounded-lg flex border border-gray-200">
            <button
              @click="setBookingFilter('all')"
              class="px-2.5 py-1 rounded-md transition-all cursor-pointer"
              :class="bookingFilterMode === 'all' ? 'bg-white font-bold text-gray-800 shadow-xs' : 'text-gray-500 hover:text-gray-700'"
            >
              🌐 Semua Outlet
            </button>
            <button
              v-if="currentLocationId"
              @click="setBookingFilter('current')"
              class="px-2.5 py-1 rounded-md transition-all cursor-pointer"
              :class="bookingFilterMode === 'current' ? 'bg-white font-bold text-gray-800 shadow-xs' : 'text-gray-500 hover:text-gray-700'"
            >
              📍 Outlet Ini
            </button>
          </div>

          <button 
            @click="fetchBookings(bookingFilterMode === 'current' ? currentLocationId : null)" 
            class="text-xs text-primary-600 hover:underline flex items-center gap-1 cursor-pointer font-medium"
          >
            🔄 Refresh
          </button>
        </div>
      </div>

      <div v-if="loadingBookings" class="text-center text-gray-500 py-6 text-sm">
        Loading table bookings...
      </div>

      <div v-else-if="!tableBookings.length" class="text-center text-gray-500 py-6 text-sm">
        {{ $t('dashboard.noBookings') }}
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="booking in tableBookings" 
          :key="booking.id"
          class="flex flex-col md:flex-row md:items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-200 gap-3"
        >
          <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-mono text-xs font-bold text-gray-700 bg-gray-200 px-2 py-0.5 rounded">
                {{ booking.booking_code }}
              </span>
              <span v-if="booking.location?.name" class="px-2 py-0.5 text-xs font-bold bg-primary-50 text-primary-700 rounded-md border border-primary-200">
                📍 {{ booking.location.name }}
              </span>
              <span class="font-bold text-gray-800 text-sm sm:text-base">
                {{ booking.customer_name }}
              </span>
              <a 
                v-if="booking.whatsapp_number"
                :href="getWaLink(booking.whatsapp_number, booking)"
                target="_blank"
                class="inline-flex items-center gap-1 text-xs text-emerald-600 font-semibold hover:underline bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200"
              >
                💬 {{ booking.whatsapp_number }}
              </a>
            </div>

            <div class="flex items-center gap-3 text-xs text-gray-600 flex-wrap">
              <span>📅 <strong>{{ booking.reservation_date }}</strong></span>
              <span>⏰ <strong>{{ booking.reservation_time }}</strong></span>
              <span v-if="booking.guest_count" class="px-2 py-0.5 bg-amber-50 text-amber-800 font-bold rounded border border-amber-200">
                🪑 Kapasitas/Kursi: {{ booking.guest_count }}
              </span>
              <span v-if="booking.notes" class="text-gray-500 italic">"{{ booking.notes }}"</span>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0 self-end md:self-center">
            <!-- Status Badge -->
            <span 
              class="px-2.5 py-1 text-xs font-bold rounded-full uppercase"
              :class="{
                'bg-amber-100 text-amber-800 border border-amber-300': booking.status === 'pending',
                'bg-emerald-100 text-emerald-800 border border-emerald-300': booking.status === 'confirmed',
                'bg-rose-100 text-rose-800 border border-rose-300': booking.status === 'cancelled'
              }"
            >
              {{ booking.status === 'pending' ? $t('dashboard.bookingStatusPending') : (booking.status === 'confirmed' ? $t('dashboard.bookingStatusConfirmed') : $t('dashboard.bookingStatusCancelled')) }}
            </span>

            <!-- Action buttons for staff -->
            <button
              v-if="booking.status !== 'confirmed'"
              @click="updateBookingStatus(booking.id, 'confirmed')"
              :disabled="updatingBookingId === booking.id"
              class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors cursor-pointer disabled:opacity-50"
            >
              ✓ {{ $t('dashboard.confirmBooking') }}
            </button>

            <button
              v-if="booking.status !== 'cancelled'"
              @click="updateBookingStatus(booking.id, 'cancelled')"
              :disabled="updatingBookingId === booking.id"
              class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50"
            >
              ✕ {{ $t('dashboard.cancelBooking') }}
            </button>

            <button
              @click="deleteBooking(booking)"
              :disabled="updatingBookingId === booking.id"
              class="px-2.5 py-1 bg-gray-200 hover:bg-red-600 hover:text-white text-gray-700 rounded-lg text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50"
              :title="$t('dashboard.deleteBooking')"
            >
              🗑️ {{ $t('dashboard.deleteBooking') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'
import OutletSelector from '@/components/OutletSelector.vue'
import api from '@/services/api'

const { t } = useI18n()
const dashboardStore = useDashboardStore()
const authStore = useAuthStore()
const stats = ref(null)
const currentLocationId = ref(null)
const userLocation = ref(null)

const isOwner = computed(() => {
  const role = authStore.user?.role
  return (role === 'owner' || role === 'inventory') && !authStore.user?.outlet_id
})
const showNoOutletWarning = computed(() => !currentLocationId.value && !stats.value)

// Check if user location is FNB type
const isUserLocationFnb = computed(() => {
  return userLocation.value?.type?.toUpperCase() === 'FNB'
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const tableBookings = ref([])
const loadingBookings = ref(false)
const updatingBookingId = ref(null)
const bookingFilterMode = ref('all')

const setBookingFilter = (mode) => {
  bookingFilterMode.value = mode
  if (mode === 'current' && currentLocationId.value) {
    fetchBookings(currentLocationId.value)
  } else {
    fetchBookings(null)
  }
}

const pendingBookingsCount = computed(() => {
  return tableBookings.value.filter(b => b.status === 'pending').length
})

const getWaLink = (phone, booking) => {
  let num = String(phone || '').replace(/\D/g, '')
  if (num.startsWith('0')) num = `62${num.slice(1)}`
  const msg = `Halo ${booking.customer_name}, konfirmasi mengenai reservasi meja (${booking.booking_code}) pada ${booking.reservation_date} jam ${booking.reservation_time}.`
  return `https://wa.me/${num}?text=${encodeURIComponent(msg)}`
}

const fetchBookings = async (locationId) => {
  loadingBookings.value = true
  try {
    const params = {}
    if (bookingFilterMode.value === 'current' && locationId) {
      params.location_id = locationId
    }
    const res = await api.get('/table-bookings', { params })
    tableBookings.value = res.data?.data || []
  } catch (err) {
    console.error('Failed to fetch table bookings:', err)
  } finally {
    loadingBookings.value = false
  }
}

const updateBookingStatus = async (bookingId, status) => {
  updatingBookingId.value = bookingId
  try {
    await api.put(`/table-bookings/${bookingId}/status`, { status })
    await fetchBookings(currentLocationId.value)
  } catch (err) {
    console.error('Failed to update booking status:', err)
    alert(`Gagal mengubah status: ${err.response?.data?.message || 'Silakan coba lagi.'}`)
  } finally {
    updatingBookingId.value = null
  }
}

const deleteBooking = async (booking) => {
  if (!confirm(t('dashboard.deleteBookingConfirm'))) return
  updatingBookingId.value = booking.id
  try {
    await api.delete(`/table-bookings/${booking.id}`)
    await fetchBookings(currentLocationId.value)
  } catch (err) {
    console.error('Failed to delete table booking:', err)
    alert(`Gagal menghapus reservasi: ${err.response?.data?.message || 'Silakan coba lagi.'}`)
  } finally {
    updatingBookingId.value = null
  }
}

const loadDashboard = async (locationId) => {
  if (!locationId) return
  
  try {
    const params = { 
      location_id: locationId 
    }
    
    // Get location details to get outlet_id
    try {
      const response = await api.get(`/locations/${locationId}`)
      userLocation.value = response.data
      if (userLocation.value?.outlet_id) {
        params.outlet_id = userLocation.value.outlet_id
      }
      console.log('Loading dashboard with params:', params)
    } catch (error) {
      console.error('Failed to load location details:', error)
    }
    
    stats.value = await dashboardStore.fetchDashboard(params)
    console.log('Dashboard stats loaded:', stats.value)

    // Load table bookings for this location
    fetchBookings(locationId)
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  }
}

const handleOutletChange = (locationId) => {
  currentLocationId.value = locationId
  if (locationId) {
    loadDashboard(locationId)
  } else {
    fetchBookings()
  }
}

onMounted(async () => {
  const userOutletId = authStore.user?.outlet_id
  const userLocationId = authStore.user?.location_id
  
  // Load user location info if available (to check if FNB type)
  if (userLocationId) {
    try {
      const response = await api.get(`/locations/${userLocationId}`)
      userLocation.value = response.data
      console.log('User location type:', userLocation.value?.type)
      currentLocationId.value = userLocationId
      await loadDashboard(userLocationId)
    } catch (error) {
      console.error('Failed to load user location:', error)
    }
  } else if (isOwner.value) {
    // Owner will select location via OutletSelector
    const savedLocation = localStorage.getItem('owner_selected_location')
    if (savedLocation) {
      currentLocationId.value = parseInt(savedLocation)
      await loadDashboard(parseInt(savedLocation))
    } else {
      fetchBookings()
    }
  } else {
    fetchBookings()
  }
})
</script>
