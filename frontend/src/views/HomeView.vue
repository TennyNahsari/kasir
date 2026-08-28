<template>
  <div class="min-h-screen bg-[#F9F6F0] text-[#2C2C2C] font-sans antialiased selection:bg-[#C9A96E] selection:text-[#F9F6F0]">
    
    <!-- Top Admin Bar (If Logged In) -->
    <div v-if="authStore.user" class="bg-[#6B2E3E] text-[#F9F6F0] px-4 py-2 text-xs sm:text-sm flex justify-between items-center tracking-wider">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-[#C9A96E] animate-pulse"></span>
        <span>Akun Aktif: <strong>{{ authStore.user.name }}</strong> ({{ authStore.user.role?.toUpperCase() }})</span>
      </div>
      <router-link to="/dashboard" class="inline-flex items-center gap-1 font-medium hover:text-[#C9A96E] transition-colors underline">
        <span>Masuk ke Dashboard POS</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
      </router-link>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 bg-[#F9F6F0]/95 backdrop-blur-md border-b border-[#E5D9C5] shadow-xs transition-all duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 sm:h-24">
          
          <!-- Brand Logo & Title -->
          <router-link to="/" class="flex items-center gap-3 group">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full border border-[#C9A96E] flex items-center justify-center bg-[#1E1E1E] text-[#C9A96E] group-hover:scale-105 transition-transform">
              <span class="font-display text-xl italic font-bold">L</span>
            </div>
            <div>
              <span class="font-display text-xl sm:text-2xl font-bold tracking-[0.05em] text-[#2C2C2C] block leading-none group-hover:text-[#6B2E3E] transition-colors">
                L'ÉTOILE
              </span>
              <span class="font-sans text-[10px] sm:text-xs tracking-[0.2em] text-[#5A5A5A] uppercase block mt-1">
                Café & Culinary Art
              </span>
            </div>
          </router-link>

          <!-- Desktop Navigation Menu -->
          <nav class="hidden md:flex items-center gap-8 font-sans text-sm tracking-[0.1em] font-medium uppercase text-[#2C2C2C]">
            <a href="#hero" class="hover:text-[#C9A96E] transition-colors">Beranda</a>
            <a href="#about" class="hover:text-[#C9A96E] transition-colors">Tentang Kami</a>
            <a href="#menu" class="hover:text-[#C9A96E] transition-colors">Menu F&B</a>
            <a href="#outlets" class="hover:text-[#C9A96E] transition-colors">Outlet F&B</a>
            <a href="#contact" class="hover:text-[#C9A96E] transition-colors">Kontak</a>
          </nav>

          <!-- Action Buttons -->
          <div class="flex items-center gap-3">
            <a href="#menu" class="px-4 py-2.5 sm:px-5 sm:py-2.5 rounded-lg bg-[#C9A96E] text-[#F9F6F0] font-sans font-medium text-xs sm:text-sm tracking-wider uppercase hover:bg-[#B59458] transition-all shadow-xs">
              Lihat Menu
            </a>

            <router-link v-if="!authStore.user" to="/login" class="hidden sm:inline-flex px-4 py-2.5 rounded-lg border border-[#6B2E3E] text-[#6B2E3E] font-sans font-medium text-xs sm:text-sm tracking-wider uppercase hover:bg-[#6B2E3E] hover:text-[#F9F6F0] transition-all">
              Login POS
            </router-link>

            <!-- Mobile Menu Toggle Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-[#2C2C2C] hover:text-[#C9A96E] focus:outline-none">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mobileMenuOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Dropdown Navigation -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-[#F9F6F0] border-b border-[#E5D9C5] px-6 py-6 space-y-4 font-sans text-sm tracking-[0.1em] font-medium uppercase">
        <a href="#hero" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">Beranda</a>
        <a href="#about" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">Tentang Kami</a>
        <a href="#menu" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">Menu F&B</a>
        <a href="#outlets" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">Outlet F&B</a>
        <a href="#contact" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">Kontak</a>
        <div class="pt-4 border-t border-[#E5D9C5] flex flex-col gap-2">
          <router-link v-if="!authStore.user" to="/login" @click="mobileMenuOpen = false" class="text-center py-2.5 rounded-lg border border-[#6B2E3E] text-[#6B2E3E]">
            Login POS
          </router-link>
        </div>
      </div>
    </header>

    <!-- HERO SECTION -->
    <section id="hero" class="relative bg-[#1E1E1E] text-[#F9F6F0] py-24 sm:py-32 lg:py-40 overflow-hidden">
      <!-- Background Overlay & Texture Pattern -->
      <div class="absolute inset-0 bg-gradient-to-r from-[#1E1E1E] via-[#1E1E1E]/90 to-[#1E1E1E]/60 z-10"></div>
      <div class="absolute inset-0 bg-[radial-gradient(#C9A96E_1px,transparent_1px)] [background-size:24px_24px] opacity-10"></div>

      <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center md:text-left">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 items-center">
          
          <div class="md:col-span-8 space-y-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#C9A96E]/40 bg-[#C9A96E]/10 text-[#C9A96E] text-xs font-sans tracking-[0.15em] uppercase">
              <span>✦</span>
              <span>Timeless Culinary & Artisanal Coffee</span>
            </div>

            <!-- Hero Title: Playfair Display Bold Italic 64px -->
            <h1 class="font-display italic font-bold text-4xl sm:text-5xl lg:text-6xl text-[#F9F6F0] leading-[1.15] tracking-[0.03em]">
              Keharmonisan Rasa,<br/>
              <span class="text-[#C9A96E] not-italic">Kemewahan Modern</span> yang Hangat
            </h1>

            <!-- Tagline: Playfair Display Italic 24px -->
            <p class="font-display italic text-lg sm:text-2xl text-[#E5D9C5] font-normal leading-relaxed max-w-2xl">
              "Setiap sajian dikoreografi secara presisi dari bahan segar terbaik, diiringi aroma kopi pilihan dan atmosfer elegan."
            </p>

            <!-- CTA Buttons -->
            <div class="pt-4 flex flex-wrap gap-4 justify-center md:justify-start">
              <a href="#menu" class="px-8 py-3.5 rounded-lg bg-[#C9A96E] text-[#F9F6F0] font-sans font-medium text-base tracking-wide hover:bg-[#B59458] hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                Eksplorasi Menu
              </a>
              <a href="#outlets" class="px-8 py-3.5 rounded-lg border border-[#E5D9C5]/50 text-[#F9F6F0] font-sans font-medium text-base tracking-wide hover:bg-[#F9F6F0]/10 transition-all">
                Pilih Outlet F&B
              </a>
            </div>
          </div>

          <!-- Hero Visual Card -->
          <div class="md:col-span-4 hidden md:block">
            <div class="relative p-2 rounded-2xl border border-[#C9A96E]/30 bg-[#2C2C2C]/80 shadow-2xl backdrop-blur-xs">
              <div class="aspect-4/5 rounded-xl overflow-hidden relative">
                <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80" alt="Cafe Ambience" class="w-full h-full object-cover opacity-90 hover:scale-105 transition-transform duration-700"/>
                <div class="absolute inset-0 bg-gradient-to-t from-[#1E1E1E] via-transparent to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6 text-center">
                  <p class="font-display italic text-lg text-[#C9A96E]">L'ÉTOILE Signature Experience</p>
                  <p class="font-sans text-xs text-[#E5D9C5] uppercase tracking-widest mt-1">White Marble • Dark Wood • Fine Coffee</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ABOUT SECTION -->
    <section id="about" class="py-20 sm:py-28 bg-[#F9F6F0]">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        
        <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold">Filosofi Kami</p>
        
        <!-- Section Title: Playfair Display Bold 36px -->
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#2C2C2C] mt-2 tracking-[0.05em]">
          Kehangatan & Prepresisian Kuliner
        </h2>
        
        <!-- Decorative Thin Divider #E5D9C5 -->
        <div class="w-20 h-0.5 bg-[#E5D9C5] mx-auto my-6"></div>

        <!-- Description: Lora Regular 17px, line-height 1.8 -->
        <p class="font-body text-base sm:text-lg text-[#5A5A5A] leading-[1.8] max-w-3xl mx-auto">
          L'ÉTOILE dirancang sebagai tempat perlindungan dari keramaian kota. Kami menghadirkan perpaduan estetika marmer hangat, kayu gelap yang bersahaja, serta racikan makanan dan minuman dengan standar tertinggi untuk menyempurnakan setiap momen Anda.
        </p>

        <!-- 3 Feature Columns -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 text-left">
          
          <div class="p-8 rounded-xl bg-white border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all">
            <div class="w-12 h-12 rounded-full bg-[#F9F6F0] border border-[#C9A96E] flex items-center justify-center text-2xl text-[#C9A96E] mb-5">
              ☕
            </div>
            <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2">Artisanal Coffee</h3>
            <p class="font-body text-sm text-[#5A5A5A] leading-relaxed">
              Biji kopi arabika terbaik hasil sangrai presisi, diseduh oleh barista berpengalaman untuk cita rasa yang khas.
            </p>
          </div>

          <div class="p-8 rounded-xl bg-white border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all">
            <div class="w-12 h-12 rounded-full bg-[#F9F6F0] border border-[#C9A96E] flex items-center justify-center text-2xl text-[#C9A96E] mb-5">
              🥗
            </div>
            <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2">Bahan Organik Segar</h3>
            <p class="font-body text-sm text-[#5A5A5A] leading-relaxed">
              Setiap piring diolah menggunakan bahan alami bermutu tinggi yang dipilih langsung setiap pagi.
            </p>
          </div>

          <div class="p-8 rounded-xl bg-white border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all">
            <div class="w-12 h-12 rounded-full bg-[#F9F6F0] border border-[#C9A96E] flex items-center justify-center text-2xl text-[#C9A96E] mb-5">
              🕯️
            </div>
            <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2">Warm Luxury Ambience</h3>
            <p class="font-body text-sm text-[#5A5A5A] leading-relaxed">
              Suasana intim nan lapang yang tenang, ideal untuk pertemuan bisnis, perayaan, hingga momen bersantai.
            </p>
          </div>

        </div>

      </div>
    </section>

    <!-- INTERACTIVE MENU SECTION (BY F&B LOCATION & CATEGORIES) -->
    <section id="menu" class="py-20 sm:py-28 bg-white border-t border-b border-[#E5D9C5]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
          <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold">Sajian Terpilih</p>
          <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#2C2C2C] mt-2 tracking-[0.05em]">
            Daftar Menu F&B
          </h2>
          <div class="w-20 h-0.5 bg-[#E5D9C5] mx-auto my-4"></div>
          <!-- Sub Title: Inter Regular 20px #5A5A5A -->
          <p class="font-sans text-base sm:text-xl text-[#5A5A5A]">
            Pilih lokasi outlet F&B kami untuk menampilkan sajian makanan, minuman, dan kudapan khas setempat.
          </p>
        </div>

        <!-- 1. LOCATION SELECTOR (ONLY FNB TYPE LOCATIONS) -->
        <div class="mb-10">
          <div class="flex items-center justify-between mb-4">
            <label class="font-sans text-xs sm:text-sm font-semibold tracking-wider text-[#2C2C2C] uppercase flex items-center gap-2">
              <span>📍 Pilih Outlet F&B:</span>
              <span v-if="fnbLocations.length === 0" class="text-[#6B2E3E] text-xs font-normal">(Memuat outlet F&B...)</span>
            </label>
            <span class="text-xs text-[#5A5A5A] font-sans">Menampilkan Outlet Tipe F&B</span>
          </div>

          <!-- Location Selector Tabs -->
          <div v-if="fnbLocations.length > 0" class="flex flex-wrap gap-3">
            <button 
              v-for="loc in fnbLocations" 
              :key="loc.id"
              @click="selectLocation(loc.id)"
              :class="selectedLocationId === loc.id 
                ? 'bg-[#1E1E1E] text-[#F9F6F0] border-[#1E1E1E] shadow-md' 
                : 'bg-[#F9F6F0] text-[#2C2C2C] border-[#E5D9C5] hover:border-[#C9A96E]'"
              class="px-5 py-3 rounded-xl border text-sm font-sans font-medium transition-all flex items-center gap-2 cursor-pointer"
            >
              <span class="w-2 h-2 rounded-full" :class="selectedLocationId === loc.id ? 'bg-[#C9A96E]' : 'bg-[#E5D9C5]'"></span>
              <span>{{ loc.name }}</span>
              <span class="text-[10px] px-2 py-0.5 rounded bg-[#C9A96E]/20 text-[#C9A96E] font-semibold uppercase">
                {{ loc.type || 'F&B' }}
              </span>
            </button>
          </div>

          <!-- Fallback when no FNB location found in DB -->
          <div v-else class="p-4 rounded-xl bg-[#F9F6F0] border border-[#E5D9C5] text-sm text-[#5A5A5A]">
            <p>ℹ️ Memuat outlet F&B atau menampilkan outlet default...</p>
          </div>
        </div>

        <!-- 2. CATEGORY TABS (Dynamic from database) -->
        <div class="flex items-center justify-center gap-2 sm:gap-4 mb-12 border-b border-[#E5D9C5] pb-4 overflow-x-auto">
          <button 
            @click="activeCategory = 'all'"
            :class="activeCategory === 'all' 
              ? 'border-[#C9A96E] text-[#C9A96E] font-semibold' 
              : 'border-transparent text-[#5A5A5A] hover:text-[#2C2C2C]'"
            class="px-4 py-2 border-b-2 text-sm sm:text-base font-sans tracking-wide transition-all whitespace-nowrap"
          >
            Semua Menu
          </button>
          
          <button 
            v-for="cat in displayCategories"
            :key="cat.id"
            @click="activeCategory = cat.id"
            :class="activeCategory === cat.id 
              ? 'border-[#C9A96E] text-[#C9A96E] font-semibold' 
              : 'border-transparent text-[#5A5A5A] hover:text-[#2C2C2C]'"
            class="px-4 py-2 border-b-2 text-sm sm:text-base font-sans tracking-wide transition-all flex items-center gap-2 whitespace-nowrap"
          >
            <span>{{ getCategoryIcon(cat.name) }}</span>
            <span>{{ cat.name }}</span>
          </button>
        </div>

        <!-- 3. PRODUCTS GRID WITH HORIZONTAL PAGINATION -->
        <div v-if="loadingProducts" class="py-16 text-center text-[#5A5A5A] font-sans">
          <div class="inline-block animate-spin w-8 h-8 border-4 border-[#C9A96E] border-t-transparent rounded-full mb-3"></div>
          <p>Memuat daftar sajian pilihan...</p>
        </div>

        <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          
          <div 
            v-for="product in paginatedProducts" 
            :key="product.id"
            class="bg-[#F9F6F0] rounded-2xl overflow-hidden border border-[#E5D9C5] shadow-xs hover:shadow-lg transition-all duration-300 hover:-translate-y-1 flex flex-col group"
          >
            <!-- Product Image -->
            <div class="h-48 sm:h-56 bg-[#1E1E1E] overflow-hidden relative">
              <img 
                :src="getProductImageUrl(product)" 
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                @error="handleImageError"
              />
              <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-[#1E1E1E]/80 backdrop-blur-xs text-[#C9A96E] text-[11px] font-sans font-semibold uppercase tracking-wider border border-[#C9A96E]/30">
                {{ getCategoryName(product) }}
              </div>
            </div>

            <!-- Product Details -->
            <div class="p-6 flex-1 flex flex-col justify-between">
              <div>
                <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2 group-hover:text-[#6B2E3E] transition-colors">
                  {{ product.name }}
                </h3>
                
                <!-- Description: Lora Regular 17px, line-height 1.8 -->
                <p class="font-body text-sm text-[#5A5A5A] leading-[1.8] line-clamp-3 mb-4">
                  {{ product.description || 'Sajian istimewa dari dapur L\'ÉTOILE, dibuat dari bahan bermutu tinggi untuk cita rasa tak terlupakan.' }}
                </p>
              </div>

              <div class="pt-4 border-t border-[#E5D9C5] flex items-center justify-between mt-auto">
                <!-- Price: Inter SemiBold 18px #C9A96E -->
                <div class="font-sans font-semibold text-lg text-[#C9A96E]">
                  {{ formatCurrency(product.selling_price) }}
                </div>

                <!-- Add to Cart Button -->
                <button 
                  @click="addToCart(product)"
                  class="px-3.5 py-2 rounded-lg bg-[#C9A96E] text-[#F9F6F0] font-sans font-medium text-xs tracking-wider uppercase hover:bg-[#6B2E3E] transition-all flex items-center gap-1.5 cursor-pointer active:scale-95"
                >
                  <span>{{ getCartItemQty(product.id) > 0 ? `(${getCartItemQty(product.id)}) Tambah` : '+ Pesan' }}</span>
                </button>
              </div>
            </div>
          </div>

        </div>

        <!-- Menu pagination: three menu cards per page -->
        <div v-if="menuPageCount > 1" class="flex items-center justify-center gap-5 mt-10">
          <button
            type="button"
            @click="previousMenuPage"
            :disabled="currentMenuPage === 1"
            class="w-10 h-10 rounded-full border border-[#C9A96E] text-[#C9A96E] flex items-center justify-center transition-all hover:bg-[#C9A96E] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-[#C9A96E]"
            aria-label="Menu sebelumnya"
          >
            <span class="text-xl leading-none">‹</span>
          </button>
          <span class="text-sm text-[#5A5A5A] font-sans min-w-20 text-center">
            {{ currentMenuPage }} / {{ menuPageCount }}
          </span>
          <button
            type="button"
            @click="nextMenuPage"
            :disabled="currentMenuPage === menuPageCount"
            class="w-10 h-10 rounded-full border border-[#C9A96E] text-[#C9A96E] flex items-center justify-center transition-all hover:bg-[#C9A96E] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-[#C9A96E]"
            aria-label="Menu berikutnya"
          >
            <span class="text-xl leading-none">›</span>
          </button>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 bg-[#F9F6F0] rounded-2xl border border-[#E5D9C5] p-8">
          <p class="text-4xl mb-3">🍽️</p>
          <h4 class="font-display font-bold text-xl text-[#2C2C2C] mb-1">Belum Ada Sajian pada Kategori Ini</h4>
          <p class="font-body text-sm text-[#5A5A5A] max-w-md mx-auto">
            Silakan pilih kategori atau outlet F&B lain untuk melihat daftar menu yang tersedia.
          </p>
        </div>

      </div>
    </section>

    <!-- OUTLETS SECTION -->
    <section id="outlets" class="py-20 sm:py-28 bg-[#F9F6F0]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
          <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold">Lokasi & Cabang</p>
          <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#2C2C2C] mt-2 tracking-[0.05em]">
            Outlet F&B Kami
          </h2>
          <div class="w-20 h-0.5 bg-[#E5D9C5] mx-auto my-4"></div>
          <p class="font-body text-base text-[#5A5A5A] leading-relaxed">
            Kunjungi cabang terdekat Anda untuk menikmati suasana kasual nan mewah atau lakukan pemesanan via QR Code di meja.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div 
            v-for="loc in (fnbLocations.length > 0 ? fnbLocations : sampleOutlets)" 
            :key="loc.id"
            class="bg-white rounded-2xl p-8 border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all flex flex-col justify-between"
          >
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 rounded-full bg-[#C9A96E]/15 text-[#C9A96E] font-sans text-xs font-semibold uppercase tracking-wider">
                  {{ loc.type || 'F&B OUTLET' }}
                </span>
                <span class="text-xs text-[#5A5A5A] font-sans">Kode: {{ loc.code || 'OUT-FNB' }}</span>
              </div>

              <h3 class="font-display font-bold text-2xl text-[#2C2C2C] mb-3">
                {{ loc.name }}
              </h3>

              <div class="space-y-2 text-sm text-[#5A5A5A] font-body mb-6">
                <p class="flex items-start gap-2">
                  <span>📍</span>
                  <span>{{ loc.address || 'Jl. Sultan Agung No. 45, Jakarta Selatan' }}</span>
                </p>
                <p class="flex items-center gap-2">
                  <span>📞</span>
                  <span>{{ loc.phone || '(021) 555-0192' }}</span>
                </p>
                <p class="flex items-center gap-2">
                  <span>🕒</span>
                  <span>08:00 - 22:00 WIB</span>
                </p>
              </div>
            </div>

            <div class="pt-4 border-t border-[#E5D9C5] flex gap-3">
              <button 
                @click="selectLocation(loc.id); scrollToMenu()" 
                class="flex-1 text-center py-2.5 rounded-lg bg-[#1E1E1E] text-[#F9F6F0] font-sans text-xs font-medium uppercase tracking-wider hover:bg-[#6B2E3E] transition-all cursor-pointer"
              >
                Pesan Online
              </button>
              <button 
                @click="selectLocation(loc.id); scrollToMenu()" 
                class="px-4 py-2.5 rounded-lg border border-[#C9A96E] text-[#C9A96E] font-sans text-xs font-medium uppercase hover:bg-[#C9A96E] hover:text-[#F9F6F0] transition-all"
              >
                Menu
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section class="py-20 bg-[#1E1E1E] text-[#F9F6F0]">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold mb-2">Testimoni Pengunjung</p>
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#F9F6F0] tracking-[0.05em] mb-12">
          Ulasan Dari Hati
        </h2>

        <div class="relative">
          <blockquote class="font-body italic text-xl sm:text-2xl text-[#E5D9C5] leading-relaxed">
            "Kombinasi kopi arabika dengan atmosfer hangat nan mewah menjadikan tempat ini pilihan utama saya untuk pertemuan penting maupun bersantai sore."
          </blockquote>
          
          <div class="mt-6 flex justify-center text-[#C9A96E] text-lg">
            ★★★★★
          </div>
          
          <div class="mt-4">
            <p class="font-display font-bold text-lg text-[#F9F6F0]">Alexander Wright</p>
            <p class="font-sans text-xs text-[#5A5A5A] uppercase tracking-widest mt-0.5">Penikmat Kuliner & Fotografer</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer id="contact" class="bg-[#1E1E1E] text-[#A0A0A0] border-t border-[#2C2C2C] pt-16 pb-12 font-sans text-sm font-light">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
          
          <!-- Brand Info -->
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full border border-[#C9A96E] flex items-center justify-center bg-[#2C2C2C] text-[#C9A96E]">
                <span class="font-display text-lg italic font-bold">L</span>
              </div>
              <span class="font-display text-xl font-bold tracking-[0.05em] text-[#F9F6F0]">
                L'ÉTOILE
              </span>
            </div>
            <p class="font-body text-xs text-[#A0A0A0] leading-relaxed">
              Timeless luxury, warm sophistication, clean airy experience for culinary and cafe enthusiasts.
            </p>
          </div>

          <!-- Quick Navigation -->
          <div>
            <h4 class="font-display font-bold text-base text-[#F9F6F0] mb-4">Navigasi Utama</h4>
            <ul class="space-y-2 text-xs">
              <li><a href="#hero" class="hover:text-[#C9A96E] transition-colors">Beranda</a></li>
              <li><a href="#about" class="hover:text-[#C9A96E] transition-colors">Tentang Kami</a></li>
              <li><a href="#menu" class="hover:text-[#C9A96E] transition-colors">Daftar Menu F&B</a></li>
              <li><a href="#outlets" class="hover:text-[#C9A96E] transition-colors">Daftar Outlet F&B</a></li>
            </ul>
          </div>

          <!-- Jam Operasional -->
          <div>
            <h4 class="font-display font-bold text-base text-[#F9F6F0] mb-4">Jam Operasional</h4>
            <ul class="space-y-2 text-xs">
              <li class="flex justify-between">
                <span>Senin - Jumat:</span>
                <span class="text-[#F9F6F0]">08:00 - 22:00</span>
              </li>
              <li class="flex justify-between">
                <span>Sabtu - Minggu:</span>
                <span class="text-[#F9F6F0]">07:00 - 23:00</span>
              </li>
              <li class="pt-2 text-[11px] text-[#C9A96E]">
                *Layanan QR Order aktif sepanjang jam buka
              </li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div>
            <h4 class="font-display font-bold text-base text-[#F9F6F0] mb-4">Kontak & Layanan</h4>
            <ul class="space-y-2 text-xs">
              <li>Email: info@letoile-resto.com</li>
              <li>Layanan Pelanggan: +62 812-3456-7890</li>
              <li class="pt-2">
                <router-link to="/login" class="inline-block px-3 py-1.5 rounded border border-[#C9A96E] text-[#C9A96E] hover:bg-[#C9A96E] hover:text-[#1E1E1E] transition-all text-xs font-medium">
                  Portal Login Kasir & Owner
                </router-link>
              </li>
            </ul>
          </div>

        </div>

        <div class="pt-8 border-t border-[#2C2C2C] text-center text-xs text-[#A0A0A0]">
          <p>© 2026 L'ÉTOILE Café & Restaurant System. Designed with Timeless Luxury Standards.</p>
        </div>
      </div>
    </footer>

    <!-- Floating Cart Bar -->
    <div
      v-if="cart.length > 0"
      class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-[#E5D9C5] shadow-2xl z-40"
    >
      <div class="max-w-3xl mx-auto px-4 py-3">
        <button
          @click="showOrderModal = true"
          class="w-full bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] hover:from-[#5A2634] hover:to-[#1E1E1E] text-[#F9F6F0] py-3.5 rounded-xl font-sans font-bold flex items-center justify-between shadow-lg active:scale-[0.98] transition-all cursor-pointer"
        >
          <div class="flex items-center gap-2.5 pl-4">
            <span class="bg-[#C9A96E] text-[#F9F6F0] px-2.5 py-0.5 rounded-full text-xs font-bold">
              {{ cartTotalItems }}
            </span>
            <span class="text-sm sm:text-base tracking-wide">Lihat Pesanan</span>
          </div>
          <span class="text-sm sm:text-base font-bold pr-4">{{ formatCurrency(cartTotalPrice) }}</span>
        </button>
      </div>
    </div>

    <!-- ORDER MODAL -->
    <div
      v-if="showOrderModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-end sm:items-center sm:justify-center"
      @click.self="showOrderModal = false"
    >
      <div class="bg-white w-full sm:max-w-lg sm:mx-4 rounded-t-2xl sm:rounded-2xl max-h-[85vh] sm:max-h-[90vh] overflow-hidden flex flex-col animate-[slideUp_0.3s_ease-out]">
        <!-- Modal Header -->
        <div class="flex-shrink-0 bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] text-[#F9F6F0] px-5 sm:px-6 py-4">
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-lg sm:text-xl font-display font-bold">🛒 Pesanan Online</h2>
              <p class="text-xs sm:text-sm text-[#E5D9C5] mt-0.5 font-sans">{{ cartTotalItems }} item • {{ getSelectedLocationName() }}</p>
            </div>
            <button 
              @click="showOrderModal = false" 
              class="text-[#F9F6F0] hover:bg-white/20 p-2 rounded-full transition-colors cursor-pointer"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Scrollable Content: Cart Items + Form -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-3">
          <!-- Cart Items -->
          <div class="space-y-2.5">
            <div
              v-for="item in cart"
              :key="item.product_id"
              class="flex items-center gap-3 bg-[#F9F6F0] p-3.5 rounded-xl border border-[#E5D9C5]"
            >
              <div class="flex-1 min-w-0">
                <h3 class="font-sans font-semibold text-[#2C2C2C] text-sm sm:text-base truncate">
                  {{ item.product_name }}
                </h3>
                <p class="text-xs sm:text-sm text-[#C9A96E] font-semibold">
                  {{ formatCurrency(item.price) }}
                </p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <button
                  @click="decreaseCartQty(item)"
                  class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white border-2 border-[#E5D9C5] flex items-center justify-center hover:border-[#6B2E3E] hover:text-[#6B2E3E] transition-colors active:scale-90 cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                  </svg>
                </button>
                <span class="w-8 sm:w-10 text-center font-bold text-sm sm:text-base text-[#2C2C2C]">
                  {{ item.quantity }}
                </span>
                <button
                  @click="increaseCartQty(item)"
                  class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#C9A96E] text-[#F9F6F0] flex items-center justify-center hover:bg-[#B59458] transition-colors active:scale-90 cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Separator -->
          <div class="border-t border-[#E5D9C5] pt-3 space-y-3">
            <!-- Total -->
            <div class="bg-[#F9F6F0] p-3.5 rounded-xl border border-[#E5D9C5]">
              <div class="flex justify-between items-center">
                <span class="text-[#5A5A5A] font-sans font-medium text-sm sm:text-base">Total Pembayaran</span>
                <span class="text-[#C9A96E] font-sans font-bold text-lg sm:text-xl">
                  {{ formatCurrency(cartTotalPrice) }}
                </span>
              </div>
            </div>

            <!-- Order Type Badge (fixed to online) -->
            <div class="flex items-center gap-2 px-1">
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#6B2E3E]/10 border border-[#6B2E3E]/25 text-[#6B2E3E] text-xs font-sans font-semibold">
                <span>🌐</span>
                <span>Pesanan Online</span>
              </span>
            </div>

            <!-- Payment Info Section -->
            <div v-if="false && hasPaymentInfo" class="bg-[#1E1E1E] rounded-xl p-4 space-y-3">
              <p class="text-[#C9A96E] font-sans font-semibold text-xs uppercase tracking-wider">💳 Informasi Pembayaran</p>
              
              <!-- Bank Transfer -->
              <div v-if="paymentInfo.bank_account_number" class="bg-white/10 rounded-lg p-3">
                <p class="text-[#E5D9C5] text-[11px] uppercase tracking-wider font-sans font-medium mb-1.5">Transfer Bank</p>
                <div class="space-y-1">
                  <p v-if="paymentInfo.bank_name" class="text-[#F9F6F0] font-sans font-bold text-sm">{{ paymentInfo.bank_name }}</p>
                  <p class="text-[#C9A96E] font-mono font-bold text-base tracking-wider">{{ paymentInfo.bank_account_number }}</p>
                  <p v-if="paymentInfo.bank_account_name" class="text-[#E5D9C5] font-sans text-xs">a.n. {{ paymentInfo.bank_account_name }}</p>
                </div>
              </div>

              <!-- QRIS -->
              <div v-if="qrisImageUrl" class="bg-white/10 rounded-lg p-3">
                <p class="text-[#E5D9C5] text-[11px] uppercase tracking-wider font-sans font-medium mb-2">QRIS</p>
                <div class="bg-white rounded-lg p-2 inline-block">
                  <img :src="qrisImageUrl" alt="QRIS" class="w-40 h-40 object-contain" />
                </div>
              </div>
            </div>

            <button
              v-if="hasPaymentInfo"
              type="button"
              @click="openPaymentModal"
              class="w-full flex items-center justify-between px-4 py-3 rounded-xl border border-[#C9A96E]/40 bg-[#1E1E1E] text-[#F9F6F0] hover:bg-[#2C2C2C] transition-colors"
            >
              <span class="text-[#C9A96E] font-sans font-semibold text-xs uppercase tracking-wider">Informasi Pembayaran</span>
              <span class="text-[#E5D9C5] text-xs">Lihat →</span>
            </button>

            <!-- Customer Name -->
            <input
              v-model="orderCustomerName"
              type="text"
              placeholder="Nama Pemesan *"
              class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors bg-[#F9F6F0]"
            />

            <!-- Table Number (optional) -->
            <input
              v-model="orderTableNumber"
              type="text"
              placeholder="Nomor Meja (opsional)"
              class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors bg-[#F9F6F0]"
            />

            <!-- Notes -->
            <textarea
              v-model="orderNote"
              placeholder="Catatan pesanan (opsional)"
              rows="2"
              class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors resize-none bg-[#F9F6F0]"
            ></textarea>
          </div>
        </div>

        <!-- Fixed Submit Button -->
        <div class="flex-shrink-0 bg-white border-t border-[#E5D9C5] px-4 sm:px-5 py-3">
          <button
            @click="submitOnlineOrder"
            :disabled="orderSubmitting || !orderCustomerName.trim()"
            class="w-full bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] hover:from-[#5A2634] hover:to-[#1E1E1E] text-[#F9F6F0] py-3 sm:py-3.5 rounded-xl font-sans font-bold text-sm sm:text-base disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed shadow-lg active:scale-[0.98] transition-all cursor-pointer"
          >
            {{ orderSubmitting ? 'Mengirim Pesanan...' : 'Kirim Pesanan Online' }}
          </button>
        </div>
      </div>
    </div>

    <!-- PAYMENT INFORMATION MODAL -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click.self="showPaymentModal = false"
    >
      <div class="bg-[#1E1E1E] rounded-2xl max-w-md w-full max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-white/10">
          <h2 class="text-base sm:text-lg font-display font-bold text-[#F9F6F0]">Informasi Pembayaran</h2>
          <button type="button" @click="showPaymentModal = false" class="text-[#F9F6F0] hover:bg-white/20 p-2 rounded-full transition-colors" aria-label="Tutup">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto">
          <div v-if="paymentInfo.bank_account_number" class="bg-white/10 rounded-xl p-4">
            <p class="text-[#E5D9C5] text-[11px] uppercase tracking-wider font-sans font-medium mb-2">Transfer Bank</p>
            <p v-if="paymentInfo.bank_name" class="text-[#F9F6F0] font-sans font-bold text-sm">{{ paymentInfo.bank_name }}</p>
            <p class="text-[#C9A96E] font-mono font-bold text-base tracking-wider">{{ paymentInfo.bank_account_number }}</p>
            <p v-if="paymentInfo.bank_account_name" class="text-[#E5D9C5] font-sans text-xs">a.n. {{ paymentInfo.bank_account_name }}</p>
          </div>

          <div v-if="qrisImageUrl" class="bg-white/10 rounded-xl p-4">
            <p class="text-[#E5D9C5] text-[11px] uppercase tracking-wider font-sans font-medium mb-2">QRIS</p>
            <div class="bg-white rounded-lg p-2 inline-block">
              <img :src="qrisImageUrl" alt="QRIS" class="w-56 h-56 object-contain" />
            </div>
          </div>
        </div>

        <div class="px-5 sm:px-6 py-4 border-t border-white/10">
          <button type="button" @click="showPaymentModal = false" class="w-full py-3 rounded-xl bg-[#C9A96E] text-[#F9F6F0] font-sans font-bold text-sm hover:bg-[#B59458] transition-colors">
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div
      v-if="showOrderSuccess"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-sm w-full text-center animate-[scaleUp_0.3s_ease-out]">
        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 sm:w-10 sm:h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 class="text-xl sm:text-2xl font-display font-bold text-[#2C2C2C] mb-2">🎉 Pesanan Terkirim!</h3>
        <p class="text-sm sm:text-base text-[#5A5A5A] font-body mb-2">
          Pesanan online Anda sedang diproses.<br>Mohon tunggu konfirmasi.
        </p>
        <p v-if="lastOrderNo" class="text-xs text-[#C9A96E] font-sans font-semibold mb-6">No. Pesanan: {{ lastOrderNo }}</p>
        <button
          @click="resetOnlineOrder"
          class="w-full bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] hover:from-[#5A2634] hover:to-[#1E1E1E] text-[#F9F6F0] py-3 rounded-xl font-sans font-bold text-sm shadow-md active:scale-[0.98] transition-all cursor-pointer"
        >
          Selesai
        </button>
      </div>
    </div>

    <!-- Floating WhatsApp contact button -->
    <a
      v-if="whatsappUrl"
      :href="whatsappUrl"
      target="_blank"
      rel="noopener noreferrer"
      :class="['fixed right-6 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white shadow-lg flex items-center justify-center hover:bg-[#1ebe5d] hover:scale-105 transition-all', cart.length > 0 ? 'bottom-20' : 'bottom-6']"
      aria-label="Hubungi kami melalui WhatsApp"
    >
      <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M20.52 3.48A11.86 11.86 0 0012.08 0C5.52 0 .18 5.34.18 11.9c0 2.1.55 4.15 1.59 5.96L.08 24l6.28-1.65a11.9 11.9 0 005.72 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.16-3.47-8.43zM12.09 21.8h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.21-3.73.98 1-3.64-.23-.37a9.87 9.87 0 01-1.52-5.28C2.19 6.45 6.63 2 12.08 2a9.9 9.9 0 017.02 2.92A9.9 9.9 0 0122 11.94c0 5.45-4.45 9.86-9.91 9.86zm5.42-7.4c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-1.72-.86-2.85-1.53-3.99-3.47-.3-.51.3-.47.86-1.56.1-.2.05-.37-.03-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49 1.89.82 2.63.89 3.57.75.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35z" />
      </svg>
    </a>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const locations = ref([])
const products = ref([])
const categories = ref([])
const selectedLocationId = ref(null)
const activeCategory = ref('all')
const loadingProducts = ref(false)
const whatsappNumber = ref('')
const currentMenuPage = ref(1)
const menuPerPage = 3

// ── Online Order State ──
const cart = ref([])
const showOrderModal = ref(false)
const showPaymentModal = ref(false)
const showOrderSuccess = ref(false)
const orderSubmitting = ref(false)
const orderCustomerName = ref('')
const orderTableNumber = ref('')
const orderNote = ref('')
const lastOrderNo = ref('')

// ── Payment Info ──
const paymentInfo = ref({
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  qris_image: ''
})

const hasPaymentInfo = computed(() => {
  return paymentInfo.value.bank_account_number || paymentInfo.value.qris_image
})

const qrisImageUrl = computed(() => {
  if (!paymentInfo.value.qris_image) return ''
  if (paymentInfo.value.qris_image.startsWith('http')) return paymentInfo.value.qris_image
  return `http://localhost:8000/storage/${paymentInfo.value.qris_image}`
})

const whatsappUrl = computed(() => {
  let number = String(whatsappNumber.value || '').replace(/\D/g, '')
  if (number.startsWith('0')) number = `62${number.slice(1)}`
  return number ? `https://wa.me/${number}?text=${encodeURIComponent('Halo, saya ingin bertanya mengenai menu.')}` : ''
})

// Sample fallback outlets in case database has no locations yet
const sampleOutlets = ref([
  { id: 1, name: 'L\'ÉTOILE Outlet Central F&B', code: 'OUT-FNB01', type: 'FNB', address: 'Jl. Senopati No. 88, Jakarta Selatan', phone: '(021) 720-1188' },
  { id: 2, name: 'L\'ÉTOILE Cafe & Bistro F&B', code: 'OUT-FNB02', type: 'FNB', address: 'Jl. Dago Boulevard No. 12, Bandung', phone: '(022) 420-5599' }
])

// Homepage menu must use the same F&B categories as Stock Management.
const fnbCategoryNames = new Set(['makanan fnb', 'minuman fnb', 'snack fnb'])
const isFnbCategory = (category) =>
  fnbCategoryNames.has((category?.name || '').trim().toLowerCase())

// Filter ONLY locations where type is FNB
const fnbLocations = computed(() => {
  const filtered = locations.value.filter(loc => {
    const locType = (loc.type || '').toUpperCase()
    return locType === 'FNB' || locType === 'F&B'
  })
  return filtered.length > 0 ? filtered : sampleOutlets.value
})

// Categories that have products in current selection
const displayCategories = computed(() => {
  const allProds = products.value
  const prodCategoryIds = new Set(allProds.map(p => p.category_id))
  
  return categories.value.filter(cat =>
    isFnbCategory(cat) && prodCategoryIds.has(cat.id)
  )
})

// Filter products based on selected category
const filteredProducts = computed(() => {
  const allProds = products.value
  
  if (activeCategory.value === 'all') return allProds

  return allProds.filter(prod => prod.category_id === activeCategory.value)
})

const menuPageCount = computed(() =>
  Math.ceil(filteredProducts.value.length / menuPerPage) || 1
)

const paginatedProducts = computed(() => {
  const start = (currentMenuPage.value - 1) * menuPerPage
  return filteredProducts.value.slice(start, start + menuPerPage)
})

const previousMenuPage = () => {
  if (currentMenuPage.value > 1) currentMenuPage.value--
}

const nextMenuPage = () => {
  if (currentMenuPage.value < menuPageCount.value) currentMenuPage.value++
}

watch([activeCategory, selectedLocationId], () => {
  currentMenuPage.value = 1
})

const getCategoryIcon = (name) => {
  const n = (name || '').toLowerCase()
  if (n.includes('makanan') || n.includes('food')) return '🍽️'
  if (n.includes('minuman') || n.includes('drink') || n.includes('beverage')) return '☕'
  if (n.includes('snack') || n.includes('kudapan')) return '🥐'
  if (n.includes('dessert') || n.includes('kue')) return '🍰'
  return '🍴'
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount || 0)
}

const getCategoryName = (product) => {
  return product.category?.name || 'Sajian F&B'
}

const getProductImageUrl = (product) => {
  if (!product.image) {
    return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80'
  }
  if (product.image.startsWith('http')) return product.image
  return `http://localhost:8000/storage/${product.image}`
}

const handleImageError = (e) => {
  e.target.src = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80'
}

const selectLocation = async (locId) => {
  selectedLocationId.value = locId
  fetchProductsByLocation(locId)
}

const scrollToMenu = () => {
  const el = document.getElementById('menu')
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}

// ── Cart Functions ──
const addToCart = (product) => {
  const existing = cart.value.find(item => item.product_id === product.id)
  if (existing) {
    existing.quantity++
  } else {
    cart.value.push({
      product_id: product.id,
      product_name: product.name,
      price: product.selling_price,
      quantity: 1,
      discount: 0,
      notes: ''
    })
  }
}

const getCartItemQty = (productId) => {
  const item = cart.value.find(i => i.product_id === productId)
  return item ? item.quantity : 0
}

const cartTotalItems = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.quantity, 0)
})

const cartTotalPrice = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const increaseCartQty = (item) => {
  item.quantity++
}

const decreaseCartQty = (item) => {
  if (item.quantity > 1) {
    item.quantity--
  } else {
    cart.value = cart.value.filter(i => i.product_id !== item.product_id)
    if (cart.value.length === 0) {
      showOrderModal.value = false
    }
  }
}

const getSelectedLocationName = () => {
  const loc = fnbLocations.value.find(l => l.id === selectedLocationId.value)
  return loc ? loc.name : 'Outlet'
}

const openPaymentModal = () => {
  showOrderModal.value = false
  showPaymentModal.value = true
}

const submitOnlineOrder = async () => {
  if (!orderCustomerName.value.trim()) return
  
  try {
    orderSubmitting.value = true
    
    let notes = 'Online Order'
    if (orderCustomerName.value) {
      notes += ` - ${orderCustomerName.value}`
    }
    if (orderTableNumber.value) {
      notes += ` | Meja: ${orderTableNumber.value}`
    }
    if (orderNote.value) {
      notes += ` | Note: ${orderNote.value}`
    }
    
    const payload = {
      location_id: parseInt(selectedLocationId.value),
      table_id: orderTableNumber.value ? parseInt(orderTableNumber.value) : null,
      order_type: 'online',
      customer_name: orderCustomerName.value || null,
      items: cart.value,
      discount: 0,
      tax: 0,
      payment_method: 'cash',
      paid_amount: 0,
      notes: notes
    }
    
    const res = await api.post('/public/orders', payload)
    lastOrderNo.value = res.data?.data?.transaction_no || res.data?.transaction_no || ''
    
    showOrderModal.value = false
    showOrderSuccess.value = true
  } catch (err) {
    console.error('Online order error:', err)
    alert(`Gagal mengirim pesanan: ${err.response?.data?.message || 'Silakan coba lagi.'}`)
  } finally {
    orderSubmitting.value = false
  }
}

const resetOnlineOrder = () => {
  cart.value = []
  orderCustomerName.value = ''
  orderTableNumber.value = ''
  orderNote.value = ''
  lastOrderNo.value = ''
  showOrderSuccess.value = false
}

const fetchLocations = async () => {
  try {
    const res = await api.get('/public/locations')
    const data = res.data?.data || res.data || []
    const fnbData = data.filter(loc => ['FNB', 'F&B'].includes((loc.type || '').toUpperCase()))
    locations.value = fnbData.length > 0 ? fnbData : sampleOutlets.value
    
    // Auto select first location
    selectedLocationId.value = locations.value[0].id
    fetchProductsByLocation(locations.value[0].id)
  } catch (err) {
    console.warn('Failed to load locations from API, using fallback F&B outlets:', err)
    locations.value = sampleOutlets.value
    selectedLocationId.value = sampleOutlets.value[0].id
  }
}

const fetchCategories = async () => {
  try {
    const res = await api.get('/public/categories')
    const data = res.data?.data || res.data || []
    categories.value = data.filter(isFnbCategory)
  } catch (err) {
    console.warn('Failed to load categories:', err)
  }
}

const fetchWhatsappSetting = async () => {
  try {
    const res = await api.get('/public/settings/whatsapp')
    const setting = res.data?.data || res.data || {}
    whatsappNumber.value = setting.whatsapp_number || ''
  } catch (err) {
    console.warn('Failed to load WhatsApp setting:', err.response?.data || err.message)
  }
}

const fetchProductsByLocation = async (locId) => {
  loadingProducts.value = true
  try {
    const res = await api.get('/public/products', {
      params: { location_id: locId, per_page: 100, is_active: true }
    })
    const prods = res.data?.data || res.data || []
    // The public products endpoint already limits results to positive stock
    // for the selected location. Keep its empty result empty; never show demo data.
    products.value = prods.filter(product => isFnbCategory(product.category))
    activeCategory.value = 'all'
  } catch (err) {
    console.warn('Failed to load location products:', err)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

const fetchPaymentSetting = async () => {
  try {
    const res = await api.get('/public/settings/payment')
    const data = res.data || {}
    paymentInfo.value = {
      bank_name: data.bank_name || '',
      bank_account_number: data.bank_account_number || '',
      bank_account_name: data.bank_account_name || '',
      qris_image: data.qris_image || ''
    }
  } catch (err) {
    console.warn('Failed to load payment setting:', err.response?.data || err.message)
  }
}

onMounted(() => {
  fetchCategories()
  fetchLocations()
  fetchWhatsappSetting()
  fetchPaymentSetting()
})
</script>

<style scoped>
@keyframes slideUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes scaleUp {
  from {
    transform: scale(0.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
