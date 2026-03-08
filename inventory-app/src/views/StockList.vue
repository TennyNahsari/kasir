<template>
  <div class="p-3 sm:p-4 lg:p-6">
    <div class="mb-3 sm:mb-4 lg:mb-6 flex flex-col gap-3">
      <div>
        <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-800">{{ $t('stocks.title') }}</h1>
        <p class="text-xs sm:text-sm text-gray-600">{{ $t('stocks.subtitle') }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button @click="exportToExcel" class="bg-purple-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span class="hidden sm:inline">{{ $t('stocks.exportExcel') }}</span>
          <span class="sm:hidden">Excel</span>
        </button>
        <button @click="openAddModal" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 text-xs sm:text-sm flex-1 sm:flex-none">
          + {{ $t('stocks.addStock') }}
        </button>
        <button @click="showAdjustModal = true" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 text-xs sm:text-sm flex-1 sm:flex-none">
          {{ $t('stocks.adjustStock') }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-3 sm:mb-4 lg:mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.locationLabel') }}</label>
          <select v-model="filters.location_id" @change="loadStocks" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">{{ $t('stocks.allLocations') }}</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.searchProduct') }}</label>
          <input v-model="filters.search" @input="loadStocks" type="text" :placeholder="$t('stocks.searchPlaceholder')" class="w-full border-gray-300 rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.stockStatus') }}</label>
          <select v-model="filters.showLowStock" @change="loadStocks" class="w-full border-gray-300 rounded-lg text-sm">
            <option :value="false">{{ $t('stocks.allStock') }}</option>
            <option :value="true">{{ $t('stocks.lowStockOnly') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.displayLabel') }}</label>
          <select v-model="filters.hideZeroStock" @change="loadStocks" class="w-full border-gray-300 rounded-lg text-sm">
            <option :value="false">{{ $t('stocks.showAll') }}</option>
            <option :value="true">{{ $t('stocks.hideZeroStock') }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Stock Table (Desktop) -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('stocks.product') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('stocks.location') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('stocks.quantity') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('stocks.reorderLevel') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('stocks.status') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="stock in stocks" :key="stock.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ stock.product_name }}</div>
                <div class="text-sm text-gray-500">{{ stock.sku }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ stock.location_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                {{ stock.quantity }} {{ stock.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                {{ stock.reorder_level }} {{ stock.uom }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(stock)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStockStatus(stock) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="printBarcode(stock)" class="text-purple-600 hover:text-purple-900 mr-3">{{ $t('stocks.barcode') }}</button>
                <button @click="openLedger(stock)" class="text-blue-600 hover:text-blue-900 mr-3">{{ $t('stocks.history') }}</button>
                <button @click="adjustStock(stock)" class="text-green-600 hover:text-green-900 mr-3">{{ $t('stocks.adjust') }}</button>
                <button @click="deleteStock(stock)" class="text-red-600 hover:text-red-900">{{ $t('common.delete') }}</button>
              </td>
            </tr>
            <tr v-if="stocks.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                {{ $t('stocks.noStockData') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :pagination="pagination" @page-change="changePage" />
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-2 sm:space-y-3">
      <div v-for="stock in stocks" :key="stock.id" class="bg-white rounded-lg shadow p-3 sm:p-4">
        <div class="flex justify-between items-start mb-2">
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-sm sm:text-base text-gray-900 truncate">{{ stock.product_name }}</h3>
            <p class="text-xs text-gray-500">{{ stock.sku }}</p>
          </div>
          <span :class="getStatusClass(stock)" class="px-2 py-1 text-xs font-semibold rounded-full flex-shrink-0 ml-2">
            {{ getStockStatus(stock) }}
          </span>
        </div>
        
        <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
          <div>
            <span class="text-gray-600">{{ $t('stocks.location') }}:</span>
            <div class="font-medium text-gray-900 truncate">{{ stock.location_name }}</div>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('stocks.quantity') }}:</span>
            <div class="font-semibold text-gray-900">{{ stock.quantity }} {{ stock.uom }}</div>
          </div>
          <div class="col-span-2">
            <span class="text-gray-600">{{ $t('stocks.reorderLevel') }}:</span>
            <span class="font-medium ml-1">{{ stock.reorder_level }} {{ stock.uom }}</span>
          </div>
        </div>
        
        <div class="flex flex-wrap gap-1.5">
          <button @click="printBarcode(stock)" class="flex-1 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 active:scale-95 transition-transform">
            {{ $t('stocks.barcode') }}
          </button>
          <button @click="openLedger(stock)" class="flex-1 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 active:scale-95 transition-transform">
            {{ $t('stocks.history') }}
          </button>
          <button @click="adjustStock(stock)" class="flex-1 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 active:scale-95 transition-transform">
            {{ $t('stocks.adjust') }}
          </button>
          <button @click="deleteStock(stock)" class="py-1.5 px-2.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 active:scale-95 transition-transform">
            {{ $t('common.delete') }}
          </button>
        </div>
      </div>
      
      <div v-if="stocks.length === 0" class="bg-white rounded-lg shadow p-6 sm:p-8 text-center text-gray-500 text-sm">
        {{ $t('stocks.noStockData') }}
      </div>

      <Pagination :pagination="pagination" @page-change="changePage" />
    </div>

    <!-- Adjust Stock Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-3 sm:p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-md max-h-[95vh] overflow-y-auto">
        <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">{{ $t('stocks.modalAdjustTitle') }}</h3>
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.locationLabel') }}</label>
            <select v-model="adjustForm.location_id" class="w-full border-gray-300 rounded-lg text-sm" required>
              <option value="">{{ $t('stocks.selectLocation') }}</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.productLabel') }}</label>
            <input v-model="adjustForm.product_search" @input="searchProducts" type="text" :placeholder="$t('stocks.productSearchPlaceholder')" class="w-full border-gray-300 rounded-lg text-sm">
            <div v-if="productSearchResults.length > 0" class="mt-2 border rounded-lg max-h-32 sm:max-h-40 overflow-y-auto">
              <div v-for="product in productSearchResults" :key="product.id" @click="selectProduct(product)" class="p-2 hover:bg-gray-100 cursor-pointer text-xs sm:text-sm">
                {{ product.name }} ({{ product.sku }})
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.newQuantity') }}</label>
            <input v-model.number="adjustForm.new_quantity" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg text-sm" required>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.reorderLevelLabel') }}</label>
            <input v-model.number="adjustForm.reorder_level" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('stocks.reorderPlaceholder')">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.notesLabel') }}</label>
            <textarea v-model="adjustForm.notes" rows="3" class="w-full border-gray-300 rounded-lg text-sm"></textarea>
          </div>
        </div>
        <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
          <button @click="closeAdjustModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">{{ $t('common.cancel') }}</button>
          <button @click="submitAdjustment" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">{{ $t('stocks.adjustStock') }}</button>
        </div>
      </div>
    </div>

    <!-- Add Stock Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-3 sm:p-4">
      <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-md max-h-[95vh] overflow-y-auto">
        <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">{{ $t('stocks.modalAddTitle') }}</h3>
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.locationRequired') }} <span class="text-red-500">*</span></label>
            <select v-model="addForm.location_id" class="w-full border-gray-300 rounded-lg text-sm" required>
              <option value="">{{ $t('stocks.selectLocation') }}</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.productRequired') }} <span class="text-red-500">*</span></label>
            <input v-model="addForm.product_search" @input="searchProductsForAdd" type="text" :placeholder="$t('stocks.productSearchPlaceholder')" class="w-full border-gray-300 rounded-lg text-sm">
            <div v-if="addProductSearchResults.length > 0" class="mt-2 border rounded-lg max-h-32 sm:max-h-40 overflow-y-auto">
              <div v-for="product in addProductSearchResults" :key="product.id" @click="selectProductForAdd(product)" class="p-2 hover:bg-gray-100 cursor-pointer text-xs sm:text-sm">
                {{ product.name }} ({{ product.sku }})
              </div>
            </div>
            <p v-if="addForm.product_id" class="mt-1 text-xs sm:text-sm text-green-600">{{ $t('stocks.selectedProduct', { name: addForm.product_search }) }}</p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.initialQuantity') }} <span class="text-red-500">*</span></label>
            <input v-model.number="addForm.quantity" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg text-sm" required>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.reorderLevelLabel') }}</label>
            <input v-model.number="addForm.reorder_level" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('stocks.reorderOptional')">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.notesLabel') }}</label>
            <textarea v-model="addForm.notes" rows="3" class="w-full border-gray-300 rounded-lg text-sm" :placeholder="$t('stocks.notesPlaceholder')"></textarea>
          </div>
        </div>
        <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
          <button @click="closeAddModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">{{ $t('common.cancel') }}</button>
          <button @click="submitAddStock" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">{{ $t('stocks.addStock') }}</button>
        </div>
      </div>
    </div>

    <!-- Barcode Print Modal -->
    <div v-if="showBarcodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">{{ $t('stocks.modalBarcodeTitle') }}</h3>
          <button @click="closeBarcodeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div>
            <p class="text-sm text-gray-600">{{ $t('stocks.productInfo') }} <span class="font-medium text-gray-900">{{ barcodeData.product_name }}</span></p>
            <p class="text-sm text-gray-600">{{ $t('stocks.skuInfo') }} <span class="font-medium text-gray-900">{{ barcodeData.sku }}</span></p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.labelSize') }}</label>
            <select v-model="barcodeData.labelSize" @change="generateBarcodePreview" class="w-full border-gray-300 rounded-lg">
              <option value="small">{{ $t('stocks.labelSmall') }}</option>
              <option value="medium">{{ $t('stocks.labelMedium') }}</option>
              <option value="large">{{ $t('stocks.labelLarge') }}</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('stocks.numberOfLabels') }}</label>
            <input v-model.number="barcodeData.copies" type="number" min="1" max="100" class="w-full border-gray-300 rounded-lg">
          </div>
          
          <div class="border rounded-lg p-4 bg-gray-50" id="barcode-preview">
            <div class="text-center">
              <div class="inline-block" :style="previewStyle">
                <svg id="barcode-svg"></svg>
                <p class="text-xs mt-2 font-mono" :style="{ fontSize: labelSizes[barcodeData.labelSize].skuFontSize }">{{ barcodeData.sku }}</p>
                <p class="text-xs text-gray-600" :style="{ fontSize: labelSizes[barcodeData.labelSize].nameFontSize }">{{ barcodeData.product_name }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button @click="closeBarcodeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">{{ $t('common.cancel') }}</button>
          <button @click="printBarcodeLabels" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            {{ $t('stocks.print') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import * as XLSX from 'xlsx'
import JsBarcode from 'jsbarcode'

const router = useRouter()
const { t } = useI18n()

const stocks = ref([])
const locations = ref([])
const showAdjustModal = ref(false)
const showAddModal = ref(false)
const showBarcodeModal = ref(false)
const productSearchResults = ref([])
const addProductSearchResults = ref([])

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
  sku: '',
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

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  location_id: '',
  search: '',
  showLowStock: false,
  hideZeroStock: false
})

const adjustForm = ref({
  product_id: null,
  product_search: '',
  location_id: '',
  new_quantity: 0,
  reorder_level: 0,
  notes: ''
})

const addForm = ref({
  product_id: null,
  product_search: '',
  location_id: '',
  quantity: 0,
  reorder_level: 0,
  notes: ''
})

onMounted(async () => {
  await loadLocations()
  await loadStocks()
})

const loadLocations = async () => {
  try {
    const { data } = await api.get('/locations')
    locations.value = data.data || data  // Handle pagination response
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const printBarcode = async (stock) => {
  barcodeData.value = {
    sku: stock.sku,
    product_name: stock.product_name,
    copies: 1,
    labelSize: 'medium'
  }
  showBarcodeModal.value = true
  
  // Wait for DOM to update then generate barcode
  await nextTick()
  generateBarcodePreview()
}

const generateBarcodePreview = () => {
  try {
    const svg = document.getElementById('barcode-svg')
    const size = labelSizes[barcodeData.value.labelSize]
    
    if (svg && barcodeData.value.sku) {
      JsBarcode(svg, barcodeData.value.sku, {
        format: 'CODE128',
        width: size.barcodeWidth,
        height: size.barcodeHeight,
        displayValue: false,
        margin: size.barcodeMargin
      })
    }
  } catch (error) {
    console.error('Failed to generate barcode:', error)
    alert(t('stocks.barcodeGenerateFailed'))
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
        <div class="barcode-text">${barcodeData.value.sku}</div>
        <div class="product-name">${barcodeData.value.product_name}</div>
      </div>
    `
  }
  
  printWindow.document.write(`
    <html>
      <head>
        <title>Print Barcode - ${barcodeData.value.sku}</title>
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
  
  // Generate barcodes in print window
  setTimeout(() => {
    for (let i = 0; i < barcodeData.value.copies; i++) {
      const svg = printWindow.document.getElementById(`barcode-${i}`)
      if (svg) {
        JsBarcode(svg, barcodeData.value.sku, {
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
    sku: '',
    product_name: '',
    copies: 1,
    labelSize: 'medium'
  }
}

const exportToExcel = () => {
  try {
    const exportData = stocks.value.map(stock => ({
      'Product Name': stock.product_name,
      'SKU': stock.sku,
      'Location': stock.location_name,
      'Quantity': stock.quantity,
      'UOM': stock.uom,
      'Reorder Level': stock.reorder_level,
      'Status': getStockStatus(stock)
    }))

    if (exportData.length === 0) {
      alert(t('stocks.noDataToExport'))
      return
    }

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    ws['!cols'] = [
      { wch: 30 },  // Product Name
      { wch: 15 },  // SKU
      { wch: 25 },  // Location
      { wch: 12 },  // Quantity
      { wch: 10 },  // UOM
      { wch: 15 },  // Reorder Level
      { wch: 15 }   // Status
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Stock Levels')

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Stock_Levels_${timestamp}.xlsx`

    XLSX.writeFile(wb, filename)
    alert(t('stocks.exportSuccess', { filename: filename, count: exportData.length }))
  } catch (error) {
    console.error('Export error:', error)
    alert(t('stocks.exportFailed') + ': ' + error.message)
  }
}

const loadStocks = async (page = 1) => {
  try {
    const params = { page, per_page: 20 }
    if (filters.value.location_id) params.location_id = filters.value.location_id
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.hideZeroStock) params.hide_zero_stock = true

    const endpoint = filters.value.showLowStock ? '/inventory-stocks/low-stock' : '/inventory-stocks'
    const { data } = await api.get(endpoint, { params })
    stocks.value = data.data || data
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      per_page: data.per_page || 20,
      total: data.total || stocks.value.length,
      from: data.from || 0,
      to: data.to || 0,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url
    }
  } catch (error) {
    console.error('Failed to load stocks:', error)
  }
}

const changePage = (page) => {
  loadStocks(page)
}

const getStockStatus = (stock) => {
  if (stock.available_quantity <= 0) return t('stocks.outOfStock')
  if (stock.quantity <= stock.reorder_level) return t('stocks.lowStock')
  return t('stocks.inStock')
}

const getStatusClass = (stock) => {
  if (stock.available_quantity <= 0) return 'bg-red-100 text-red-800'
  if (stock.quantity <= stock.reorder_level) return 'bg-orange-100 text-orange-800'
  return 'bg-green-100 text-green-800'
}

const adjustStock = (stock) => {
  adjustForm.value = {
    product_id: stock.product_id,
    product_search: stock.product_name,
    location_id: stock.location_id,
    new_quantity: stock.quantity,
    reorder_level: stock.reorder_level || 0,
    notes: ''
  }
  showAdjustModal.value = true
}

const searchProducts = async () => {
  if (adjustForm.value.product_search.length < 2) {
    productSearchResults.value = []
    return
  }
  try {
    const searchQuery = encodeURIComponent(adjustForm.value.product_search)
    const { data } = await api.get(`/products?search=${searchQuery}&type=INVENTORY`)
    // Handle paginated response and filter client-side
    let results = (data.data || data)
    results = results.filter(p => p.type === 'INVENTORY')
    productSearchResults.value = results.slice(0, 5)
  } catch (error) {
    console.error('Failed to search products:', error)
  }
}

const selectProduct = (product) => {
  adjustForm.value.product_id = product.id
  adjustForm.value.product_search = product.name
  productSearchResults.value = []
}

const submitAdjustment = async () => {
  try {
    await api.post('/inventory-stocks/adjust', {
      product_id: adjustForm.value.product_id,
      location_id: adjustForm.value.location_id,
      new_quantity: adjustForm.value.new_quantity,
      reorder_level: adjustForm.value.reorder_level,
      notes: adjustForm.value.notes
    })
    alert(t('stocks.adjustSuccess'))
    closeAdjustModal()
    await loadStocks()
  } catch (error) {
    alert(t('stocks.adjustFailed') + ': ' + (error.response?.data?.message || error.message))
  }
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  adjustForm.value = {
    product_id: null,
    product_search: '',
    location_id: '',
    new_quantity: 0,
    reorder_level: 0,
    notes: ''
  }
}

const openLedger = (stock) => {
  router.push({
    path: '/inventory/ledger',
    query: {
      product_id: stock.product_id,
      location_id: stock.location_id
    }
  })
}

const openAddModal = () => {
  addForm.value = {
    product_id: null,
    product_search: '',
    location_id: '',
    quantity: 0,
    reorder_level: 0,
    notes: ''
  }
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  addProductSearchResults.value = []
}

const searchProductsForAdd = async () => {
  if (addForm.value.product_search.length < 2) {
    addProductSearchResults.value = []
    return
  }
  try {
    const searchQuery = encodeURIComponent(addForm.value.product_search)
    const { data } = await api.get(`/products?search=${searchQuery}&type=INVENTORY`)
    // Filter client-side as backup
    let results = (data.data || data)
    results = results.filter(p => p.type === 'INVENTORY')
    addProductSearchResults.value = results.slice(0, 5)
  } catch (error) {
    console.error('Failed to search products:', error)
  }
}

const selectProductForAdd = (product) => {
  addForm.value.product_id = product.id
  addForm.value.product_search = product.name
  addProductSearchResults.value = []
}

const submitAddStock = async () => {
  if (!addForm.value.product_id) {
    alert(t('stocks.selectProductError'))
    return
  }
  if (!addForm.value.location_id) {
    alert(t('stocks.selectLocationError'))
    return
  }
  if (addForm.value.quantity < 0) {
    alert(t('stocks.quantityError'))
    return
  }

  try {
    await api.post('/inventory-stocks', {
      product_id: addForm.value.product_id,
      location_id: addForm.value.location_id,
      quantity: addForm.value.quantity,
      reorder_level: addForm.value.reorder_level || 0,
      notes: addForm.value.notes
    })
    alert(t('stocks.addSuccess'))
    closeAddModal()
    await loadStocks()
  } catch (error) {
    alert(t('stocks.addFailed') + ': ' + (error.response?.data?.message || error.message))
  }
}

const deleteStock = async (stock) => {
  // Check if stock has quantity
  if (stock.quantity > 0) {
    const confirmMsg = `This stock has quantity ${stock.quantity}. You need to adjust quantity to 0 before deleting. Do you want to proceed to adjust?`
    if (!confirm(confirmMsg)) {
      return
    }
    // Open adjust modal instead
    adjustStock(stock)
    return
  }

  const confirmDelete = confirm(`Are you sure you want to delete stock for "${stock.product_name}" at ${stock.location_name}?`)
  if (!confirmDelete) {
    return
  }

  try {
    await api.delete(`/inventory-stocks/${stock.id}`)
    alert(t('stocks.deleteSuccess'))
    await loadStocks()
  } catch (error) {
    console.error('Failed to delete stock:', error)
    alert(t('stocks.deleteFailed') + ': ' + (error.response?.data?.message || error.message))
  }
}
</script>
