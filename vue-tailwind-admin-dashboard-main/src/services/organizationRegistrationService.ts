import api from './api';

export interface OrganizationRegistration {
  id: number;
  organization_name: string;
  organization_type: string;
  organization_subdomain: string;
  organization_logo?: string;
  organization_description?: string;
  admin_first_name: string;
  admin_last_name: string;
  admin_email: string;
  status: 'pending' | 'approved' | 'rejected';
  rejection_reason?: string;
  reviewed_by?: number;
  reviewed_at?: string;
  created_at: string;
  updated_at: string;
  reviewer?: {
    id: number;
    name: string;
    email: string;
  };
}

export const organizationRegistrationService = {
  async submit(payload: Record<string, any>): Promise<OrganizationRegistration> {
    const response = await api.post('/register/organization', payload);
    return response.data.data;
  },

  async list(params?: Record<string, any>) {
    const response = await api.get('/organization-registrations', { params });
    return response.data; // Includes data and meta for pagination
  },

  async getById(id: number): Promise<OrganizationRegistration> {
    const response = await api.get(`/organization-registrations/${id}`);
    return response.data.data;
  },

  async approve(id: number) {
    const response = await api.post(`/organization-registrations/${id}/approve`);
    return response.data;
  },

  async reject(id: number, reason: string) {
    const response = await api.post(`/organization-registrations/${id}/reject`, { reason });
    return response.data;
  }
};
