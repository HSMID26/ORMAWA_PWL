import api from './api'
import type { AuthResponse, UserProfile } from '@/types/api'

export const authService = {
  async login(email: string, password: string) {
    const { data } = await api.post<AuthResponse>('/login', { email, password })
    return data
  },

  async me() {
    const { data } = await api.get<{ user: UserProfile }>('/me')
    return data.user
  },

  async logout() {
    await api.post('/logout')
  },
}
