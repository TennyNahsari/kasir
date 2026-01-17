import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useCompanyStore = defineStore('company', () => {
  const company = ref({
    name: 'PT Sumber Rezeki Jaya',
    address: 'Jl. Raya Bisnis No. 123, Jakarta Selatan 12345',
    phone: '+62 21 1234 5678',
    email: 'info@sumberrezeki.com',
    website: 'www.sumberrezeki.com',
    logo: null,
    defaultTaxPercentage: 11,
    taxLabel: 'PPN',
    currency: 'IDR',
    currencySymbol: 'Rp',
    footerText: 'Dokumen ini dibuat secara otomatis dan sah tanpa tanda tangan.',
    bankAccounts: []
  })

  // Load from localStorage on init
  const loadFromStorage = () => {
    const saved = localStorage.getItem('company_settings')
    if (saved) {
      try {
        company.value = { ...company.value, ...JSON.parse(saved) }
      } catch (e) {
        console.error('Error loading company settings:', e)
      }
    }
  }

  // Save to localStorage
  const saveSettings = (settings) => {
    company.value = { ...company.value, ...settings }
    localStorage.setItem('company_settings', JSON.stringify(company.value))
  }

  // Initialize
  loadFromStorage()

  return {
    company,
    saveSettings,
    loadFromStorage
  }
})
