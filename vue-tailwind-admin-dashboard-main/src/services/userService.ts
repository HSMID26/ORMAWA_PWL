import api from './api'
import type { ApiListResponse, ApiSingleResponse, UserProfile } from '@/types/api'

export interface UserFilterParams {
  role?: string
  status?: string
  search?: string
  organization_id?: number
  exclude_super_admin?: boolean
}

export const userService = {
  async list(params?: UserFilterParams) {
    const { data } = await api.get<ApiListResponse<UserProfile>>('/users', { params })
    return data.data
  },

  async getById(id: number) {
    const { data } = await api.get<ApiSingleResponse<UserProfile>>(`/users/${id}`)
    return data.data
  },

  async create(payload: Record<string, any>) {
    const { data } = await api.post<ApiSingleResponse<UserProfile>>('/users', payload)
    return data.data
  },

  async update(id: number, payload: Record<string, any>) {
    const { data } = await api.put<ApiSingleResponse<UserProfile>>(`/users/${id}`, payload)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/users/${id}`)
  },

  async activate(id: number) {
    const { data } = await api.post<ApiSingleResponse<UserProfile>>(`/users/${id}/activate`)
    return data.data
  },

  async deactivate(id: number) {
    const { data } = await api.post<ApiSingleResponse<UserProfile>>(`/users/${id}/deactivate`)
    return data.data
  },

  async resetPassword(id: number, password: string) {
    const { data } = await api.post<{ status: string; message: string }>(`/users/${id}/reset-password`, { password })
    return data
  },
}

export interface RoleAccess {
  id: number
  name: string
  permissions: string[]
}

export interface RoleAccessResponse {
  roles: RoleAccess[]
  permissions: Array<{ id: number; name: string; guard_name: string }>
  groups: Record<string, Record<string, string>>
}

export const roleService = {
  async access() {
    const { data } = await api.get<{ status: string; data: RoleAccessResponse }>('/roles')
    return data.data
  },

  async updatePermissions(roleId: number, permissions: string[]) {
    const { data } = await api.put<{ status: string; message: string; data: { id: number; name: string; permissions: string[] } }>(
      `/roles/${roleId}/permissions`,
      { permissions }
    )
    return data.data
  },
}

