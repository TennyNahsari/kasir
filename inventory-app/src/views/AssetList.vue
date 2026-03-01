<template>
  <div class="p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
      <h2 class="text-2xl font-bold">{{ $t('assets.title') }}</h2>
      <div class="flex space-x-3">
        <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          {{ $t('assets.exportExcel') }}
        </button>
        <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
          + {{ $t('assets.addAsset') }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="label text-sm">{{ $t('assets.searchLabel') }}</label>
          <input
            v-model="filters.search"
            type="text"
            class="input"
            :placeholder="$t('assets.searchPlaceholder')"
            @input="loadAssets"
          >
        </div>
        <div>
          <label class="label text-sm">{{ $t('assets.statusLabel') }}</label>
          <select v-model="filters.status" class="input" @change="loadAssets">
            <option value="">{{ $t('assets.allStatus') }}</option>
            <option value="AVAILABLE">{{ $t('assets.available') }}</option>
            <option value="ASSIGNED">{{ $t('assets.assigned') }}</option>
            <option value="IN_USE">{{ $t('assets.inUse') }}</option>
            <option value="MAINTENANCE">{{ $t('assets.maintenance') }}</option>
            <option value="DAMAGED">{{ $t('assets.damaged') }}</option>
            <option value="DISPOSED">{{ $t('assets.disposed') }}</option>
          </select>
        </div>
        <div>
          <label class="label text-sm">{{ $t('assets.locationLabel') }}</label>
          <select v-model="filters.location_id" class="input" @change="loadAssets">
            <option value="">{{ $t('assets.allLocations') }}</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="label text-sm">{{ $t('assets.picLabel') }}</label>
          <input v-model="filters.pic" type="text" class="input" :placeholder="$t('assets.picPlaceholder')" @input="loadAssets">
        </div>
      </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block card overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.assetTag') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.product') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.serialNumber') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.location') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.pic') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.status') }}</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">{{ $t('assets.condition') }}</th>
            <th class="px-4 py-3 text-center text-sm font-semibold">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="asset in assets" :key="asset.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-mono font-semibold text-blue-600">
              {{ asset.asset_tag }}
            </td>
            <td class="px-4 py-3 text-sm">{{ asset.product?.name }}</td>
            <td class="px-4 py-3 text-sm font-mono">{{ asset.serial_number || '-' }}</td>
            <td class="px-4 py-3 text-sm">{{ asset.location?.name }}</td>
            <td class="px-4 py-3 text-sm">{{ asset.pic || '-' }}</td>
            <td class="px-4 py-3 text-sm">
              <span :class="getStatusBadgeClass(asset.status)" class="badge">
                {{ asset.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm">
              <span :class="getConditionBadgeClass(asset.condition)" class="badge">
                {{ asset.condition }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-center">
              <div class="flex gap-2 justify-center">
                <button @click="printBarcode(asset)" class="text-purple-600 hover:text-purple-900 font-medium">
                  {{ $t('assets.barcode') }}
                </button>
                <button @click="printQRCode(asset)" class="text-green-600 hover:text-green-900 font-medium">
                  {{ $t('assets.qrCode') }}
                </button>
                <button @click="viewAsset(asset)" class="text-blue-600 hover:text-blue-700 font-medium">
                  {{ $t('assets.view') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!loading && assets.length === 0" class="text-center py-8 text-gray-500">
        {{ $t('assets.noAssets') }}
      </div>

      <div v-if="loading" class="text-center py-8 text-gray-500">
        {{ $t('assets.loading') }}
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-3">
      <div v-for="asset in assets" :key="asset.id" class="card p-4">
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-semibold text-blue-600 font-mono">{{ asset.asset_tag }}</h3>
            <p class="text-sm text-gray-700 font-medium">{{ asset.product?.name }}</p>
            <p class="text-xs text-gray-500">SN: {{ asset.serial_number || '-' }}</p>
          </div>
          <div class="flex flex-col gap-1 items-end">
            <span :class="getStatusBadgeClass(asset.status)" class="badge text-xs">
              {{ asset.status }}
            </span>
            <span :class="getConditionBadgeClass(asset.condition)" class="badge text-xs">
              {{ asset.condition }}
            </span>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
          <div>
            <span class="text-gray-600">{{ $t('assets.location') }}:</span>
            <span class="font-medium ml-1">{{ asset.location?.name }}</span>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('assets.pic') }}:</span>
            <span class="font-medium ml-1">{{ asset.pic || '-' }}</span>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('assets.purchaseDate') }}:</span>
            <span class="font-medium ml-1">{{ formatDate(asset.purchase_date) }}</span>
          </div>
          <div>
            <span class="text-gray-600">{{ $t('assets.value') }}:</span>
            <span class="font-medium ml-1">{{ formatCurrency(asset.current_value) }}</span>
          </div>
        </div>
        
        <div class="flex gap-2">
          <button @click="printBarcode(asset)" class="flex-1 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100">
            {{ $t('assets.barcode') }}
          </button>
          <button @click="viewAsset(asset)" class="flex-1 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
            {{ $t('assets.viewDetails') }}
          </button>
        </div>
      </div>

      <div v-if="!loading && assets.length === 0" class="card p-8 text-center text-gray-500">
        {{ $t('assets.noAssets') }}
      </div>

      <div v-if="loading" class="card p-8 text-center text-gray-500">
        {{ $t('assets.loading') }}
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="flex justify-between items-center mt-6">
      <div class="text-sm text-gray-600">
        {{ $t('assets.showing') }} {{ pagination.from }} {{ $t('assets.to') }} {{ pagination.to }} {{ $t('assets.of') }} {{ pagination.total }} {{ $t('assets.assetsText') }}
      </div>
      <div class="flex gap-2">
        <button
          @click="loadAssets(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-secondary text-sm disabled:opacity-50"
        >
          {{ $t('assets.previous') }}
        </button>
        <button
          @click="loadAssets(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-secondary text-sm disabled:opacity-50"
        >
          {{ $t('assets.next') }}
        </button>
      </div>
    </div>

    <!-- Add Asset Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">{{ $t('assets.modalAddTitle') }}</h3>
            <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="createAsset" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.productRequired') }} <span class="text-red-500">*</span></label>
                <input 
                  v-model="productSearch" 
                  @focus="showProductDropdown = true"
                  @input="filterProducts"
                  @blur="handleProductBlur"
                  type="text" 
                  required 
                  class="w-full border-gray-300 rounded-lg"
                  :placeholder="$t('assets.productPlaceholder')"
                  autocomplete="off"
                >
                <div 
                  v-if="showProductDropdown && filteredProducts.length > 0" 
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                >
                  <div 
                    v-for="product in filteredProducts" 
                    :key="product.id"
                    @click="selectProduct(product)"
                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0"
                  >
                    <div class="font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-sm text-gray-500">SKU: {{ product.sku || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.serialNumberRequired') }} <span class="text-red-500">*</span></label>
                <input v-model="assetForm.serial_number" type="text" required class="w-full border-gray-300 rounded-lg" :placeholder="$t('assets.serialNumberPlaceholder')">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.locationRequired') }} <span class="text-red-500">*</span></label>
                <select v-model="assetForm.location_id" required class="w-full border-gray-300 rounded-lg">
                  <option value="">{{ $t('assets.selectLocation') }}</option>
                  <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                    {{ loc.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.conditionRequired') }} <span class="text-red-500">*</span></label>
                <select v-model="assetForm.condition" required class="w-full border-gray-300 rounded-lg">
                  <option value="NEW">{{ $t('assets.conditionNew') }}</option>
                  <option value="GOOD">{{ $t('assets.conditionGood') }}</option>
                  <option value="FAIR">{{ $t('assets.conditionFair') }}</option>
                  <option value="POOR">{{ $t('assets.conditionPoor') }}</option>
                  <option value="BROKEN">{{ $t('assets.conditionBroken') }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.purchaseDateRequired') }} <span class="text-red-500">*</span></label>
                <input v-model="assetForm.purchase_date" type="date" required class="w-full border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.purchasePriceRequired') }} <span class="text-red-500">*</span></label>
                <input v-model="assetForm.purchase_price" type="number" step="0.01" required class="w-full border-gray-300 rounded-lg" placeholder="0">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.usefulLife') }}</label>
                <input v-model="assetForm.useful_life_years" type="number" step="1" class="w-full border-gray-300 rounded-lg" placeholder="5">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.warrantyExpiry') }}</label>
                <input v-model="assetForm.warranty_expiry" type="date" class="w-full border-gray-300 rounded-lg">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('assets.notes') }}</label>
              <textarea v-model="assetForm.notes" rows="3" class="w-full border-gray-300 rounded-lg" :placeholder="$t('assets.notesPlaceholder')"></textarea>
            </div>

            <div class="flex gap-3 justify-end pt-4">
              <button type="button" @click="closeAddModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                {{ $t('common.cancel') }}
              </button>
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                {{ $t('common.save') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Barcode Modal -->
    <div v-if="showBarcodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-semibold">Print Asset Tag Barcode</h3>
          <button @click="closeBarcodeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-600">Asset: <span class="font-medium text-gray-900">{{ barcodeData.product_name }}</span></p>
              <p class="text-sm text-gray-600">Asset Tag: <span class="font-medium text-gray-900">{{ barcodeData.asset_tag }}</span></p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Label Size</label>
              <select v-model="barcodeData.labelSize" @change="generateBarcodePreview" class="w-full border-gray-300 rounded-lg">
                <option value="small">Small (30mm x 20mm) - Compact</option>
                <option value="medium">Medium (50mm x 30mm) - Standard</option>
                <option value="large">Large (100mm x 50mm) - Wide</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Number of Labels</label>
              <input v-model.number="barcodeData.copies" type="number" min="1" max="100" class="w-full border-gray-300 rounded-lg">
            </div>
            
            <div class="border rounded-lg p-4 bg-gray-50" id="barcode-preview">
              <div class="text-center">
                <div class="inline-block" :style="previewStyle">
                  <svg id="barcode-svg"></svg>
                  <p class="text-xs mt-2 font-mono" :style="{ fontSize: labelSizes[barcodeData.labelSize].skuFontSize }">{{ barcodeData.asset_tag }}</p>
                  <p class="text-xs text-gray-600" :style="{ fontSize: labelSizes[barcodeData.labelSize].nameFontSize }">{{ barcodeData.product_name }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex gap-3 justify-end p-4 border-t">
          <button @click="closeBarcodeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button @click="printBarcodeLabels" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            Print
          </button>
        </div>
      </div>
    </div>

    <!-- QR Code Modal -->
    <div v-if="showQRModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-semibold">Print Asset QR Code</h3>
          <button @click="closeQRModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-600">Asset: <span class="font-medium text-gray-900">{{ qrData.product_name }}</span></p>
              <p class="text-sm text-gray-600">Asset Tag: <span class="font-medium text-gray-900">{{ qrData.asset_tag }}</span></p>
              <p class="text-sm text-gray-600">Serial: <span class="font-medium text-gray-900">{{ qrData.serial_number }}</span></p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Label Size</label>
              <select v-model="qrData.labelSize" @change="generateQRPreview" class="w-full border-gray-300 rounded-lg">
                <option value="small">Small (50mm x 50mm)</option>
                <option value="medium">Medium (70mm x 70mm)</option>
                <option value="large">Large (100mm x 100mm)</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Number of Labels</label>
              <input v-model.number="qrData.copies" type="number" min="1" max="100" class="w-full border-gray-300 rounded-lg">
            </div>
            
            <div class="border rounded-lg p-4 bg-gray-50" id="qr-preview">
              <div class="text-center">
                <div class="inline-block" :style="qrPreviewStyle">
                  <canvas id="qr-canvas"></canvas>
                  <p class="text-xs mt-2 font-mono font-bold" :style="{ fontSize: qrLabelSizes[qrData.labelSize].tagFontSize }">{{ qrData.asset_tag }}</p>
                  <p class="text-xs text-gray-600" :style="{ fontSize: qrLabelSizes[qrData.labelSize].nameFontSize }">{{ qrData.product_name }}</p>
                </div>
              </div>
            </div>
            
            <div class="text-xs text-gray-500 bg-blue-50 p-3 rounded">
              <p class="font-medium mb-1">\u2139\ufe0f Scan Info:</p>
              <p>Scanning this QR code will show asset details including product info, location, PIC, status, and history.</p>
            </div>
          </div>
        </div>
        
        <div class="flex gap-3 justify-end p-4 border-t">
          <button @click="closeQRModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button @click="printQRLabels" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Print
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
import assetService from '@/services/assetService'
import locationService from '@/services/locationService'
import userService from '@/services/userService'
import api from '@/services/api'
import * as XLSX from 'xlsx'
import JsBarcode from 'jsbarcode'
import QRCode from 'qrcode'

const router = useRouter()
const { t } = useI18n()

const assets = ref([])
const locations = ref([])
const users = ref([])
const assetProducts = ref([])
const loading = ref(false)
const showAddModal = ref(false)
const showBarcodeModal = ref(false)
const showQRModal = ref(false)

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
  asset_tag: '',
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

// QR Code configuration
const qrLabelSizes = {
  small: {
    width: '50mm',
    height: '60mm',
    qrSize: 150,
    tagFontSize: '10px',
    nameFontSize: '8px',
    padding: '2mm'
  },
  medium: {
    width: '70mm',
    height: '85mm',
    qrSize: 220,
    tagFontSize: '12px',
    nameFontSize: '10px',
    padding: '3mm'
  },
  large: {
    width: '100mm',
    height: '120mm',
    qrSize: 300,
    tagFontSize: '16px',
    nameFontSize: '12px',
    padding: '4mm'
  }
}

const qrData = ref({
  asset_id: '',
  asset_tag: '',
  serial_number: '',
  product_name: '',
  copies: 1,
  labelSize: 'medium'
})

const qrPreviewStyle = computed(() => {
  const size = qrLabelSizes[qrData.value.labelSize]
  return {
    border: '1px dashed #ccc',
    padding: size.padding,
    display: 'inline-block'
  }
})

const filters = ref({
  search: '',
  status: '',
  location_id: '',
  pic: ''
})

const assetForm = ref({
  product_id: '',
  serial_number: '',
  location_id: '',
  condition: 'NEW',
  purchase_date: new Date().toISOString().split('T')[0],
  purchase_price: '',
  useful_life_years: 5,
  warranty_expiry: '',
  notes: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

onMounted(async () => {
  await Promise.all([
    loadAssets(),
    loadLocations(),
    loadUsers(),
    loadAssetProducts()
  ])
})

const exportToExcel = () => {
  try {
    const exportData = assets.value.map(asset => ({
      'Asset Tag': asset.asset_tag,
      'Product': asset.product?.name || '',
      'Serial Number': asset.serial_number || '',
      'Location': asset.location?.name || '',
      'PIC': asset.pic || '',
      'Status': asset.status,
      'Condition': asset.condition || '',
      'Purchase Date': asset.purchase_date || '',
      'Purchase Price': asset.purchase_price || 0,
      'Vendor': asset.vendor || '',
      'Warranty Expiry': asset.warranty_expiry || '',
      'Notes': asset.notes || ''
    }))

    if (exportData.length === 0) {
      alert(t('assets.noDataToExport'))
      return
    }

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(exportData)

    ws['!cols'] = [
      { wch: 15 },  // Asset Tag
      { wch: 25 },  // Product
      { wch: 20 },  // Serial Number
      { wch: 20 },  // Location
      { wch: 20 },  // PIC
      { wch: 15 },  // Status
      { wch: 12 },  // Condition
      { wch: 15 },  // Purchase Date
      { wch: 15 },  // Purchase Price
      { wch: 25 },  // Vendor
      { wch: 15 },  // Warranty Expiry
      { wch: 40 }   // Notes
    ]

    XLSX.utils.book_append_sheet(wb, ws, 'Assets')

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    const filename = `Assets_${timestamp}.xlsx`

    XLSX.writeFile(wb, filename)
    alert(t('assets.exportSuccess', { filename: filename, count: exportData.length }))
  } catch (error) {
    console.error('Export error:', error)
    alert(t('assets.exportFailed') + ': ' + error.message)
  }
}

const loadAssets = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      ...filters.value
    }
    
    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })

    const response = await assetService.getAssets(params)
    
    if (response.data) {
      assets.value = response.data
      pagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        per_page: response.per_page || 20,
        total: response.total || 0,
        from: response.from || 0,
        to: response.to || 0
      }
    } else {
      assets.value = response
    }
  } catch (error) {
    console.error('Failed to load assets:', error)
    alert(t('assets.loadFailed') + ': ' + (error.response?.data?.message || error.message))
  }  finally {
    loading.value = false
  }
}

const loadLocations = async () => {
  try {
    const response = await locationService.getLocations()
    locations.value = response.data || response
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await userService.getUsers()
    users.value = response.data || response
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const loadAssetProducts = async () => {
  try {
    // Force query string in URL to ensure params are sent
    const response = await api.get('/products?type=ASSET&per_page=500')
    assetProducts.value = response.data.data || response.data
    // CRITICAL: Filter client-side as backup
    assetProducts.value = assetProducts.value.filter(p => p.type === 'ASSET')
    filteredProducts.value = assetProducts.value
    console.log('Loaded asset products:', assetProducts.value.length, 'items')
    console.log('Product types:', assetProducts.value.map(p => p.type))
  } catch (error) {
    console.error('Failed to load asset products:', error)
  }
}

const filterProducts = () => {
  const search = productSearch.value.toLowerCase()
  if (!search) {
    filteredProducts.value = assetProducts.value
  } else {
    filteredProducts.value = assetProducts.value.filter(product => 
      product.name.toLowerCase().includes(search) ||
      (product.sku && product.sku.toLowerCase().includes(search))
    )
  }
  showProductDropdown.value = true
}

const selectProduct = (product) => {
  assetForm.value.product_id = product.id
  productSearch.value = product.name
  showProductDropdown.value = false
}

const handleProductBlur = () => {
  setTimeout(() => {
    showProductDropdown.value = false
  }, 200)
}

const createAsset = async () => {
  try {
    loading.value = true
    await assetService.createAsset(assetForm.value)
    alert(t('assets.createSuccess'))
    closeAddModal()
    await loadAssets()
  } catch (error) {
    console.error('Failed to create asset:', error)
    alert(t('assets.createFailed') + ': ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const closeAddModal = () => {
  showAddModal.value = false
  productSearch.value = ''
  showProductDropdown.value = false
  filteredProducts.value = assetProducts.value
  assetForm.value = {
    product_id: '',
    serial_number: '',
    location_id: '',
    condition: 'NEW',
    purchase_date: new Date().toISOString().split('T')[0],
    purchase_price: '',
    useful_life_years: 5,
    warranty_expiry: '',
    notes: ''
  }
}

const printBarcode = async (asset) => {
  barcodeData.value = {
    asset_tag: asset.asset_tag,
    product_name: asset.product?.name || 'Unknown Product',
    copies: 1,
    labelSize: 'medium'
  }
  showBarcodeModal.value = true
  
  await nextTick()
  generateBarcodePreview()
}

const generateBarcodePreview = () => {
  try {
    const svg = document.getElementById('barcode-svg')
    const size = labelSizes[barcodeData.value.labelSize]
    
    if (svg && barcodeData.value.asset_tag) {
      JsBarcode(svg, barcodeData.value.asset_tag, {
        format: 'CODE128',
        width: size.barcodeWidth,
        height: size.barcodeHeight,
        displayValue: false,
        margin: size.barcodeMargin
      })
    }
  } catch (error) {
    console.error('Failed to generate barcode:', error)
    alert(t('assets.barcodeGenerateFailed'))
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
        <div class="barcode-text">${barcodeData.value.asset_tag}</div>
        <div class="product-name">${barcodeData.value.product_name}</div>
      </div>
    `
  }
  
  printWindow.document.write(`
    <html>
      <head>
        <title>Print Asset Tag - ${barcodeData.value.asset_tag}</title>
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
        JsBarcode(svg, barcodeData.value.asset_tag, {
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
    asset_tag: '',
    product_name: '',
    copies: 1,
    labelSize: 'medium'
  }
}

// QR Code Functions
const printQRCode = async (asset) => {
  const assetURL = `${window.location.origin}/assets/${asset.id}`
  
  qrData.value = {
    asset_id: asset.id,
    asset_tag: asset.asset_tag,
    serial_number: asset.serial_number || 'N/A',
    product_name: asset.product?.name || 'Unknown Product',
    url: assetURL,
    copies: 1,
    labelSize: 'medium'
  }
  showQRModal.value = true
  
  await nextTick()
  generateQRPreview()
}

const generateQRPreview = async () => {
  try {
    const canvas = document.getElementById('qr-canvas')
    const size = qrLabelSizes[qrData.value.labelSize]
    
    if (canvas && qrData.value.url) {
      // Generate QR code with asset URL
      await QRCode.toCanvas(canvas, qrData.value.url, {
        width: size.qrSize,
        margin: 2,
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      })
    }
  } catch (error) {
    console.error('Failed to generate QR code:', error)
    alert(t('assets.qrGenerateFailed'))
  }
}

const printQRLabels = async () => {
  const printWindow = window.open('', '', 'width=800,height=600')
  const size = qrLabelSizes[qrData.value.labelSize]
  
  let labelsHTML = ''
  for (let i = 0; i < qrData.value.copies; i++) {
    labelsHTML += `
      <div class="qr-label">
        <canvas id="qr-${i}"></canvas>
        <div class="qr-tag">${qrData.value.asset_tag}</div>
        <div class="qr-product">${qrData.value.product_name}</div>
      </div>
    `
  }
  
  printWindow.document.write(`
    <html>
      <head>
        <title>Print QR Code - ${qrData.value.asset_tag}</title>
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
          
          .qr-label {
            width: ${size.width};
            height: ${size.height};
            padding: ${size.padding};
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-after: always;
            border: 1px dashed #ccc;
          }
          
          .qr-label:last-child {
            page-break-after: auto;
          }
          
          canvas {
            display: block;
            margin: 0 auto;
          }
          
          .qr-tag {
            font-family: monospace;
            font-size: ${size.tagFontSize};
            font-weight: bold;
            margin-top: 2mm;
            color: #000;
          }
          
          .qr-product {
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
            .qr-label { 
              margin: 0;
              border: none;
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
  
  // Generate QR codes for each copy
  setTimeout(async () => {
    for (let i = 0; i < qrData.value.copies; i++) {
      const canvas = printWindow.document.getElementById(`qr-${i}`)
      if (canvas) {
        await QRCode.toCanvas(canvas, qrData.value.url, {
          width: size.qrSize,
          margin: 2,
          color: {
            dark: '#000000',
            light: '#FFFFFF'
          }
        })
      }
    }
    
    setTimeout(() => {
      printWindow.print()
      printWindow.close()
    }, 500)
  }, 500)
}

const closeQRModal = () => {
  showQRModal.value = false
  qrData.value = {
    asset_id: '',
    asset_tag: '',
    serial_number: '',
    product_name: '',
    url: '',
    copies: 1,
    labelSize: 'medium'
  }
}

const viewAsset = (asset) => {
  router.push(`/assets/${asset.id}`)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'AVAILABLE': 'badge-green',
    'ASSIGNED': 'badge-blue',
    'IN_USE': 'badge-blue',
    'MAINTENANCE': 'badge-yellow',
    'DAMAGED': 'badge-red',
    'DISPOSED': 'badge-gray'
  }
  return classes[status] || 'badge-gray'
}

const getConditionBadgeClass = (condition) => {
  const classes = {
    'NEW': 'badge-green',
    'GOOD': 'badge-blue',
    'FAIR': 'badge-yellow',
    'POOR': 'badge-orange',
    'BROKEN': 'badge-red'
  }
  return classes[condition] || 'badge-gray'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}
</script>
