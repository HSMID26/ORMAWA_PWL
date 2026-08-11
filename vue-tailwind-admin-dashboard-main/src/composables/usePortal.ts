import { computed } from 'vue'
import { portalOrganizations, portalNews, portalEvents } from '@/config/portal'

export function usePortal() {
  const organizations = computed(() => portalOrganizations)
  const featuredOrganizations = computed(() => portalOrganizations.filter((org) => org.featured))
  const news = computed(() => portalNews)
  const events = computed(() => portalEvents)

  return {
    organizations,
    featuredOrganizations,
    news,
    events,
  }
}
