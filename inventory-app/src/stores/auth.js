import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const initialized = ref(false)

  // Initialize by checking if user is authenticated via cookie
  const initAuth = async () => {
    if (initialized.value) return
    
    initialized.value = true
    loading.value = true
    try {
      // Try to get current user from API (cookie will be sent automatically)
      const response = await api.get('/me')
      user.value = response.data
    } catch (error) {
      // Not authenticated or session expired
      user.value = null
    } finally {
      loading.value = false
    }
  }

  const login = async (email, password) => {
    loading.value = true
    try {
      // IMPORTANT: Get CSRF cookie first for Laravel Sanctum
      // Use axios directly (not api instance) because /sanctum is NOT under /api prefix
      const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
      const sanctumUrl = baseUrl.replace('/api', '/sanctum/csrf-cookie')
      await axios.get(sanctumUrl, { withCredentials: true })
      
      // Then login using api instance (which has /api baseURL)
      const response = await api.post('/login', { email, password })
      user.value = response.data.user
      initialized.value = true
      return true
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      initialized.value = false
    }
  }

  const isOwner = () => user.value?.role === 'owner'
  const isSupervisor = () => user.value?.role === 'supervisor'
  const isKasir = () => user.value?.role === 'kasir'
  const isKitchen = () => user.value?.role === 'kitchen'

  return {
    user,
    loading,
    initialized,
    initAuth,
    login,
    logout,
    isOwner,
    isSupervisor,
    isKasir,
    isKitchen
  }
})
