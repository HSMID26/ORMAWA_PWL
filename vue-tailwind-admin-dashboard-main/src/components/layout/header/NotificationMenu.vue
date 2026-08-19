<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:bottom-0 hover:text-gray-700 h-11 w-11 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-dark dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
      @click="toggleDropdown"
      aria-label="Notifications"
    >
      <span
        v-if="unreadCount > 0"
        class="absolute right-0 top-0.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-error-500 text-[10px] font-medium text-white dark:border-gray-900"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
      <svg
        class="fill-current"
        width="20"
        height="20"
        viewBox="0 0 20 20"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
          fill=""
        />
      </svg>
    </button>

    <!-- Dropdown Start -->
    <div
      v-if="dropdownOpen"
      class="absolute -right-[80px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0 z-50"
    >
      <div
        class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800"
      >
        <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notification</h5>
        
        <div class="flex items-center gap-2">
          <button v-if="unreadCount > 0" @click="handleMarkAllAsRead" class="text-xs text-brand-500 hover:text-brand-600 font-medium">
            Mark all as read
          </button>
          <button @click="closeDropdown" class="text-gray-500 dark:text-gray-400 ml-2">
            <svg
              class="fill-current"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                fill=""
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col gap-3 p-4">
        <div v-for="i in 3" :key="i" class="animate-pulse flex space-x-4">
          <div class="rounded-full bg-gray-200 h-10 w-10 dark:bg-gray-700"></div>
          <div class="flex-1 space-y-2 py-1">
            <div class="h-2 bg-gray-200 rounded dark:bg-gray-700"></div>
            <div class="space-y-3">
              <div class="grid grid-cols-3 gap-4">
                <div class="h-2 bg-gray-200 rounded col-span-2 dark:bg-gray-700"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center justify-center h-full text-center">
        <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">Gagal memuat notifikasi.</p>
        <button @click="fetchNotifications" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600">
          Coba lagi
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center h-full text-center">
        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada notifikasi.</p>
      </div>

      <!-- List State -->
      <ul v-else class="flex flex-col h-auto overflow-y-auto custom-scrollbar">
        <li v-for="notification in notifications" :key="notification.id">
          <button
            @click="handleItemClick(notification)"
            class="w-full text-left flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5 transition-colors"
            :class="!notification.read_at ? 'bg-brand-50/30 dark:bg-brand-900/10' : ''"
          >
            <span class="relative flex items-center justify-center shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-brand-500">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </span>

            <span class="block w-full">
              <span class="mb-1 block text-theme-sm text-gray-600 dark:text-gray-300">
                <span class="font-semibold block text-gray-800 dark:text-white/90">
                  {{ notification.title }}
                </span>
                <span class="text-xs mt-0.5 line-clamp-2">
                  {{ notification.message }}
                </span>
              </span>

              <span class="flex items-center gap-2 text-gray-400 text-[11px] dark:text-gray-500 font-medium">
                <span>{{ formatRelativeTime(notification.created_at) }}</span>
                <span v-if="!notification.read_at" class="w-1.5 h-1.5 bg-brand-500 rounded-full ml-auto"></span>
              </span>
            </span>
          </button>
        </li>
      </ul>
    </div>
    <!-- Dropdown End -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { notificationService, type Notification } from '@/services/notificationService'

const router = useRouter()

const dropdownOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)
const notifications = ref<Notification[]>([])
const unreadCount = ref(0)
const loading = ref(false)
const error = ref(false)
let pollingInterval: number | null = null

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
  if (dropdownOpen.value) {
    fetchNotifications()
  }
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown()
  }
}

const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && dropdownOpen.value) {
    closeDropdown()
  }
}

const fetchUnreadCount = async () => {
  try {
    const response = await notificationService.getUnreadCount()
    unreadCount.value = response.data.unread_count
  } catch (err) {
    console.error('Failed to fetch unread count')
  }
}

const fetchNotifications = async () => {
  loading.value = true
  error.value = false
  try {
    const response = await notificationService.getNotifications({ limit: 20 })
    notifications.value = response.data.data
  } catch (err) {
    error.value = true
    console.error('Failed to fetch notifications')
  } finally {
    loading.value = false
  }
}

const handleItemClick = async (notification: Notification) => {
  // Mark as read if not already
  if (!notification.read_at) {
    try {
      await notificationService.markAsRead(notification.id)
      notification.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (err) {
      console.error('Failed to mark notification as read')
    }
  }

  closeDropdown()

  // Navigate if action_url exists
  if (notification.action_url) {
    router.push(notification.action_url)
  }
}

const handleMarkAllAsRead = async () => {
  try {
    await notificationService.markAllAsRead()
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString()
    })
    unreadCount.value = 0
  } catch (err) {
    console.error('Failed to mark all as read')
  }
}

const formatRelativeTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)
  
  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  return `${Math.floor(diffInSeconds / 86400)}d ago`
}

const startPolling = () => {
  fetchUnreadCount() // Initial fetch
  pollingInterval = window.setInterval(() => {
    fetchUnreadCount()
  }, 60000) // Poll every 60 seconds
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscape)
  startPolling()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})
</script>
