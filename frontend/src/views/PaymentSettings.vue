<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Pengaturan Pembayaran Online</h2>
      <p class="text-sm text-gray-500 mt-1">Informasi rekening bank dan QRIS yang ditampilkan pada halaman order online.</p>
    </div>

    <form @submit.prevent="save" class="card space-y-6">
      <!-- Bank Transfer Section -->
      <div>
        <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <span>🏦</span> Rekening Bank Transfer
        </h3>
        <div class="space-y-4">
          <div>
            <label class="label">Nama Bank</label>
            <input
              v-model="form.bank_name"
              type="text"
              class="input"
              placeholder="BCA / BNI / Mandiri / dll"
              :disabled="loading"
            >
          </div>
          <div>
            <label class="label">Nomor Rekening</label>
            <input
              v-model="form.bank_account_number"
              type="text"
              class="input"
              placeholder="1234567890"
              :disabled="loading"
            >
          </div>
          <div>
            <label class="label">Atas Nama</label>
            <input
              v-model="form.bank_account_name"
              type="text"
              class="input"
              placeholder="PT. Contoh Usaha"
              :disabled="loading"
            >
          </div>
        </div>
      </div>

      <!-- QRIS Section -->
      <div class="pt-4 border-t">
        <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <span>📱</span> QRIS (QR Code Pembayaran)
        </h3>

        <!-- Current QRIS Image Preview -->
        <div v-if="currentQrisUrl" class="mb-4">
          <p class="text-xs text-gray-500 mb-2">Gambar QRIS saat ini:</p>
          <div class="relative inline-block">
            <img
              :src="currentQrisUrl"
              alt="QRIS"
              class="w-48 h-48 object-contain border rounded-xl bg-white p-2"
            >
            <button
              type="button"
              @click="deleteQris"
              class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
              title="Hapus gambar QRIS"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Upload QRIS -->
        <div>
          <label class="label">{{ currentQrisUrl ? 'Ganti Gambar QRIS' : 'Upload Gambar QRIS' }}</label>
          <input
            type="file"
            ref="qrisFileInput"
            accept="image/*"
            @change="onQrisFileChange"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F9F6F0] file:text-[#6B2E3E] hover:file:bg-[#E5D9C5] transition-all"
            :disabled="loading"
          >
          <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks: 2MB.</p>
        </div>

        <!-- Preview new upload -->
        <div v-if="qrisPreview" class="mt-3">
          <p class="text-xs text-green-600 mb-1 font-medium">Preview upload baru:</p>
          <img
            :src="qrisPreview"
            alt="Preview QRIS"
            class="w-48 h-48 object-contain border-2 border-green-300 rounded-xl bg-white p-2"
          >
        </div>
      </div>

      <div v-if="message" class="text-sm" :class="saved ? 'text-green-600' : 'text-red-600'">
        {{ message }}
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Menyimpan...' : 'Simpan Pengaturan' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import api from '@/services/api'

const form = ref({
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
})

const currentQrisImage = ref('')
const qrisFile = ref(null)
const qrisPreview = ref(null)
const qrisFileInput = ref(null)
const loading = ref(false)
const saved = ref(false)
const message = ref('')

const currentQrisUrl = computed(() => {
  if (!currentQrisImage.value) return ''
  if (currentQrisImage.value.startsWith('http')) return currentQrisImage.value
  const imagePath = currentQrisImage.value.replace(/^\/?(storage\/)?/, '')
  const apiBaseUrl = api.defaults.baseURL || ''
  const appBaseUrl = apiBaseUrl.replace(/\/api\/?$/, '') || window.location.origin
  return `${appBaseUrl}/storage/${imagePath}`
})

const onQrisFileChange = (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    message.value = 'File QRIS harus berupa gambar.'
    saved.value = false
    e.target.value = ''
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    message.value = 'Ukuran gambar QRIS maksimal 2MB.'
    saved.value = false
    e.target.value = ''
    return
  }

  if (qrisPreview.value) URL.revokeObjectURL(qrisPreview.value)
  qrisFile.value = file
  qrisPreview.value = URL.createObjectURL(file)
  message.value = ''
}

const load = async () => {
  try {
    const { data } = await api.get('/settings/payment')
    form.value.bank_name = data.bank_name || ''
    form.value.bank_account_number = data.bank_account_number || ''
    form.value.bank_account_name = data.bank_account_name || ''
    currentQrisImage.value = data.qris_image || ''
  } catch (error) {
    message.value = 'Gagal memuat pengaturan pembayaran.'
    saved.value = false
  }
}

const save = async () => {
  loading.value = true
  message.value = ''
  try {
    const formData = new FormData()
    formData.append('bank_name', form.value.bank_name || '')
    formData.append('bank_account_number', form.value.bank_account_number || '')
    formData.append('bank_account_name', form.value.bank_account_name || '')
    const selectedFile = qrisFile.value || qrisFileInput.value?.files?.[0]
    if (selectedFile) {
      formData.append('qris_image', selectedFile)
    }

    const { data } = await api.post('/settings/payment', formData, {
      // Remove the axios instance's JSON header. The browser must set the
      // multipart boundary itself, otherwise Laravel may not detect the file.
      transformRequest: [(payload, headers) => {
        if (typeof headers.delete === 'function') {
          headers.delete('Content-Type')
        } else {
          delete headers['Content-Type']
        }
        return payload
      }]
    })

    currentQrisImage.value = data.qris_image || ''
    qrisFile.value = null
    qrisPreview.value = null
    if (qrisFileInput.value) qrisFileInput.value.value = ''

    message.value = 'Pengaturan pembayaran berhasil disimpan.'
    saved.value = true
  } catch (error) {
    message.value = error.response?.data?.message || 'Gagal menyimpan pengaturan pembayaran.'
    saved.value = false
  } finally {
    loading.value = false
  }
}

const deleteQris = async () => {
  if (!confirm('Hapus gambar QRIS?')) return
  try {
    await api.delete('/settings/payment/qris')
    currentQrisImage.value = ''
    message.value = 'Gambar QRIS berhasil dihapus.'
    saved.value = true
  } catch (error) {
    message.value = 'Gagal menghapus gambar QRIS.'
    saved.value = false
  }
}

onMounted(load)
</script>
