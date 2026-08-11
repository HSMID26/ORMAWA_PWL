import { defineStore } from 'pinia'
import { ref } from 'vue'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<string | null>(null)
  const role = ref<string | null>(null)
  const organization_id = ref<number | null>(null)
  const token = ref<string | null>(null)

  const login = async (mockRole: string) => {
    // Mock login logic
    user.value = 'John Doe'
    role.value = mockRole
    organization_id.value = mockRole === 'super_admin' ? null : 1
    token.value = 'mock-jwt-token'

    // Redirect based on role
    if (role.value === 'super_admin') {
      await router.push('/dashboard')
    } else {
      await router.push('/organization/dashboard')
    }
  }

  const logout = async () => {
    user.value = null
    role.value = null
    organization_id.value = null
    token.value = null
    await router.push('/login')
  }

  return { user, role, organization_id, token, login, logout }
})
