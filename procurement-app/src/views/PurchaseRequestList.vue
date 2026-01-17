<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Purchase Requests</h1>
        <p class="text-gray-600">Manage procurement requests</p>
      </div>
      <div class="flex space-x-3">
        <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export Excel
        </button>
        <button @click="$router.push('/procurement/purchase-requests/create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
          Create PR
        </button>
      </div>
    </div>

    <!-- Export Date Range -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-center gap-4">
        <span class="text-sm font-medium text-gray-700">Export Date Range:</span>
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">From:</label>
          <input v-model="exportFilters.start_date" type="date" class="border-gray-300 rounded-lg text-sm">
        </div>
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">To:</label>
          <input v-model="exportFilters.end_date" type="date" class="border-gray-300 rounded-lg text-sm">
        </div>
        <button @click="clearExportDates" class="text-sm text-blue-600 hover:text-blue-800">Clear</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="loadPRs" class="w-full border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option value="DRAFT">Draft</option>
            <option value="SUBMITTED">Submitted</option>
            <option value="APPROVED">Approved</option>
            <option value="REJECTED">Rejected</option>
            <option value="CANCELLED">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select v-model="filters.department_id" @change="loadPRs" class="w-full border-gray-300 rounded-lg">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input v-model="filters.from_date" @change="loadPRs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input v-model="filters.to_date" @change="loadPRs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
      </div>
    </div>

    <!-- PRs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PR Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested By</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="pr in prs" :key="pr.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ pr.pr_number }}</div>
                <div class="text-xs text-gray-500" v-if="pr.notes">{{ pr.notes.substring(0, 30) }}...</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(pr.request_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ pr.location?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ pr.requested_by_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(pr.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ pr.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ pr.items_count }} item(s)
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="viewPR(pr)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                <button v-if="pr.status === 'DRAFT' || pr.status === 'CANCELLED' || pr.status === 'REJECTED'" @click="deletePR(pr)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
            <tr v-if="prs.length === 0">
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                No purchase requests found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import * as XLSX from 'xlsx'

const router = useRouter()

const prs = ref([])
const departments = ref([])

const filters = ref({
  status: '',
  department_id: '',
  from_date: '',
  to_date: ''
})

const exportFilters = ref({
  start_date: '',
  end_date: ''
})

onMounted(async () => {
  await loadDepartments()
  await loadPRs()
})

const loadPRs = async () => {
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.department_id) params.department_id = filters.value.department_id
    if (filters.value.from_date) params.from_date = filters.value.from_date
    if (filters.value.to_date) params.to_date = filters.value.to_date

    const { data } = await api.get('/purchase-requests', { params })
    // Handle Laravel pagination response
    prs.value = data.data || data || []
  } catch (error) {
    console.error('Failed to load PRs:', error)
    prs.value = []
  }
}

const loadDepartments = async () => {
  try {
    const { data } = await api.get('/locations')
    // Filter only departments
    departments.value = data.filter(loc => loc.type === 'DEPARTMENT')
  } catch (error) {
    console.error('Failed to load departments:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'SUBMITTED': 'bg-blue-100 text-blue-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'REJECTED': 'bg-red-100 text-red-800',
    'CANCELLED': 'bg-red-100 text-red-800',
    'PARTIALLY_ORDERED': 'bg-yellow-100 text-yellow-800',
    'FULLY_ORDERED': 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const viewPR = (pr) => {
  router.push(`/procurement/purchase-requests/${pr.id}`)
}

const exportToExcel = () => {
  try {
    let dataToExport = prs.value

    // Filter by export date range if specified
    if (exportFilters.value.start_date || exportFilters.value.end_date) {
      dataToExport = dataToExport.filter(pr => {
        const prDate = new Date(pr.request_date)
        if (exportFilters.value.start_date && prDate < new Date(exportFilters.value.start_date)) {
          return false
        }
        if (exportFilters.value.end_date && prDate > new Date(exportFilters.value.end_date)) {
          return false
        }
        return true
      })
    }

    if (dataToExport.length === 0) {
      alert('No data to export for the selected date range')
      return
    }

    const exportData = dataToExport.map(pr => ({
      'PR Number': pr.pr_number,
      'Date': formatDate(pr.request_date),
      'Location': pr.location?.name || '-',
      'Requested By': pr.requested_by_name,
      'Status': pr.status,
      'Items Count': pr.items_count,
      'Notes': pr.notes || ''
    }))

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    ws['!cols'] = [
      { wch: 20 },  // PR Number
      { wch: 15 },  // Date
      { wch: 25 },  // Location
      { wch: 25 },  // Requested By
      { wch: 15 },  // Status
      { wch: 12 },  // Items Count
      { wch: 40 }   // Notes
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Purchase Requests')

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Purchase_Requests_${timestamp}.xlsx`

    XLSX.writeFile(wb, filename)
    alert(`Excel file exported successfully: ${filename} (${dataToExport.length} records)`)
  } catch (error) {
    console.error('Export error:', error)
    alert('Failed to export Excel file: ' + error.message)
  }
}

const clearExportDates = () => {
  exportFilters.value.start_date = ''
  exportFilters.value.end_date = ''
}

const deletePR = async (pr) => {
  if (!confirm(`Are you sure you want to delete PR ${pr.pr_number}?`)) return
  
  try {
    await api.delete(`/purchase-requests/${pr.id}`)
    alert('Purchase Request deleted successfully')
    await loadPRs()
  } catch (error) {
    alert('Failed to delete PR: ' + (error.response?.data?.message || error.message))
  }
}
</script>
