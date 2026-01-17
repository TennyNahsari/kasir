// Company/Organization configuration for PDF and documents
export const companyConfig = {
  name: 'PT Sumber Rezeki Jaya', // Ganti dengan nama perusahaan Anda
  address: 'Jl. Raya Bisnis No. 123, Jakarta Selatan 12345',
  phone: '+62 21 1234 5678',
  email: 'info@sumberrezeki.com',
  website: 'www.sumberrezeki.com',
  
  // Logo URL (bisa pakai URL external atau import local file)
  // Contoh: '/logo.png' atau 'https://yoursite.com/logo.png'
  logo: null, // Set ke null jika tidak ada logo
  
  // Tax settings
  defaultTaxPercentage: 11, // PPN 11%
  taxLabel: 'PPN',
  
  // Currency
  currency: 'IDR',
  currencySymbol: 'Rp',
  
  // PDF Footer text
  footerText: 'Dokumen ini dibuat secara otomatis dan sah tanpa tanda tangan.',
  
  // Bank details (optional - untuk ditampilkan di PDF)
  bankAccounts: [
    {
      bank: 'Bank Central Asia (BCA)',
      accountNumber: '1234567890',
      accountName: 'PT Sumber Rezeki Jaya'
    },
    {
      bank: 'Bank Mandiri',
      accountNumber: '0987654321',
      accountName: 'PT Sumber Rezeki Jaya'
    }
  ]
}
