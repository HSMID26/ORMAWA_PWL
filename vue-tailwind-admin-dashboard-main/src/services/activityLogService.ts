import api from './api';

export interface ActivityLogParams {
  page?: number;
  per_page?: number;
  module?: string;
  action?: string;
  user_id?: string | number;
  date_from?: string;
  date_to?: string;
  search?: string;
}

export const activityLogService = {
  getLogs(params: ActivityLogParams = {}) {
    return api.get('/activity-logs', { params });
  }
};
