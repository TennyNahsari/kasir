<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Company Settings</h1>
      <p class="text-gray-600 mt-2">Manage your company information for documents and PDFs</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Basic Information -->
        <div>
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
              <input 
                v-model="form.name" 
                type="text" 
                required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input 
                v-model="form.phone" 
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
              <textarea 
                v-model="form.address" 
                rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input 
                v-model="form.email" 
                type="email"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
              <input 
                v-model="form.website" 
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>
        </div>

        <!-- Tax & Currency -->
        <div class="border-t pt-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Tax & Currency</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tax Label</label>
              <input 
                v-model="form.taxLabel" 
                type="text"
                placeholder="PPN"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Default Tax (%)</label>
              <input 
                v-model.number="form.defaultTaxPercentage" 
                type="number"
                step="0.01"
                min="0"
                max="100"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
              <select 
                v-model="form.currency"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="IDR">IDR - Indonesian Rupiah</option>
                <option value="USD">USD - US Dollar</option>
                <option value="EUR">EUR - Euro</option>
              </select>
            </div>
          </div>
        </div>

        <!-- PDF Settings -->
        <div class="border-t pt-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">PDF Settings</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Footer Text</label>
            <textarea 
              v-model="form.footerText" 
              rows="2"
              placeholder="Dokumen ini dibuat secara otomatis dan sah tanpa tanda tangan."
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
          </div>
        </div>

        <!-- Bank Accounts -->
        <div class="border-t pt-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Bank Accounts</h2>
            <button 
              type="button" 
              @click="addBankAccount"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
              + Add Bank Account
            </button>
          </div>
          
          <div v-for="(account, index) in form.bankAccounts" :key="index" class="bg-gray-50 p-4 rounded-lg mb-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                <input 
                  v-model="account.bank" 
                  type="text"
                  placeholder="Bank Central Asia"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                <input 
                  v-model="account.accountNumber" 
                  type="text"
                  placeholder="1234567890"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                <div class="flex gap-2">
                  <input 
                    v-model="account.accountName" 
                    type="text"
                    placeholder="PT Company Name"
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <button 
                    type="button" 
                    @click="removeBankAccount(index)"
                    class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700">
                    ✕
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <p v-if="form.bankAccounts.length === 0" class="text-gray-500 text-sm text-center py-4">
            No bank accounts added yet
          </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t">
          <button 
            type="button" 
            @click="resetForm"
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            Reset
          </button>
          <button 
            type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Save Settings
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useCompanyStore } from '@/stores/company'

const companyStore = useCompanyStore()

const form = ref({
  name: '',
  address: '',
  phone: '',
  email: '',
  website: '',
  logo: null,
  defaultTaxPercentage: 11,
  taxLabel: 'PPN',
  currency: 'IDR',
  currencySymbol: 'Rp',
  footerText: '',
  bankAccounts: []
})

const loadSettings = () => {
  form.value = { ...companyStore.company }
}

const saveSettings = () => {
  companyStore.saveSettings(form.value)
  alert('Company settings saved successfully!')
}

const resetForm = () => {
  loadSettings()
}

const addBankAccount = () => {
  form.value.bankAccounts.push({
    bank: '',
    accountNumber: '',
    accountName: ''
  })
}

const removeBankAccount = (index) => {
  form.value.bankAccounts.splice(index, 1)
}

onMounted(() => {
  loadSettings()
})
</script>
