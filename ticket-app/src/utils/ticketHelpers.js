// Status badge colors
export const getStatusClass = (status) => {
  const classes = {
    'OPEN': 'bg-blue-100 text-blue-800',
    'ASSIGNED': 'bg-purple-100 text-purple-800',
    'IN_PROGRESS': 'bg-yellow-100 text-yellow-800',
    'ON_HOLD': 'bg-orange-100 text-orange-800',
    'RESOLVED': 'bg-green-100 text-green-800',
    'CLOSED': 'bg-gray-100 text-gray-800',
    'CANCELLED': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Priority badge colors
export const getPriorityClass = (priority) => {
  return priority === 'HIGH' 
    ? 'bg-red-100 text-red-800' 
    : 'bg-gray-100 text-gray-800'
}

// Type badge colors
export const getTypeClass = (type) => {
  return type === 'INCIDENT'
    ? 'bg-red-100 text-red-800'
    : 'bg-blue-100 text-blue-800'
}

// Asset status colors
export const getAssetStatusClass = (status) => {
  const classes = {
    'AVAILABLE': 'bg-green-100 text-green-800',
    'ASSIGNED': 'bg-blue-100 text-blue-800',
    'IN_USE': 'bg-purple-100 text-purple-800',
    'MAINTENANCE': 'bg-yellow-100 text-yellow-800',
    'DAMAGED': 'bg-red-100 text-red-800',
    'RETIRED': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Format status text
export const formatStatus = (status) => {
  return status.replace(/_/g, ' ')
}

// Format date
export const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Format datetime
export const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Check if ticket is overdue
export const isOverdue = (ticket) => {
  if (!ticket.sla_due_date) return false
  if (['RESOLVED', 'CLOSED', 'CANCELLED'].includes(ticket.status)) return false
  return new Date(ticket.sla_due_date) < new Date()
}

// Get time remaining
export const getTimeRemaining = (dueDate) => {
  if (!dueDate) return null
  
  const now = new Date()
  const due = new Date(dueDate)
  const diff = due - now
  
  if (diff < 0) return 'Overdue'
  
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(hours / 24)
  
  if (days > 0) return `${days} day${days > 1 ? 's' : ''}`
  if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''}`
  return 'Less than 1 hour'
}

// Ticket status options
export const TICKET_STATUSES = [
  { value: 'OPEN', label: 'Open' },
  { value: 'ASSIGNED', label: 'Assigned' },
  { value: 'IN_PROGRESS', label: 'In Progress' },
  { value: 'ON_HOLD', label: 'On Hold' },
  { value: 'RESOLVED', label: 'Resolved' },
  { value: 'CLOSED', label: 'Closed' },
  { value: 'CANCELLED', label: 'Cancelled' }
]

// Ticket type options
export const TICKET_TYPES = [
  { value: 'INCIDENT', label: 'Incident' },
  { value: 'MAINTENANCE', label: 'Maintenance' }
]

// Priority options
export const PRIORITY_OPTIONS = [
  { value: 'NORMAL', label: 'Normal' },
  { value: 'HIGH', label: 'High' }
]
