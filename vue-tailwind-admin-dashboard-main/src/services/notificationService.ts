import api from './api'

export interface Notification {
  id: number
  user_id: number
  organization_id: number | null
  type: string
  title: string
  message: string
  action_url: string | null
  read_at: string | null
  metadata: any
  created_at: string
  updated_at: string
}

export const notificationService = {
  getNotifications(params?: { limit?: number; page?: number }) {
    return api.get('/notifications', { params })
  },
  
  getUnreadCount() {
    return api.get('/notifications/unread-count')
  },
  
  markAsRead(id: number) {
    return api.post(`/notifications/${id}/read`)
  },
  
  markAllAsRead() {
    return api.post('/notifications/read-all')
  }
}
