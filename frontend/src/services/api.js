import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true // Important: send cookies with requests
})

// Request interceptor - no need to add token manually, cookies are sent automatically
api.interceptors.request.use(
  (config) => {
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
      const currentPath = window.location.pathname
      const requestUrl = error.config?.url || ''
      
      const isPublicPage = currentPath === '/' || currentPath.startsWith('/order/')
      const isPublicApi = requestUrl.includes('/public/') || requestUrl.includes('/me')
      const isLoginPage = currentPath.includes('/login')
      
      if (!isPublicPage && !isPublicApi && !isLoginPage) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
