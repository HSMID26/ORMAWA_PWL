import type { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios'
import axios from 'axios'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'

const api: AxiosInstance = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers = config.headers ?? {}
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    const status = error.response?.status
    const message = (error.response?.data as { message?: string; errors?: Record<string, string[]> })?.message

    if (status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      localStorage.removeItem('auth_role')
      localStorage.removeItem('auth_organization')
      window.dispatchEvent(new Event('auth:expired'))
    }

    return Promise.reject({
      status,
      message: message || error.message || 'Terjadi kesalahan.',
      errors: (error.response?.data as { errors?: Record<string, string[]> })?.errors,
      data: error.response?.data,
    })
  },
)

export default api
