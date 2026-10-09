<template>
  <div class="max-w-4xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Pengaturan Hero Slider</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola gambar slide dan teks yang tampil di bagian utama (Hero Section) Landing Page.</p>
      </div>
      <button 
        @click="addEmptySlide" 
        type="button"
        class="px-4 py-2.5 rounded-lg bg-[#C9A96E] text-white font-medium text-sm hover:bg-[#B59458] transition-all flex items-center gap-2 shadow-xs cursor-pointer"
        :disabled="loading"
      >
        <span>➕</span> Tambah Slide Baru
      </button>
    </div>

    <!-- Feedback Message -->
    <div 
      v-if="message" 
      class="mb-4 p-4 rounded-xl text-sm font-medium transition-all" 
      :class="saved ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'"
    >
      {{ message }}
    </div>

    <form @submit.prevent="saveSlides" class="space-y-6">
      <!-- Loading state -->
      <div v-if="loading && slides.length === 0" class="card py-12 text-center text-gray-500">
        <div class="animate-spin text-3xl mb-2">⏳</div>
        <p>Memuat data slider...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="slides.length === 0" class="card py-12 text-center text-gray-500">
        <p class="text-base font-semibold text-gray-700">Belum Ada Slide</p>
        <p class="text-xs text-gray-400 mt-1">Klik tombol "Tambah Slide Baru" di atas untuk mengunggah gambar slider.</p>
      </div>

      <!-- Slides List -->
      <div v-else class="space-y-4">
        <div 
          v-for="(slide, index) in slides" 
          :key="slide.id" 
          class="bg-white border border-[#E5D9C5] rounded-xl p-5 shadow-xs hover:shadow-md transition-shadow relative"
        >
          <div class="flex flex-col md:flex-row gap-5 items-start">
            
            <!-- Slide Image Preview & File Upload Box -->
            <div class="w-full md:w-64 shrink-0">
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">Gambar Slide #{{ index + 1 }}</label>
              
              <!-- Image Preview Box -->
              <div class="aspect-16/9 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 relative group flex items-center justify-center">
                <img 
                  :src="getSlideImageUrl(slide.previewUrl || slide.image)" 
                  alt="Slide Image" 
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center">
                  <label class="px-3 py-1.5 bg-white text-gray-800 text-xs font-semibold rounded-lg cursor-pointer hover:bg-gray-100 shadow-sm">
                    📷 Ganti File Gambar
                    <input 
                      type="file" 
                      accept="image/*" 
                      class="hidden" 
                      @change="(e) => onFileSelected(e, index)"
                    />
                  </label>
                </div>
              </div>

              <!-- Visible File Upload Button -->
              <div class="mt-2.5">
                <label class="block w-full text-center px-3 py-2 bg-[#F9F6F0] border border-[#E5D9C5] rounded-lg text-xs font-semibold text-[#6B2E3E] hover:bg-[#E5D9C5]/50 transition-colors cursor-pointer">
                  <span v-if="slide.uploading">⏳ Mengunggah...</span>
                  <span v-else>📁 Upload Gambar dari File</span>
                  <input 
                    type="file" 
                    accept="image/*" 
                    class="hidden" 
                    :disabled="slide.uploading"
                    @change="(e) => onFileSelected(e, index)"
                  />
                </label>
              </div>
              
              <!-- Direct Image URL Option -->
              <div class="mt-2">
                <input 
                  type="text" 
                  v-model="slide.image" 
                  placeholder="Atau tempel URL gambar..." 
                  class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#C9A96E]"
                />
              </div>
            </div>

            <!-- Slide Content Inputs -->
            <div class="flex-1 space-y-3 w-full">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Slide (Headline Utama)</label>
                <input 
                  type="text" 
                  v-model="slide.title" 
                  placeholder="Contoh: L'ÉTOILE Signature Experience" 
                  class="input text-sm"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sub-judul / Deskripsi Singkat</label>
                <input 
                  type="text" 
                  v-model="slide.subtitle" 
                  placeholder="Contoh: White Marble • Dark Wood • Fine Coffee" 
                  class="input text-sm"
                />
              </div>
            </div>

            <!-- Actions (Move Up, Move Down, Remove) -->
            <div class="flex md:flex-col gap-1 shrink-0 self-end md:self-start">
              <button 
                type="button" 
                @click="moveSlideUp(index)" 
                :disabled="index === 0"
                class="p-2 text-gray-500 hover:text-gray-800 disabled:opacity-30 rounded-lg hover:bg-gray-100"
                title="Pindah Ke Atas"
              >
                ▲
              </button>
              <button 
                type="button" 
                @click="moveSlideDown(index)" 
                :disabled="index === slides.length - 1"
                class="p-2 text-gray-500 hover:text-gray-800 disabled:opacity-30 rounded-lg hover:bg-gray-100"
                title="Pindah Ke Bawah"
              >
                ▼
              </button>
              <button 
                type="button" 
                @click="removeSlide(index)" 
                class="p-2 text-red-500 hover:text-red-700 rounded-lg hover:bg-red-50"
                title="Hapus Slide"
              >
                🗑️
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div v-if="slides.length > 0" class="flex justify-end pt-4">
        <button 
          type="submit" 
          class="px-6 py-3 bg-[#6B2E3E] text-white font-semibold text-sm rounded-lg hover:bg-[#582432] transition-colors shadow-md flex items-center gap-2 cursor-pointer"
          :disabled="loading"
        >
          <span v-if="loading">⏳ Menyimpan...</span>
          <span v-else>💾 Simpan Seluruh Slider</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const slides = ref([])
const loading = ref(false)
const saved = ref(false)
const message = ref('')

const fetchSlides = async () => {
  loading.value = true
  try {
    const res = await api.get('/settings/hero-slider')
    slides.value = (res.data || []).map(s => ({ ...s, uploading: false, previewUrl: '' }))
  } catch (err) {
    console.error('Failed to fetch hero slider slides:', err)
    message.value = 'Gagal memuat data slider'
    saved.value = false
  } finally {
    loading.value = false
  }
}

const getSlideImageUrl = (imagePath) => {
  if (!imagePath) {
    return 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80'
  }
  if (imagePath.startsWith('http') || imagePath.startsWith('blob:') || imagePath.startsWith('data:')) {
    return imagePath
  }
  const cleanPath = imagePath.replace(/^\//, '').replace(/^storage\//, '')
  const origin = window.location.origin
  return `${origin}/storage/${cleanPath}`
}

const handleImageError = (e) => {
  e.target.src = 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80'
}

const addEmptySlide = () => {
  slides.value.push({
    id: 'slide_' + Date.now(),
    image: 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80',
    title: 'L\'ÉTOILE New Atmosphere',
    subtitle: 'Artisanal Coffee & Gourmet Specialties',
    uploading: false,
    previewUrl: ''
  })
}

const moveSlideUp = (index) => {
  if (index > 0) {
    const temp = slides.value[index]
    slides.value[index] = slides.value[index - 1]
    slides.value[index - 1] = temp
  }
}

const moveSlideDown = (index) => {
  if (index < slides.value.length - 1) {
    const temp = slides.value[index]
    slides.value[index] = slides.value[index + 1]
    slides.value[index + 1] = temp
  }
}

const removeSlide = (index) => {
  if (confirm('Apakah Anda yakin ingin menghapus slide ini?')) {
    slides.value.splice(index, 1)
  }
}

const onFileSelected = async (event, index) => {
  const file = event.target.files[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('File harus berupa gambar (JPG, PNG, WebP)')
    return
  }

  // Instant local preview
  slides.value[index].previewUrl = URL.createObjectURL(file)
  slides.value[index].uploading = true

  const formData = new FormData()
  formData.append('image', file)

  try {
    const res = await api.post('/settings/hero-slider/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data && res.data.path) {
      slides.value[index].image = res.data.path
      slides.value[index].previewUrl = ''
      message.value = 'Gambar slide berhasil diunggah! Klik "Simpan Seluruh Slider" untuk menyimpan perubahan.'
      saved.value = true
      setTimeout(() => { message.value = '' }, 4000)
    }
  } catch (err) {
    console.error('Failed to upload slide image:', err)
    alert('Gagal mengunggah gambar slide: ' + (err.response?.data?.message || err.message))
    slides.value[index].previewUrl = ''
  } finally {
    slides.value[index].uploading = false
  }
}

const saveSlides = async () => {
  loading.value = true
  message.value = ''
  try {
    // Sanitize payload
    const payload = slides.value.map(s => ({
      id: s.id,
      image: s.image,
      title: s.title || '',
      subtitle: s.subtitle || ''
    }))

    const res = await api.post('/settings/hero-slider', { slides: payload })
    slides.value = (res.data || []).map(s => ({ ...s, uploading: false, previewUrl: '' }))
    saved.value = true
    message.value = 'Semua perubahan hero slider berhasil disimpan ke database!'
    setTimeout(() => { message.value = '' }, 5000)
  } catch (err) {
    console.error('Failed to save slides:', err)
    saved.value = false
    message.value = 'Gagal menyimpan perubahan slider: ' + (err.response?.data?.message || err.message)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSlides()
})
</script>
