<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Vendors</h1>
        <p class="text-gray-600">Manage vendor information</p>
      </div>
      <button @click="showCreateModal = true" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
        Add Vendor
      </button>
    </div>

    <!-- Vendors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="vendor in vendors" :key="vendor.id" class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ vendor.name }}</h3>
            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full mt-1" :class="vendor.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
              {{ vendor.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <div class="flex space-x-2">
            <button @click="editVendor(vendor)" class="text-blue-600 hover:text-blue-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button @click="deleteVendor(vendor)" class="text-red-600 hover:text-red-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="space-y-2 text-sm text-gray-600">
          <div v-if="vendor.contact_person">
            <p class="font-medium text-gray-700">Contact Person:</p>
            <p>{{ vendor.contact_person }}</p>
          </div>
          <div v-if="vendor.email" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            {{ vendor.email }}
          </div>
          <div v-if="vendor.phone" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            {{ vendor.phone }}
          </div>
          <div v-if="vendor.address">
            <p class="font-medium text-gray-700 mt-2">Address:</p>
            <p>{{ vendor.address }}</p>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t">
          <div class="grid grid-cols-2 gap-2 text-sm">
            <div>
              <p class="text-gray-500">Payment Terms</p>
              <p class="font-medium">{{ vendor.payment_terms || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Lead Time</p>
              <p class="font-medium">{{ vendor.lead_time_days }} days</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl m-4">
        <h3 class="text-lg font-semibold mb-4">{{ editingVendor ? 'Edit' : 'Add' }} Vendor</h3>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Code *</label>
              <input v-model="vendorForm.vendor_code" type="text" class="w-full border-gray-300 rounded-lg" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Name *</label>
              <input v-model="vendorForm.name" type="text" class="w-full border-gray-300 rounded-lg" required>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
              <input v-model="vendorForm.contact_person" type="text" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="vendorForm.email" type="email" class="w-full border-gray-300 rounded-lg">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input v-model="vendorForm.phone" type="text" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fax</label>
              <input v-model="vendorForm.fax" type="text" class="w-full border-gray-300 rounded-lg">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea v-model="vendorForm.address" rows="2" class="w-full border-gray-300 rounded-lg"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
              <input v-model="vendorForm.payment_terms" type="text" placeholder="e.g., NET 30" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Lead Time (days)</label>
              <input v-model.number="vendorForm.lead_time_days" type="number" min="0" class="w-full border-gray-300 rounded-lg">
            </div>
          </div>
          <div class="flex items-center">
            <input v-model="vendorForm.is_active" type="checkbox" class="rounded border-gray-300 text-orange-600 mr-2">
            <label class="text-sm font-medium text-gray-700">Active</label>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="saveVendor" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const vendors = ref([])
const showCreateModal = ref(false)
const editingVendor = ref(null)

const vendorForm = ref({
  vendor_code: '',
  name: '',
  contact_person: '',
  email: '',
  phone: '',
  fax: '',
  address: '',
  payment_terms: '',
  lead_time_days: 0,
  is_active: true
})

onMounted(async () => {
  await loadVendors()
})

const loadVendors = async () => {
  try {
    const { data } = await api.get('/vendors')
    vendors.value = data
  } catch (error) {
    console.error('Failed to load vendors:', error)
  }
}

const editVendor = (vendor) => {
  editingVendor.value = vendor
  vendorForm.value = {
    vendor_code: vendor.vendor_code,
    name: vendor.name,
    contact_person: vendor.contact_person || '',
    email: vendor.email || '',
    phone: vendor.phone || '',
    fax: vendor.fax || '',
    address: vendor.address || '',
    payment_terms: vendor.payment_terms || '',
    lead_time_days: vendor.lead_time_days || 0,
    is_active: vendor.is_active
  }
  showCreateModal.value = true
}

const saveVendor = async () => {
  try {
    if (!vendorForm.value.vendor_code || !vendorForm.value.name) {
      alert('Please fill in all required fields')
      return
    }

    if (editingVendor.value) {
      await api.put(`/vendors/${editingVendor.value.id}`, vendorForm.value)
      alert('Vendor updated successfully')
    } else {
      await api.post('/vendors', vendorForm.value)
      alert('Vendor created successfully')
    }

    closeModal()
    await loadVendors()
  } catch (error) {
    alert('Failed to save vendor: ' + (error.response?.data?.message || error.message))
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingVendor.value = null
  vendorForm.value = {
    vendor_code: '',
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    fax: '',
    address: '',
    payment_terms: '',
    lead_time_days: 0,
    is_active: true
  }
}

const deleteVendor = async (vendor) => {
  if (!confirm(`Delete vendor "${vendor.name}"? This cannot be undone.`)) return
  
  try {
    await api.delete(`/vendors/${vendor.id}`)
    alert('Vendor deleted successfully')
    await loadVendors()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete vendor')
  }
}
</script>
