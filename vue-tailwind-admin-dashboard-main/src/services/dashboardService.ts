import api from './api'

export const getSuperAdminDashboard = async () => {
  try {
    const response = await api.get(`/super-admin/dashboard`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching dashboard summary:', error)
    throw error
  }
}

