import { defineStore } from 'pinia'
import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface ToastMessage {
  id: number
  message: string
  type: ToastType
  duration?: number
}

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<ToastMessage[]>([])
  let nextId = 0

  const addToast = (message: string, type: ToastType = 'info', duration: number = 4000) => {
    const id = nextId++
    const toast = { id, message, type, duration }
    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }
  }

  const success = (message: string, duration?: number) => addToast(message, 'success', duration)
  const error = (message: string, duration: number = 6000) => addToast(message, 'error', duration) // Error lasts longer
  const warning = (message: string, duration?: number) => addToast(message, 'warning', duration)
  const info = (message: string, duration?: number) => addToast(message, 'info', duration)

  const removeToast = (id: number) => {
    const index = toasts.value.findIndex((t) => t.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  return {
    toasts,
    addToast,
    success,
    error,
    warning,
    info,
    removeToast,
  }
})
