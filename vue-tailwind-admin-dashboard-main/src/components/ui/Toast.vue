<template>
  <div class="fixed top-[calc(64px+16px)] lg:top-[calc(76px+16px)] right-4 left-4 sm:left-auto z-[9999] flex flex-col gap-3 sm:max-w-sm w-auto pointer-events-none">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        :class="['rounded-xl border p-4 shadow-lg flex items-start gap-3 pointer-events-auto', variantClasses[toast.type].container]"
        role="alert"
        aria-live="assertive"
      >
        <div :class="['-mt-0.5 shrink-0', variantClasses[toast.type].icon]">
          <component :is="icons[toast.type]" />
        </div>
        
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-800 dark:text-white/90">
            {{ toast.message }}
          </p>
        </div>

        <button 
          @click="toastStore.removeToast(toast.id)" 
          class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-sm"
          aria-label="Tutup notifikasi"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6L6 18M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useToastStore } from '@/stores/toast'
import { SuccessIcon, ErrorIcon, WarningIcon, InfoCircleIcon } from '@/icons'

const toastStore = useToastStore()

const variantClasses = {
  success: {
    container: 'border-success-500 bg-success-50 dark:border-success-500/30 dark:bg-gray-800',
    icon: 'text-success-500',
  },
  error: {
    container: 'border-error-500 bg-error-50 dark:border-error-500/30 dark:bg-gray-800',
    icon: 'text-error-500',
  },
  warning: {
    container: 'border-warning-500 bg-warning-50 dark:border-warning-500/30 dark:bg-gray-800',
    icon: 'text-warning-500',
  },
  info: {
    container: 'border-blue-light-500 bg-blue-light-50 dark:border-blue-light-500/30 dark:bg-gray-800',
    icon: 'text-blue-light-500',
  },
}

const icons = {
  success: SuccessIcon,
  error: ErrorIcon,
  warning: WarningIcon,
  info: InfoCircleIcon,
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}
</style>
