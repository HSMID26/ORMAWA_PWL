import api from './api';

export interface Announcement {
  id: number;
  organization_id: number;
  user_id: number;
  title: string;
  slug: string;
  content: string;
  effective_date: string | null;
  priority: 'low' | 'normal' | 'high' | 'urgent';
  status: 'draft' | 'review' | 'published' | 'rejected';
  published_at: string | null;
  meta_title: string | null;
  meta_description: string | null;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
  };
}

export const announcementService = {
  async list(): Promise<Announcement[]> {
    const response = await api.get('/announcements');
    return response.data.data;
  },

  async getById(id: number): Promise<Announcement> {
    const response = await api.get(`/announcements/${id}`);
    return response.data.data;
  },

  async create(data: Partial<Announcement>): Promise<Announcement> {
    const response = await api.post('/announcements', data);
    return response.data.data;
  },

  async update(id: number, data: Partial<Announcement>): Promise<Announcement> {
    const response = await api.put(`/announcements/${id}`, data);
    return response.data.data;
  },

  async remove(id: number): Promise<void> {
    await api.delete(`/announcements/${id}`);
  }
};
