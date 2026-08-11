import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { resolveOrganizationConfig, publicNews, publicEvents, publicGallery, publicDocuments, publicLeadership } from '@/config/organization'

export function usePublicSite() {
  const route = useRoute()
  const slug = computed(() => (route.params.slug as string | undefined) || 'hmif')
  const config = computed(() => resolveOrganizationConfig(slug.value))
  const navigation = computed(() => config.value.navigation.filter((item) => item.enabled))
  const modules = computed(() => config.value.modules)

  function resolvePath(path: string) {
    return slug.value ? `/org/${slug.value}${path}` : path
  }

  return {
    config,
    navigation,
    modules,
    news: publicNews,
    events: publicEvents,
    gallery: publicGallery,
    documents: publicDocuments,
    leadership: publicLeadership,
    resolvePath,
  }
}
