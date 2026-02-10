import api from '../utils/axios'

export default {
  async getLocations(params = {}) {
    const response = await api.get('/locations', { params })
    return response.data
  },

  async getLocation(id) {
    const response = await api.get(`/locations/${id}`)
    return response.data
  },

  async createLocation(data) {
    const response = await api.post('/locations', data)
    return response.data
  },

  async updateLocation(id, data) {
    const response = await api.put(`/locations/${id}`, data)
    return response.data
  },

  async deleteLocation(id) {
    const response = await api.delete(`/locations/${id}`)
    return response.data
  }
}
