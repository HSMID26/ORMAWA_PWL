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
    let message = (error.response?.data as { message?: string })?.message || error.message

    if (status === 401) {
      const isLoginRequest = error.config?.url?.includes('/login')
      if (!isLoginRequest) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        localStorage.removeItem('auth_role')
        localStorage.removeItem('auth_organization')
        if (typeof window !== 'undefined') {
          window.dispatchEvent(new Event('auth:expired'))
        }
        message = 'Sesi Anda telah berakhir. Silakan login kembali.'
      }
    } else if (status === 403) {
      message = 'Anda tidak memiliki izin untuk melakukan tindakan ini.'
    } else if (status === 404) {
      message = 'Data tidak ditemukan.'
    } else if (status === 422) {
      // For 422, we will let the components extract the field validation errors.
      // But we can set a fallback message.
      message = 'Data yang dikirim tidak valid.'
    } else if (status === 500) {
      message = 'Terjadi kesalahan pada server.'
    } else if (!status) {
      message = 'Tidak dapat terhubung ke server.'
    }

    return Promise.reject({
      status,
      message,
      errors: (error.response?.data as { errors?: Record<string, string[]> })?.errors,
      data: error.response?.data,
    })
  },
)

export default api
