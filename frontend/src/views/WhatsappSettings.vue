<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Pengaturan WhatsApp</h2>
      <p class="text-sm text-gray-500 mt-1">Nomor ini digunakan oleh tombol WhatsApp di homepage.</p>
    </div>

    <form @submit.prevent="save" class="card space-y-5">
      <div>
        <label class="label">Nomor WhatsApp</label>
        <input
          v-model="whatsappNumber"
          type="tel"
          class="input"
          placeholder="6281234567890"
          :disabled="loading"
        >
        <p class="text-xs text-gray-500 mt-1">Gunakan format internasional, contoh: 6281234567890.</p>
      </div>

      <div v-if="message" class="text-sm" :class="saved ? 'text-green-600' : 'text-red-600'">
        {{ message }}
      </div>

      <div class="flex justify-end">
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Menyimpan...' : 'Simpan Pengaturan' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '@/services/api'

const whatsappNumber = ref('')
const loading = ref(false)
const saved = ref(false)
const message = ref('')

const load = async () => {
  try {
    const { data } = await api.get('/settings/whatsapp')
    whatsappNumber.value = data.whatsapp_number || ''
  } catch (error) {
    message.value = 'Gagal memuat pengaturan WhatsApp.'
    saved.value = false
  }
}

const save = async () => {
  loading.value = true
  message.value = ''
  try {
    await api.put('/settings/whatsapp', { whatsapp_number: whatsappNumber.value })
    message.value = 'Pengaturan WhatsApp berhasil disimpan.'
    saved.value = true
  } catch (error) {
    message.value = error.response?.data?.message || 'Gagal menyimpan pengaturan WhatsApp.'
    saved.value = false
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
