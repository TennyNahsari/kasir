<template>
  <div class="space-y-4">
    <!-- Outlet Selector for Owner Only -->
    <OutletSelector v-if="isOwner" @outlet-changed="handleOutletChange" />

    <!-- Fixed Outlet Display for Kasir/Supervisor -->
    <div v-if="!isOwner && userOutletName" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <p class="text-blue-800 text-sm">
        🏪 <strong>Outlet:</strong> {{ userOutletName }}
      </p>
    </div>

    <!-- No Outlet Warning -->
    <div v-if="showNoOutletWarning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-yellow-800 text-sm">
        ⚠️ <strong>{{ isOwner ? 'Silakan pilih outlet terlebih dahulu' : 'User tidak memiliki outlet' }}</strong>
        {{ isOwner ? '' : '- Hubungi admin untuk assign outlet.' }}
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
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
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

      <div v-if="!loading && products.length === 0 && currentOutletId" class="text-center py-8">
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

const isOwner = computed(() => authStore.user?.role === 'owner' && !authStore.user?.outlet_id)
const showNoOutletWarning = computed(() => !currentOutletId.value && !loading.value)

const categories = computed(() => productStore.categories)
const products = computed(() => productStore.products)

const filteredProducts = computed(() => {
  let filtered = products.value

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
    alert('Checkout failed: ' + error.message)
  }
}

const closeSuccess = () => {
  showSuccess.value = false
  barcodeInput.value?.focus()
}

const loadProductsForOutlet = async (outletId) => {
  if (!outletId) return
  
  loading.value = true
  debugInfo.value = null
  try {
    console.log('Loading products for outlet:', outletId)
    const [productsResponse] = await Promise.all([
      productStore.fetchProducts({ location_id: outletId, is_active: true }),
      productStore.fetchCategories()
    ])
    
    console.log('Products loaded:', products.value.length)
    console.log('Products data:', products.value)
    
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
  console.log('handleOutletChange called with locationId:', locationId)
  currentOutletId.value = locationId
  if (locationId) {
    // Clear cart when switching outlet
    cartStore.clearCart()
    loadProductsForOutlet(locationId)
  }
}

const getLocationIdForOutlet = async (outletId) => {
  try {
    console.log('Looking up location for outlet:', outletId)
    const response = await api.get('/locations', {
      params: {
        type: 'OUTLET',
        outlet_id: outletId
      }
    })
    
    if (response.data && response.data.length > 0) {
      const location = response.data[0]
      console.log('Found location:', location)
      userOutletName.value = location.name // Set outlet name for display
      return location.id
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
    // Load location name for display
    try {
      const response = await api.get(`/locations/${userLocationId}`)
      userOutletName.value = response.data.name
      console.log('Location name:', response.data.name)
    } catch (error) {
      console.error('Failed to load location:', error)
    }
    
    currentOutletId.value = userLocationId
    await loadProductsForOutlet(userLocationId)
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
      // Owner will select location via OutletSelector component
      // Check if there's a saved location from previous session
      const savedLocation = localStorage.getItem('owner_selected_location')
      if (savedLocation) {
        currentOutletId.value = savedLocation
        await loadProductsForOutlet(savedLocation)
      } else {
        loading.value = false
      }
    } else {
      // User has no outlet and is not owner - show error
      loading.value = false
    }
  }
})
</script>
