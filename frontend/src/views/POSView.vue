<template>
  <div class="space-y-4">
    <!-- Outlet Selector for Owner & Inventory -->
    <OutletSelector v-if="isOwner" @outlet-changed="handleOutletChange" />

    <!-- Fixed Outlet Display for Staff/Supervisor -->
    <div v-if="!isOwner && userOutletName" class="rounded-lg p-4" 
      :class="isFnbMode ? 'bg-orange-50 border border-orange-200' : 'bg-blue-50 border border-blue-200'">
      <div class="flex items-center justify-between">
        <p :class="isFnbMode ? 'text-orange-800' : 'text-blue-800'" class="text-sm">
          <span v-if="isFnbMode">🍽️</span>
          <span v-else>🏪</span>
          <strong>Outlet:</strong> {{ userOutletName }}
          <span v-if="isFnbMode" class="ml-2 px-2 py-1 bg-orange-200 rounded text-xs font-semibold">
            F&B Mode
          </span>
          <span v-else class="ml-2 px-2 py-1 bg-blue-200 rounded text-xs font-semibold">
            Retail/Outlet
          </span>
        </p>
      </div>
      <p v-if="isFnbMode" class="text-orange-700 text-xs mt-1">
        📋 Hanya menampilkan produk F&B ({{ categories.length }} kategori)
      </p>
    </div>

    <!-- Invalid Location Type Warning -->
    <div v-if="outletInfo && !isValidPosLocation" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <p class="text-red-800 text-sm">
        ⚠️ <strong>Location Type "{{ outletInfo.type }}" tidak dapat menggunakan POS Kasir</strong>
      </p>
      <p class="text-red-600 text-xs mt-1">
        POS Kasir hanya tersedia untuk Location Type: <strong>OUTLET</strong> atau <strong>FNB</strong><br>
        Location Type {{ outletInfo.type }} (Warehouse/Department) menggunakan sistem inventory.
      </p>
    </div>

    <!-- No Outlet Warning -->
    <div v-if="showNoOutletWarning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-yellow-800 text-sm">
        ⚠️ <strong>{{ isOwner ? 'Silakan pilih location terlebih dahulu' : 'User tidak memiliki location' }}</strong>
        {{ isOwner ? '' : '- Hubungi admin untuk assign location.' }}
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Product Selection (Left) -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Search & Scanner -->
        <div class="card">
        <div class="flex gap-3">
          <input
            ref="barcodeInput"
            v-model="searchQuery"
            @keyup.enter="handleBarcodeSearch"
            type="text"
            class="input flex-1"
            placeholder="Scan barcode atau cari produk..."
            autofocus
          >
          <button @click="handleBarcodeSearch" class="btn btn-primary">
            Cari
          </button>
        </div>
      </div>

      <!-- Categories -->
      <div class="card">
        <div class="flex gap-2 flex-wrap">
          <button
            @click="selectedCategory = null"
            class="btn"
            :class="selectedCategory === null ? 'btn-primary' : 'btn-secondary'"
          >
            Semua
          </button>
          <button
            v-for="category in categories"
            :key="category.id"
            @click="selectedCategory = category.id"
            class="btn"
            :class="selectedCategory === category.id ? 'btn-primary' : 'btn-secondary'"
          >
            {{ category.name }}
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="isValidPosLocation" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          @click="addToCart(product)"
          class="card cursor-pointer hover:shadow-md transition-shadow"
        >
          <div class="aspect-square bg-gray-100 rounded mb-2 flex items-center justify-center overflow-hidden">
            <img 
              v-if="product.image" 
              :src="`http://localhost:8000/storage/${product.image}`" 
              :alt="product.name"
              class="w-full h-full object-cover"
            >
            <span v-else class="text-4xl">📦</span>
          </div>
          <h4 class="font-medium text-sm mb-1 truncate">{{ product.name }}</h4>
          <p class="text-primary-600 font-semibold">
            {{ formatCurrency(product.selling_price) }}
          </p>
          <p v-if="product.track_stock" class="text-xs" :class="getAvailableStock(product) <= 0 ? 'text-red-500' : 'text-gray-500'">
            Stok: {{ getAvailableStock(product) }}
            <span v-if="product.reserved_quantity > 0" class="text-orange-500"> ({{ product.reserved_quantity }} reserved)</span>
          </p>
        </div>
      </div>

      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-500">Loading products...</p>
      </div>

      <div v-if="!loading && products.length === 0 && currentOutletId && isValidPosLocation" class="text-center py-8">
        <p class="text-gray-500 mb-2">⚠️ Tidak ada produk tersedia</p>
        <p class="text-xs text-gray-400">Pastikan outlet sudah terdaftar di inventory dan memiliki stock produk</p>
        <div v-if="debugInfo" class="mt-4 p-4 bg-gray-50 rounded text-left text-xs">
          <p class="font-bold mb-2">Debug Info:</p>
          <p>Location: {{ debugInfo.location_name }} (ID: {{ debugInfo.location_id }})</p>
          <p>Inventory Stocks: {{ debugInfo.inventory_stocks_count }}</p>
          <p>Active Products: {{ debugInfo.active_products_count }}</p>
          <p class="text-orange-600 mt-2">{{ debugInfo.hint }}</p>
        </div>
      </div>
    </div>

    <!-- Cart (Right) -->
    <div class="card h-fit sticky top-6">
      <h3 class="text-xl font-bold mb-4">Keranjang</h3>

      <!-- Cart Items -->
      <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
        <div
          v-for="item in cartStore.items"
          :key="item.product_id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded"
        >
          <div class="flex-1">
            <div class="font-medium text-sm">{{ item.product_name }}</div>
            <div class="text-xs text-gray-600">
              {{ formatCurrency(item.price) }} x {{ item.quantity }}
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="updateQuantity(item.product_id, item.quantity - 1)"
              class="w-8 h-8 bg-gray-200 rounded hover:bg-gray-300"
            >
              -
            </button>
            <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
            <button
              @click="updateQuantity(item.product_id, item.quantity + 1)"
              class="w-8 h-8 bg-primary-600 text-white rounded hover:bg-primary-700"
            >
              +
            </button>
            <button
              @click="cartStore.removeItem(item.product_id)"
              class="ml-2 text-red-600 hover:text-red-700"
            >
              🗑️
            </button>
          </div>
        </div>

        <div v-if="cartStore.items.length === 0" class="text-center py-8 text-gray-500">
          Keranjang kosong
        </div>
      </div>

      <!-- Summary -->
      <div class="border-t pt-4 space-y-2">
        <div class="flex justify-between text-sm">
          <span>Subtotal</span>
          <span>{{ formatCurrency(cartStore.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span>Diskon</span>
          <input
            v-model.number="discount"
            @change="cartStore.setDiscount(discount)"
            type="number"
            class="input w-32 text-right"
            min="0"
          >
        </div>
        <div class="flex justify-between font-bold text-lg border-t pt-2">
          <span>Total</span>
          <span class="text-primary-600">{{ formatCurrency(cartStore.total) }}</span>
        </div>
      </div>

      <!-- Payment -->
      <div class="mt-4 space-y-3">
        <div>
          <label class="label">Metode Pembayaran</label>
          <select v-model="paymentMethod" class="input">
            <option value="cash">Tunai</option>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer</option>
            <option value="ewallet">E-Wallet</option>
          </select>
        </div>

        <div>
          <label class="label">Jumlah Bayar</label>
          <input
            v-model.number="paidAmount"
            type="number"
            class="input"
            :min="cartStore.total"
            placeholder="0"
          >
        </div>

        <div v-if="changeAmount > 0" class="p-3 bg-green-50 rounded">
          <div class="text-sm text-gray-600">Kembalian</div>
          <div class="text-xl font-bold text-green-600">
            {{ formatCurrency(changeAmount) }}
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-4 space-y-2">
        <button
          @click="processCheckout"
          :disabled="cartStore.items.length === 0 || paidAmount < cartStore.total"
          class="btn btn-primary w-full"
        >
          Bayar & Cetak
        </button>
        <button
          @click="cartStore.clear()"
          class="btn btn-danger w-full"
        >
          Clear
        </button>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div v-if="showSuccess" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="card max-w-md">
      <div class="text-center">
        <div class="text-6xl mb-4">✅</div>
        <h3 class="text-2xl font-bold mb-2">Transaksi Berhasil!</h3>
        <p class="text-gray-600 mb-4">{{ successMessage }}</p>
        <button @click="closeSuccess" class="btn btn-primary">
          OK
        </button>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import OutletSelector from '@/components/OutletSelector.vue'
import { useProductStore } from '@/stores/product'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const productStore = useProductStore()
const cartStore = useCartStore()
const authStore = useAuthStore()

const searchQuery = ref('')
const selectedCategory = ref(null)
const discount = ref(0)
const paymentMethod = ref('cash')
const paidAmount = ref(0)
const loading = ref(false)
const showSuccess = ref(false)
const successMessage = ref('')
const barcodeInput = ref(null)
const currentOutletId = ref(null)
const debugInfo = ref(null)
const userOutletName = ref('')
const outletInfo = ref(null)

const isOwner = computed(() => {
  const role = authStore.user?.role
  return (role === 'owner' || role === 'inventory') && !authStore.user?.outlet_id
})
const showNoOutletWarning = computed(() => !currentOutletId.value && !loading.value && !isOwner.value)

// Check if current location is valid for POS (OUTLET, FNB, or Owner/Inventory)
const isValidPosLocation = computed(() => {
  // Owner/Inventory can access POS from any location
  if (isOwner.value) return true
  
  if (!outletInfo.value) return true // Still loading
  const locationType = outletInfo.value?.type?.toUpperCase()
  return locationType === 'OUTLET' || locationType === 'FNB'
})

// Check if current location/outlet is FNB type
const isFnbMode = computed(() => {
  const locationType = outletInfo.value?.type?.toUpperCase()
  return outletInfo.value?.outlet?.business_type === 'fnb' || locationType === 'FNB'
})

// Filter categories based on outlet/location type
const categories = computed(() => {
  const allCategories = productStore.categories
  const allProducts = productStore.products
  
  // Helper function to check if category has products with stock
  const hasProductsWithStock = (categoryId) => {
    return allProducts.some(p => {
      if (p.category_id !== categoryId) return false
      
      // If product doesn't track stock (like F&B items), always show
      if (!p.track_stock) return true
      
      // Check available stock
      const availableStock = p.available_stock ?? (p.stock - (p.reserved_quantity || 0))
      return availableStock > 0
    })
  }
  
  // Owner/Inventory sees all categories
  if (isOwner.value) {
    return allCategories
  }
  
  let filteredCategories = allCategories
  
  // If FNB mode, only show FNB categories
  if (isFnbMode.value) {
    filteredCategories = allCategories.filter(cat => 
      cat.name.includes('FNB') || 
      cat.slug.includes('fnb') ||
      cat.slug.includes('FNB')
    )
  }
  
  // Filter out categories with no products or no stock
  return filteredCategories.filter(cat => hasProductsWithStock(cat.id))
})

const products = computed(() => productStore.products)

const filteredProducts = computed(() => {
  let filtered = products.value
  
  // Owner sees all products (no filter)
  if (!isOwner.value) {
    // If outlet/location is FNB, only show products from FNB categories
    if (isFnbMode.value) {
      const fnbCategoryIds = categories.value.map(c => c.id)
      filtered = filtered.filter(p => fnbCategoryIds.includes(p.category_id))
    }
  }

  if (selectedCategory.value) {
    filtered = filtered.filter(p => p.category_id === selectedCategory.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.sku.toLowerCase().includes(query)
    )
  }

  return filtered
})

const changeAmount = computed(() => {
  return paidAmount.value > cartStore.total ? paidAmount.value - cartStore.total : 0
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const getAvailableStock = (product) => {
  return product.available_stock ?? (product.stock - (product.reserved_quantity || 0))
}

const addToCart = (product) => {
  // Check available stock (quantity - reserved_quantity)
  const availableStock = product.available_stock ?? (product.stock - (product.reserved_quantity || 0))
  
  if (product.track_stock && availableStock <= 0) {
    alert('Stok habis!')
    return
  }
  
  // Check if adding one more would exceed available stock
  const currentCartQuantity = cartStore.items.find(item => item.product_id === product.id)?.quantity || 0
  if (product.track_stock && currentCartQuantity >= availableStock) {
    alert(`Stok tidak cukup! Stok tersedia: ${availableStock}`)
    return
  }
  
  cartStore.addItem(product)
  searchQuery.value = ''
  barcodeInput.value?.focus()
}

const updateQuantity = (productId, quantity) => {
  if (quantity <= 0) {
    cartStore.removeItem(productId)
  } else {
    cartStore.updateItem(productId, { quantity })
  }
}

const handleBarcodeSearch = async () => {
  if (!searchQuery.value) return

  try {
    const product = await productStore.findByBarcode(searchQuery.value)
    addToCart(product)
  } catch (error) {
    // If not found by barcode, search will filter products
    console.log('Product not found by barcode, showing search results')
  }
}

const processCheckout = async () => {
  if (paidAmount.value < cartStore.total) {
    alert('Jumlah bayar kurang!')
    return
  }

  if (!currentOutletId.value) {
    alert('Silakan pilih outlet terlebih dahulu!')
    return
  }

  console.log('Processing checkout with location_id:', currentOutletId.value)
  console.log('Cart items:', cartStore.items)
  console.log('Payment method:', paymentMethod.value)
  console.log('Paid amount:', paidAmount.value)

  try {
    const transaction = await cartStore.checkout({
      payment_method: paymentMethod.value,
      paid_amount: paidAmount.value
    }, currentOutletId.value)

    successMessage.value = `No. Transaksi: ${transaction.transaction_no}`
    showSuccess.value = true

    // Reset
    paidAmount.value = 0
    discount.value = 0
    searchQuery.value = ''
    
    // TODO: Print receipt
    
  } catch (error) {
    console.error('Checkout error:', error)
    console.error('Error response:', error.response?.data)
    
    const errorMessage = error.response?.data?.message || error.message
    const validationErrors = error.response?.data?.errors
    
    let displayMessage = 'Checkout failed: ' + errorMessage
    
    // Add location info if error is about location/outlet
    if (errorMessage.includes('outlet') || errorMessage.includes('location')) {
      displayMessage += `\n\nLocation ID yang digunakan: ${currentOutletId.value}`
      displayMessage += `\nOutlet info: ${outletInfo.value?.name || 'N/A'}`
      displayMessage += `\nOutlet ID: ${outletInfo.value?.outlet_id || 'N/A'}`
    }
    
    if (validationErrors) {
      displayMessage += '\n\nValidation errors:\n'
      Object.keys(validationErrors).forEach(key => {
        displayMessage += `- ${key}: ${validationErrors[key].join(', ')}\n`
      })
    }
    
    alert(displayMessage)
  }
}

const closeSuccess = () => {
  showSuccess.value = false
  barcodeInput.value?.focus()
}

const loadProductsForOutlet = async (outletId) => {
  if (!outletId) {
    console.warn('⚠️ loadProductsForOutlet called with empty outletId')
    return
  }
  
  // Ensure outletId is a valid number
  const validOutletId = Number(outletId)
  if (isNaN(validOutletId) || validOutletId <= 0) {
    console.error('❌ Invalid outletId:', outletId, 'Type:', typeof outletId)
    alert('Invalid outlet ID. Please select a valid outlet.')
    return
  }
  
  console.log('✅ Loading products for outlet:', validOutletId, 'Type:', typeof validOutletId)
  
  loading.value = true
  debugInfo.value = null
  try {
    console.log('Loading products for outlet:', validOutletId)
    
    // Fetch location info to get outlet business_type
    const locationResponse = await api.get(`/locations/${validOutletId}`)
    outletInfo.value = locationResponse.data
    userOutletName.value = locationResponse.data.name // Set outlet name for display
    console.log('Outlet info:', outletInfo.value)
    console.log('Outlet business type:', outletInfo.value?.outlet?.business_type)
    console.log('Location type:', outletInfo.value?.type)
    console.log('Is Owner:', isOwner.value)
    
    // Get location type for later use
    const locationType = outletInfo.value?.type?.toUpperCase()
    
    // Owner and Inventory can access from any location type - skip validation
    if (!isOwner.value) {
      // Validate location type for POS (non-owner/non-inventory users)
      if (locationType !== 'OUTLET' && locationType !== 'FNB') {
        console.warn(`⚠️ Location type "${outletInfo.value?.type}" is not valid for POS. Only OUTLET and FNB are allowed.`)
        loading.value = false
        return
      }
    } else {
      console.log('👑 Owner/Inventory mode - showing all categories and products')
    }
    
    // Prepare params for fetching products
    const productParams = { 
      location_id: validOutletId,  // Use validated number
      is_active: true 
    }
    
    console.log('📦 Product params prepared:', productParams, {
      location_id_value: productParams.location_id,
      location_id_type: typeof productParams.location_id
    })
    
    // Check if FNB mode
    const isFnb = outletInfo.value?.outlet?.business_type === 'fnb' || locationType === 'FNB'
    if (isFnb) {
      console.log('🍽️ FNB Mode detected - will filter to FNB categories only')
    } else {
      console.log('🏪 OUTLET Mode - showing all categories and products')
    }
    
    const [productsResponse] = await Promise.all([
      productStore.fetchProducts(productParams),
      productStore.fetchCategories()
    ])
    
    console.log('Products loaded:', products.value.length)
    console.log('Products data:', products.value)
    console.log('Categories:', categories.value.map(c => c.name))
    
    // Extract debug info if available
    if (productsResponse && productsResponse.debug) {
      debugInfo.value = productsResponse.debug
      console.log('Debug info:', debugInfo.value)
    }
  } catch (error) {
    console.error('Failed to load data:', error)
    console.error('Error details:', error.response?.data)
    
    // Extract debug info from error response
    if (error.response?.data?.debug) {
      debugInfo.value = error.response.data.debug
    }
    
    alert('Gagal load data: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const handleOutletChange = (locationId) => {
  console.log('handleOutletChange called with locationId:', locationId, 'Type:', typeof locationId)
  
  // Ensure it's a valid number
  const validLocationId = locationId && !isNaN(locationId) && locationId > 0 ? Number(locationId) : null
  
  if (!validLocationId) {
    console.warn('⚠️ Invalid or empty location ID received')
    currentOutletId.value = null
    return
  }
  
  console.log('Setting currentOutletId to:', validLocationId)
  currentOutletId.value = validLocationId
  
  // Clear cart when switching outlet
  cartStore.clearCart()
  loadProductsForOutlet(validLocationId)
}

const getLocationIdForOutlet = async (outletId) => {
  try {
    console.log('Looking up location for outlet:', outletId)
    const response = await api.get('/locations', {
      params: {
        outlet_id: outletId,
        is_active: true
      }
    })
    
    if (response.data && response.data.length > 0) {
      // Filter to only OUTLET or FNB type locations
      const validLocations = response.data.filter(loc => 
        loc.type === 'OUTLET' || loc.type === 'FNB'
      )
      
      if (validLocations.length > 0) {
        const location = validLocations[0]
        console.log('Found location:', location)
        userOutletName.value = location.name // Set outlet name for display
        return location.id
      } else {
        console.warn('No OUTLET or FNB type location found for outlet:', outletId)
        console.log('Available locations:', response.data.map(l => ({id: l.id, name: l.name, type: l.type})))
      }
    }
    
    console.warn('No location found for outlet:', outletId)
    return null
  } catch (error) {
    console.error('Failed to lookup location:', error)
    return null
  }
}

onMounted(async () => {
  // Priority 1: Check if user has location_id (new field)
  const userLocationId = authStore.user?.location_id
  
  if (userLocationId) {
    console.log('User has location_id:', userLocationId)
    currentOutletId.value = userLocationId
    await loadProductsForOutlet(userLocationId)
    // userOutletName will be set by loadProductsForOutlet
  } else {
    // Priority 2: Fallback to outlet_id (for old users without location_id)
    const userOutletId = authStore.user?.outlet_id
    
    if (userOutletId) {
      console.log('User has outlet_id (old):', userOutletId, '- looking up location...')
      // Convert outlet_id to location_id
      const locationId = await getLocationIdForOutlet(userOutletId)
      if (locationId) {
        currentOutletId.value = locationId
        await loadProductsForOutlet(locationId)
      } else {
        alert('Outlet belum terdaftar di inventory. Hubungi admin.')
        loading.value = false
      }
    } else if (isOwner.value) {
      // Owner/Inventory will select location via OutletSelector component
      // Check if there's a saved location from previous session
      const savedLocation = localStorage.getItem('owner_selected_location')
      if (savedLocation) {
        currentOutletId.value = savedLocation
        await loadProductsForOutlet(savedLocation)
      } else {
        loading.value = false
      }
    } else {
      // User has no location and is not owner/inventory - show error
      loading.value = false
    }
  }
})
</script>
