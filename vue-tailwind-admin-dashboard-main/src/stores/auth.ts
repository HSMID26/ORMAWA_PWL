import { defineStore } from 'pinia'
import { ref } from 'vue'
import router from '@/router'
import { authService } from '@/services/authService'
import type { UserProfile } from '@/types/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<UserProfile | null>(null)
  const role = ref<string | null>(null)
  const organization_id = ref<number | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const persistAuth = (authToken: string, authUser: UserProfile) => {
    token.value = authToken
    user.value = authUser
    role.value = authUser.role ?? null
    organization_id.value = authUser.organization?.id ?? null
    localStorage.setItem('auth_token', authToken)
    localStorage.setItem('auth_user', JSON.stringify(authUser))
    localStorage.setItem('auth_role', authUser.role ?? '')
    localStorage.setItem('auth_organization', authUser.organization?.id?.toString() ?? '')
  }

  const clearAuth = async () => {
    token.value = null
    user.value = null
    role.value = null
    organization_id.value = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_role')
    localStorage.removeItem('auth_organization')
    error.value = null
    await router.replace('/login')
  }

  const restoreSession = async () => {
    const storedToken = localStorage.getItem('auth_token')
    const storedUser = localStorage.getItem('auth_user')

    if (!storedToken) {
      return false
    }

    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser) as UserProfile
        role.value = user.value?.role ?? null
        organization_id.value = user.value?.organization?.id ?? null
      } catch {
        localStorage.removeItem('auth_user')
      }
    }

    try {
      isLoading.value = true
      const currentUser = await authService.me()
      user.value = currentUser
      role.value = currentUser.role ?? null
      organization_id.value = currentUser.organization?.id ?? null
      localStorage.setItem('auth_user', JSON.stringify(currentUser))
      localStorage.setItem('auth_role', currentUser.role ?? '')
      localStorage.setItem('auth_organization', currentUser.organization?.id?.toString() ?? '')
      isLoading.value = false
      return true
    } catch (err) {
      isLoading.value = false
      await clearAuth()
      return false
    }
  }

  const login = async (email: string, password: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authService.login(email, password)
      persistAuth(response.access_token, response.user)
      isLoading.value = false

      if (role.value === 'Super Admin') {
        await router.push('/dashboard')
      } else {
        await router.push('/organization/dashboard')
      }

      return true
    } catch (err: any) {
      isLoading.value = false
      let msg = 'Gagal melakukan login. Silakan coba lagi.'
      if (err && typeof err === 'object') {
        if (err.status === 401 || err.status === 422) {
          msg = 'Email atau kata sandi yang Anda masukkan salah.'
        } else if (err.status === 403) {
          msg = 'Akun Anda tidak memiliki akses ke sistem.'
        } else if (err.status === 500) {
          msg = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
        } else if (err.status === undefined && err.message) {
          msg = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.'
        }
      } else if (err instanceof Error) {
        msg = err.message
      }
      error.value = msg
      return false
    }
  }

  const logout = async () => {
    try {
      await authService.logout()
    } catch {
      // Ignore API errors and clear local session
    }
    await clearAuth()
  }

  return {
    user,
    role,
    organization_id,
    token,
    isLoading,
    error,
    login,
    logout,
    restoreSession,
    clearAuth,
  }
})
