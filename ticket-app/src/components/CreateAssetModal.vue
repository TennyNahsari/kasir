<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="$emit('close')">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Edit Asset' : 'Create New Asset' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Product Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Product <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.product_id"
            required
            :disabled="loadingProducts"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select Product</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.name }} - {{ product.sku }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Serial Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
            <input
              v-model="form.serial_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Condition -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Condition <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.condition"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="cond in ASSET_CONDITIONS" :key="cond.value" :value="cond.value">
                {{ cond.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Location -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Location <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.location_id"
            required
            :disabled="loadingLocations"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select Location</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>

        <!-- PIC (Person in Charge) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            PIC (Person in Charge)
          </label>
          <div class="relative">
            <input
              v-model="picSearch"
              @focus="showPicDropdown = true"
              @blur="() => setTimeout(() => showPicDropdown = false, 200)"
              type="text"
              placeholder="Search by email..."
              :disabled="loadingUsers"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <div v-if="showPicDropdown && filteredUsers.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
              <button
                v-for="user in filteredUsers"
                :key="user.id"
                type="button"
                @click="selectPic(user)"
                class="w-full px-3 py-2 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
              >
                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                <div class="text-xs text-gray-500">{{ user.email }}</div>
              </button>
            </div>
            <div v-if="selectedPic" class="mt-2 inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
              <span>{{ selectedPic.name }} ({{ selectedPic.email }})</span>
              <button type="button" @click="clearPic" class="ml-2 text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Purchase Information -->
        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Purchase Information</h4>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Purchase Price <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.purchase_price"
                type="number"
                required
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Purchase Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.purchase_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Depreciation Information -->
        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Depreciation</h4>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Useful Life (Years) <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.useful_life_years"
                type="number"
                required
                min="1"
                max="50"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Residual Value <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.residual_value"
                type="number"
                required
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-3">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ submitting ? 'Saving...' : (isEdit ? 'Update Asset' : 'Create Asset') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import assetService from '@/services/assetService'
import axios from '@/utils/axios'
import { ASSET_CONDITIONS } from '@/utils/assetHelpers'

const props = defineProps({
  asset: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const isEdit = computed(() => !!props.asset)

const form = ref({
  product_id: '',
  serial_number: '',
  condition: 'GOOD',
  location_id: '',
  pic: '',
  purchase_price: 0,
  purchase_date: new Date().toISOString().split('T')[0],
  useful_life_years: 5,
  residual_value: 0,
  description: ''
})

const products = ref([])
const locations = ref([])
const users = ref([])
const loadingProducts = ref(false)
const loadingLocations = ref(false)
const loadingUsers = ref(false)
const submitting = ref(false)
const error = ref(null)

// PIC autocomplete
const picSearch = ref('')
const showPicDropdown = ref(false)
const selectedPic = ref(null)

const filteredUsers = computed(() => {
  if (!picSearch.value) return users.value.slice(0, 10)
  return users.value.filter(user => 
    user.email.toLowerCase().includes(picSearch.value.toLowerCase()) ||
    user.name.toLowerCase().includes(picSearch.value.toLowerCase())
  ).slice(0, 10)
})

const selectPic = (user) => {
  selectedPic.value = user
  form.value.pic = user.email
  picSearch.value = ''
  showPicDropdown.value = false
}

const clearPic = () => {
  selectedPic.value = null
  form.value.pic = ''
  picSearch.value = ''
}

const loadProducts = async () => {
  try {
    loadingProducts.value = true
    const response = await axios.get('/products')
    products.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading products:', err)
  } finally {
    loadingProducts.value = false
  }
}

const loadLocations = async () => {
  try {
    loadingLocations.value = true
    const response = await axios.get('/locations')
    locations.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading locations:', err)
  } finally {
    loadingLocations.value = false
  }
}

const loadUsers = async () => {
  try {
    loadingUsers.value = true
    const response = await axios.get('/users', { params: { is_active: 1 } })
    users.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading users:', err)
  } finally {
    loadingUsers.value = false
  }
}

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    if (isEdit.value) {
      await assetService.updateAsset(props.asset.id, form.value)
    } else {
      await assetService.createAsset(form.value)
    }

    emit('saved')
  } catch (err) {
    console.error('Error saving asset:', err)
    error.value = err.response?.data?.message || 'Failed to save asset'
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  loadProducts()
  loadLocations()
  await loadUsers()

  if (props.asset) {
    form.value = {
      product_id: props.asset.product_id,
      serial_number: props.asset.serial_number || '',
      condition: props.asset.condition,
      location_id: props.asset.location_id,
      pic: props.asset.pic || '',
      purchase_price: props.asset.purchase_price,
      purchase_date: props.asset.purchase_date?.split('T')[0] || '',
      useful_life_years: props.asset.useful_life_years || 5,
      residual_value: props.asset.residual_value || 0,
      description: props.asset.description || ''
    }
    
    // Set selected PIC if editing
    if (props.asset.pic) {
      const user = users.value.find(u => u.email === props.asset.pic)
      if (user) {
        selectedPic.value = user
      }
    }
  }
})
</script>
