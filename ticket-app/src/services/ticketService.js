import api from '../utils/axios'

export default {
  // Get all tickets with filters
  getTickets(params = {}) {
    return api.get('/tickets', { params })
  },

  // Get ticket detail
  getTicket(id) {
    return api.get(`/tickets/${id}`)
  },

  // Create ticket
  createTicket(data) {
    return api.post('/tickets', data)
  },

  // Update ticket
  updateTicket(id, data) {
    return api.put(`/tickets/${id}`, data)
  },

  // Delete ticket
  deleteTicket(id) {
    return api.delete(`/tickets/${id}`)
  },

  // Get my assets
  getMyAssets() {
    return api.get('/tickets/my-assets')
  },

  // Get ticket statistics
  getStatistics() {
    return api.get('/tickets/statistics')
  },

  // Get dashboard data
  getDashboard() {
    return api.get('/ticket-dashboard')
  },

  // Add worklog to ticket
  addWorklog(ticketId, data) {
    return api.post(`/tickets/${ticketId}/worklogs`, data)
  },

  // Upload attachment to ticket
  uploadAttachment(ticketId, formData) {
    return api.post(`/tickets/${ticketId}/attachments`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // Delete attachment
  deleteAttachment(ticketId, attachmentId) {
    return api.delete(`/tickets/${ticketId}/attachments/${attachmentId}`)
  }
}
