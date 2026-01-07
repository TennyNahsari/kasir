import api from './api'

export default {
  async getUsers(params = {}) {
    const response = await api.get('/users', { params })
    return response.data
  },

  async getUser(id) {
    const response = await api.get(`/users/${id}`)
    return response.data
  }
}
