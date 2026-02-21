import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import axios from 'axios'

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
      // Get CSRF cookie - use plain axios to avoid baseURL issue
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
      
      // Then login using api instance (which has baseURL /api)
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

  const isOwner = () => {
    const role = user.value?.role
    return role === 'owner' || role === 'inventory'
  }
  const isSupervisor = () => user.value?.role === 'supervisor'

  return {
    user,
    loading,
    initialized,
    initAuth,
    login,
    logout,
    isOwner,
    isSupervisor
  }
})
