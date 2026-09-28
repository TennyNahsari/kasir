<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white border-t">
    <!-- Info -->
    <div class="text-sm text-gray-700">
      Showing <span class="font-medium">{{ from }}</span> to <span class="font-medium">{{ to }}</span> of 
      <span class="font-medium">{{ total }}</span> results
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center gap-2">
      <!-- Per Page Selector -->
      <select 
        :value="perPage" 
        @change="$emit('update:perPage', parseInt($event.target.value))"
        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
      >
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>

      <!-- Previous Button -->
      <button
        @click="$emit('update:currentPage', currentPage - 1)"
        :disabled="currentPage <= 1"
        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Page Numbers -->
      <div class="hidden sm:flex items-center gap-1">
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="page !== '...' && $emit('update:currentPage', page)"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-lg',
            page === currentPage 
              ? 'bg-blue-600 text-white' 
              : page === '...'
              ? 'text-gray-400 cursor-default'
              : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
          ]"
          :disabled="page === '...'"
        >
          {{ page }}
        </button>
      </div>

      <!-- Mobile: Current Page Info -->
      <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700">
        {{ currentPage }} / {{ lastPage }}
      </div>

      <!-- Next Button -->
      <button
        @click="$emit('update:currentPage', currentPage + 1)"
        :disabled="currentPage >= lastPage"
        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  lastPage: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  from: {
    type: Number,
    required: true
  },
  to: {
    type: Number,
    required: true
  }
})

defineEmits(['update:currentPage', 'update:perPage'])

// Calculate visible page numbers showing initial pages, ending pages, and middle current page with ellipsis
const visiblePages = computed(() => {
  const total = props.lastPage
  const current = props.currentPage

  if (total <= 6) {
    const pages = []
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
    return pages
  }

  const pages = []
  const firstPages = [1, 2]
  const lastPages = [total - 1, total]

  firstPages.forEach(p => pages.push(p))

  const middlePages = []
  for (let i = current - 1; i <= current + 1; i++) {
    if (i > 2 && i < total - 1) {
      middlePages.push(i)
    }
  }

  if (middlePages.length > 0 && middlePages[0] > 3) {
    pages.push('...')
  } else if (middlePages.length === 0 && current > 2 && current < total - 1) {
    pages.push('...')
  }

  middlePages.forEach(p => {
    if (!pages.includes(p)) pages.push(p)
  })

  if (middlePages.length > 0 && middlePages[middlePages.length - 1] < total - 2) {
    pages.push('...')
  } else if (middlePages.length === 0 && (current <= 2 || current >= total - 1) && total > 4) {
    if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }

  lastPages.forEach(p => {
    if (!pages.includes(p)) pages.push(p)
  })

  return pages
})
</script>
