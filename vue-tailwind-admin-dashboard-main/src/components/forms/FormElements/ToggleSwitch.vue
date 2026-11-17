<template>
  <div class="flex items-center gap-3">
    <button
      type="button"
      role="switch"
      :aria-checked="modelValue === trueValue"
      :disabled="disabled"
      @click="toggle"
      class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
      :class="modelValue === trueValue ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700'"
    >
      <span
        aria-hidden="true"
        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        :class="modelValue === trueValue ? 'translate-x-5' : 'translate-x-0'"
      ></span>
    </button>
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
      <span v-if="disabled" class="inline-block w-4 h-4 mr-1 border-2 border-brand-500 border-t-transparent rounded-full animate-spin align-middle"></span>
      <span v-else class="font-bold text-[10px] uppercase tracking-wider mr-1 px-1.5 py-0.5 rounded" :class="modelValue === trueValue ? 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
        {{ modelValue === trueValue ? 'ON' : 'OFF' }}
      </span>
      {{ modelValue === trueValue ? activeLabel : inactiveLabel }}
    </span>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    required: true
  },
  trueValue: {
    type: [String, Number, Boolean],
    default: true
  },
  falseValue: {
    type: [String, Number, Boolean],
    default: false
  },
  activeLabel: {
    type: String,
    default: 'Aktif'
  },
  inactiveLabel: {
    type: String,
    default: 'Nonaktif'
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const toggle = () => {
  if (props.disabled) return
  const newValue = props.modelValue === props.trueValue ? props.falseValue : props.trueValue
  emit('update:modelValue', newValue)
  emit('change', newValue)
}
</script>
