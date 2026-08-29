<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">💬 {{ $t('settings.whatsapp.title') }}</h2>
      <p class="text-sm text-gray-500 mt-1">{{ $t('settings.whatsapp.subtitle') }}</p>
    </div>

    <form @submit.prevent="save" class="card space-y-5">
      <!-- WhatsApp Number -->
      <div>
        <label class="label">💬 {{ $t('settings.whatsapp.numberLabel') }}</label>
        <input
          v-model="form.whatsapp_number"
          type="tel"
          class="input"
          :placeholder="$t('settings.whatsapp.placeholder')"
          :disabled="loading"
        >
        <p class="text-xs text-gray-500 mt-1">{{ $t('settings.whatsapp.helpText') }}</p>
      </div>

      <!-- Social Media Section Header -->
      <div class="pt-4 border-t border-gray-200">
        <h3 class="text-base font-bold text-gray-800 mb-1">📱 Media Sosial Outlet</h3>
        <p class="text-xs text-gray-500 mb-4">Link media sosial di bawah ini akan tampil sebagai icon di bagian footer homepage.</p>

        <div class="space-y-4">
          <!-- Instagram -->
          <div>
            <label class="label text-xs font-semibold text-gray-700">📸 Link Instagram</label>
            <input
              v-model="form.instagram_url"
              type="url"
              class="input text-sm"
              placeholder="https://instagram.com/nama_outlet"
              :disabled="loading"
            >
          </div>

          <!-- Facebook -->
          <div>
            <label class="label text-xs font-semibold text-gray-700">📘 Link Facebook</label>
            <input
              v-model="form.facebook_url"
              type="url"
              class="input text-sm"
              placeholder="https://facebook.com/nama_outlet"
              :disabled="loading"
            >
          </div>

          <!-- TikTok -->
          <div>
            <label class="label text-xs font-semibold text-gray-700">🎵 Link TikTok</label>
            <input
              v-model="form.tiktok_url"
              type="url"
              class="input text-sm"
              placeholder="https://tiktok.com/@nama_outlet"
              :disabled="loading"
            >
          </div>

          <!-- YouTube -->
          <div>
            <label class="label text-xs font-semibold text-gray-700">▶️ Link YouTube</label>
            <input
              v-model="form.youtube_url"
              type="url"
              class="input text-sm"
              placeholder="https://youtube.com/@nama_outlet"
              :disabled="loading"
            >
          </div>
        </div>
      </div>

      <div v-if="message" class="text-sm font-medium" :class="saved ? 'text-green-600' : 'text-red-600'">
        {{ message }}
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? $t('settings.whatsapp.savingButton') : $t('settings.whatsapp.saveButton') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const { t } = useI18n()

const form = ref({
  whatsapp_number: '',
  instagram_url: '',
  facebook_url: '',
  tiktok_url: '',
  youtube_url: ''
})

const loading = ref(false)
const saved = ref(false)
const message = ref('')

const load = async () => {
  try {
    const { data } = await api.get('/settings/whatsapp')
    form.value = {
      whatsapp_number: data.whatsapp_number || '',
      instagram_url: data.instagram_url || '',
      facebook_url: data.facebook_url || '',
      tiktok_url: data.tiktok_url || '',
      youtube_url: data.youtube_url || ''
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
    message.value = t('settings.whatsapp.loadFailed')
    saved.value = false
  }
}

const save = async () => {
  loading.value = true
  message.value = ''
  try {
    await api.put('/settings/whatsapp', form.value)
    message.value = t('settings.whatsapp.saveSuccess')
    saved.value = true
  } catch (error) {
    message.value = error.response?.data?.message || t('settings.whatsapp.saveFailed')
    saved.value = false
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
