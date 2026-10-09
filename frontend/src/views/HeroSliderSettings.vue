<template>
  <div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Pengaturan Hero Slider</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola gambar slide dan teks yang tampil di bagian utama (Hero Section) Landing Page.</p>
      </div>
      <button 
        @click="addEmptySlide" 
        class="px-4 py-2.5 rounded-lg bg-[#C9A96E] text-white font-medium text-sm hover:bg-[#B59458] transition-colors flex items-center gap-2 shadow-xs"
        :disabled="loading"
      >
        <span>➕</span> Tambah Slide Baru
      </button>
    </div>

    <!-- Feedback Message -->
    <div v-if="message" class="mb-4 p-4 rounded-xl text-sm font-medium transition-all" :class="saved ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'">
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
        <p class="text-xs text-gray-400 mt-1">Klik tombol "Tambah Slide Baru" untuk menambahkan gambar slider.</p>
      </div>

      <!-- Slides List -->
      <div v-else class="space-y-4">
        <div 
          v-for="(slide, index) in slides" 
          :key="slide.id" 
          class="bg-white border border-[#E5D9C5] rounded-xl p-5 shadow-xs hover:shadow-md transition-shadow relative"
        >
          <div class="flex flex-col md:flex-row gap-5 items-start">
            
            <!-- Slide Image Preview / Upload -->
            <div class="w-full md:w-56 shrink-0">
              <label class="block text-xs font-semibold text-gray-600 mb-1">Gambar Slide #{{ index + 1 }}</label>
              <div class="aspect-16/9 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 relative group">
                <img 
                  :src="getSlideImageUrl(slide.image)" 
                  alt="Slide Image" 
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <label class="px-3 py-1.5 bg-white text-gray-800 text-xs font-semibold rounded-lg cursor-pointer hover:bg-gray-100 shadow-xs">
                    Ganti Foto
                    <input 
                      type="file" 
                      accept="image/*" 
                      class="hidden" 
                      @change="(e) => onFileSelected(e, index)"
                    />
                  </label>
                </div>
              </div>
              
              <!-- Direct Image URL Option -->
              <div class="mt-2">
                <input 
                  type="text" 
                  v-model="slide.image" 
                  placeholder="Atau masukkan URL Gambar..." 
                  class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#C9A96E]"
                />
              </div>
            </div>

            <!-- Slide Content Inputs -->
            <div class="flex-1 space-y-3 w-full">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Slide (Headline)</label>
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
          class="px-6 py-3 bg-[#6B2E3E] text-white font-semibold text-sm rounded-lg hover:bg-[#582432] transition-colors shadow-md flex items-center gap-2"
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
    slides.value = res.data || []
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
  if (imagePath.startsWith('http')) {
    return imagePath
  }
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
  return `${baseUrl.replace(/\/api\/?$/, '')}/storage/${imagePath.replace(/^\//, '')}`
}

const handleImageError = (e) => {
  e.target.src = 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80'
}

const addEmptySlide = () => {
  slides.value.push({
    id: 'slide_' + Date.now(),
    image: 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80',
    title: 'L\'ÉTOILE New Atmosphere',
    subtitle: 'Artisanal Coffee & Gourmet Specialties'
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

  const formData = new FormData()
  formData.append('image', file)

  loading.value = true
  try {
    const res = await api.post('/settings/hero-slider/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data && res.data.path) {
      slides.value[index].image = res.data.path
      message.value = 'Gambar slide berhasil diunggah'
      saved.value = true
      setTimeout(() => { message.value = '' }, 3000)
    }
  } catch (err) {
    console.error('Failed to upload slide image:', err)
    alert('Gagal mengunggah gambar slide')
  } finally {
    loading.value = false
  }
}

const saveSlides = async () => {
  loading.value = true
  message.value = ''
  try {
    const res = await api.post('/settings/hero-slider', { slides: slides.value })
    slides.value = res.data || []
    saved.value = true
    message.value = 'Semua perubahan hero slider berhasil disimpan!'
    setTimeout(() => { message.value = '' }, 4000)
  } catch (err) {
    console.error('Failed to save slides:', err)
    saved.value = false
    message.value = 'Gagal menyimpan perubahan slider.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSlides()
})
</script>
