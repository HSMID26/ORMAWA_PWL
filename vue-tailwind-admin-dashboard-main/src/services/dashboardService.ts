import api from './api'

export interface OrganizationDashboardSummary {
  user_role?: string
  organization: {
    id: number
    nama: string
    jenis: string
    subdomain: string
    status: string
    logo: string | null
    warna_tema: string
  }
  period: {
    id: number
    period_name: string
    start_date: string
    end_date: string
    status: string
    remaining_days: number
  } | null
  posts: {
    total: number
    draft: number
    review: number
    published: number
    rejected: number
  }
  my_posts?: {
    total: number
    draft: number
    review: number
    published: number
    rejected: number
  } | null
  my_recent_posts?: Array<{
    id: number
    judul: string
    status: string
    created_at: string
    updated_at: string
  }> | null
  agenda: {
    total: number
    upcoming: number
    today: number
    past: number
    upcoming_list: Array<{
      id: number
      judul: string
      tanggal_pelaksanaan: string
      deskripsi: string
      status: string
    }>
  }
  announcements: {
    total: number
    active: number
    urgent: number
  }
  members: {
    total_users: number
    total_editors?: number
    total_contributors?: number
    total_pengurus: number
  }
  recent_activities: Array<{
    id: number
    user_id: number | null
    organization_id: number | null
    action: string
    module: string
    description: string
    subject_type: string | null
    subject_id: number | null
    metadata: any
    created_at: string
    user?: {
      id: number
      name: string
    }
  }>
}

export interface SuperAdminDashboardSummary {
  organizations: {
    total: number
    active: number
    inactive: number
    pending_registrations: number
  }
  periods: {
    active: number
    pending: number
    expired: number
    expiring_soon: number
  }
  users: {
    total: number
    active: number
    inactive: number
    org_admins: number
    editors: number
    contributors: number
  }
  content: {
    posts: {
      total: number
      published: number
      review: number
      draft: number
      rejected: number
    }
    agendas: {
      total: number
      upcoming: number
      today: number
      past: number
    }
    announcements: {
      total: number
      published: number
      draft: number
      archived: number
    }
    media: {
      total: number
      images: number
      documents: number
    }
  }
  governance_alerts: {
    pending_registrations: number
    pending_renewals: number
    expiring_periods: number
    expired_periods: number
    inactive_organizations: number
  }
  recent_activities: Array<{
    id: number
    actor: string
    action: string
    module: string
    description: string
    organization: string
    organization_id: number | null
    created_at: string
    metadata?: any
    ip_address?: string | null
  }>
}

export const getSuperAdminDashboard = async (): Promise<SuperAdminDashboardSummary> => {
  try {
    const response = await api.get<{ status: string; data: SuperAdminDashboardSummary }>(`/super-admin/dashboard`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching super admin dashboard summary:', error)
    throw error
  }
}

export const getOrganizationDashboard = async () => {
  try {
    const response = await api.get<{ status: string; data: OrganizationDashboardSummary }>(`/organization/dashboard`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching organization dashboard summary:', error)
    throw error
  }
}

export const dashboardService = {
  getSuperAdminDashboard,
  getOrganizationDashboard,
}

