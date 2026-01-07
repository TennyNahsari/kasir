import api from './api'

export default {
  // Get all assets with filters
  async getAssets(params = {}) {
    const response = await api.get('/assets', { params })
    return response.data
  },

  // Get single asset
  async getAsset(id) {
    const response = await api.get(`/assets/${id}`)
    return response.data
  },

  // Get available assets
  async getAvailableAssets(params = {}) {
    const response = await api.get('/assets/available', { params })
    return response.data
  },

  // Get assets by user
  async getAssetsByUser(userId) {
    const response = await api.get(`/assets/by-user/${userId}`)
    return response.data
  },

  // Get asset history
  async getAssetHistory(id) {
    const response = await api.get(`/assets/${id}/history`)
    return response.data
  },

  // Create asset
  async createAsset(data) {
    const response = await api.post('/assets', data)
    return response.data
  },

  // Update asset
  async updateAsset(id, data) {
    const response = await api.put(`/assets/${id}`, data)
    return response.data
  },

  // Assign asset to user
  async assignAsset(id, data) {
    const response = await api.post(`/assets/${id}/assign`, data)
    return response.data
  },

  // Return asset from user
  async returnAsset(id, data) {
    const response = await api.post(`/assets/${id}/return`, data)
    return response.data
  },

  // Transfer asset to another location
  async transferAsset(id, data) {
    const response = await api.post(`/assets/${id}/transfer`, data)
    return response.data
  },

  // Dispose asset
  async disposeAsset(id, data) {
    const response = await api.post(`/assets/${id}/dispose`, data)
    return response.data
  },

  // Add manual movement history
  async addMovementHistory(id, data) {
    const response = await api.post(`/assets/${id}/history`, data)
    return response.data
  }
}
