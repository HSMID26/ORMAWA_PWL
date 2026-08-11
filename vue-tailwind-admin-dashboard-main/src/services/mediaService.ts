import api from './api'

export const mediaService = {
  async upload(file: File) {
    const formData = new FormData()
    formData.append('image', file)
    const { data } = await api.post<{ status: string; url: string }>('/upload-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return data
  },
}
