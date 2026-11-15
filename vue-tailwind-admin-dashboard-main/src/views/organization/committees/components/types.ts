export interface CommitteeFormData {
  name: string
  position: string
  department: string
  organization_period_id: number | undefined
  status: 'active' | 'inactive'
  photo: File | null
  photo_url?: string
}

export interface CommitteeFilterParams {
  search: string
  organization_period_id: number | undefined
  status: string
}
