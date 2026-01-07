<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Goods Receipt Notes</h1>
        <p class="text-gray-600">Receive goods from vendors</p>
      </div>
      <div class="flex space-x-3">
        <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export Excel
        </button>
        <button v-if="canCreateGRN" @click="$router.push('/procurement/goods-receipts/create')" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
          Create GRN
        </button>
      </div>
    </div>

    <!-- Export Date Range -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
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
        <button @click="clearExportDates" class="text-sm text-green-600 hover:text-green-800">Clear</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="loadGRNs" class="w-full border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option value="DRAFT">Draft</option>
            <option value="QC_PENDING">QC Pending</option>
            <option value="QC_APPROVED">QC Approved</option>
            <option value="POSTED">Posted</option>
            <option value="REJECTED">Rejected</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
          <input v-model="filters.po_number" @input="loadGRNs" type="text" placeholder="Search PO..." class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input v-model="filters.from_date" @change="loadGRNs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input v-model="filters.to_date" @change="loadGRNs" type="date" class="w-full border-gray-300 rounded-lg">
        </div>
      </div>
    </div>

    <!-- GRNs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="grn in grns" :key="grn.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ grn.grn_number }}</div>
                <div class="text-xs text-gray-500" v-if="grn.invoice_number">Invoice: {{ grn.invoice_number }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(grn.receipt_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grn.po_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grn.vendor_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(grn.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ grn.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="viewGRN(grn)" class="text-blue-600 hover:text-blue-900">View</button>
              </td>
            </tr>
            <tr v-if="grns.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                No goods receipts found
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
import { useProcurementPermissions } from '@/composables/useProcurementPermissions'
import * as XLSX from 'xlsx'

const router = useRouter()
const { canCreateGRN } = useProcurementPermissions()

const grns = ref([])

const filters = ref({
  status: '',
  po_number: '',
  from_date: '',
  to_date: ''
})

const exportFilters = ref({
  start_date: '',
  end_date: ''
})

onMounted(async () => {
  await loadGRNs()
})

const loadGRNs = async () => {
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.po_number) params.po_number = filters.value.po_number
    if (filters.value.from_date) params.from_date = filters.value.from_date
    if (filters.value.to_date) params.to_date = filters.value.to_date

    const { data } = await api.get('/goods-receipts', { params })
    // Handle Laravel pagination response
    grns.value = data.data || data || []
  } catch (error) {
    console.error('Failed to load GRNs:', error)
    grns.value = []
  }
}

const getStatusClass = (status) => {
  const classes = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'QUALITY_CHECK': 'bg-yellow-100 text-yellow-800',
    'APPROVED': 'bg-green-100 text-green-800',
    'POSTED': 'bg-blue-100 text-blue-800',
    'REJECTED': 'bg-red-100 text-red-800'
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

const exportToExcel = () => {
  try {
    let dataToExport = grns.value

    // Filter by export date range if specified
    if (exportFilters.value.start_date || exportFilters.value.end_date) {
      dataToExport = dataToExport.filter(grn => {
        const grnDate = new Date(grn.receipt_date)
        if (exportFilters.value.start_date && grnDate < new Date(exportFilters.value.start_date)) {
          return false
        }
        if (exportFilters.value.end_date && grnDate > new Date(exportFilters.value.end_date)) {
          return false
        }
        return true
      })
    }

    if (dataToExport.length === 0) {
      alert('No data to export for the selected date range')
      return
    }

    const exportData = dataToExport.map(grn => ({
      'GRN Number': grn.grn_number,
      'Date': formatDate(grn.receipt_date),
      'PO Number': grn.po_number,
      'Vendor': grn.vendor_name,
      'Invoice Number': grn.invoice_number || '',
      'Status': grn.status,
      'Notes': grn.notes || ''
    }))

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    ws['!cols'] = [
      { wch: 20 },  // GRN Number
      { wch: 15 },  // Date
      { wch: 20 },  // PO Number
      { wch: 30 },  // Vendor
      { wch: 25 },  // Invoice Number
      { wch: 15 },  // Status
      { wch: 40 }   // Notes
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Goods Receipt Notes')

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Goods_Receipt_Notes_${timestamp}.xlsx`

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

const viewGRN = (grn) => {
  router.push(`/procurement/goods-receipts/${grn.id}`)
}
</script>
