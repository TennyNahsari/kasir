<template>
  <div class="p-3 sm:p-4 lg:p-6">
    <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
      <div>
        <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-1 sm:mb-2">{{ $t('services.title') }}</h1>
        <p class="text-sm sm:text-base text-gray-600">{{ $t('services.subtitle') }}</p>
      </div>
      <div class="flex flex-wrap gap-2 w-full sm:w-auto">
        <button @click="exportToExcel" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2 text-sm active:scale-95 transition-transform">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span class="hidden sm:inline">{{ $t('services.exportExcel') }}</span>
          <span class="sm:hidden">Excel</span>
        </button>
        <button @click="showCreateModal = true" class="flex-1 sm:flex-none bg-purple-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-purple-700 flex items-center justify-center text-sm sm:text-base active:scale-95 transition-transform">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          {{ $t('services.addContract') }}
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6">
      <div class="bg-white rounded-lg shadow p-3 sm:p-4 lg:p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-xs sm:text-sm">{{ $t('services.statsActive') }}</p>
            <p class="text-lg sm:text-2xl font-bold text-green-600">{{ stats.total_active || 0 }}</p>
          </div>
          <div class="bg-green-100 p-2 sm:p-3 rounded-full">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-3 sm:p-4 lg:p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-xs sm:text-sm">{{ $t('services.statsExpiringSoon') }}</p>
            <p class="text-lg sm:text-2xl font-bold text-yellow-600">{{ stats.expiring_soon || 0 }}</p>
          </div>
          <div class="bg-yellow-100 p-2 sm:p-3 rounded-full">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-3 sm:p-4 lg:p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-xs sm:text-sm">{{ $t('services.statsPending') }}</p>
            <p class="text-lg sm:text-2xl font-bold text-blue-600">{{ stats.total_pending || 0 }}</p>
          </div>
          <div class="bg-blue-100 p-2 sm:p-3 rounded-full">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-3 sm:p-4 lg:p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-xs sm:text-sm">{{ $t('services.statsExpired') }}</p>
            <p class="text-lg sm:text-2xl font-bold text-red-600">{{ stats.expired || 0 }}</p>
          </div>
          <div class="bg-red-100 p-2 sm:p-3 rounded-full">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-4 sm:mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.searchLabel') }}</label>
          <input v-model="filters.search" type="text" :placeholder="$t('services.searchPlaceholder')"
            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.statusLabel') }}</label>
          <select v-model="filters.status"
            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">{{ $t('services.allStatus') }}</option>
            <option value="ACTIVE">{{ $t('services.active') }}</option>
            <option value="PENDING">{{ $t('services.pending') }}</option>
            <option value="EXPIRED">{{ $t('services.expired') }}</option>
            <option value="TERMINATED">{{ $t('services.terminated') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.contractTypeLabel') }}</label>
          <select v-model="filters.contract_type"
            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">{{ $t('services.allTypes') }}</option>
            <option value="RENTAL">{{ $t('services.rental') }}</option>
            <option value="SUBSCRIPTION">{{ $t('services.subscription') }}</option>
            <option value="MAINTENANCE">{{ $t('services.maintenanceType') }}</option>
            <option value="CONSULTING">{{ $t('services.consulting') }}</option>
            <option value="UTILITY">{{ $t('services.utility') }}</option>
            <option value="OTHER">{{ $t('services.other') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.vendorLabel') }}</label>
          <select v-model="filters.vendor_id"
            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">{{ $t('services.allVendors') }}</option>
            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
              {{ vendor.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-0 mt-3 sm:mt-4">
        <button @click="resetFilters" class="text-purple-600 hover:text-purple-800 text-sm py-2 sm:py-0">
          {{ $t('services.resetFilters') }}
        </button>
        <button @click="loadContracts" class="bg-purple-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-purple-700 text-sm active:scale-95 transition-transform">
          {{ $t('services.applyFilters') }}
        </button>
      </div>
    </div>

    <!-- Contracts Table - Desktop -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.contract') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.productService') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.vendor') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.pic') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.type') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.startEnd') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.valueLabel') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('services.statusLabel') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">{{ $t('services.loadingText') }}</td>
            </tr>
            <tr v-else-if="contracts.length === 0">
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">{{ $t('services.noContracts') }}</td>
            </tr>
            <tr v-else v-for="contract in contracts" :key="contract.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ contract.contract_number }}</div>
                <div v-if="contract.is_expiring_soon" class="text-xs text-yellow-600 font-medium mt-1">
                  ⚠️ {{ $t('services.expiresIn', {days: contract.days_until_expiry}) }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ contract.product?.name || 'N/A' }}</div>
                <div class="text-xs text-gray-500">{{ contract.product?.sku || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ contract.vendor?.name || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ contract.pic || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="badge badge-gray text-xs">{{ formatContractType(contract.contract_type) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(contract.start_date) }}</div>
                <div class="text-xs text-gray-500">{{ formatDate(contract.end_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ formatCurrency(contract.contract_value) }}</div>
                <div class="text-xs text-gray-500">{{ contract.billing_cycle }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(contract.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ contract.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <button @click="printBarcode(contract)" class="text-purple-600 hover:text-purple-900">
                    {{ $t('services.barcode') }}
                  </button>
                  <button @click="viewContract(contract.id)" class="text-purple-600 hover:text-purple-900">
                    {{ $t('services.viewDetails') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row items-center justify-between border-t gap-2 sm:gap-0">
        <div class="text-xs sm:text-sm text-gray-700">
          {{ $t('services.showing') }} {{ pagination.from }} {{ $t('services.to') }} {{ pagination.to }} {{ $t('services.of') }} {{ pagination.total }} {{ $t('services.contractsText') }}
        </div>
        <div class="flex space-x-2">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            {{ $t('services.previous') }}
          </button>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            {{ $t('services.next') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Contracts Mobile Card View -->
    <div class="lg:hidden space-y-3">
      <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        {{ $t('services.loadingText') }}
      </div>
      <div v-else-if="contracts.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        {{ $t('services.noContracts') }}
      </div>
      <div v-else v-for="contract in contracts" :key="contract.id" class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-start mb-3">
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900 text-sm">{{ contract.contract_number }}</h3>
            <p class="text-xs text-gray-600 mt-0.5">{{ contract.product?.name || 'N/A' }}</p>
            <p class="text-xs text-gray-500">{{ contract.product?.sku || 'N/A' }}</p>
          </div>
          <span :class="getStatusClass(contract.status)" class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap ml-2">
            {{ contract.status }}
          </span>
        </div>
        
        <div v-if="contract.is_expiring_soon" class="text-xs text-yellow-600 font-medium mb-2 bg-yellow-50 px-2 py-1 rounded">
          ⚠️ {{ $t('services.expiresIn', {days: contract.days_until_expiry}) }}
        </div>
        
        <div class="space-y-1 text-xs mb-3">
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('services.vendor') }}:</span>
            <span class="font-medium text-gray-900">{{ contract.vendor?.name || 'N/A' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('services.pic') }}:</span>
            <span class="font-medium text-gray-900">{{ contract.pic || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('services.type') }}:</span>
            <span class="badge badge-gray text-xs">{{ formatContractType(contract.contract_type) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('services.startEnd') }}:</span>
            <span class="font-medium text-gray-900">{{ formatDate(contract.start_date) }} - {{ formatDate(contract.end_date) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('services.valueLabel') }}:</span>
            <span class="font-medium text-gray-900">{{ formatCurrency(contract.contract_value) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('services.billingCycle') }}:</span>
            <span class="font-medium text-gray-900">{{ contract.billing_cycle }}</span>
          </div>
        </div>
        
        <div class="flex gap-2">
          <button @click="printBarcode(contract)" class="flex-1 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 active:scale-95 transition-transform">
            {{ $t('services.barcode') }}
          </button>
          <button @click="viewContract(contract.id)" class="flex-1 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 active:scale-95 transition-transform">
            {{ $t('services.viewDetails') }}
          </button>
        </div>
      </div>
      
      <!-- Mobile Pagination -->
      <div v-if="pagination.total > 0" class="bg-white rounded-lg shadow px-4 py-3">
        <div class="text-xs text-center text-gray-700 mb-2">
          {{ $t('services.showing') }} {{ pagination.from }} {{ $t('services.to') }} {{ pagination.to }} {{ $t('services.of') }} {{ pagination.total }} {{ $t('services.contractsText') }}
        </div>
        <div class="flex justify-center space-x-2">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            {{ $t('services.previous') }}
          </button>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            {{ $t('services.next') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Create Service Contract Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-2xl max-h-[95vh] overflow-y-auto">
        <h3 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">{{ $t('services.modalCreateTitle') }}</h3>
        <form @submit.prevent="createContract">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div class="sm:col-span-2 relative">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.productServiceRequired') }} <span class="text-red-500">*</span></label>
              <input 
                v-model="productSearch" 
                @focus="showProductDropdown = true"
                @input="filterProducts"
                @blur="handleProductBlur"
                type="text" 
                required 
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :placeholder="$t('services.productPlaceholder')"
                autocomplete="off"
              >
              <div 
                v-if="showProductDropdown && filteredProducts.length > 0" 
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 sm:max-h-60 overflow-y-auto"
              >
                <div 
                  v-for="product in filteredProducts" 
                  :key="product.id"
                  @click="selectProduct(product)"
                  class="px-4 py-2 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-0"
                >
                  <div class="font-medium text-gray-900">{{ product.name }}</div>
                  <div class="text-sm text-gray-500">SKU: {{ product.sku || 'N/A' }}</div>
                </div>
              </div>
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.vendorRequired') }} <span class="text-red-500">*</span></label>
              <select v-model="createForm.vendor_id" required
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">{{ $t('services.selectVendor') }}</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.locationOptional') }}</label>
              <select v-model="createForm.location_id"
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">{{ $t('services.selectLocation') }}</option>
                <option v-for="location in locations" :key="location.id" :value="location.id">
                  {{ location.name }}
                </option>
              </select>
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.picField') }}</label>
              <input v-model="createForm.pic" type="text" :placeholder="$t('services.picPlaceholder')"
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.contractTypeRequired') }} <span class="text-red-500">*</span></label>
              <select v-model="createForm.contract_type" required
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">{{ $t('services.selectType') }}</option>
                <option value="RENTAL">{{ $t('services.rentalType') }}</option>
                <option value="SUBSCRIPTION">{{ $t('services.subscriptionType') }}</option>
                <option value="MAINTENANCE">{{ $t('services.maintenanceTypeOption') }}</option>
                <option value="CONSULTING">{{ $t('services.consultingType') }}</option>
                <option value="UTILITY">{{ $t('services.utilityType') }}</option>
                <option value="OTHER">{{ $t('services.otherType') }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.billingCycleRequired') }} <span class="text-red-500">*</span></label>
              <select v-model="createForm.billing_cycle" required
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="MONTHLY">{{ $t('services.monthly') }}</option>
                <option value="QUARTERLY">{{ $t('services.quarterly') }}</option>
                <option value="YEARLY">{{ $t('services.yearly') }}</option>
                <option value="ONE_TIME">{{ $t('services.oneTime') }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.startDateRequired') }} <span class="text-red-500">*</span></label>
              <input v-model="createForm.start_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.endDateRequired') }} <span class="text-red-500">*</span></label>
              <input v-model="createForm.end_date" type="date" required
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.contractValueRequired') }} <span class="text-red-500">*</span></label>
              <input v-model.number="createForm.contract_value" type="number" step="0.01" required min="0"
                placeholder="0.00"
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">{{ $t('services.description') }}</label>
              <textarea v-model="createForm.notes" rows="3" :placeholder="$t('services.descriptionPlaceholder')"
                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
            </div>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-4 sm:mt-6">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
              {{ $t('common.cancel') }}
            </button>
            <button type="submit" :disabled="creating" class="bg-purple-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-purple-700 disabled:opacity-50 text-sm">
              {{ $t('common.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Barcode Modal -->
    <div v-if="showBarcodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[95vh] overflow-y-auto">
        <div class="flex justify-between items-center p-3 sm:p-4 border-b">
          <h3 class="text-base sm:text-lg font-semibold">Print Contract Barcode</h3>
          <button @click="closeBarcodeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-4 sm:p-6">
          <div class="space-y-3 sm:space-y-4">
            <div>
              <p class="text-xs sm:text-sm text-gray-600">Service: <span class="font-medium text-gray-900">{{ barcodeData.product_name }}</span></p>
              <p class="text-xs sm:text-sm text-gray-600">Contract: <span class="font-medium text-gray-900">{{ barcodeData.contract_number }}</span></p>
            </div>
            
            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Label Size</label>
              <select v-model="barcodeData.labelSize" @change="generateBarcodePreview" class="w-full border-gray-300 rounded-lg text-sm">
                <option value="small">Small (30mm x 20mm) - Compact</option>
                <option value="medium">Medium (50mm x 30mm) - Standard</option>
                <option value="large">Large (100mm x 50mm) - Wide</option>
              </select>
            </div>
            
            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Number of Labels</label>
              <input v-model.number="barcodeData.copies" type="number" min="1" max="100" class="w-full border-gray-300 rounded-lg text-sm">
            </div>
            
            <div class="border rounded-lg p-3 sm:p-4 bg-gray-50" id="barcode-preview">
              <div class="text-center">
                <div class="inline-block" :style="previewStyle">
                  <svg id="barcode-svg-contract"></svg>
                  <p class="text-xs mt-2 font-mono" :style="{ fontSize: labelSizes[barcodeData.labelSize].skuFontSize }">{{ barcodeData.contract_number }}</p>
                  <p class="text-xs text-gray-600" :style="{ fontSize: labelSizes[barcodeData.labelSize].nameFontSize }">{{ barcodeData.product_name }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 justify-end p-3 sm:p-4 border-t">
          <button @click="closeBarcodeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
            Cancel
          </button>
          <button @click="printBarcodeLabels" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm active:scale-95 transition-transform">
            Print
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import serviceService from '@/services/serviceService'
import api from '@/services/api'
import * as XLSX from 'xlsx'
import JsBarcode from 'jsbarcode'

const router = useRouter()
const { t } = useI18n()

const contracts = ref([])
const loading = ref(false)
const vendors = ref([])
const locations = ref([])
const serviceProducts = ref([])
const stats = ref({})
const showCreateModal = ref(false)
const creating = ref(false)
const showBarcodeModal = ref(false)

// Product autocomplete states
const productSearch = ref('')
const showProductDropdown = ref(false)
const filteredProducts = ref([])

const labelSizes = {
  small: {
    width: '30mm',
    height: '20mm',
    barcodeWidth: 1,
    barcodeHeight: 25,
    barcodeMargin: 5,
    skuFontSize: '8px',
    nameFontSize: '6px',
    padding: '1mm'
  },
  medium: {
    width: '50mm',
    height: '30mm',
    barcodeWidth: 1.5,
    barcodeHeight: 40,
    barcodeMargin: 8,
    skuFontSize: '10px',
    nameFontSize: '8px',
    padding: '2mm'
  },
  large: {
    width: '100mm',
    height: '50mm',
    barcodeWidth: 2,
    barcodeHeight: 60,
    barcodeMargin: 10,
    skuFontSize: '14px',
    nameFontSize: '10px',
    padding: '3mm'
  }
}

const barcodeData = ref({
  contract_number: '',
  product_name: '',
  copies: 1,
  labelSize: 'medium'
})

const previewStyle = computed(() => {
  const size = labelSizes[barcodeData.value.labelSize]
  return {
    border: '1px dashed #ccc',
    padding: size.padding,
    display: 'inline-block'
  }
})

const filters = ref({
  search: '',
  status: '',
  contract_type: '',
  vendor_id: ''
})

const createForm = ref({
  product_id: '',
  vendor_id: '',
  location_id: '',
  pic: '',
  contract_type: '',
  billing_cycle: 'MONTHLY',
  start_date: new Date().toISOString().split('T')[0],
  end_date: '',
  contract_value: 0,
  notes: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
})

onMounted(async () => {
  await Promise.all([
    loadContracts(),
    loadVendors(),
    loadLocations(),
    loadServiceProducts(),
    loadStats()
  ])
})

const exportToExcel = () => {
  try {
    const exportData = contracts.value.map(contract => ({
      'Contract Number': contract.contract_number,
      'Product/Service': contract.product?.name || '',
      'Vendor': contract.vendor?.name || '',
      'Location': contract.location?.name || '',
      'PIC': contract.pic || '',
      'Type': contract.contract_type,
      'Billing Cycle': contract.billing_cycle || '',
      'Start Date': contract.start_date,
      'End Date': contract.end_date,
      'Contract Value': contract.contract_value || 0,
      'Status': contract.status,
      'Notes': contract.notes || ''
    }))

    if (exportData.length === 0) {
      alert(t('services.noDataToExport'))
      return
    }

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    ws['!cols'] = [
      { wch: 20 },  // Contract Number
      { wch: 30 },  // Product/Service
      { wch: 25 },  // Vendor
      { wch: 20 },  // Location
      { wch: 20 },  // PIC
      { wch: 15 },  // Type
      { wch: 15 },  // Billing Cycle
      { wch: 15 },  // Start Date
      { wch: 15 },  // End Date
      { wch: 15 },  // Contract Value
      { wch: 12 },  // Status
      { wch: 40 }   // Notes
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Service Contracts')

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Service_Contracts_${timestamp}.xlsx`

    XLSX.writeFile(wb, filename)
    alert(t('services.exportSuccess', { filename: filename, count: exportData.length }))
  } catch (error) {
    console.error('Export error:', error)
    alert(t('services.exportFailed') + ': ' + error.message)
  }
}

watch(filters, () => {
  pagination.value.current_page = 1
}, { deep: true })

const loadContracts = async () => {
  loading.value = true
  try {
    // Clean up filters - remove empty values
    const cleanFilters = Object.entries(filters.value).reduce((acc, [key, value]) => {
      if (value !== '' && value !== null && value !== undefined) {
        acc[key] = value
      }
      return acc
    }, {})
    
    const params = {
      ...cleanFilters,
      page: pagination.value.current_page,
      per_page: 15
    }
    
    console.log('Request params:', params)
    const { data } = await serviceService.getAll(params)
    console.log('Service contracts response:', data)
    
    // Handle both paginated and direct array response
    if (data.data && Array.isArray(data.data)) {
      contracts.value = data.data
      pagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        from: data.from || 0,
        to: data.to || 0,
        total: data.total || 0
      }
    } else if (Array.isArray(data)) {
      contracts.value = data
      pagination.value = {
        current_page: 1,
        last_page: 1,
        from: 1,
        to: data.length,
        total: data.length
      }
    } else {
      contracts.value = []
      console.error('Unexpected data format:', data)
    }
    
    console.log('Contracts loaded:', contracts.value)
    // Debug: Log all contract IDs
    console.log('Contract IDs:', contracts.value.map(c => ({ id: c.id, contract_number: c.contract_number, status: c.status })))
  } catch (error) {
    console.error('Failed to load contracts:', error)
    console.error('Error response:', error.response?.data)
    alert(t('services.loadFailed') + ': ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const loadVendors = async () => {
  try {
    const { data } = await api.get('/vendors')
    vendors.value = Array.isArray(data) ? data : (data.data || [])
  } catch (error) {
    console.error('Failed to load vendors:', error)
  }
}

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = Array.isArray(data) ? data : (data.data || [])
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadServiceProducts = async () => {
  try {
    // Force query string in URL to ensure params are sent
    const { data } = await api.get('/products?type=SERVICE&per_page=500')
    serviceProducts.value = data.data || []
    // CRITICAL: Filter client-side as backup
    serviceProducts.value = serviceProducts.value.filter(p => p.type === 'SERVICE')
    filteredProducts.value = serviceProducts.value
    console.log('Loaded service products:', serviceProducts.value.length, 'items')
    console.log('Product types:', serviceProducts.value.map(p => p.type))
  } catch (error) {
    console.error('Failed to load service products:', error)
  }
}

const filterProducts = () => {
  const search = productSearch.value.toLowerCase()
  if (!search) {
    filteredProducts.value = serviceProducts.value
  } else {
    filteredProducts.value = serviceProducts.value.filter(product => 
      product.name.toLowerCase().includes(search) ||
      (product.sku && product.sku.toLowerCase().includes(search))
    )
  }
  showProductDropdown.value = true
}

const selectProduct = (product) => {
  createForm.value.product_id = product.id
  productSearch.value = product.name
  showProductDropdown.value = false
}

const handleProductBlur = () => {
  setTimeout(() => {
    showProductDropdown.value = false
  }, 200)
}

const loadStats = async () => {
  try {
    const { data } = await serviceService.getStats()
    stats.value = data
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    contract_type: '',
    vendor_id: ''
  }
  loadContracts()
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadContracts()
}

const viewContract = (id) => {
  console.log('Navigating to service contract ID:', id, 'Type:', typeof id)
  router.push(`/services/${id}`)
}

const createContract = async () => {
  // Validate end date is after start date
  if (new Date(createForm.value.end_date) <= new Date(createForm.value.start_date)) {
    alert(t('services.endDateError'))
    return
  }
  
  creating.value = true
  try {
    await serviceService.create(createForm.value)
    alert(t('services.createSuccess'))
    closeCreateModal()
    await loadContracts()
    await loadStats()
  } catch (error) {
    console.error('Failed to create contract:', error)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || error.message
    alert(t('services.createFailed') + ': ' + errorMsg)
  } finally {
    creating.value = false
  }
}

const closeCreateModal = () => {
  showCreateModal.value = false
  productSearch.value = ''
  showProductDropdown.value = false
  filteredProducts.value = serviceProducts.value
  // Reset form
  createForm.value = {
    product_id: '',
    vendor_id: '',
    location_id: '',
    pic: '',
    contract_type: '',
    billing_cycle: 'MONTHLY',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    contract_value: 0,
    notes: ''
  }
}

const printBarcode = async (contract) => {
  barcodeData.value = {
    contract_number: contract.contract_number,
    product_name: contract.product?.name || 'Service Contract',
    copies: 1,
    labelSize: 'medium'
  }
  showBarcodeModal.value = true
  
  await nextTick()
  generateBarcodePreview()
}

const generateBarcodePreview = () => {
  try {
    const svg = document.getElementById('barcode-svg-contract')
    const size = labelSizes[barcodeData.value.labelSize]
    
    if (svg && barcodeData.value.contract_number) {
      JsBarcode(svg, barcodeData.value.contract_number, {
        format: 'CODE128',
        width: size.barcodeWidth,
        height: size.barcodeHeight,
        displayValue: false,
        margin: size.barcodeMargin
      })
    }
  } catch (error) {
    console.error('Failed to generate barcode:', error)
    alert(t('services.barcodeGenerateFailed'))
  }
}

const printBarcodeLabels = () => {
  const printWindow = window.open('', '', 'width=800,height=600')
  const size = labelSizes[barcodeData.value.labelSize]
  
  let labelsHTML = ''
  for (let i = 0; i < barcodeData.value.copies; i++) {
    labelsHTML += `
      <div class="barcode-label">
        <svg id="barcode-${i}"></svg>
        <div class="barcode-text">${barcodeData.value.contract_number}</div>
        <div class="product-name">${barcodeData.value.product_name}</div>
      </div>
    `
  }
  
  printWindow.document.write(`
    <html>
      <head>
        <title>Print Contract Barcode - ${barcodeData.value.contract_number}</title>
        <style>
          @page {
            size: ${size.width} ${size.height};
            margin: 0;
          }
          
          * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
          }
          
          body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
          }
          
          .barcode-label {
            width: ${size.width};
            height: ${size.height};
            padding: ${size.padding};
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-after: always;
          }
          
          .barcode-label:last-child {
            page-break-after: auto;
          }
          
          svg {
            display: block;
            margin: 0 auto;
          }
          
          .barcode-text {
            font-family: monospace;
            font-size: ${size.skuFontSize};
            font-weight: bold;
            margin-top: 2mm;
          }
          
          .product-name {
            font-size: ${size.nameFontSize};
            color: #333;
            margin-top: 1mm;
            max-width: 90%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }
          
          @media print {
            body { 
              margin: 0; 
              padding: 0; 
            }
            .barcode-label { 
              margin: 0; 
            }
          }
        </style>
      </head>
      <body>
        ${labelsHTML}
      </body>
    </html>
  `)
  
  printWindow.document.close()
  
  setTimeout(() => {
    for (let i = 0; i < barcodeData.value.copies; i++) {
      const svg = printWindow.document.getElementById(`barcode-${i}`)
      if (svg) {
        JsBarcode(svg, barcodeData.value.contract_number, {
          format: 'CODE128',
          width: size.barcodeWidth,
          height: size.barcodeHeight,
          displayValue: false,
          margin: size.barcodeMargin
        })
      }
    }
    
    setTimeout(() => {
      printWindow.print()
      printWindow.close()
    }, 500)
  }, 500)
}

const closeBarcodeModal = () => {
  showBarcodeModal.value = false
  barcodeData.value = {
    contract_number: '',
    product_name: '',
    copies: 1,
    labelSize: 'medium'
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatContractType = (type) => {
  const types = {
    'RENTAL': 'Rental',
    'SUBSCRIPTION': 'Subscription',
    'MAINTENANCE': 'Maintenance',
    'CONSULTING': 'Consulting',
    'UTILITY': 'Utility',
    'OTHER': 'Other'
  }
  return types[type] || type
}

const getStatusClass = (status) => {
  const classes = {
    'ACTIVE': 'bg-green-100 text-green-800',
    'PENDING': 'bg-blue-100 text-blue-800',
    'EXPIRED': 'bg-red-100 text-red-800',
    'TERMINATED': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>

<style scoped>
.badge {
  @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium;
}

.badge-gray {
  @apply bg-gray-100 text-gray-800;
}
</style>
