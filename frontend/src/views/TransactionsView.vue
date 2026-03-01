<template>
  <div>
    <div class="flex justify-between items-center mb-4 sm:mb-6">
      <h2 class="text-xl sm:text-2xl font-bold">{{ $t('transactions.title') }}</h2>
      <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        {{ $t('transactions.exportExcel') }}
      </button>
    </div>

    <!-- Filter -->
    <div class="card mb-4 sm:mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4">
        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.dateFrom') }}</label>
          <input v-model="dateFrom" type="date" class="input text-sm">
        </div>
        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.dateTo') }}</label>
          <input v-model="dateTo" type="date" class="input text-sm">
        </div>
        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.businessType') }}</label>
          <select v-model="businessType" class="input text-sm">
            <option value="">{{ $t('transactions.all') }}</option>
            <option value="retail">{{ $t('transactions.retail') }}</option>
            <option value="minimarket">{{ $t('transactions.minimarket') }}</option>
            <option value="fnb">{{ $t('transactions.fnb') }}</option>
          </select>
        </div>
        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.paymentMethod') }}</label>
          <select v-model="paymentMethod" class="input text-sm">
            <option value="">{{ $t('transactions.all') }}</option>
            <option value="cash">{{ $t('transactions.cash') }}</option>
            <option value="qris">{{ $t('transactions.qris') }}</option>
            <option value="transfer">{{ $t('transactions.transfer') }}</option>
            <option value="ewallet">{{ $t('transactions.ewallet') }}</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="loadTransactions" class="btn btn-primary w-full text-sm">
            {{ $t('transactions.filter') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden lg:block card overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('transactions.transactionNo') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('transactions.date') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('transactions.type') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('transactions.cashier') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('transactions.payment') }}</th>
            <th class="px-4 py-3 text-right text-sm font-semibold">{{ $t('transactions.total') }}</th>
            <th class="px-4 py-3 text-center text-sm font-semibold">{{ $t('transactions.status') }}</th>
            <th class="px-4 py-3 text-center text-sm font-semibold">{{ $t('transactions.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-mono">{{ transaction.transaction_no }}</td>
            <td class="px-4 py-3 text-sm">{{ formatDate(transaction.created_at) }}</td>
            <td class="px-4 py-3 text-sm">
              <span :class="[
                'px-2 py-1 rounded text-xs font-medium',
                transaction.business_type === 'retail' ? 'bg-blue-100 text-blue-700' :
                transaction.business_type === 'minimarket' ? 'bg-green-100 text-green-700' :
                'bg-orange-100 text-orange-700'
              ]">
                {{ getBusinessTypeLabel(transaction.business_type) }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm">{{ transaction.user?.name || $t('transactions.customer') }}</td>
            <td class="px-4 py-3 text-sm capitalize">{{ transaction.payment_method || '-' }}</td>
            <td class="px-4 py-3 text-sm text-right font-semibold">
              {{ formatCurrency(transaction.total) }}
            </td>
            <td class="px-4 py-3 text-sm text-center">
              <span
                class="px-2 py-1 rounded text-xs font-medium"
                :class="{
                  'bg-green-100 text-green-700': transaction.status === 'completed',
                  'bg-red-100 text-red-700': transaction.status === 'void',
                  'bg-yellow-100 text-yellow-700': transaction.status === 'refund',
                  'bg-gray-100 text-gray-700': transaction.status === 'pending',
                  'bg-blue-100 text-blue-700': transaction.status === 'processed',
                  'bg-purple-100 text-purple-700': transaction.status === 'delivered'
                }"
              >
                {{ transaction.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-center">
              <div class="flex items-center justify-center gap-2">
                <button @click="viewDetail(transaction)" class="text-blue-600 hover:text-blue-700 font-medium">
                  {{ $t('transactions.detail') }}
                </button>
                <button 
                  v-if="transaction.business_type === 'fnb' && ['pending', 'processed', 'delivered'].includes(transaction.status)"
                  @click="showStatusModal(transaction)" 
                  class="text-green-600 hover:text-green-700 font-medium"
                >
                  {{ $t('transactions.changeStatus') }}
                </button>
                <button @click="deleteTransaction(transaction)" class="text-red-600 hover:text-red-700 font-medium">
                  {{ $t('transactions.delete') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="transactions.length === 0" class="text-center py-8 text-gray-500 text-sm">
        {{ $t('transactions.noTransactions') }}
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-3">
      <div v-for="transaction in transactions" :key="transaction.id" class="card p-3">
        <div class="flex justify-between items-start mb-2">
          <div>
            <div class="font-mono text-xs font-semibold text-gray-900">{{ transaction.transaction_no }}</div>
            <div class="text-xs text-gray-600">{{ formatDate(transaction.created_at) }}</div>
          </div>
          <span :class="[
            'px-2 py-0.5 rounded text-xs font-medium',
            transaction.business_type === 'retail' ? 'bg-blue-100 text-blue-700' :
            transaction.business_type === 'minimarket' ? 'bg-green-100 text-green-700' :
            'bg-orange-100 text-orange-700'
          ]">
            {{ getBusinessTypeLabel(transaction.business_type) }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
          <div>
            <span class="text-gray-600">{{ $t('transactions.cashier') }}:</span>
            <span class="font-medium ml-1">{{ transaction.user?.name || $t('transactions.customer') }}</span>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('transactions.payment') }}:</span>
            <span class="font-medium ml-1 capitalize">{{ transaction.payment_method || '-' }}</span>
          </div>
        </div>

        <!-- Notes Preview for Mobile -->
        <div v-if="transaction.notes" class="text-xs text-gray-600 mb-2 bg-blue-50 p-2 rounded">
          📝 {{ transaction.notes.length > 50 ? transaction.notes.substring(0, 50) + '...' : transaction.notes }}
        </div>

        <div class="flex justify-between items-center pt-3 border-t">
          <div>
            <div class="text-xs text-gray-600">{{ $t('transactions.total') }}</div>
            <div class="text-base font-bold text-primary-600">{{ formatCurrency(transaction.total) }}</div>
          </div>
          <div class="flex items-center gap-2">
            <span :class="[
              'px-2 py-1 rounded text-xs font-medium',
              transaction.status === 'completed' ? 'bg-green-100 text-green-700' :
              transaction.status === 'void' ? 'bg-red-100 text-red-700' :
              transaction.status === 'refund' ? 'bg-yellow-100 text-yellow-700' :
              transaction.status === 'processed' ? 'bg-blue-100 text-blue-700' :
              transaction.status === 'delivered' ? 'bg-purple-100 text-purple-700' :
              'bg-gray-100 text-gray-700'
            ]">
              {{ transaction.status }}
            </span>
            <button 
              v-if="transaction.business_type === 'fnb' && ['pending', 'processed', 'delivered'].includes(transaction.status)"
              @click="showStatusModal(transaction)" 
              class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-lg hover:bg-green-100"
            >
              {{ $t('transactions.changeStatus') }}
            </button>
            <button @click="viewDetail(transaction)" class="text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100">
              {{ $t('transactions.detail') }}
            </button>
            <button @click="deleteTransaction(transaction)" class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100">
              {{ $t('transactions.delete') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="transactions.length === 0" class="text-center py-8 text-gray-500 text-sm">
        {{ $t('transactions.noTransactions') }}
      </div>
    </div>

    <!-- Detail Modal -->
  <div v-if="showDetail && selectedTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="card max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-xl font-bold">{{ $t('transactions.detailTitle') }}</h3>
          <p class="text-sm text-gray-600">{{ selectedTransaction.transaction_no }}</p>
        </div>
        <button @click="showDetail = false" class="text-gray-500 hover:text-gray-700 text-2xl">
          ×
        </button>
      </div>

      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-gray-600">{{ $t('transactions.date') }}</div>
            <div class="font-medium">{{ formatDate(selectedTransaction.created_at) }}</div>
          </div>
          <div>
            <div class="text-gray-600">{{ $t('transactions.cashier') }}</div>
            <div class="font-medium">{{ selectedTransaction.user?.name || $t('transactions.customer') }}</div>
          </div>
          <div>
            <div class="text-gray-600">{{ $t('transactions.paymentMethod') }}</div>
            <div class="font-medium capitalize">{{ selectedTransaction.payment_method || '-' }}</div>
          </div>
          <div>
            <div class="text-gray-600">{{ $t('transactions.status') }}</div>
            <div class="font-medium capitalize">{{ selectedTransaction.status }}</div>
          </div>
        </div>

        <!-- Notes Section -->
        <div v-if="selectedTransaction.notes" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
          <div class="text-xs text-blue-600 font-semibold mb-1">{{ $t('transactions.notes') }}:</div>
          <div class="text-sm text-gray-700">{{ selectedTransaction.notes }}</div>
        </div>

        <div class="border-t pt-4">
          <h4 class="font-semibold mb-3">{{ $t('transactions.items') }}</h4>
          <div class="space-y-2">
            <div
              v-for="item in selectedTransaction.items"
              :key="item.id"
              class="flex justify-between text-sm"
            >
              <div>
                <div class="font-medium">{{ item.product_name }}</div>
                <div class="text-gray-600">
                  {{ formatCurrency(item.price) }} x {{ item.quantity }}
                </div>
              </div>
              <div class="text-right font-medium">
                {{ formatCurrency(item.subtotal) }}
              </div>
            </div>
          </div>
        </div>

        <div class="border-t pt-4 space-y-2">
          <div class="flex justify-between text-sm">
            <span>{{ $t('transactions.subtotal') }}</span>
            <span>{{ formatCurrency(selectedTransaction.subtotal) }}</span>
          </div>
          <div v-if="selectedTransaction.discount > 0" class="flex justify-between text-sm">
            <span>{{ $t('transactions.discount') }}</span>
            <span class="text-red-600">-{{ formatCurrency(selectedTransaction.discount) }}</span>
          </div>
          <div v-if="selectedTransaction.tax > 0" class="flex justify-between text-sm">
            <span>{{ $t('transactions.tax') }}</span>
            <span>{{ formatCurrency(selectedTransaction.tax) }}</span>
          </div>
          <div class="flex justify-between font-bold text-lg border-t pt-2">
            <span>{{ $t('transactions.total') }}</span>
            <span class="text-primary-600">{{ formatCurrency(selectedTransaction.total) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span>{{ $t('transactions.paid') }}</span>
            <span>{{ formatCurrency(selectedTransaction.paid_amount) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span>{{ $t('transactions.change') }}</span>
            <span class="text-green-600">{{ formatCurrency(selectedTransaction.change_amount) }}</span>
          </div>
        </div>

        <div class="border-t pt-4">
          <button @click="printReceipt" class="btn btn-primary w-full">
            {{ $t('transactions.printReceipt') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Status Modal for F&B -->
  <div v-if="showStatusUpdate && statusTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="card max-w-md w-full">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-xl font-bold">{{ $t('transactions.changeStatusTitle') }}</h3>
          <p class="text-sm text-gray-600">{{ statusTransaction.transaction_no }}</p>
        </div>
        <button @click="showStatusUpdate = false" class="text-gray-500 hover:text-gray-700 text-2xl">
          ×
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="label">{{ $t('transactions.currentStatus') }}</label>
          <div class="font-medium text-lg capitalize">{{ statusTransaction.status }}</div>
        </div>

        <div>
          <label class="label">{{ $t('transactions.changeToStatus') }}</label>
          <select v-model="newStatus" class="input">
            <option value="pending">{{ $t('transactions.statusPending') }}</option>
            <option value="processed">{{ $t('transactions.statusProcessed') }}</option>
            <option value="delivered">{{ $t('transactions.statusDelivered') }}</option>
            <option value="completed">{{ $t('transactions.statusCompleted') }}</option>
          </select>
        </div>

        <div class="flex gap-3">
          <button @click="showStatusUpdate = false" class="btn btn-secondary flex-1">
            {{ $t('transactions.cancel') }}
          </button>
          <button @click="updateStatus" class="btn btn-primary flex-1">
            {{ $t('transactions.save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  </div>

  <!-- Receipt Print Component (hidden, only for printing) -->
  <ReceiptPrint v-if="printTransaction" :transaction="printTransaction" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import ReceiptPrint from '@/components/ReceiptPrint.vue'
import * as XLSX from 'xlsx'

const { t } = useI18n()
const transactions = ref([])
const dateFrom = ref('')
const dateTo = ref('')
const businessType = ref('')
const paymentMethod = ref('')
const showDetail = ref(false)
const selectedTransaction = ref(null)
const showStatusUpdate = ref(false)
const statusTransaction = ref(null)
const newStatus = ref('')
const printTransaction = ref(null)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getBusinessTypeLabel = (type) => {
  const labels = {
    retail: 'Retail',
    minimarket: 'Minimarket',
    fnb: 'F&B'
  }
  return labels[type] || type
}

const loadTransactions = async () => {
  try {
    const params = {}
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    if (businessType.value) params.business_type = businessType.value
    if (paymentMethod.value) params.payment_method = paymentMethod.value
    // Backend will auto-filter by user's location_id or outlet_id
    // No need to send outlet_id from frontend

    console.log('Loading transactions with params:', params)
    const response = await api.get('/transactions', { params })
    console.log('Transactions loaded:', response.data.data?.length || 0, 'transactions')
    transactions.value = response.data.data
  } catch (error) {
    console.error('Failed to load transactions:', error)
  }
}

// Remove unused loadOutlets function
// const loadOutlets = async () => {
//   if (!isOwner.value) return
//   try {
//     const response = await api.get('/outlets')
//     outlets.value = response.data
//   } catch (error) {
//     console.error('Failed to load outlets:', error)
//   }
// }

const viewDetail = async (transaction) => {
  try {
    const response = await api.get(`/transactions/${transaction.id}`)
    selectedTransaction.value = response.data
    showDetail.value = true
  } catch (error) {
    console.error('Failed to load transaction detail:', error)
  }
}

const printReceipt = () => {
  printTransaction.value = selectedTransaction.value
  setTimeout(() => {
    window.print()
    printTransaction.value = null
  }, 100)
}

const showStatusModal = (transaction) => {
  statusTransaction.value = transaction
  newStatus.value = transaction.status
  showStatusUpdate.value = true
}

const updateStatus = async () => {
  try {
    await api.put(`/transactions/${statusTransaction.value.id}`, {
      status: newStatus.value
    })
    
    showStatusUpdate.value = false
    statusTransaction.value = null
    await loadTransactions()
    alert(t('transactions.statusUpdateSuccess'))
  } catch (error) {
    console.error('Failed to update status:', error)
    alert(t('transactions.statusUpdateFailed'))
  }
}

const deleteTransaction = async (transaction) => {
  if (!confirm(t('transactions.deleteConfirm', { no: transaction.transaction_no }))) {
    return
  }

  try {
    await api.delete(`/transactions/${transaction.id}`)
    await loadTransactions()
    alert(t('transactions.deleteSuccess'))
  } catch (error) {
    console.error('Failed to delete transaction:', error)
    alert(t('transactions.deleteFailed'))
  }
}

const exportToExcel = async () => {
  try {
    // Use the same filters as the current view
    const params = {}
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    if (businessType.value) params.business_type = businessType.value
    if (paymentMethod.value) params.payment_method = paymentMethod.value

    // Fetch all transactions for export (no pagination)
    const response = await api.get('/transactions', { params })
    const exportData = (response.data.data || []).map(transaction => ({
      'Transaction No': transaction.transaction_no,
      'Date': formatDate(transaction.created_at),
      'Business Type': getBusinessTypeLabel(transaction.business_type),
      'Cashier': transaction.user?.name || t('transactions.customer'),
      'Payment Method': transaction.payment_method || '-',
      'Subtotal': transaction.subtotal,
      'Discount': transaction.discount || 0,
      'Tax': transaction.tax || 0,
      'Total': transaction.total,
      'Paid Amount': transaction.paid_amount,
      'Change': transaction.change_amount,
      'Status': transaction.status,
      'Notes': transaction.notes || ''
    }))

    if (exportData.length === 0) {
      alert(t('transactions.noDataToExport'))
      return
    }

    // Create workbook and worksheet
    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    // Set column widths
    ws['!cols'] = [
      { wch: 20 },  // Transaction No
      { wch: 20 },  // Date
      { wch: 15 },  // Business Type
      { wch: 20 },  // Cashier
      { wch: 15 },  // Payment Method
      { wch: 15 },  // Subtotal
      { wch: 12 },  // Discount
      { wch: 12 },  // Tax
      { wch: 15 },  // Total
      { wch: 15 },  // Paid Amount
      { wch: 12 },  // Change
      { wch: 12 },  // Status
      { wch: 30 }   // Notes
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Transactions')

    // Generate filename with date range
    const fromDate = dateFrom.value || 'all'
    const toDate = dateTo.value || 'all'
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Transactions_${fromDate}_to_${toDate}_${timestamp}.xlsx`

    // Write file
    XLSX.writeFile(wb, filename)
    alert(t('transactions.exportSuccess', { count: exportData.length }))
  } catch (error) {
    console.error('Export error:', error)
    alert(t('transactions.exportFailed'))
  }
}

onMounted(() => {
  // Set default date range (today)
  const today = new Date().toISOString().split('T')[0]
  dateFrom.value = today
  dateTo.value = today
  
  loadTransactions()
})
</script>
