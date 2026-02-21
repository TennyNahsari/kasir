import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useProductStore = defineStore('product', () => {
  const products = ref([])
  const categories = ref([])
  const loading = ref(false)
  const productsPagination = ref(null)

  const fetchProducts = async (params = {}) => {
    loading.value = true
    try {
      // If location_id is provided, use the new endpoint for inventory stocks
      const endpoint = params.location_id ? '/products-by-location' : '/products'
      console.log('Fetching products from:', endpoint, 'with params:', params)
      console.log('Params type check:', {
        location_id: params.location_id,
        location_id_type: typeof params.location_id,
        is_active: params.is_active,
        is_active_type: typeof params.is_active
      })
      
      const response = await api.get(endpoint, { params })
      console.log('Products API response:', response.data)
      
      // Handle response with debug info
      if (response.data && typeof response.data === 'object' && 'data' in response.data) {
        products.value = response.data.data || []
        // Store pagination info if available
        if (response.data.meta || response.data.current_page) {
          productsPagination.value = response.data.meta || {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
          }
        }
        return response.data // Return full response including debug info
      } else {
        products.value = params.location_id ? response.data : response.data.data
      }
      
      console.log('Products stored:', products.value.length, 'items')
      return response.data
    } catch (error) {
      console.error('Error fetching products:', error)
      console.error('Error response:', error.response?.data)
      throw error
    } finally {
      loading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await api.get('/categories')
      console.log('Categories API response:', response.data)
      categories.value = response.data
      console.log('Categories stored in store:', categories.value.length, 'items')
      console.log('Category names:', categories.value.map(c => c.name))
      return response.data
    } catch (error) {
      console.error('Error fetching categories:', error)
      throw error
    }
  }

  const findByBarcode = async (barcode) => {
    try {
      const response = await api.post('/products/find-barcode', { barcode })
      return response.data
    } catch (error) {
      throw error
    }
  }

  const createProduct = async (data) => {
    try {
      const config = data instanceof FormData ? {
        headers: { 'Content-Type': 'multipart/form-data' }
      } : {}
      
      const response = await api.post('/products', data, config)
      products.value.unshift(response.data)
      return response.data
    } catch (error) {
      throw error
    }
  }

  const updateProduct = async (id, data) => {
    try {
      const config = data instanceof FormData ? {
        headers: { 'Content-Type': 'multipart/form-data' }
      } : {}
      
      const response = await api.post(`/products/${id}`, data, config)
      const index = products.value.findIndex(p => p.id === id)
      if (index !== -1) {
        products.value[index] = response.data
      }
      return response.data
    } catch (error) {
      throw error
    }
  }

  const deleteProduct = async (id) => {
    try {
      await api.delete(`/products/${id}`)
      products.value = products.value.filter(p => p.id !== id)
    } catch (error) {
      throw error
    }
  }

  const addCategory = async (data) => {
    try {
      const response = await api.post('/categories', data)
      categories.value.push(response.data)
      return response.data
    } catch (error) {
      throw error
    }
  }

  const updateCategory = async (id, data) => {
    try {
      const response = await api.put(`/categories/${id}`, data)
      const index = categories.value.findIndex(c => c.id === id)
      if (index !== -1) {
        categories.value[index] = response.data
      }
      return response.data
    } catch (error) {
      throw error
    }
  }

  const deleteCategory = async (id) => {
    try {
      await api.delete(`/categories/${id}`)
      categories.value = categories.value.filter(c => c.id !== id)
    } catch (error) {
      throw error
    }
  }

  return {
    products,
    categories,
    loading,
    productsPagination,
    fetchProducts,
    fetchCategories,
    findByBarcode,
    createProduct,
    updateProduct,
    deleteProduct,
    addCategory,
    updateCategory,
    deleteCategory
  }
})
