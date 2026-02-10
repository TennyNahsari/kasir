<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Edit Product' : 'Create New Product' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>

        <!-- SKU -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            SKU <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.sku"
            type="text"
            required
            maxlength="50"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            maxlength="200"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Description
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Category -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Category <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.category_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select category...</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Selling Price -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Selling Price <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.selling_price"
              type="number"
              required
              min="0"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Cost Price -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Cost Price <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.cost_price"
              type="number"
              required
              min="0"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Is Active -->
        <div class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="is_active"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="is_active" class="ml-2 block text-sm text-gray-700">
            Active
          </label>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            :disabled="submitting"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="submitting"
          >
            {{ submitting ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/utils/axios'

const props = defineProps({
  product: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'saved'])

const isEdit = computed(() => !!props.product)

const form = ref({
  sku: '',
  name: '',
  description: '',
  category_id: '',
  selling_price: 0,
  cost_price: 0,
  is_active: true
})

const submitting = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = null

    if (isEdit.value) {
      await axios.put(`/products/${props.product.id}`, form.value)
    } else {
      await axios.post('/products', form.value)
    }
    
    emit('saved')
  } catch (err) {
    console.error('Error saving product:', err)
    error.value = err.response?.data?.message || 'Failed to save product'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  if (props.product) {
    form.value = {
      sku: props.product.sku || '',
      name: props.product.name || '',
      description: props.product.description || '',
      category_id: props.product.category_id || '',
      selling_price: props.product.selling_price || 0,
      cost_price: props.product.cost_price || 0,
      is_active: props.product.is_active !== undefined ? props.product.is_active : true
    }
  }
})
</script>
