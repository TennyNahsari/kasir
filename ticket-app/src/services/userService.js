import api from '../utils/axios'

export default {
  // Get all users with filters
  getUsers(params = {}) {
    return api.get('/users', { params })
  },

  // Get user detail
  getUser(id) {
    return api.get(`/users/${id}`)
  },

  // Create user
  createUser(data) {
    return api.post('/users', data)
  },

  // Update user
  updateUser(id, data) {
    return api.put(`/users/${id}`, data)
  },

  // Delete user
  deleteUser(id) {
    return api.delete(`/users/${id}`)
  }
}
