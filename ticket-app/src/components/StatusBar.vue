<template>
  <div>
    <div class="flex justify-between text-sm mb-1">
      <span class="font-medium text-gray-700">{{ label }}</span>
      <span class="text-gray-500">{{ value }}</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2">
      <div 
        :class="color" 
        class="h-2 rounded-full transition-all duration-300"
        :style="{ width: percentage + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: Number,
    required: true
  },
  max: {
    type: Number,
    default: 100
  },
  color: {
    type: String,
    default: 'bg-blue-500'
  }
})

const percentage = computed(() => {
  if (props.max === 0) return 0
  return Math.min((props.value / props.max) * 100, 100)
})
</script>
