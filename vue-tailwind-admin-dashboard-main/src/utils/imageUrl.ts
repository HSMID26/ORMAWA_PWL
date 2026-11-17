/**
 * Resolve relative or storage image path to an absolute, browser-accessible URL.
 */
export function resolveImageUrl(path?: string | null): string {
  if (!path || typeof path !== 'string') return ''
  const trimmed = path.trim()
  if (!trimmed) return ''

  // Already absolute or data/blob
  if (
    trimmed.startsWith('http://') ||
    trimmed.startsWith('https://') ||
    trimmed.startsWith('data:') ||
    trimmed.startsWith('blob:')
  ) {
    return trimmed
  }

  // Get backend host base from VITE_API_BASE_URL (default: http://127.0.0.1:8000)
  const apiBase = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'
  const hostBase = apiBase.replace(/\/api\/?$/, '')

  const cleanPath = trimmed.startsWith('/') ? trimmed : `/${trimmed}`
  return `${hostBase}${cleanPath}`
}
