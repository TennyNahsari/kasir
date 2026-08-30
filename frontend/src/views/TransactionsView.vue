<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $t('transactions.title') }}</h2>
      </div>
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <button 
          @click="loadTransactions" 
          class="flex-1 sm:flex-none bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 text-sm font-semibold transition-all active:scale-95 shadow-sm"
        >
          <span class="text-base">🔄</span> Refresh
        </button>
        <button @click="exportToExcel" class="flex-1 sm:flex-none bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 text-sm font-semibold shadow-sm">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          {{ $t('transactions.exportExcel') }}
        </button>
      </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4 sm:mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 sm:gap-4">
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
            <option value="fnb">{{ $t('transactions.fnb') }}</option>
          </select>
        </div>
        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.orderTypeLabel') }}</label>
          <select v-model="orderType" class="input text-sm">
            <option value="">{{ $t('transactions.all') }}</option>
            <option value="dine_in">🍽️ {{ $t('transactions.dineIn') }}</option>
            <option value="take_away">🛍️ {{ $t('transactions.takeAway') }}</option>
            <option value="online">🛵 {{ $t('transactions.online') }}</option>
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
            <td class="px-4 py-3 text-sm font-mono">
              <div class="flex flex-col gap-0.5 items-start font-sans">
                <span class="font-mono font-bold text-gray-900 text-sm">{{ transaction.transaction_no }}</span>

                <!-- Customer Name -->
                <span v-if="transaction.customer_name" class="text-xs font-semibold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 inline-flex items-center gap-1">
                  👤 {{ transaction.customer_name }}
                </span>

                <!-- Table Number -->
                <span v-if="getTableDisplay(transaction)" class="text-xs font-semibold text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 inline-flex items-center gap-1">
                  🪑 {{ getTableDisplay(transaction) }}
                </span>

                <!-- Booking Code -->
                <span v-if="transaction.booking_code" class="text-xs font-semibold text-blue-800 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 inline-flex items-center gap-1">
                  🔑 {{ $t('transactions.bookingCode') }}: {{ transaction.booking_code }}
                </span>

                <!-- Payment Due At Badge -->
                <span v-if="transaction.payment_due_at" class="text-xs font-semibold text-amber-900 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 inline-flex items-center gap-1">
                  ⏰ {{ $t('transactions.paymentDeadline') }}: Jam {{ formatTimeOnly(transaction.payment_due_at) }}
                </span>

                <!-- Addon Order Badge -->
                <span 
                  v-if="hasAddonOrder(transaction)" 
                  @click="openConfirmAddonModal(transaction)"
                  class="text-[11px] font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 px-1.5 py-0.5 rounded border border-amber-300 inline-flex items-center gap-1 animate-pulse cursor-pointer"
                  :title="$t('transactions.confirmAddonButton')"
                >
                  🔔 {{ $t('transactions.addonOrder') }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-sm">{{ formatDate(transaction.created_at) }}</td>
            <td class="px-4 py-3 text-sm">
              <div class="flex flex-col gap-1 items-start">
                <span :class="[
                  'px-2 py-0.5 rounded text-xs font-medium',
                  transaction.business_type === 'retail' ? 'bg-blue-100 text-blue-700' :
                  transaction.business_type === 'minimarket' ? 'bg-green-100 text-green-700' :
                  'bg-orange-100 text-orange-700'
                ]">
                  {{ getBusinessTypeLabel(transaction.business_type) }}
                </span>
                <span 
                  v-if="transaction.order_type"
                  :class="[
                    'px-2 py-0.5 rounded text-[11px] font-semibold border',
                    transaction.order_type === 'take_away' ? 'bg-orange-50 text-orange-800 border-orange-200' :
                    transaction.order_type === 'online' ? 'bg-purple-50 text-purple-800 border-purple-200' :
                    'bg-blue-50 text-blue-800 border-blue-200'
                  ]"
                >
                  {{ transaction.order_type === 'take_away' ? '🛍️ Take Away' : (transaction.order_type === 'online' ? '🛵 Online' : '🍽️ Dine In') }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
              {{ transaction.user?.name || $t('transactions.customerOrder') }}
            </td>
            <td class="px-4 py-3 text-sm capitalize">
              <div class="flex flex-col gap-1 items-start">
                <span>{{ transaction.payment_method || '-' }}</span>
                <button
                  v-if="transaction.payment_proof"
                  @click.stop.prevent="openProofsModal(transaction)"
                  class="mt-1 flex items-center gap-1.5 px-2 py-0.5 rounded bg-green-50 hover:bg-green-100 border border-green-200 text-green-700 text-[11px] font-semibold transition-all shadow-sm cursor-pointer"
                  :title="$t('transactions.viewProofsTooltip')"
                >
                  <span>🖼️</span> {{ $t('transactions.viewProofsButton', { count: getPaymentProofs(transaction).length }) }}
                  <span class="flex h-1.5 w-1.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                  </span>
                </button>
              </div>
            </td>
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
                {{ formatStatusLabel(transaction.status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-center">
              <div class="flex items-center justify-center gap-2">
                <button 
                  v-if="transaction.has_unconfirmed_addon"
                  @click="openConfirmAddonModal(transaction)" 
                  class="text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 px-2 py-1 rounded transition-all active:scale-95 flex items-center gap-1 shadow-sm"
                  title="Klik untuk melihat & mengonfirmasi order tambahan"
                >
                  ✓ Konfirmasi Order
                </button>
                <button @click="viewDetail(transaction)" class="text-blue-600 hover:text-blue-700 font-medium">
                  {{ $t('transactions.detail') }}
                </button>
                <button @click="handleBrowserPrint(transaction)" class="text-amber-600 hover:text-amber-700 font-medium" :title="$t('transactions.printReceipt')">
                  {{ $t('transactions.printReceipt') }}
                </button>
                <button 
                  v-if="['pending', 'processed', 'delivered'].includes(transaction.status)"
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
          <div class="flex flex-col gap-0.5 items-start">
            <div class="font-mono text-xs font-bold text-gray-900">{{ transaction.transaction_no }}</div>
            <div class="text-xs text-gray-600">{{ formatDate(transaction.created_at) }}</div>
            
            <!-- Customer Name -->
            <span v-if="transaction.customer_name" class="text-xs font-semibold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 inline-flex items-center gap-1">
              👤 {{ transaction.customer_name }}
            </span>

            <!-- Table Number -->
            <span v-if="getTableDisplay(transaction)" class="text-xs font-semibold text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 inline-flex items-center gap-1">
              🪑 {{ getTableDisplay(transaction) }}
            </span>

            <!-- Booking Code -->
            <span v-if="transaction.booking_code" class="text-xs font-semibold text-blue-800 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 inline-flex items-center gap-1">
              🔑 Kode Booking: {{ transaction.booking_code }}
            </span>

            <!-- Addon Order Badge -->
            <span v-if="hasAddonOrder(transaction)" class="text-[11px] font-bold text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded border border-amber-300 inline-flex items-center gap-1 animate-pulse">
              🔔 Order Tambahan
            </span>
          </div>
          <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
            <div class="flex items-center gap-1.5 flex-wrap justify-end">
              <span :class="[
                'px-2 py-0.5 rounded text-xs font-medium',
                transaction.business_type === 'retail' ? 'bg-blue-100 text-blue-700' :
                transaction.business_type === 'minimarket' ? 'bg-green-100 text-green-700' :
                'bg-orange-100 text-orange-700'
              ]">
                {{ getBusinessTypeLabel(transaction.business_type) }}
              </span>
              <span 
                v-if="transaction.order_type"
                :class="[
                  'px-2 py-0.5 rounded text-[11px] font-semibold border',
                  transaction.order_type === 'take_away' ? 'bg-orange-50 text-orange-800 border-orange-200' :
                  transaction.order_type === 'online' ? 'bg-purple-50 text-purple-800 border-purple-200' :
                  'bg-blue-50 text-blue-800 border-blue-200'
                ]"
              >
                {{ transaction.order_type === 'take_away' ? '🛍️ Take Away' : (transaction.order_type === 'online' ? '🛵 Online' : '🍽️ Dine In') }}
              </span>
            </div>
            
            <!-- Proof Trigger Button for Mobile -->
            <button
              v-if="transaction.payment_proof"
              @click.stop.prevent="openProofsModal(transaction)"
              class="mt-1 inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-green-50 border border-green-200 text-green-700 text-[11px] font-semibold shadow-sm transition-all cursor-pointer"
            >
              <span>🖼️</span> {{ $t('transactions.viewProofsButtonMobile', { count: getPaymentProofs(transaction).length }) }}
              <span class="flex h-1.5 w-1.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
              </span>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
          <div>
            <span class="text-gray-600">{{ $t('transactions.cashier') }}:</span>
            <span class="font-semibold ml-1 text-gray-900">{{ transaction.user?.name || $t('transactions.customerOrder') }}</span>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('transactions.payment') }}:</span>
            <span class="font-medium ml-1 capitalize">{{ transaction.payment_method || '-' }}</span>
          </div>
          <div v-if="transaction.payment_due_at" class="col-span-2">
            <span class="text-amber-800 font-semibold text-xs bg-amber-50 px-2 py-0.5 rounded border border-amber-200 inline-flex items-center gap-1">
              ⏰ {{ $t('transactions.paymentDeadline') }}: Jam {{ formatTimeOnly(transaction.payment_due_at) }} WIB
            </span>
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
            <button 
              v-if="transaction.has_unconfirmed_addon"
              @click="openConfirmAddonModal(transaction)" 
              class="text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 px-2 py-1 rounded-lg transition-all active:scale-95 shadow-sm"
            >
              ✓ {{ $t('transactions.confirmAddonButton') }}
            </button>
            <span :class="[
              'px-2 py-1 rounded text-xs font-medium',
              transaction.status === 'completed' ? 'bg-green-100 text-green-700' :
              transaction.status === 'void' ? 'bg-red-100 text-red-700' :
              transaction.status === 'refund' ? 'bg-yellow-100 text-yellow-700' :
              transaction.status === 'processed' ? 'bg-blue-100 text-blue-700' :
              transaction.status === 'delivered' ? 'bg-purple-100 text-purple-700' :
              'bg-gray-100 text-gray-700'
            ]">
              {{ formatStatusLabel(transaction.status) }}
            </span>
            <button 
              v-if="['pending', 'processed', 'delivered'].includes(transaction.status)"
              @click="showStatusModal(transaction)" 
              class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-lg hover:bg-green-100"
            >
              {{ $t('transactions.changeStatus') }}
            </button>
            <button @click="viewDetail(transaction)" class="text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100">
              {{ $t('transactions.detail') }}
            </button>
            <button @click="handleBrowserPrint(transaction)" class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-1.5 rounded-lg hover:bg-amber-100 border border-amber-200">
              {{ $t('transactions.printReceipt') }}
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

    <!-- Pagination (Visible for both Desktop and Mobile) -->
    <Pagination
      v-if="totalItems > 0"
      :current-page="currentPage"
      :last-page="lastPage"
      :per-page="perPage"
      :total="totalItems"
      :from="fromItem"
      :to="toItem"
      @update:currentPage="onPageChange"
      @update:perPage="onPerPageChange"
    />

    <!-- Detail Modal -->
  <div v-if="showDetail && selectedTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="card max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-start mb-3 sm:mb-4">
        <div>
          <h3 class="text-lg sm:text-xl font-bold">{{ $t('transactions.detailTitle') }}</h3>
          <p class="text-xs sm:text-sm text-gray-600">{{ selectedTransaction.transaction_no }}</p>
        </div>
        <button @click="showDetail = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
          ×
        </button>
      </div>

      <div class="space-y-3 sm:space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
          <div>
            <div class="text-gray-600">{{ $t('transactions.date') }}</div>
            <div class="font-medium">{{ formatDate(selectedTransaction.created_at) }}</div>
          </div>
          <div>
            <div class="text-gray-600">{{ selectedTransaction.customer_name ? 'Nama Pemesan' : $t('transactions.cashier') }}</div>
            <div class="font-bold text-gray-900">{{ selectedTransaction.customer_name || selectedTransaction.user?.name || $t('transactions.customer') }}</div>
          </div>
          <div>
            <div class="text-gray-600">{{ $t('transactions.paymentMethod') }}</div>
            <div class="font-medium capitalize">{{ selectedTransaction.payment_method || '-' }}</div>
          </div>
          <div>
            <div class="text-gray-600">{{ $t('transactions.status') }}</div>
            <div class="font-medium capitalize">{{ selectedTransaction.status }}</div>
          </div>
          <div>
            <div class="text-gray-600">Tipe Pesanan</div>
            <div class="font-medium">
              <span 
                v-if="selectedTransaction.order_type"
                :class="selectedTransaction.order_type === 'take_away' ? 'bg-orange-100 text-orange-800 border-orange-200' : 'bg-blue-100 text-blue-800 border-blue-200'"
                class="px-2 py-0.5 rounded text-xs font-bold border"
              >
                {{ selectedTransaction.order_type === 'take_away' ? '🛍️ Take Away (Bawa Pulang)' : '🍽️ Dine In (Makan di Tempat)' }}
              </span>
              <span v-else class="text-gray-400">-</span>
            </div>
          </div>
          <div v-if="getTableDisplay(selectedTransaction)">
            <div class="text-gray-600">{{ $t('transactions.tableNumber') }}</div>
            <div class="font-bold text-emerald-800 flex items-center gap-1 mt-0.5">
              <span class="bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded text-xs">
                🪑 {{ getTableDisplay(selectedTransaction) }}
              </span>
            </div>
          </div>
          <div v-if="selectedTransaction.booking_code">
            <div class="text-gray-600">{{ $t('transactions.bookingCode') }}</div>
            <div class="font-bold text-blue-800 flex items-center gap-1 mt-0.5">
              <span class="bg-blue-50 border border-blue-200 px-2 py-0.5 rounded text-xs">
                🔑 {{ selectedTransaction.booking_code }}
              </span>
            </div>
          </div>
          <div v-if="selectedTransaction.payment_due_at">
            <div class="text-gray-600">{{ $t('transactions.paymentDeadline') }}</div>
            <div class="font-bold text-amber-900 flex items-center gap-1 mt-0.5">
              <span class="bg-amber-50 border border-amber-200 px-2 py-0.5 rounded text-xs font-mono">
                ⏰ Jam {{ formatTimeOnly(selectedTransaction.payment_due_at) }} WIB ({{ formatDate(selectedTransaction.payment_due_at) }})
              </span>
            </div>
          </div>
        </div>

        <!-- Notes Section -->
        <div v-if="selectedTransaction.notes" class="bg-blue-50 border border-blue-200 rounded-lg p-2 sm:p-3">
          <div class="text-xs text-blue-600 font-semibold mb-1">{{ $t('transactions.notes') }}:</div>
          <div class="text-xs sm:text-sm text-gray-700">{{ selectedTransaction.notes }}</div>
        </div>

        <div class="border-t pt-3 sm:pt-4">
          <h4 class="font-semibold mb-2 sm:mb-3 text-sm sm:text-base">{{ $t('transactions.items') }}</h4>
          <div class="space-y-2">
            <div
              v-for="item in selectedTransaction.items"
              :key="item.id"
              class="flex justify-between text-xs sm:text-sm"
            >
              <div class="flex-1 min-w-0 pr-2">
                <div class="font-medium truncate">{{ item.product_name }}</div>
                <div class="text-gray-600">
                  {{ formatCurrency(item.price) }} x {{ item.quantity }}
                </div>
              </div>
              <div class="text-right font-medium flex-shrink-0">
                {{ formatCurrency(item.subtotal) }}
              </div>
            </div>
          </div>
        </div>

        <div class="border-t pt-3 sm:pt-4 space-y-1.5 sm:space-y-2">
          <div class="flex justify-between text-xs sm:text-sm">
            <span>{{ $t('transactions.subtotal') }}</span>
            <span>{{ formatCurrency(selectedTransaction.subtotal) }}</span>
          </div>
          <div v-if="selectedTransaction.discount > 0" class="flex justify-between text-xs sm:text-sm">
            <span>{{ $t('transactions.discount') }}</span>
            <span class="text-red-600">-{{ formatCurrency(selectedTransaction.discount) }}</span>
          </div>
          <div v-if="selectedTransaction.tax > 0" class="flex justify-between text-xs sm:text-sm">
            <span>{{ $t('transactions.tax') }}</span>
            <span>{{ formatCurrency(selectedTransaction.tax) }}</span>
          </div>
          <div class="flex justify-between font-bold text-base sm:text-lg border-t pt-2">
            <span>{{ $t('transactions.total') }}</span>
            <span class="text-primary-600">{{ formatCurrency(selectedTransaction.total) }}</span>
          </div>
          <div class="flex justify-between text-xs sm:text-sm">
            <span>{{ $t('transactions.paid') }}</span>
            <span>{{ formatCurrency(selectedTransaction.paid_amount) }}</span>
          </div>
          <div class="flex justify-between text-xs sm:text-sm">
            <span>{{ $t('transactions.change') }}</span>
            <span class="text-green-600">{{ formatCurrency(selectedTransaction.change_amount) }}</span>
          </div>
        </div>



        <div class="border-t pt-3 sm:pt-4">
          <button @click="handleBrowserPrint(selectedTransaction)" class="btn bg-amber-600 hover:bg-amber-700 text-white w-full text-sm sm:text-base font-semibold flex items-center justify-center gap-2 shadow-sm">
            {{ $t('transactions.printReceipt') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Status Modal for F&B -->
  <div v-if="showStatusUpdate && statusTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="card max-w-md w-full">
      <div class="flex justify-between items-start mb-3 sm:mb-4">
        <div>
          <h3 class="text-lg sm:text-xl font-bold">{{ $t('transactions.changeStatusTitle') }}</h3>
          <p class="text-xs sm:text-sm text-gray-600">{{ statusTransaction.transaction_no }}</p>
        </div>
        <button @click="showStatusUpdate = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
          ×
        </button>
      </div>

      <div class="space-y-3 sm:space-y-4">
        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.currentStatus') }}</label>
          <div class="font-medium text-base sm:text-lg capitalize">{{ statusTransaction.status }}</div>
        </div>

        <div>
          <label class="label text-xs sm:text-sm">{{ $t('transactions.changeToStatus') }}</label>
          <select v-model="newStatus" class="input text-sm">
            <option value="pending">{{ $t('transactions.statusPending') }}</option>
            <option value="processed">{{ $t('transactions.statusProcessed') }}</option>
            <option value="delivered">{{ $t('transactions.statusDelivered') }}</option>
            <option value="completed">{{ $t('transactions.statusCompleted') }}</option>
            <option value="void">Void / Cancelled (Dibatalkan)</option>
          </select>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
          <button @click="showStatusUpdate = false" class="btn btn-secondary flex-1 text-sm sm:text-base">
            {{ $t('transactions.cancel') }}
          </button>
          <button @click="updateStatus" class="btn btn-primary flex-1 text-sm sm:text-base">
            {{ $t('transactions.save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  </div>

  <!-- Add-on Order Confirmation Modal -->
  <div v-if="showAddonModal && addonModalTransaction" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-4 sm:p-6 space-y-4 animate-fade-in">
      <div class="flex items-center justify-between border-b pb-3">
        <div class="flex items-center gap-2">
          <span class="text-xl">🔔</span>
          <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $t('transactions.addonModalTitle') }}</h3>
        </div>
        <button @click="showAddonModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
      </div>

      <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 space-y-1.5 text-xs sm:text-sm">
        <div class="flex justify-between text-amber-950 font-semibold">
          <span>{{ $t('transactions.transactionNo') }}:</span>
          <span>{{ addonModalTransaction.transaction_no }}</span>
        </div>
        <div v-if="getTableDisplay(addonModalTransaction)" class="flex justify-between text-amber-900">
          <span>{{ $t('transactions.tableLocation') }}:</span>
          <span>🪑 {{ getTableDisplay(addonModalTransaction) }}</span>
        </div>
        <div v-if="addonModalTransaction.customer_name" class="flex justify-between text-amber-900">
          <span>{{ $t('transactions.customerName') }}:</span>
          <span>👤 {{ addonModalTransaction.customer_name }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="label text-xs sm:text-sm font-semibold text-gray-700">{{ $t('transactions.addonDetails') }}:</label>
        <div class="p-3 bg-gray-50 border rounded-lg space-y-1 text-xs sm:text-sm">
          <div v-if="addonModalTransaction.addon_summary" class="flex flex-wrap gap-1.5">
            <span 
              v-for="(itemStr, idx) in addonModalTransaction.addon_summary.split('|')" 
              :key="idx"
              class="inline-block px-2.5 py-1 bg-amber-100 text-amber-900 font-bold rounded-md border border-amber-300 text-xs"
            >
              {{ itemStr.trim() }}
            </span>
          </div>
          <div v-else class="text-gray-500 italic">
            Ada penambahan menu baru pada transaksi meja ini.
          </div>
        </div>
      </div>

      <div class="flex justify-between items-center pt-2 border-t text-sm font-semibold">
        <span class="text-gray-600">{{ $t('transactions.newTotalBill') }}:</span>
        <span class="text-emerald-700 font-bold text-base sm:text-lg">{{ formatCurrency(addonModalTransaction.total) }}</span>
      </div>

      <div class="flex flex-col-reverse sm:flex-row gap-2 pt-2">
        <button @click="showAddonModal = false" class="btn btn-secondary flex-1 text-sm">
          {{ $t('transactions.cancel') }}
        </button>
        <button @click="executeConfirmAddon" class="btn bg-amber-500 hover:bg-amber-600 text-white flex-1 text-sm font-bold shadow">
          ✓ {{ $t('transactions.acceptConfirm') }}
        </button>
      </div>
    </div>
  </div>

  <!-- PROOFS MODAL -->
  <div
    v-if="showProofsModal && activeProofsTransaction"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-[fadeIn_0.2s_ease-out]"
    @click.self="closeProofsModal"
  >
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[85vh] overflow-hidden shadow-2xl flex flex-col animate-[scaleUp_0.2s_ease-out]">
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
        <div class="text-left">
          <h3 class="text-base font-bold text-gray-900">
            {{ $t('transactions.paymentProofTitle') }}
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">
            {{ $t('transactions.transactionNo') }} #{{ activeProofsTransaction.transaction_no }} ({{ activeProofsTransaction.customer_name || $t('transactions.customer') }})
          </p>
        </div>
        <button 
          @click="closeProofsModal"
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-full transition-colors cursor-pointer text-xl leading-none"
        >
          ×
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-4">
        <p class="text-xs text-gray-500 leading-relaxed bg-blue-50 border border-blue-100 text-blue-800 p-3 rounded-lg flex items-start gap-2 text-left">
          <span>💡</span>
          <span>{{ $t('transactions.paymentProofHelpText') }}</span>
        </p>

        <div class="grid grid-cols-2 gap-4">
          <div 
            v-for="(proof, index) in getPaymentProofs(activeProofsTransaction)" 
            :key="index"
            class="group relative border border-gray-200 rounded-xl overflow-hidden bg-gray-50 hover:shadow transition-shadow"
          >
            <!-- Proof image -->
            <a 
              :href="getPaymentProofUrl(proof)" 
              target="_blank" 
              rel="noopener noreferrer"
              class="block aspect-[3/4] overflow-hidden bg-black"
            >
              <img 
                :src="getPaymentProofUrl(proof)" 
                alt="Bukti Transfer" 
                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
              />
            </a>

            <!-- Image Index Label -->
            <div class="absolute top-2 left-2 bg-black/70 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
              {{ $t('transactions.proofLabel', { index: index + 1 }) }}
            </div>

            <!-- Delete action overlay -->
            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2 text-center">
              <button
                @click.stop.prevent="confirmDeleteProof(activeProofsTransaction, proof)"
                class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1.5 px-3 rounded-lg shadow transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <span>🗑️</span> {{ $t('transactions.deleteProofButton') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-5 py-4 border-t border-gray-100 flex justify-end bg-gray-50">
        <button 
          @click="closeProofsModal"
          class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-100 transition-colors cursor-pointer"
        >
          {{ $t('common.close') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Receipt Print Component (hidden, only for printing) -->
  <ReceiptPrint v-if="printTransaction" :transaction="printTransaction" />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import ReceiptPrint from '@/components/ReceiptPrint.vue'
import Pagination from '@/components/Pagination.vue'
import { printReceiptBrowser } from '@/utils/printer'
import * as XLSX from 'xlsx'

const { t, locale } = useI18n()
const authStore = useAuthStore()

const getReceiptLabels = () => {
  return {
    store: t('receipt.store'),
    transactionNo: t('receipt.transactionNo'),
    date: t('receipt.date'),
    cashier: t('receipt.cashier'),
    customer: t('receipt.customer'),
    table: t('receipt.table'),
    item: t('receipt.item'),
    qty: t('receipt.qty'),
    price: t('receipt.price'),
    total: t('receipt.total'),
    subtotal: t('receipt.subtotal'),
    discount: t('receipt.discount'),
    tax: t('receipt.tax'),
    totalAmount: t('receipt.totalAmount'),
    paid: t('receipt.paid'),
    change: t('receipt.change'),
    paymentMethod: t('receipt.paymentMethod'),
    thankYou: t('receipt.thankYou'),
    nonRefundable: t('receipt.nonRefundable')
  }
}

const handleBrowserPrint = (transaction) => {
  if (!transaction) return
  try {
    printReceiptBrowser(transaction, getReceiptLabels(), locale.value, authStore.user?.outlet)
  } catch (err) {
    console.error('Browser print failed:', err)
    alert(t('transactions.printFailed'))
  }
}
const transactions = ref([])
const dateFrom = ref('')
const dateTo = ref('')
const businessType = ref('')
const paymentMethod = ref('')
const orderType = ref('')

// Pagination state
const currentPage = ref(1)
const perPage = ref(10)
const lastPage = ref(1)
const totalItems = ref(0)
const fromItem = ref(0)
const toItem = ref(0)
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

const formatTimeOnly = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  const hours = String(d.getHours()).padStart(2, '0')
  const minutes = String(d.getMinutes()).padStart(2, '0')
  return `${hours}:${minutes}`
}

const formatStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processed: 'Processed',
    delivered: 'Delivered',
    completed: 'Completed',
    void: 'Cancelled (Void)',
    refund: 'Refund'
  }
  return labels[status] || status
}

const getBusinessTypeLabel = (type) => {
  const labels = {
    retail: 'Retail',
    minimarket: 'Minimarket',
    fnb: 'F&B'
  }
  return labels[type] || type
}

const hasAddonOrder = (transaction) => {
  if (!transaction) return false
  return Boolean(transaction.has_unconfirmed_addon)
}

const showAddonModal = ref(false)
const addonModalTransaction = ref(null)

const openConfirmAddonModal = (transaction) => {
  addonModalTransaction.value = transaction
  showAddonModal.value = true
}

const executeConfirmAddon = async () => {
  if (!addonModalTransaction.value) return
  try {
    await api.post(`/transactions/${addonModalTransaction.value.id}/confirm-addon`)
    addonModalTransaction.value.has_unconfirmed_addon = false
    addonModalTransaction.value.addon_summary = null
    showAddonModal.value = false
    await loadTransactions()
  } catch (error) {
    console.error('Failed to confirm addon order:', error)
    alert('Gagal mengonfirmasi order tambahan')
  }
}

const getTableDisplay = (transaction) => {
  if (!transaction) return null
  if (transaction.table?.table_number) {
    return `Meja ${transaction.table.table_number}`
  }
  if (transaction.notes) {
    const match = transaction.notes.match(/Meja:\s*([^|]+)/i)
    if (match && match[1]) {
      return `Meja ${match[1].trim()}`
    }
  }
  return null
}

const loadTransactions = async () => {
  try {
    // Auto cancel transactions created today whose payment deadline has passed
    try {
      await api.post('/transactions/cancel-expired', { today_only: true })
    } catch (cancelErr) {
      console.warn('Auto cancel expired check warning:', cancelErr)
    }

    const params = {
      page: currentPage.value,
      per_page: perPage.value
    }
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    if (businessType.value) params.business_type = businessType.value
    if (paymentMethod.value) params.payment_method = paymentMethod.value
    if (orderType.value) params.order_type = orderType.value

    console.log('Loading transactions with params:', params)
    const response = await api.get('/transactions', { params })
    const resData = response.data || {}
    transactions.value = resData.data || []
    currentPage.value = resData.current_page || 1
    lastPage.value = resData.last_page || 1
    perPage.value = resData.per_page || 10
    totalItems.value = resData.total || 0
    fromItem.value = resData.from || 0
    toItem.value = resData.to || 0
  } catch (error) {
    console.error('Failed to load transactions:', error)
  }
}

const onPageChange = (page) => {
  currentPage.value = page
  loadTransactions()
}

const onPerPageChange = (size) => {
  perPage.value = size
  currentPage.value = 1
  loadTransactions()
}

watch([dateFrom, dateTo, businessType, paymentMethod, orderType], () => {
  currentPage.value = 1
  loadTransactions()
})

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
    if (orderType.value) params.order_type = orderType.value

    // Fetch all transactions for export (no pagination)
    const response = await api.get('/transactions', { params })
    const exportData = (response.data.data || []).map(transaction => ({
      'Transaction No': transaction.transaction_no,
      'Customer Name': transaction.customer_name || '-',
      'Table Number': getTableDisplay(transaction) || '-',
      'Addon Order': transaction.has_unconfirmed_addon ? 'Yes' : 'No',
      'Date': formatDate(transaction.created_at),
      'Business Type': getBusinessTypeLabel(transaction.business_type),
      'Order Type': transaction.order_type === 'take_away' ? 'Take Away' : (transaction.order_type === 'dine_in' ? 'Dine In' : '-'),
      'Channel': transaction.user?.name || t('transactions.customerOrder'),
      'Payment Method': transaction.payment_method || '-',
      'Subtotal': transaction.subtotal,
      'Discount': transaction.discount || 0,
      'Tax': transaction.tax || 0,
      'Total': transaction.total,
      'Paid Amount': transaction.paid_amount || 0,
      'Change': transaction.change_amount || 0,
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
      { wch: 22 },  // Transaction No
      { wch: 20 },  // Customer Name
      { wch: 15 },  // Table Number
      { wch: 14 },  // Addon Order
      { wch: 20 },  // Date
      { wch: 15 },  // Business Type
      { wch: 15 },  // Order Type
      { wch: 20 },  // Channel
      { wch: 16 },  // Payment Method
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

const showProofsModal = ref(false)
const activeProofsTransaction = ref(null)

const openProofsModal = (transaction) => {
  activeProofsTransaction.value = transaction
  showProofsModal.value = true
}

const closeProofsModal = () => {
  activeProofsTransaction.value = null
  showProofsModal.value = false
}

const confirmDeleteProof = async (transaction, proofPath) => {
  if (!confirm(t('transactions.deleteProofConfirm'))) return
  try {
    await api.delete(`/transactions/${transaction.id}/payment-proof`, {
      params: { file_path: proofPath }
    })
    
    // Reload transactions to reflect deletion
    await loadTransactions()
    
    // Update local modal state
    if (activeProofsTransaction.value && activeProofsTransaction.value.id === transaction.id) {
      const currentArray = getPaymentProofs(activeProofsTransaction.value)
      const updatedArray = currentArray.filter(p => p !== proofPath)
      
      activeProofsTransaction.value.payment_proof = updatedArray.length > 0 ? JSON.stringify(updatedArray) : null
      
      if (updatedArray.length === 0) {
        closeProofsModal()
      }
    }
    
    if (selectedTransaction.value && selectedTransaction.value.id === transaction.id) {
      // Re-fetch detail
      const res = await api.get(`/transactions/${transaction.id}`)
      selectedTransaction.value = res.data
    }
    
    alert(t('transactions.deleteProofSuccess'))
  } catch (error) {
    console.error('Failed to delete payment proof:', error)
    alert(t('transactions.deleteProofFailed'))
  }
}

const getPaymentProofs = (transaction) => {
  if (!transaction || !transaction.payment_proof) return []
  const raw = transaction.payment_proof
  try {
    if (raw.startsWith('[') && raw.endsWith(']')) {
      return JSON.parse(raw)
    }
  } catch (e) {
    console.error('Failed to parse payment proof JSON:', e)
  }
  return [raw]
}

const getPaymentProofUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

onMounted(() => {
  // Default to empty date range so all transactions (including historical seeded data) load immediately
  dateFrom.value = ''
  dateTo.value = ''
  
  loadTransactions()
})
</script>
