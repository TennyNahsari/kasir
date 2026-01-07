import api from './api'

export default {
  async getLocations(params = {}) {
    const response = await api.get('/locations', { params })
    return response.data
  },

  async getLocation(id) {
    const response = await api.get(`/locations/${id}`)
    return response.data
  }
}
