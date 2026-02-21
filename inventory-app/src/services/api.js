import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true // Important: send cookies with requests
})

// Helper function to get cookie value
function getCookie(name) {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) {
    return decodeURIComponent(parts.pop().split(';').shift())
  }
  return null
}

// Request interceptor - add CSRF token from cookie
api.interceptors.request.use(
  (config) => {
    // Get XSRF token from cookie and add to header
    const token = getCookie('XSRF-TOKEN')
    if (token) {
      config.headers['X-XSRF-TOKEN'] = token
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Only redirect if not already on login page
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
