import api from './api'

export default {
  // Get all service contracts with filters
  getAll(params = {}) {
    return api.get('/service-contracts', { params })
  },

  // Get single service contract
  get(id) {
    return api.get(`/service-contracts/${id}`)
  },

  // Create new service contract (manual)
  create(data) {
    return api.post('/service-contracts', data)
  },

  // Update service contract
  update(id, data) {
    return api.put(`/service-contracts/${id}`, data)
  },

  // Renew contract
  renew(id, data) {
    return api.post(`/service-contracts/${id}/renew`, data)
  },

  // Terminate contract
  terminate(id, reason) {
    return api.post(`/service-contracts/${id}/terminate`, { reason })
  },

  // Get dashboard stats
  getStats() {
    return api.get('/service-contracts/stats')
  }
}
