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

// Asset condition colors
export const getConditionClass = (condition) => {
  const classes = {
    'NEW': 'bg-green-100 text-green-800',
    'GOOD': 'bg-blue-100 text-blue-800',
    'FAIR': 'bg-yellow-100 text-yellow-800',
    'POOR': 'bg-orange-100 text-orange-800',
    'BROKEN': 'bg-red-100 text-red-800'
  }
  return classes[condition] || 'bg-gray-100 text-gray-800'
}

// Format currency
export const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
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
export const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
// Calculate depreciation
export const calculateDepreciation = (purchasePrice, residualValue, usefulLifeYears, purchaseDate) => {
  if (!purchasePrice || !purchaseDate || !usefulLifeYears) {
    return {
      currentValue: 0,
      annualDepreciation: 0,
      ageYears: 0
    }
  }

  const purchaseDateObj = new Date(purchaseDate)
  const now = new Date()
  const ageYears = (now - purchaseDateObj) / (1000 * 60 * 60 * 24 * 365.25)
  
  const annualDepreciation = (purchasePrice - (residualValue || 0)) / usefulLifeYears
  
  if (ageYears >= usefulLifeYears) {
    return {
      currentValue: residualValue || 0,
      annualDepreciation,
      ageYears
    }
  }

  const totalDepreciation = annualDepreciation * ageYears
  const currentValue = purchasePrice - totalDepreciation

  return {
    currentValue: Math.max(currentValue, residualValue || 0),
    annualDepreciation,
    ageYears
  }
}

// Asset status options
export const ASSET_STATUSES = [
  { value: 'AVAILABLE', label: 'Available' },
  { value: 'ASSIGNED', label: 'Assigned' },
  { value: 'IN_USE', label: 'In Use' },
  { value: 'MAINTENANCE', label: 'Maintenance' },
  { value: 'DAMAGED', label: 'Damaged' },
  { value: 'RETIRED', label: 'Retired' }
]

// Asset condition options
export const ASSET_CONDITIONS = [
  { value: 'NEW', label: 'New' },
  { value: 'GOOD', label: 'Good' },
  { value: 'FAIR', label: 'Fair' },
  { value: 'POOR', label: 'Poor' },
  { value: 'BROKEN', label: 'Broken' }
]

// Movement types
export const MOVEMENT_TYPES = [
  { value: 'PURCHASED', label: 'Purchased' },
  { value: 'ASSIGNED', label: 'Assigned' },
  { value: 'RETURNED', label: 'Returned' },
  { value: 'TRANSFERRED', label: 'Transferred' },
  { value: 'MAINTENANCE', label: 'Maintenance' },
  { value: 'REPAIRED', label: 'Repaired' },
  { value: 'DAMAGED', label: 'Damaged' },
  { value: 'DISPOSED', label: 'Disposed' }
]
