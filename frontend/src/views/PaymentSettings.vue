<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">{{ $t('settings.payment.title') }}</h2>
      <p class="text-sm text-gray-500 mt-1">{{ $t('settings.payment.subtitle') }}</p>
    </div>

    <form @submit.prevent="save" class="card space-y-6">
      <!-- Bank Transfer Section -->
      <div>
        <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <span>🏦</span> {{ $t('settings.payment.bankSection') }}
        </h3>
        <div class="space-y-4">
          <div>
            <label class="label">{{ $t('settings.payment.bankName') }}</label>
            <input
              v-model="form.bank_name"
              type="text"
              class="input"
              :placeholder="$t('settings.payment.bankPlaceholder')"
              :disabled="loading"
            >
          </div>
          <div>
            <label class="label">{{ $t('settings.payment.accountNumber') }}</label>
            <input
              v-model="form.bank_account_number"
              type="text"
              class="input"
              :placeholder="$t('settings.payment.accountPlaceholder')"
              :disabled="loading"
            >
          </div>
          <div>
            <label class="label">{{ $t('settings.payment.accountName') }}</label>
            <input
              v-model="form.bank_account_name"
              type="text"
              class="input"
              :placeholder="$t('settings.payment.ownerPlaceholder')"
              :disabled="loading"
            >
          </div>
        </div>
      </div>

      <!-- QRIS Section -->
      <div class="pt-4 border-t">
        <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <span>📱</span> {{ $t('settings.payment.qrisSection') }}
        </h3>

        <!-- Current QRIS Image Preview -->
        <div v-if="currentQrisUrl" class="mb-4">
          <p class="text-xs text-gray-500 mb-2">{{ $t('settings.payment.currentQris') }}</p>
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
              :title="$t('settings.payment.deleteQrisConfirm')"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Upload QRIS -->
        <div>
          <label class="label">{{ currentQrisUrl ? $t('settings.payment.changeQris') : $t('settings.payment.uploadQris') }}</label>
          <input
            type="file"
            ref="qrisFileInput"
            accept="image/*"
            @change="onQrisFileChange"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F9F6F0] file:text-[#6B2E3E] hover:file:bg-[#E5D9C5] transition-all"
            :disabled="loading"
          >
          <p class="text-xs text-gray-400 mt-1">{{ $t('settings.payment.imageHelpText') }}</p>
        </div>

        <!-- Preview new upload -->
        <div v-if="qrisPreview" class="mt-3">
          <p class="text-xs text-green-600 mb-1 font-medium">{{ $t('settings.payment.previewNew') }}</p>
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
          {{ loading ? $t('settings.payment.savingButton') : $t('settings.payment.saveButton') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const { t } = useI18n()

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
    message.value = t('settings.payment.fileMustBeImage')
    saved.value = false
    e.target.value = ''
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    message.value = t('settings.payment.fileTooLarge')
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
    message.value = t('settings.payment.loadFailed')
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

    message.value = t('settings.payment.saveSuccess')
    saved.value = true
  } catch (error) {
    message.value = error.response?.data?.message || t('settings.payment.saveFailed')
    saved.value = false
  } finally {
    loading.value = false
  }
}

const deleteQris = async () => {
  if (!confirm(t('settings.payment.deleteQrisConfirm'))) return
  try {
    await api.delete('/settings/payment/qris')
    currentQrisImage.value = ''
    message.value = t('settings.payment.deleteQrisSuccess')
    saved.value = true
  } catch (error) {
    message.value = t('settings.payment.deleteQrisFailed')
    saved.value = false
  }
}

onMounted(load)
</script>
