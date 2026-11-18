import api from './api'

export type WorkProgramStatus = 'planned' | 'in_progress' | 'completed' | 'cancelled'

export interface WorkProgram {
  id: number
  name: string
  objective: string
  period_year: number
  responsible_member_id: number | null
  responsible_member?: { id: number; name: string } | null
  target_participants: number
  estimated_budget: string | number
  status: WorkProgramStatus
}

export type WorkProgramInput = Omit<WorkProgram, 'id' | 'responsible_member'>

interface WorkProgramPage {
  status: string
  data: WorkProgram[]
  summary: {
    total: number
    planned: number
    in_progress: number
    completed: number
    cancelled: number
  }
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

interface WorkProgramResponse {
  status: string
  data: WorkProgram
}

export const workProgramService = {
  async list(page = 1) {
    const { data } = await api.get<WorkProgramPage>('/work-programs', {
      params: { page, per_page: 20 },
    })
    return data
  },

  async create(payload: WorkProgramInput) {
    const { data } = await api.post<WorkProgramResponse>('/work-programs', payload)
    return data.data
  },

  async update(id: number, payload: WorkProgramInput) {
    const { data } = await api.put<WorkProgramResponse>(`/work-programs/${id}`, payload)
    return data.data
  },

  async remove(id: number) {
    await api.delete(`/work-programs/${id}`)
  },
}
