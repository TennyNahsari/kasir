<template>
  <div :class="cardClass" class="rounded-lg shadow-sm p-6">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div :class="iconBgClass" class="rounded-md p-3">
          <span class="text-2xl">{{ icon }}</span>
        </div>
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 truncate">
            {{ title }}
          </dt>
          <dd>
            <div class="text-3xl font-bold text-gray-900">
              {{ value }}
            </div>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    default: '📊'
  },
  color: {
    type: String,
    default: 'blue'
  }
})

const colorClasses = {
  blue: {
    card: 'bg-white border-blue-100',
    icon: 'bg-blue-50'
  },
  green: {
    card: 'bg-white border-green-100',
    icon: 'bg-green-50'
  },
  red: {
    card: 'bg-white border-red-100',
    icon: 'bg-red-50'
  },
  yellow: {
    card: 'bg-white border-yellow-100',
    icon: 'bg-yellow-50'
  },
  purple: {
    card: 'bg-white border-purple-100',
    icon: 'bg-purple-50'
  },
  cyan: {
    card: 'bg-white border-cyan-100',
    icon: 'bg-cyan-50'
  },
  orange: {
    card: 'bg-white border-orange-100',
    icon: 'bg-orange-50'
  },
  gray: {
    card: 'bg-white border-gray-100',
    icon: 'bg-gray-50'
  }
}

const cardClass = computed(() => 
  `border-2 ${colorClasses[props.color]?.card || colorClasses.blue.card}`
)

const iconBgClass = computed(() => 
  colorClasses[props.color]?.icon || colorClasses.blue.icon
)
</script>
