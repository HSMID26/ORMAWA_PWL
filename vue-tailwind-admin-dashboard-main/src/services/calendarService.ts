import api from './api'

export interface CalendarEvent {
  id: string
  title: string
  start: string
  allDay: true
  extendedProps: {
    description: string
    status: string
    organization: string | null
  }
}

export const calendarService = {
  async list(start: string, end: string) {
    const { data } = await api.get<{ status: string; data: CalendarEvent[] }>('/calendar/events', {
      params: { start, end },
    })
    return data.data
  },
}
