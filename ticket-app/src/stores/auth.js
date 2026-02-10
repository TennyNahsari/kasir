import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import api from '../utils/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const isAuthenticated = ref(false)
  const authChecked = ref(false)

  async function checkAuth() {
    try {
      const response = await api.get('/me')
      user.value = response.data
      isAuthenticated.value = true
    } catch (error) {
      user.value = null
      isAuthenticated.value = false
    } finally {
      authChecked.value = true
    }
  }

  async function login(email, password) {
    try {
      // Get CSRF cookie first
      await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
        withCredentials: true
      })

      // Small delay to ensure cookie is set
      await new Promise(resolve => setTimeout(resolve, 100))

      // Login
      const response = await api.post('/login', {
        email,
        password
      })

      user.value = response.data.user
      isAuthenticated.value = true
      return { success: true }
    } catch (error) {
      console.error('Login error:', error.response?.data)
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed'
      }
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      isAuthenticated.value = false
    }
  }

  function hasRole(...roles) {
    return roles.includes(user.value?.role)
  }

  const isOwner = () => user.value?.role === 'owner'
  const isSupervisor = () => user.value?.role === 'supervisor'
  const isTechnician = () => user.value?.role === 'technician'

  return {
    user,
    isAuthenticated,
    authChecked,
    checkAuth,
    login,
    logout,
    hasRole,
    isOwner,
    isSupervisor,
    isTechnician
  }
})
