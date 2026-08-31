import api from './api'
import type { AuthResponse, UserProfile, ResetPasswordPayload, PasswordResetResponse } from '@/types/api'

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

  async forgotPassword(email: string): Promise<PasswordResetResponse> {
    const { data } = await api.post<PasswordResetResponse>('/forgot-password', { email })
    return data
  },

  async resetPassword(payload: ResetPasswordPayload): Promise<PasswordResetResponse> {
    const { data } = await api.post<PasswordResetResponse>('/reset-password', payload)
    return data
  },

  async updateProfile(payload: FormData | { name: string; avatar?: string | null }) {
    const isFormData = typeof FormData !== 'undefined' && payload instanceof FormData
    const { data } = await api.post<{ message: string; user: UserProfile }>(
      '/profile',
      payload,
      isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : undefined
    )
    return data
  },

  async updatePassword(payload: { current_password: string; password: string; password_confirmation: string }) {
    const { data } = await api.put<{ message: string }>('/password', payload)
    return data
  },
}
