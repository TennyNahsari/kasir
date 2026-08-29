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
          <nav class="hidden md:flex items-center gap-6 font-sans text-[13px] tracking-[0.1em] font-medium uppercase text-[#2C2C2C]">
            <a href="#hero" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.home') }}</a>
            <a href="#about" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.about') }}</a>
            <a href="#menu" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.menu') }}</a>
            <a href="#outlets" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.outlets') }}</a>
            <a href="#contact" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.contact') }}</a>
            <button 
              @click="openStatusModal" 
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 border-[#6B2E3E]/30 bg-transparent text-[#6B2E3E] hover:bg-[#6B2E3E] hover:text-[#F9F6F0] transition-all cursor-pointer font-bold uppercase text-[11px] tracking-wider"
            >
              🔍 {{ $t('home.nav.orderStatus') }}
            </button>
          </nav>

          <!-- Action Buttons -->
          <div class="flex items-center gap-3">
            <a href="#menu" class="px-4 py-2.5 sm:px-5 sm:py-2.5 rounded-lg bg-[#C9A96E] text-[#F9F6F0] font-sans font-medium text-xs sm:text-sm tracking-wider uppercase hover:bg-[#B59458] transition-all shadow-xs">
              {{ $t('home.nav.viewMenu') }}
            </a>

            <router-link v-if="!authStore.user" to="/login" class="hidden sm:inline-flex px-4 py-2.5 rounded-lg border border-[#6B2E3E] text-[#6B2E3E] font-sans font-medium text-xs sm:text-sm tracking-wider uppercase hover:bg-[#6B2E3E] hover:text-[#F9F6F0] transition-all">
              {{ $t('home.nav.login') }}
            </router-link>

            <!-- Language Switcher in Header -->
            <button 
              @click="toggleLanguage" 
              class="flex items-center gap-1 px-2.5 py-2 rounded-lg border-2 border-[#C9A96E]/50 text-[11px] font-bold text-[#C9A96E] hover:bg-[#C9A96E] hover:text-[#1E1E1E] transition-all cursor-pointer font-sans shadow-xs uppercase tracking-wider"
            >
              🌐 {{ locale === 'id' ? 'en' : 'id' }}
            </button>

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
        <a href="#hero" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">{{ $t('home.nav.home') }}</a>
        <a href="#about" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">{{ $t('home.nav.about') }}</a>
        <a href="#menu" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">{{ $t('home.nav.menu') }}</a>
        <a href="#outlets" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">{{ $t('home.nav.outlets') }}</a>
        <a href="#contact" @click="mobileMenuOpen = false" class="block py-1 hover:text-[#C9A96E]">{{ $t('home.nav.contact') }}</a>
        <button 
          @click="openStatusModal(); mobileMenuOpen = false" 
          class="w-full text-left py-2 hover:text-[#C9A96E] text-[#6B2E3E] font-bold"
        >
          🔍 {{ $t('home.nav.orderStatus') }}
        </button>
        <button 
          @click="toggleLanguage" 
          class="w-full text-left py-2 hover:text-[#C9A96E] text-[#C9A96E] font-bold flex items-center gap-1.5"
        >
          🌐 {{ locale === 'id' ? 'English (EN)' : 'Bahasa Indonesia (ID)' }}
        </button>
        <div class="pt-4 border-t border-[#E5D9C5] flex flex-col gap-2">
          <router-link v-if="!authStore.user" to="/login" @click="mobileMenuOpen = false" class="text-center py-2.5 rounded-lg border border-[#6B2E3E] text-[#6B2E3E]">
            {{ $t('home.nav.login') }}
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
            <!-- Hero Title -->
            <h1 class="font-display italic font-bold text-4xl sm:text-5xl lg:text-6xl text-[#F9F6F0] leading-[1.15] tracking-[0.03em]">
              {{ $t('home.hero.title1') }}<br/>
              <span class="text-[#C9A96E] not-italic">{{ $t('home.hero.title2') }}</span>
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
        
        <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold">{{ $t('home.about.tag') }}</p>
        
        <!-- Section Title: Playfair Display Bold 36px -->
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#2C2C2C] mt-2 tracking-[0.05em]">
          {{ $t('home.about.title') }}
        </h2>
        
        <!-- Decorative Thin Divider #E5D9C5 -->
        <div class="w-20 h-0.5 bg-[#E5D9C5] mx-auto my-6"></div>

        <!-- Description: Lora Regular 17px, line-height 1.8 -->
        <p class="font-body text-base sm:text-lg text-[#5A5A5A] leading-[1.8] max-w-3xl mx-auto">
          {{ $t('home.about.desc') }}
        </p>

        <!-- 3 Feature Columns -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 text-left">
          
          <div class="p-8 rounded-xl bg-white border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all">
            <div class="w-12 h-12 rounded-full bg-[#F9F6F0] border border-[#C9A96E] flex items-center justify-center text-2xl text-[#C9A96E] mb-5">
              ☕
            </div>
            <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2">{{ $t('home.about.feature1Title') }}</h3>
            <p class="font-body text-sm text-[#5A5A5A] leading-relaxed">
              {{ $t('home.about.feature1Desc') }}
            </p>
          </div>

          <div class="p-8 rounded-xl bg-white border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all">
            <div class="w-12 h-12 rounded-full bg-[#F9F6F0] border border-[#C9A96E] flex items-center justify-center text-2xl text-[#C9A96E] mb-5">
              🥗
            </div>
            <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2">{{ $t('home.about.feature2Title') }}</h3>
            <p class="font-body text-sm text-[#5A5A5A] leading-relaxed">
              {{ $t('home.about.feature2Desc') }}
            </p>
          </div>

          <div class="p-8 rounded-xl bg-white border border-[#E5D9C5] shadow-xs hover:border-[#C9A96E] transition-all">
            <div class="w-12 h-12 rounded-full bg-[#F9F6F0] border border-[#C9A96E] flex items-center justify-center text-2xl text-[#C9A96E] mb-5">
              🕯️
            </div>
            <h3 class="font-display font-bold text-xl text-[#2C2C2C] mb-2">{{ $t('home.about.feature3Title') }}</h3>
            <p class="font-body text-sm text-[#5A5A5A] leading-relaxed">
              {{ $t('home.about.feature3Desc') }}
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
          <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold">{{ $t('home.menu.tag') }}</p>
          <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#2C2C2C] mt-2 tracking-[0.05em]">
            {{ $t('home.menu.title') }}
          </h2>
          <div class="w-20 h-0.5 bg-[#E5D9C5] mx-auto my-4"></div>
          <!-- Sub Title: Inter Regular 20px #5A5A5A -->
          <p class="font-sans text-base sm:text-xl text-[#5A5A5A]">
            {{ $t('home.menu.subTitle') }}
          </p>
        </div>

        <!-- 1. LOCATION SELECTOR (ONLY FNB TYPE LOCATIONS) -->
        <div class="mb-10">
          <div class="flex items-center justify-between mb-4">
            <label class="font-sans text-xs sm:text-sm font-semibold tracking-wider text-[#2C2C2C] uppercase flex items-center gap-2">
              <span>{{ $t('home.menu.selectOutlet') }}</span>
              <span v-if="fnbLocations.length === 0" class="text-[#6B2E3E] text-xs font-normal">{{ $t('home.menu.loadingOutlet') }}</span>
            </label>
            <span class="text-xs text-[#5A5A5A] font-sans">{{ $t('home.menu.outletTypeLabel') }}</span>
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
            <p>{{ $t('home.menu.loadingFallback') }}</p>
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
            {{ $t('home.menu.allMenu') }}
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
          <p>{{ $t('home.menu.loadingMenu') }}</p>
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
                  {{ product.description || $t('home.menu.defaultProductDesc') }}
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
                  <span>{{ getCartItemQty(product.id) > 0 ? `(${getCartItemQty(product.id)}) ${$t('home.menu.addQtyBtn')}` : `+ ${$t('home.menu.addBtn')}` }}</span>
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
            :aria-label="$t('home.menu.prevPage')"
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
            :aria-label="$t('home.menu.nextPage')"
          >
            <span class="text-xl leading-none">›</span>
          </button>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 bg-[#F9F6F0] rounded-2xl border border-[#E5D9C5] p-8">
          <p class="text-4xl mb-3">🍽️</p>
          <h4 class="font-display font-bold text-xl text-[#2C2C2C] mb-1">{{ $t('home.menu.emptyTitle') }}</h4>
          <p class="font-body text-sm text-[#5A5A5A] max-w-md mx-auto">
            {{ $t('home.menu.emptyDesc') }}
          </p>
        </div>

      </div>
    </section>

    <!-- OUTLETS SECTION -->
    <section id="outlets" class="py-20 sm:py-28 bg-[#F9F6F0]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
          <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold">{{ $t('home.outlets.tag') }}</p>
          <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#2C2C2C] mt-2 tracking-[0.05em]">
            {{ $t('home.outlets.title') }}
          </h2>
          <div class="w-20 h-0.5 bg-[#E5D9C5] mx-auto my-4"></div>
          <p class="font-body text-base text-[#5A5A5A] leading-relaxed">
            {{ $t('home.outlets.desc') }}
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
                <span class="text-xs text-[#5A5A5A] font-sans">{{ $t('home.outlets.codePrefix') }} {{ loc.code || 'OUT-FNB' }}</span>
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
                  <span>{{ $t('home.outlets.hours') }}</span>
                </p>
              </div>
            </div>

            <div class="pt-4 border-t border-[#E5D9C5] flex flex-wrap sm:flex-nowrap gap-2">
              <button 
                @click="openBookingModal(loc)" 
                class="flex-1 text-center py-2.5 px-3 rounded-lg bg-[#6B2E3E] text-[#F9F6F0] font-sans text-xs font-medium uppercase tracking-wider hover:bg-[#5A2634] transition-all cursor-pointer shadow-sm"
              >
                📅 {{ $t('home.outlets.bookTableBtn') }}
              </button>
              <button 
                @click="selectLocation(loc.id); scrollToMenu()" 
                class="px-3.5 py-2.5 rounded-lg border border-[#C9A96E] text-[#C9A96E] font-sans text-xs font-medium uppercase hover:bg-[#C9A96E] hover:text-[#F9F6F0] transition-all cursor-pointer"
              >
                {{ $t('home.outlets.menuBtn') }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section class="py-20 bg-[#1E1E1E] text-[#F9F6F0]">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="font-sans text-xs sm:text-sm tracking-[0.2em] text-[#C9A96E] uppercase font-semibold mb-2">{{ $t('home.testimonials.tag') }}</p>
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#F9F6F0] tracking-[0.05em] mb-12">
          {{ $t('home.testimonials.title') }}
        </h2>

        <div class="relative">
          <blockquote class="font-body italic text-xl sm:text-2xl text-[#E5D9C5] leading-relaxed">
            {{ $t('home.testimonials.quote') }}
          </blockquote>
          
          <div class="mt-6 flex justify-center text-[#C9A96E] text-lg">
            ★★★★★
          </div>
          
          <div class="mt-4">
            <p class="font-display font-bold text-lg text-[#F9F6F0]">{{ $t('home.testimonials.author') }}</p>
            <p class="font-sans text-xs text-[#5A5A5A] uppercase tracking-widest mt-0.5">{{ $t('home.testimonials.authorTitle') }}</p>
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
              {{ $t('home.footer.brandDesc') }}
            </p>

            <!-- Social Media Links -->
            <div class="flex items-center gap-2 pt-2">
              <a
                v-if="whatsappUrl"
                :href="whatsappUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="w-8 h-8 rounded-lg bg-[#2C2C2C] border border-[#3C3C3C] text-[#A0A0A0] hover:text-[#25D366] hover:border-[#25D366] flex items-center justify-center transition-all"
                title="WhatsApp"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.52 3.48A11.86 11.86 0 0012.08 0C5.52 0 .18 5.34.18 11.9c0 2.1.55 4.15 1.59 5.96L.08 24l6.28-1.65a11.9 11.9 0 005.72 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.16-3.47-8.43zM12.09 21.8h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.21-3.73.98 1-3.64-.23-.37a9.87 9.87 0 01-1.52-5.28C2.19 6.45 6.63 2 12.08 2a9.9 9.9 0 017.02 2.92A9.9 9.9 0 0122 11.94c0 5.45-4.45 9.86-9.91 9.86zm5.42-7.4c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-1.72-.86-2.85-1.53-3.99-3.47-.3-.51.3-.47.86-1.56.1-.2.05-.37-.03-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49 1.89.82 2.63.89 3.57.75.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>
              </a>

              <a
                v-if="socialSettings.instagram_url"
                :href="socialSettings.instagram_url"
                target="_blank"
                rel="noopener noreferrer"
                class="w-8 h-8 rounded-lg bg-[#2C2C2C] border border-[#3C3C3C] text-[#A0A0A0] hover:text-[#E4405F] hover:border-[#E4405F] flex items-center justify-center transition-all"
                title="Instagram"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              </a>

              <a
                v-if="socialSettings.facebook_url"
                :href="socialSettings.facebook_url"
                target="_blank"
                rel="noopener noreferrer"
                class="w-8 h-8 rounded-lg bg-[#2C2C2C] border border-[#3C3C3C] text-[#A0A0A0] hover:text-[#1877F2] hover:border-[#1877F2] flex items-center justify-center transition-all"
                title="Facebook"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              </a>

              <a
                v-if="socialSettings.tiktok_url"
                :href="socialSettings.tiktok_url"
                target="_blank"
                rel="noopener noreferrer"
                class="w-8 h-8 rounded-lg bg-[#2C2C2C] border border-[#3C3C3C] text-[#A0A0A0] hover:text-white hover:border-white flex items-center justify-center transition-all"
                title="TikTok"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-1-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.57-1.33 1.55-1.3 2.54.01.99.55 1.93 1.38 2.47.93.61 2.16.63 3.1.06.84-.5 1.41-1.4 1.44-2.38.04-3.9.02-7.8.03-11.7.01-1.64.01-3.28.01-4.92z"/></svg>
              </a>

              <a
                v-if="socialSettings.youtube_url"
                :href="socialSettings.youtube_url"
                target="_blank"
                rel="noopener noreferrer"
                class="w-8 h-8 rounded-lg bg-[#2C2C2C] border border-[#3C3C3C] text-[#A0A0A0] hover:text-[#FF0000] hover:border-[#FF0000] flex items-center justify-center transition-all"
                title="YouTube"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
              </a>
            </div>
          </div>

          <!-- Quick Navigation -->
          <div>
            <h4 class="font-display font-bold text-base text-[#F9F6F0] mb-4">{{ $t('home.footer.mainNav') }}</h4>
            <ul class="space-y-2 text-xs">
              <li><a href="#hero" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.home') }}</a></li>
              <li><a href="#about" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.about') }}</a></li>
              <li><a href="#menu" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.menu') }}</a></li>
              <li><a href="#outlets" class="hover:text-[#C9A96E] transition-colors">{{ $t('home.nav.outlets') }}</a></li>
            </ul>
          </div>

          <!-- Jam Operasional -->
          <div>
            <h4 class="font-display font-bold text-base text-[#F9F6F0] mb-4">{{ $t('home.footer.hoursTitle') }}</h4>
            <ul class="space-y-2 text-xs">
              <li class="flex justify-between">
                <span>{{ $t('home.footer.weekdays') }}</span>
                <span class="text-[#F9F6F0]">08:00 - 22:00</span>
              </li>
              <li class="flex justify-between">
                <span>{{ $t('home.footer.weekends') }}</span>
                <span class="text-[#F9F6F0]">07:00 - 23:00</span>
              </li>
              <li class="pt-2 text-[11px] text-[#C9A96E]">
                {{ $t('home.footer.qrNote') }}
              </li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div>
            <h4 class="font-display font-bold text-base text-[#F9F6F0] mb-4">{{ $t('home.footer.contactTitle') }}</h4>
            <ul class="space-y-2 text-xs">
              <li>{{ $t('home.footer.email') }} info@letoile-resto.com</li>
              <li>{{ $t('home.footer.customerService') }} +62 812-3456-7890</li>
              <li class="pt-2">
                <router-link to="/login" class="inline-block px-3 py-1.5 rounded border border-[#C9A96E] text-[#C9A96E] hover:bg-[#C9A96E] hover:text-[#1E1E1E] transition-all text-xs font-medium">
                  {{ $t('home.footer.loginPortal') }}
                </router-link>
              </li>
            </ul>
          </div>

        </div>

        <div class="pt-8 border-t border-[#2C2C2C] text-center text-xs text-[#A0A0A0]">
          <p>{{ $t('home.footer.copyright') }}</p>
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
            <span class="text-sm sm:text-base tracking-wide">{{ locale === 'id' ? 'Lihat Pesanan' : 'View Order' }}</span>
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
              <h2 class="text-lg sm:text-xl font-display font-bold">{{ $t('home.orderModal.title') }}</h2>
              <p class="text-xs sm:text-sm text-[#E5D9C5] mt-0.5 font-sans">{{ $t('home.orderModal.itemsCount', { count: cartTotalItems, location: getSelectedLocationName() }) }}</p>
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
                <span class="text-[#5A5A5A] font-sans font-medium text-sm sm:text-base">{{ $t('home.successModal.totalLabel') }}</span>
                <span class="text-[#C9A96E] font-sans font-bold text-lg sm:text-xl">
                  {{ formatCurrency(cartTotalPrice) }}
                </span>
              </div>
            </div>

            <!-- Order Type Selection -->
            <div class="space-y-1.5 px-1">
              <label class="text-[11px] font-bold text-gray-400 uppercase tracking-wider font-sans">{{ $t('home.orderModal.orderType') }}</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  @click="orderType = 'dine_in'"
                  class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs sm:text-sm font-sans font-bold border-2 transition-all cursor-pointer"
                  :class="orderType === 'dine_in' ? 'bg-[#6B2E3E]/10 border-[#6B2E3E] text-[#6B2E3E]' : 'bg-[#F9F6F0] border-[#E5D9C5] text-[#5A5A5A] hover:bg-gray-50'"
                >
                  <span>🍽️</span> Dine In
                </button>
                <button
                  type="button"
                  @click="orderType = 'take_away'"
                  class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs sm:text-sm font-sans font-bold border-2 transition-all cursor-pointer"
                  :class="orderType === 'take_away' ? 'bg-[#6B2E3E]/10 border-[#6B2E3E] text-[#6B2E3E]' : 'bg-[#F9F6F0] border-[#E5D9C5] text-[#5A5A5A] hover:bg-gray-50'"
                >
                  <span>🛍️</span> Take Away
                </button>
              </div>
            </div>

            <!-- Customer Name -->
            <input
              v-model="orderCustomerName"
              type="text"
              :placeholder="locale === 'id' ? 'Nama Pemesan (Wajib, kecuali isi No. Transaksi)' : 'Customer Name (Required, unless transaction no. is filled)'"
              class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors bg-[#F9F6F0]"
            />

            <!-- Table Number (optional) -->
            <input
              v-model="orderTableNumber"
              type="text"
              :placeholder="orderType === 'take_away' ? (locale === 'id' ? 'Nomor Meja (opsional)' : 'Table Number (optional)') : (locale === 'id' ? 'Nomor Meja *' : 'Table Number *')"
              class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors bg-[#F9F6F0]"
            />

            <!-- Notes -->
            <textarea
              v-model="orderNote"
              :placeholder="locale === 'id' ? 'Catatan pesanan (opsional)' : 'Order notes (optional)'"
              rows="2"
              class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors resize-none bg-[#F9F6F0]"
            ></textarea>

            <!-- Booking Code (optional, renamed to Nomor Transaksi) -->
            <div class="space-y-1">
              <input
                v-model="orderBookingCode"
                type="text"
                :placeholder="locale === 'id' ? 'Nomor Transaksi Sebelumnya (opsional)' : 'Previous Transaction Number (optional)'"
                class="w-full px-4 py-2.5 sm:py-3 border-2 border-[#E5D9C5] rounded-xl text-sm sm:text-base font-sans focus:border-[#C9A96E] focus:outline-none transition-colors bg-[#F9F6F0]"
              />
              <p class="text-[11.5px] text-[#C9A96E] font-medium px-1 font-sans leading-relaxed">
                {{ $t('home.orderModal.addonHelp') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Fixed Submit Button -->
        <div class="flex-shrink-0 bg-white border-t border-[#E5D9C5] px-4 sm:px-5 py-3">
          <button
            @click="submitOnlineOrder"
            :disabled="orderSubmitting || (!orderCustomerName.trim() && !orderBookingCode.trim())"
            class="w-full bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] hover:from-[#5A2634] hover:to-[#1E1E1E] text-[#F9F6F0] py-3 sm:py-3.5 rounded-xl font-sans font-bold text-sm sm:text-base disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed shadow-lg active:scale-[0.98] transition-all cursor-pointer"
          >
            {{ orderSubmitting ? $t('home.orderModal.submitting') : $t('home.orderModal.submitButton') }}
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
      <div class="bg-white rounded-2xl max-w-md w-full text-center animate-[scaleUp_0.3s_ease-out] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 sm:p-8 flex-1 overflow-y-auto">
          <div class="w-16 h-16 sm:w-20 sm:h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="text-xl sm:text-2xl font-display font-bold text-[#2C2C2C] mb-2">{{ $t('home.successModal.title') }}</h3>
          <p class="text-sm sm:text-base text-[#5A5A5A] font-body mb-2">
            {{ $t('home.successModal.subtitle') }}
          </p>
          
          <div v-if="lastOrderNo" class="flex items-center justify-center gap-2 mb-4">
            <p class="text-xs text-[#C9A96E] font-sans font-semibold">{{ $t('home.successModal.txNo') }} <span class="font-mono">{{ lastOrderNo }}</span></p>
            <button
              type="button"
              @click="copyOrderNumber"
              class="px-2 py-0.5 rounded text-[10px] font-sans font-semibold transition-all active:scale-90 cursor-pointer"
              :class="orderNoCopied ? 'bg-green-500 text-white' : 'bg-[#C9A96E]/20 text-[#C9A96E] hover:bg-[#C9A96E]/40'"
            >
              {{ orderNoCopied ? $t('home.successModal.copied') : '📋 ' + $t('home.successModal.copy') }}
            </button>
          </div>

          <!-- Rincian Pesanan (Order Summary) -->
          <div v-if="cart.length > 0" class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-left my-4 space-y-2.5 font-sans">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ $t('home.successModal.detailsTitle') }}</p>
            <div class="max-h-40 overflow-y-auto space-y-2 pr-1">
              <div v-for="item in cart" :key="item.product_id" class="flex justify-between items-start text-xs text-gray-700">
                <div class="flex-1 min-w-0 pr-3">
                  <p class="font-medium text-gray-900 truncate">{{ item.product_name || item.name }}</p>
                  <p class="text-[10px] text-gray-500">{{ formatCurrency(item.price) }} x {{ item.quantity }}</p>
                </div>
                <span class="font-semibold text-gray-900 flex-shrink-0">
                  {{ formatCurrency(item.price * item.quantity) }}
                </span>
              </div>
            </div>
            <div class="border-t pt-2 flex justify-between items-center text-xs sm:text-sm font-bold text-gray-900">
              <span>{{ $t('home.successModal.totalLabel') }}</span>
              <span class="text-[#6B2E3E] text-sm sm:text-base">{{ formatCurrency(cartTotalPrice) }}</span>
            </div>
          </div>

          <!-- Payment Information in Success Modal -->
          <div v-if="hasPaymentInfo" class="mt-4 text-left">
            <div class="bg-[#1E1E1E] rounded-xl p-4 sm:p-5 space-y-4">
              <p class="text-[#C9A96E] font-sans font-semibold text-xs uppercase tracking-wider text-center">
                {{ $t('home.successModal.paymentTitle') }}
              </p>
              <p class="text-[#E5D9C5] font-sans text-xs text-center leading-relaxed">
                {{ $t('home.successModal.bankInfoText') }}
              </p>

              <!-- Bank Transfer -->
              <div v-if="paymentInfo.bank_account_number" class="bg-white/10 rounded-lg p-3.5 space-y-1.5">
                <p class="text-[#E5D9C5] text-[11px] uppercase tracking-wider font-sans font-medium flex items-center gap-1.5">
                  <span>🏦</span> {{ $t('home.successModal.bankTransfer') }}
                </p>
                <p v-if="paymentInfo.bank_name" class="text-[#F9F6F0] font-sans font-bold text-sm">{{ paymentInfo.bank_name }}</p>
                <div class="flex items-center gap-2">
                  <p class="text-[#C9A96E] font-mono font-bold text-lg tracking-wider">{{ paymentInfo.bank_account_number }}</p>
                  <button
                    type="button"
                    @click="copyBankAccount"
                    class="flex-shrink-0 px-2.5 py-1 rounded-md text-[10px] font-sans font-semibold uppercase tracking-wider transition-all active:scale-90 cursor-pointer"
                    :class="bankAccountCopied ? 'bg-green-500 text-white' : 'bg-[#C9A96E]/20 text-[#C9A96E] hover:bg-[#C9A96E]/40'"
                  >
                    {{ bankAccountCopied ? $t('home.successModal.copied') : '📋 ' + $t('home.successModal.copy') }}
                  </button>
                </div>
                <p v-if="paymentInfo.bank_account_name" class="text-[#E5D9C5] font-sans text-xs">a.n. {{ paymentInfo.bank_account_name }}</p>
              </div>

              <!-- QRIS -->
              <div v-if="qrisImageUrl" class="bg-white/10 rounded-lg p-3.5">
                <p class="text-[#E5D9C5] text-[11px] uppercase tracking-wider font-sans font-medium mb-2 flex items-center gap-1.5">
                  <span>📱</span> QRIS
                </p>
                <div class="flex justify-center">
                  <div class="bg-white rounded-lg p-2 inline-block">
                    <img :src="qrisImageUrl" alt="QRIS" class="w-44 h-44 sm:w-52 sm:h-52 object-contain" />
                  </div>
                </div>
                <p class="text-[#E5D9C5]/60 text-[10px] font-sans text-center mt-2">{{ $t('home.successModal.qrisText') }}</p>
              </div>
            </div>
          </div>

          <!-- Payment Confirmation Actions -->
          <div v-if="hasPaymentInfo" class="mt-4 space-y-3">
            <p class="text-xs font-sans font-semibold text-[#5A5A5A] uppercase tracking-wider text-center">{{ $t('home.statusModal.paymentTitle') }}</p>

            <!-- WhatsApp Confirmation -->
            <a
              v-if="whatsappUrl"
              :href="paymentWhatsappUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#25D366] text-white font-sans font-bold text-sm hover:bg-[#1ebe5d] transition-all active:scale-[0.98] cursor-pointer shadow-md"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.52 3.48A11.86 11.86 0 0012.08 0C5.52 0 .18 5.34.18 11.9c0 2.1.55 4.15 1.59 5.96L.08 24l6.28-1.65a11.9 11.9 0 005.72 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.16-3.47-8.43zM12.09 21.8h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.21-3.73.98 1-3.64-.23-.37a9.87 9.87 0 01-1.52-5.28C2.19 6.45 6.63 2 12.08 2a9.9 9.9 0 017.02 2.92A9.9 9.9 0 0122 11.94c0 5.45-4.45 9.86-9.91 9.86zm5.42-7.4c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-1.72-.86-2.85-1.53-3.99-3.47-.3-.51.3-.47.86-1.56.1-.2.05-.37-.03-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49 1.89.82 2.63.89 3.57.75.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>
              <span>{{ $t('home.successModal.whatsappConfirm') }}</span>
            </a>

            <!-- Upload Bukti Transfer -->
            <div class="space-y-2">
              <label
                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-[#C9A96E] text-[#C9A96E] font-sans font-bold text-sm hover:bg-[#C9A96E]/10 transition-all cursor-pointer"
                :class="{ 'border-green-500 text-green-600 bg-green-50': proofUploaded }"
              >
                <template v-if="proofUploading">
                  <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                  <span>{{ $t('common.saving') }}</span>
                </template>
                <template v-else-if="proofUploaded">
                  <span>📤</span>
                  <span>{{ $t('home.successModal.uploadMore') }}</span>
                </template>
                <template v-else>
                  <span>📤</span>
                  <span>{{ $t('home.successModal.uploadProof') }}</span>
                </template>
                <input
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="uploadPaymentProof"
                  :disabled="proofUploading"
                />
              </label>
              <p class="text-[10px] text-[#5A5A5A] text-center font-sans">{{ $t('home.statusModal.fileHelpText') }}</p>

              <!-- Previews of uploaded proofs -->
              <div v-if="uploadedProofs.length > 0" class="flex flex-wrap gap-2 justify-center pt-2">
                <div v-for="(url, idx) in uploadedProofs" :key="idx" class="relative">
                  <img :src="url" alt="Bukti Transfer" class="w-20 h-20 object-cover rounded-lg border border-green-400 shadow-sm" />
                  <div class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-green-500 text-white rounded-full flex items-center justify-center text-[9px] font-bold">✓</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Fixed bottom button -->
        <div class="flex-shrink-0 px-6 sm:px-8 pb-6 sm:px-8 pt-2 bg-white">
          <button
            @click="resetOnlineOrder"
            class="w-full bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] hover:from-[#5A2634] hover:to-[#1E1E1E] text-[#F9F6F0] py-3 rounded-xl font-sans font-bold text-sm shadow-md active:scale-[0.98] transition-all cursor-pointer"
          >
            {{ $t('home.successModal.close') }}
          </button>
        </div>
      </div>
    </div>

    <!-- CHECK STATUS MODAL -->
    <div
      v-if="showStatusModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click.self="closeStatusModal"
    >
      <div class="bg-white rounded-2xl max-w-md w-full max-h-[85vh] overflow-hidden shadow-2xl flex flex-col animate-[scaleUp_0.2s_ease-out]">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50 flex-shrink-0">
          <h2 class="text-base sm:text-lg font-display font-bold text-[#2C2C2C]">🔍 {{ $t('home.statusModal.title') }}</h2>
          <button type="button" @click="closeStatusModal" class="text-gray-400 hover:text-[#6B2E3E] p-2 rounded-full hover:bg-gray-100 transition-colors cursor-pointer text-xl leading-none">
            ×
          </button>
        </div>

        <!-- Body -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-4">
          <!-- Search input -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-wider font-sans text-left block">{{ $t('home.statusModal.inputLabel') }}</label>
            <div class="flex gap-2">
              <input
                v-model="searchTransactionNo"
                type="text"
                :placeholder="$t('home.statusModal.placeholder')"
                class="flex-1 px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm font-sans focus:border-[#C9A96E] focus:outline-none transition-colors bg-[#F9F6F0]"
                @keyup.enter="checkOrderStatus"
              />
              <button
                type="button"
                @click="checkOrderStatus"
                :disabled="searchingStatus || !searchTransactionNo.trim()"
                class="px-4 py-2.5 bg-[#6B2E3E] text-white hover:bg-[#5A2634] disabled:bg-gray-300 disabled:cursor-not-allowed rounded-xl font-sans font-bold text-sm transition-colors cursor-pointer"
              >
                {{ searchingStatus ? $t('home.statusModal.searching') : $t('home.statusModal.searchButton') }}
              </button>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="searchError" class="p-3.5 bg-red-50 border border-red-100 text-red-800 text-xs rounded-xl text-left font-medium">
            ⚠️ {{ searchError }}
          </div>

          <!-- Table Booking Search Result Card -->
          <div v-if="searchBookingResult" class="space-y-4 text-left font-sans">
            <div class="bg-[#F9F6F0] border border-[#E5D9C5] rounded-xl p-4 space-y-3">
              <div class="flex items-center justify-between border-b border-[#E5D9C5] pb-2">
                <span class="text-xs font-bold text-[#6B2E3E] uppercase tracking-wider">{{ $t('home.statusModal.bookingStatusTitle') }}</span>
                <span class="font-mono text-xs font-bold text-gray-800 bg-gray-200 px-2 py-0.5 rounded">
                  {{ searchBookingResult.booking_code }}
                </span>
              </div>

              <div class="space-y-1.5 text-xs text-gray-700">
                <p><strong>{{ $t('home.statusModal.customerName') }}:</strong> {{ searchBookingResult.customer_name }}</p>
                <p><strong>WhatsApp:</strong> {{ searchBookingResult.whatsapp_number }}</p>
                <p><strong>{{ $t('home.bookingModal.outletLabel') }}</strong> {{ searchBookingResult.location?.name || 'Outlet F&B' }}</p>
                <p><strong>{{ $t('home.statusModal.schedule') }}:</strong> {{ searchBookingResult.reservation_date }} • {{ searchBookingResult.reservation_time }}</p>
                <p v-if="searchBookingResult.guest_count"><strong>{{ $t('home.statusModal.capacity') }}:</strong> <span class="font-bold text-amber-800">{{ searchBookingResult.guest_count }}</span></p>
                <p v-if="searchBookingResult.notes" class="text-gray-500 italic">"{{ searchBookingResult.notes }}"</p>
              </div>

              <!-- Status Badge & Explanation -->
              <div class="pt-2 border-t border-[#E5D9C5] space-y-2">
                <div class="flex items-center gap-2">
                  <span 
                    class="px-3 py-1 text-xs font-bold rounded-full uppercase"
                    :class="{
                      'bg-amber-100 text-amber-800 border border-amber-300': searchBookingResult.status === 'pending',
                      'bg-emerald-100 text-emerald-800 border border-emerald-300': searchBookingResult.status === 'confirmed',
                      'bg-rose-100 text-rose-800 border border-rose-300': searchBookingResult.status === 'cancelled'
                    }"
                  >
                    {{ searchBookingResult.status === 'pending' ? $t('home.statusModal.statusPendingText') : (searchBookingResult.status === 'confirmed' ? $t('home.statusModal.statusConfirmedText') : $t('home.statusModal.statusCancelledText')) }}
                  </span>
                </div>

                <p class="text-[11px] text-gray-600 font-medium">
                  <template v-if="searchBookingResult.status === 'pending'">
                    {{ $t('home.statusModal.descPending') }}
                  </template>
                  <template v-else-if="searchBookingResult.status === 'confirmed'">
                    {{ $t('home.statusModal.descConfirmed') }}
                  </template>
                  <template v-else>
                    {{ $t('home.statusModal.descCancelled') }}
                  </template>
                </p>
              </div>
            </div>
          </div>

          <!-- Order Search Result Card -->
          <div v-if="searchResult" class="space-y-4 text-left font-sans">
            <!-- Order Header info -->
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 space-y-2">
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-500">{{ $t('home.statusModal.txNo') }}</span>
                <span class="font-mono font-bold text-gray-900">{{ searchResult.transaction_no }}</span>
              </div>
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-500">{{ $t('home.statusModal.customerName') }}</span>
                <span class="font-semibold text-gray-900">{{ searchResult.customer_name || '-' }}</span>
              </div>
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-500">{{ $t('home.statusModal.orderType') }}</span>
                <span class="font-bold text-gray-900">
                  {{ searchResult.order_type === 'take_away' ? '🛍️ Take Away' : (searchResult.order_type === 'dine_in' ? '🍽️ Dine In' : '🛵 Online') }}
                </span>
              </div>
              <div v-if="searchResult.table" class="flex justify-between items-center text-xs">
                <span class="text-gray-500">{{ $t('home.statusModal.tableNumber') }}</span>
                <span class="font-bold text-gray-900">🪑 Meja {{ searchResult.table.number }}</span>
              </div>
            </div>

            <!-- Order Status Single Badge -->
            <div class="space-y-1.5">
              <label class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">{{ $t('home.statusModal.statusTitle') }}</label>
              <div class="flex">
                <span 
                  class="px-4 py-2 rounded-xl text-sm font-bold font-mono border inline-flex items-center gap-2"
                  :class="
                    searchResult.status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-200' :
                    searchResult.status === 'processed' ? 'bg-blue-50 text-blue-800 border-blue-200' :
                    searchResult.status === 'delivered' ? 'bg-purple-50 text-purple-800 border-purple-200' :
                    searchResult.status === 'completed' ? 'bg-green-50 text-green-800 border-green-200' :
                    'bg-red-50 text-red-800 border-red-200'
                  "
                >
                  <span class="w-2.5 h-2.5 rounded-full"
                    :class="
                      searchResult.status === 'pending' ? 'bg-amber-500 animate-pulse' :
                      searchResult.status === 'processed' ? 'bg-blue-500 animate-pulse' :
                      searchResult.status === 'delivered' ? 'bg-purple-500 animate-pulse' :
                      searchResult.status === 'completed' ? 'bg-green-500' :
                      'bg-red-500'
                    "
                  ></span>
                  {{ searchResult.status }}
                </span>
              </div>
            </div>

            <!-- Order Items List -->
            <div class="space-y-2">
              <label class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">{{ $t('home.statusModal.itemsTitle') }}</label>
              <div class="max-h-36 overflow-y-auto space-y-2 border border-gray-100 bg-gray-50 rounded-xl p-3">
                <div v-for="item in searchResult.items" :key="item.id" class="flex justify-between items-start text-xs">
                  <div class="flex-1 min-w-0 pr-2">
                    <p class="font-medium text-gray-900 truncate">{{ item.product?.name || 'Sajian F&B' }}</p>
                    <p class="text-[10px] text-gray-500">{{ formatCurrency(item.price) }} x {{ item.quantity }}</p>
                  </div>
                  <span class="font-semibold text-gray-900">
                    {{ formatCurrency(item.price * item.quantity) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Total Price -->
            <div class="border-t pt-3 flex justify-between items-center text-xs sm:text-sm font-bold text-gray-900">
              <span>{{ $t('home.statusModal.totalLabel') }}</span>
              <span class="text-[#6B2E3E] text-sm sm:text-base">{{ formatCurrency(searchResult.total) }}</span>
            </div>

            <!-- WhatsApp & Upload Payment Proof in Search Results -->
            <div v-if="searchResult.payment_method === 'transfer'" class="mt-4 pt-3 border-t border-gray-100 space-y-3">
              <p class="text-xs font-sans font-semibold text-[#5A5A5A] uppercase tracking-wider">{{ $t('home.statusModal.paymentTitle') }}</p>

              <!-- WhatsApp Redirect Link -->
              <a
                v-if="whatsappUrl"
                :href="getStatusWhatsappUrl(searchResult)"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#25D366] text-white font-sans font-bold text-xs hover:bg-[#1ebe5d] transition-all active:scale-[0.98] cursor-pointer shadow-sm"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.52 3.48A11.86 11.86 0 0012.08 0C5.52 0 .18 5.34.18 11.9c0 2.1.55 4.15 1.59 5.96L.08 24l6.28-1.65a11.9 11.9 0 005.72 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.16-3.47-8.43zM12.09 21.8h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.21-3.73.98 1-3.64-.23-.37a9.87 9.87 0 01-1.52-5.28C2.19 6.45 6.63 2 12.08 2a9.9 9.9 0 017.02 2.92A9.9 9.9 0 0122 11.94c0 5.45-4.45 9.86-9.91 9.86zm5.42-7.4c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-1.72-.86-2.85-1.53-3.99-3.47-.3-.51.3-.47.86-1.56.1-.2.05-.37-.03-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49 1.89.82 2.63.89 3.57.75.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>
                <span>{{ $t('home.statusModal.whatsappConfirm') }}</span>
              </a>

              <!-- Upload file widget -->
              <div class="space-y-2">
                <label
                  class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-[#C9A96E] text-[#C9A96E] font-sans font-bold text-xs hover:bg-[#C9A96E]/10 transition-all cursor-pointer"
                  :class="{ 'border-green-500 text-green-600 bg-green-50': searchResultUploadedProofs(searchResult).length > 0 }"
                >
                  <template v-if="searchProofUploading">
                    <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                    <span>{{ $t('common.saving') }}</span>
                  </template>
                  <template v-else-if="searchResultUploadedProofs(searchResult).length > 0">
                    <span>📤</span>
                    <span>{{ $t('home.statusModal.uploadMore') }}</span>
                  </template>
                  <template v-else>
                    <span>📤</span>
                    <span>{{ $t('home.statusModal.uploadProof') }}</span>
                  </template>
                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="(e) => uploadSearchPaymentProof(e, searchResult)"
                    :disabled="searchProofUploading"
                  />
                </label>
                <p class="text-[9px] text-[#5A5A5A] text-center font-sans">{{ $t('home.statusModal.fileHelpText') }}</p>

                <!-- Previews of uploaded proofs -->
                <div v-if="searchResultUploadedProofs(searchResult).length > 0" class="flex flex-wrap gap-2 justify-center pt-2 bg-gray-50 border border-gray-100 rounded-xl p-3">
                  <div v-for="(proof, idx) in searchResultUploadedProofs(searchResult)" :key="idx" class="relative">
                    <a :href="getPaymentProofUrl(proof)" target="_blank" rel="noopener noreferrer">
                      <img :src="getPaymentProofUrl(proof)" alt="Bukti Transfer" class="w-16 h-16 object-cover rounded-lg border border-green-400 shadow-sm" />
                    </a>
                    <div class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-green-500 text-white rounded-full flex items-center justify-center text-[9px] font-bold">✓</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-4 border-t border-gray-100 flex justify-end bg-gray-50 flex-shrink-0">
          <button
            type="button"
            @click="closeStatusModal"
            class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-xl text-sm hover:bg-gray-100 transition-colors cursor-pointer"
          >
            {{ $t('home.statusModal.close') }}
          </button>
        </div>
      </div>
    </div>

    <!-- TABLE BOOKING MODAL -->
    <div
      v-if="showTableBookingModal"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click.self="showTableBookingModal = false"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-[#E5D9C5]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#1E1E1E] to-[#2C2C2C] text-[#F9F6F0] px-6 py-4 flex items-center justify-between">
          <div>
            <h2 class="font-display font-bold text-lg sm:text-xl">{{ $t('home.bookingModal.title') }}</h2>
            <p class="text-xs text-[#C9A96E] font-sans font-medium mt-0.5">
              {{ $t('home.bookingModal.outletLabel') }} {{ bookingTargetLocation?.name || 'Outlet F&B' }}
            </p>
          </div>
          <button @click="showTableBookingModal = false" class="text-gray-400 hover:text-white p-1.5 rounded-full transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form Content -->
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto font-sans">
          <!-- Customer Name -->
          <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">{{ $t('home.bookingModal.nameLabel') }}</label>
            <input
              v-model="bookingForm.customer_name"
              type="text"
              :placeholder="$t('home.bookingModal.namePlaceholder')"
              class="w-full px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm focus:border-[#C9A96E] focus:outline-none bg-[#F9F6F0]"
            />
          </div>

          <!-- WhatsApp Number -->
          <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">{{ $t('home.bookingModal.waLabel') }}</label>
            <input
              v-model="bookingForm.whatsapp_number"
              type="text"
              :placeholder="$t('home.bookingModal.waPlaceholder')"
              class="w-full px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm focus:border-[#C9A96E] focus:outline-none bg-[#F9F6F0]"
            />
          </div>

          <!-- Date and Time Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-600 uppercase mb-1">{{ $t('home.bookingModal.dateLabel') }}</label>
              <input
                v-model="bookingForm.reservation_date"
                type="date"
                :min="todayDateStr"
                class="w-full px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm focus:border-[#C9A96E] focus:outline-none bg-[#F9F6F0]"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-600 uppercase mb-1">{{ $t('home.bookingModal.timeLabel') }}</label>
              <input
                v-model="bookingForm.reservation_time"
                type="time"
                class="w-full px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm focus:border-[#C9A96E] focus:outline-none bg-[#F9F6F0]"
              />
            </div>
          </div>

          <!-- Guest Count / Capacity / Hall -->
          <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">{{ $t('home.bookingModal.guestCountLabel') }}</label>
            <input
              v-model="bookingForm.guest_count"
              type="text"
              :placeholder="$t('home.bookingModal.guestCountPlaceholder')"
              class="w-full px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm focus:border-[#C9A96E] focus:outline-none bg-[#F9F6F0]"
            />
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">{{ $t('home.bookingModal.notesLabel') }}</label>
            <textarea
              v-model="bookingForm.notes"
              rows="2"
              :placeholder="$t('home.bookingModal.notesPlaceholder')"
              class="w-full px-4 py-2.5 border-2 border-[#E5D9C5] rounded-xl text-sm focus:border-[#C9A96E] focus:outline-none resize-none bg-[#F9F6F0]"
            ></textarea>
          </div>
        </div>

        <!-- Footer Action -->
        <div class="bg-gray-50 border-t border-[#E5D9C5] px-6 py-4 flex justify-end gap-3">
          <button
            @click="showTableBookingModal = false"
            class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer"
          >
            {{ $t('home.bookingModal.closeBtn') }}
          </button>
          <button
            @click="submitTableBooking"
            :disabled="bookingSubmitting || !bookingForm.customer_name.trim() || !bookingForm.whatsapp_number.trim() || !bookingForm.reservation_date || !bookingForm.reservation_time"
            class="px-6 py-2.5 bg-gradient-to-r from-[#6B2E3E] to-[#2C2C2C] hover:from-[#5A2634] hover:to-[#1E1E1E] text-white rounded-xl font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition-all cursor-pointer"
          >
            {{ bookingSubmitting ? $t('home.bookingModal.submitting') : $t('home.bookingModal.submitBtn') }}
          </button>
        </div>
      </div>
    </div>

    <!-- BOOKING SUCCESS MODAL -->
    <div
      v-if="showBookingSuccessModal"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click.self="showBookingSuccessModal = false"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 text-center shadow-2xl border border-[#E5D9C5] space-y-4">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl shadow-inner">
          ✓
        </div>
        <h3 class="font-display font-bold text-xl text-gray-800">{{ $t('home.bookingModal.successTitle') }}</h3>
        <p class="text-sm text-gray-600 font-sans leading-relaxed">
          {{ $t('home.bookingModal.successDesc') }}
        </p>
        <div class="bg-[#F9F6F0] p-4 rounded-xl border border-[#E5D9C5] text-left text-xs font-sans space-y-1.5 text-gray-700">
          <p><strong>{{ $t('home.bookingModal.bookingCode') }}</strong> <span class="font-mono text-emerald-700 font-bold text-sm">{{ lastBookingData?.booking_code }}</span></p>
          <p><strong>{{ $t('home.bookingModal.outletLabel') }}</strong> {{ lastBookingData?.location?.name }}</p>
          <p><strong>Waktu & Kapasitas:</strong> {{ lastBookingData?.reservation_date }} • {{ lastBookingData?.reservation_time }} <span v-if="lastBookingData?.guest_count" class="font-bold text-amber-700">({{ lastBookingData.guest_count }})</span></p>
        </div>
        <button
          @click="showBookingSuccessModal = false"
          class="w-full py-3 bg-[#1E1E1E] hover:bg-[#6B2E3E] text-white rounded-xl font-bold text-sm transition-colors cursor-pointer"
        >
          {{ $t('home.bookingModal.closeBtn') }}
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
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const { t, locale } = useI18n()

const toggleLanguage = () => {
  locale.value = locale.value === 'id' ? 'en' : 'id'
}

const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const locations = ref([])
const products = ref([])
const categories = ref([])
const selectedLocationId = ref(null)
const activeCategory = ref('all')
const loadingProducts = ref(false)
const whatsappNumber = ref('')
const socialSettings = ref({
  whatsapp_number: '',
  instagram_url: '',
  facebook_url: '',
  tiktok_url: '',
  youtube_url: ''
})
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
const orderBookingCode = ref('')
const orderType = ref('dine_in')
const lastOrderNo = ref('')
const lastOrderId = ref(null)

// ── Payment Proof Upload State ──
const proofUploading = ref(false)
const proofUploaded = ref(false)
const proofPreviewUrl = ref(null)
const uploadedProofs = ref([])

// ── Table Booking State ──
const showTableBookingModal = ref(false)
const showBookingSuccessModal = ref(false)
const bookingSubmitting = ref(false)
const bookingTargetLocation = ref(null)
const lastBookingData = ref(null)

const todayDateStr = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const bookingForm = ref({
  customer_name: '',
  whatsapp_number: '',
  reservation_date: new Date().toISOString().split('T')[0],
  reservation_time: '18:30',
  guest_count: '',
  notes: ''
})

const openBookingModal = (loc) => {
  bookingTargetLocation.value = loc
  bookingForm.value = {
    customer_name: '',
    whatsapp_number: '',
    reservation_date: new Date().toISOString().split('T')[0],
    reservation_time: '18:30',
    guest_count: '',
    notes: ''
  }
  showTableBookingModal.value = true
}

const submitTableBooking = async () => {
  if (!bookingForm.value.customer_name.trim() || !bookingForm.value.whatsapp_number.trim()) return
  if (!bookingForm.value.reservation_date || !bookingForm.value.reservation_time) return
  if (!bookingTargetLocation.value?.id) return

  bookingSubmitting.value = true
  try {
    const payload = {
      location_id: bookingTargetLocation.value.id,
      customer_name: bookingForm.value.customer_name,
      whatsapp_number: bookingForm.value.whatsapp_number,
      reservation_date: bookingForm.value.reservation_date,
      reservation_time: bookingForm.value.reservation_time,
      guest_count: bookingForm.value.guest_count,
      notes: bookingForm.value.notes
    }

    const res = await api.post('/public/table-bookings', payload)
    lastBookingData.value = res.data?.data || null
    showTableBookingModal.value = false
    showBookingSuccessModal.value = true
  } catch (err) {
    console.error('Submit table booking error:', err)
    alert(`Gagal mengirim reservasi: ${err.response?.data?.message || 'Silakan coba lagi.'}`)
  } finally {
    bookingSubmitting.value = false
  }
}

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

const paymentWhatsappUrl = computed(() => {
  let number = String(whatsappNumber.value || '').replace(/\D/g, '')
  if (number.startsWith('0')) number = `62${number.slice(1)}`
  if (!number) return ''
  const msg = `Halo, saya ingin konfirmasi pembayaran untuk pesanan:\n\n📋 No. Pesanan: ${lastOrderNo.value}\n👤 Nama: ${orderCustomerName.value || '-'}\n💰 Total: ${formatCurrency(cartTotalPrice.value)}\n\nTerima kasih.`
  return `https://wa.me/${number}?text=${encodeURIComponent(msg)}`
})

const bankAccountCopied = ref(false)
const orderNoCopied = ref(false)

const copyBankAccount = async () => {
  try {
    await navigator.clipboard.writeText(paymentInfo.value.bank_account_number)
    bankAccountCopied.value = true
    setTimeout(() => { bankAccountCopied.value = false }, 2000)
  } catch {
    const el = document.createElement('textarea')
    el.value = paymentInfo.value.bank_account_number
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    bankAccountCopied.value = true
    setTimeout(() => { bankAccountCopied.value = false }, 2000)
  }
}

const copyOrderNumber = async () => {
  try {
    await navigator.clipboard.writeText(lastOrderNo.value)
    orderNoCopied.value = true
    setTimeout(() => { orderNoCopied.value = false }, 2000)
  } catch {
    const el = document.createElement('textarea')
    el.value = lastOrderNo.value
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    orderNoCopied.value = true
    setTimeout(() => { orderNoCopied.value = false }, 2000)
  }
}

// ── Check Order Status State & Functions ──
const showStatusModal = ref(false)
const searchTransactionNo = ref('')
const searchingStatus = ref(false)
const searchResult = ref(null)
const searchBookingResult = ref(null)
const searchError = ref('')
const searchProofUploading = ref(false)

const openStatusModal = () => {
  showStatusModal.value = true
  searchTransactionNo.value = ''
  searchResult.value = null
  searchBookingResult.value = null
  searchError.value = ''
}

const closeStatusModal = () => {
  showStatusModal.value = false
  searchTransactionNo.value = ''
  searchResult.value = null
  searchBookingResult.value = null
  searchError.value = ''
}

const checkOrderStatus = async () => {
  const query = searchTransactionNo.value.trim()
  if (!query) return
  searchingStatus.value = true
  searchError.value = ''

  // If query starts with BOOK-, search table bookings directly
  if (query.toUpperCase().startsWith('BOOK-')) {
    try {
      const res = await api.get('/public/table-bookings/search', { params: { query } })
      searchBookingResult.value = res.data?.data || null
      searchResult.value = null
    } catch (err) {
      console.error('Check booking status error:', err)
      searchBookingResult.value = null
      searchResult.value = null
      searchError.value = err.response?.data?.message || 'Reservasi meja tidak ditemukan.'
    } finally {
      searchingStatus.value = false
    }
    return
  }

  // Otherwise, search order first and fallback to table booking
  try {
    const res = await api.get('/public/orders/search', { params: { transaction_no: query } })
    searchResult.value = res.data?.data || null
    searchBookingResult.value = null
  } catch {
    try {
      const resBooking = await api.get('/public/table-bookings/search', { params: { query } })
      searchBookingResult.value = resBooking.data?.data || null
      searchResult.value = null
    } catch (bookingErr) {
      console.error('Check status error:', bookingErr)
      searchResult.value = null
      searchBookingResult.value = null
      searchError.value = 'Data pesanan atau reservasi meja tidak ditemukan.'
    }
  } finally {
    searchingStatus.value = false
  }
}

const searchResultUploadedProofs = (result) => {
  if (!result || !result.payment_proof) return []
  const raw = result.payment_proof
  try {
    if (raw.startsWith('[') && raw.endsWith(']')) {
      return JSON.parse(raw)
    }
  } catch (e) {
    console.error('Failed to parse payment proof JSON:', e)
  }
  return [raw]
}

const getPaymentProofUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const getStatusWhatsappUrl = (result) => {
  let number = String(whatsappNumber.value || '').replace(/\D/g, '')
  if (number.startsWith('0')) number = `62${number.slice(1)}`
  if (!number) return ''
  const msg = `Halo, saya ingin konfirmasi pembayaran untuk pesanan:\n\n📋 No. Pesanan: ${result.transaction_no}\n👤 Nama: ${result.customer_name || '-'}\n💰 Total: ${formatCurrency(result.total)}\n\nTerima kasih.`
  return `https://wa.me/${number}?text=${encodeURIComponent(msg)}`
}

const uploadSearchPaymentProof = async (e, result) => {
  const file = e.target.files[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('File harus berupa gambar (JPG, PNG, WebP).')
    if (e && e.target) e.target.value = ''
    return
  }
  if (file.size > 3 * 1024 * 1024) {
    alert('Ukuran file maksimal 3MB.')
    if (e && e.target) e.target.value = ''
    return
  }

  const currentNo = result.transaction_no

  searchProofUploading.value = true
  try {
    const formData = new FormData()
    formData.append('payment_proof', file)

    const res = await api.post(`/public/orders/${result.id}/payment-proof`, formData, {
      transformRequest: [(payload, headers) => {
        if (typeof headers.delete === 'function') {
          headers.delete('Content-Type')
        } else {
          delete headers['Content-Type']
        }
        return payload
      }]
    })

    alert(locale.value === 'id' ? 'Bukti pembayaran berhasil diupload' : 'Payment proof uploaded successfully')
    
    // Refresh the search result with updated data from backend
    if (res.data?.transaction) {
      searchResult.value = res.data.transaction
    } else {
      searchTransactionNo.value = currentNo
      await checkOrderStatus()
    }
  } catch (err) {
    console.error('Upload search payment proof error:', err)
    alert(`${locale.value === 'id' ? 'Gagal upload bukti' : 'Failed to upload proof'}: ${err.response?.data?.message || (locale.value === 'id' ? 'Silakan coba lagi.' : 'Please try again.')}`)
  } finally {
    searchProofUploading.value = false
    if (e && e.target) {
      e.target.value = ''
    }
  }
}

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
  if (!orderCustomerName.value.trim() && !orderBookingCode.value.trim()) return
  
  try {
    orderSubmitting.value = true
    
    const typeLabel = orderType.value === 'dine_in' ? 'Dine In' : 'Take Away'
    let notes = `Online Order (${typeLabel})`
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
      order_type: orderType.value,
      customer_name: orderCustomerName.value || null,
      items: cart.value,
      discount: 0,
      tax: 0,
      payment_method: 'transfer',
      paid_amount: 0,
      booking_code: orderBookingCode.value || null,
      notes: notes
    }
    
    const res = await api.post('/public/orders', payload)
    lastOrderNo.value = res.data?.data?.transaction_no || res.data?.transaction_no || ''
    lastOrderId.value = res.data?.data?.id || res.data?.id || null
    
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
  orderBookingCode.value = ''
  orderType.value = 'dine_in'
  lastOrderNo.value = ''
  lastOrderId.value = null
  proofUploading.value = false
  proofUploaded.value = false
  if (proofPreviewUrl.value) {
    URL.revokeObjectURL(proofPreviewUrl.value)
    proofPreviewUrl.value = null
  }
  uploadedProofs.value.forEach(url => {
    if (url.startsWith('blob:')) {
      URL.revokeObjectURL(url)
    }
  })
  uploadedProofs.value = []
  showOrderSuccess.value = false
}

const uploadPaymentProof = async (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('File harus berupa gambar (JPG, PNG, WebP).')
    e.target.value = ''
    return
  }
  if (file.size > 3 * 1024 * 1024) {
    alert('Ukuran file maksimal 3MB.')
    e.target.value = ''
    return
  }
  if (!lastOrderId.value) {
    alert('ID pesanan tidak ditemukan. Silakan coba lagi.')
    return
  }

  proofUploading.value = true
  try {
    const formData = new FormData()
    formData.append('payment_proof', file)

    await api.post(`/public/orders/${lastOrderId.value}/payment-proof`, formData, {
      transformRequest: [(payload, headers) => {
        if (typeof headers.delete === 'function') {
          headers.delete('Content-Type')
        } else {
          delete headers['Content-Type']
        }
        return payload
      }]
    })

    proofUploaded.value = true
    const localUrl = URL.createObjectURL(file)
    proofPreviewUrl.value = localUrl
    uploadedProofs.value.push(localUrl)
  } catch (err) {
    console.error('Upload payment proof error:', err)
    alert(`Gagal upload bukti: ${err.response?.data?.message || 'Silakan coba lagi.'}`)
  } finally {
    proofUploading.value = false
    e.target.value = ''
  }
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
    socialSettings.value = {
      whatsapp_number: setting.whatsapp_number || '',
      instagram_url: setting.instagram_url || '',
      facebook_url: setting.facebook_url || '',
      tiktok_url: setting.tiktok_url || '',
      youtube_url: setting.youtube_url || ''
    }
  } catch (err) {
    console.warn('Failed to load WhatsApp & Social settings:', err.response?.data || err.message)
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
