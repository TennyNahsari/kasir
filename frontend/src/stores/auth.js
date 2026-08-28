import { defineStore } from 'pinia'
import { ref } from 'vue'
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
    
    // Don't check auth on login page or if no login flag in localStorage
    const hasLoginFlag = localStorage.getItem('is_logged_in') === 'true'
    if (window.location.pathname.includes('/login') || !hasLoginFlag) {
      user.value = null
      loading.value = false
      return
    }
    
    try {
      const response = await api.get('/me')
      user.value = response.data
      localStorage.setItem('is_logged_in', 'true')
    } catch (error) {
      localStorage.removeItem('is_logged_in')
      user.value = null
    } finally {
      loading.value = false
    }
  }

  const login = async (email, password) => {
    loading.value = true
    try {
      const response = await api.post('/login', { email, password })
      user.value = response.data.user
      localStorage.setItem('is_logged_in', 'true')
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
      localStorage.removeItem('is_logged_in')
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
