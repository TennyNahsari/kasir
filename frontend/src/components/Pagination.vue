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

// Calculate visible page numbers with ellipsis
const visiblePages = computed(() => {
  const pages = []
  const delta = 2 // Number of pages to show on each side of current page
  
  if (props.lastPage <= 7) {
    // Show all pages if total is small
    for (let i = 1; i <= props.lastPage; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)
    
    // Calculate range around current page
    const start = Math.max(2, props.currentPage - delta)
    const end = Math.min(props.lastPage - 1, props.currentPage + delta)
    
    // Add ellipsis after first page if needed
    if (start > 2) {
      pages.push('...')
    }
    
    // Add pages around current page
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
    
    // Add ellipsis before last page if needed
    if (end < props.lastPage - 1) {
      pages.push('...')
    }
    
    // Always show last page
    pages.push(props.lastPage)
  }
  
  return pages
})
</script>
