import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const discount = ref(0)
  const tax = ref(0)

  const subtotal = computed(() => {
    return items.value.reduce((sum, item) => {
      return sum + (item.price * item.quantity - (item.discount || 0))
    }, 0)
  })

  const total = computed(() => {
    return subtotal.value - discount.value + tax.value
  })

  const itemCount = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  const addItem = (product, quantity = 1) => {
    const existingItem = items.value.find(item => item.product_id === product.id)
    
    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      items.value.push({
        product_id: product.id,
        product_name: product.name,
        price: product.selling_price,
        quantity: quantity,
        discount: 0,
        notes: ''
      })
    }
  }

  const updateItem = (productId, updates) => {
    const item = items.value.find(item => item.product_id === productId)
    if (item) {
      Object.assign(item, updates)
    }
  }

  const removeItem = (productId) => {
    items.value = items.value.filter(item => item.product_id !== productId)
  }

  const setDiscount = (amount) => {
    discount.value = amount
  }

  const setTax = (amount) => {
    tax.value = amount
  }

  const clearCart = () => {
    items.value = []
    discount.value = 0
    tax.value = 0
  }

  const checkout = async (paymentData, locationId = null) => {
    const authStore = useAuthStore()
    
    // Use provided locationId, or fall back to user's outlet_id
    const finalId = locationId || authStore.user.outlet_id
    
    if (!finalId) {
      throw new Error('Location/Outlet ID tidak tersedia. Silakan pilih outlet terlebih dahulu.')
    }
    
    console.log('Checkout with ID:', finalId, 'Type:', locationId ? 'location_id' : 'outlet_id')
    
    try {
      // Send location_id if provided, otherwise outlet_id
      const requestData = {
        items: items.value,
        discount: discount.value,
        tax: tax.value,
        ...paymentData
      }
      
      if (locationId) {
        requestData.location_id = finalId
      } else {
        requestData.outlet_id = finalId
      }
      
      console.log('Transaction request data:', requestData)
      
      const response = await api.post('/transactions', requestData)
      
      clearCart()
      return response.data
    } catch (error) {
      console.error('Checkout error:', error.response?.data || error)
      
      // Throw error with more details
      if (error.response?.data?.message) {
        const err = new Error(error.response.data.message)
        err.response = error.response
        throw err
      }
      
      throw error
    }
  }

  return {
    items,
    discount,
    tax,
    subtotal,
    total,
    itemCount,
    addItem,
    updateItem,
    removeItem,
    setDiscount,
    setTax,
    clearCart,
    checkout
  }
})
