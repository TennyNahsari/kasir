import api from '../utils/axios'

export default {
  // Get all assets with filters
  getAssets(params = {}) {
    return api.get('/assets', { params })
  },

  // Get asset detail
  getAsset(id) {
    return api.get(`/assets/${id}`)
  },

  // Create asset
  createAsset(data) {
    return api.post('/assets', data)
  },

  // Update asset
  updateAsset(id, data) {
    return api.put(`/assets/${id}`, data)
  },

  // Get available assets
  getAvailableAssets(params = {}) {
    return api.get('/assets/available', { params })
  },

  // Get assets by user
  getAssetsByUser(userId) {
    return api.get(`/assets/by-user/${userId}`)
  },

  // Get my assets (current authenticated user)
  getMyAssets() {
    return api.get('/tickets/my-assets')
  },

  // Assign asset
  assignAsset(id, data) {
    return api.post(`/assets/${id}/assign`, data)
  },

  // Return asset
  returnAsset(id, data) {
    return api.post(`/assets/${id}/return`, data)
  },

  // Transfer asset
  transferAsset(id, data) {
    return api.post(`/assets/${id}/transfer`, data)
  },

  // Dispose asset
  disposeAsset(id, data) {
    return api.post(`/assets/${id}/dispose`, data)
  },

  // Get asset history
  getAssetHistory(id) {
    return api.get(`/assets/${id}/history`)
  },

  // Add history entry
  addHistory(id, data) {
    return api.post(`/assets/${id}/history`, data)
  }
}
