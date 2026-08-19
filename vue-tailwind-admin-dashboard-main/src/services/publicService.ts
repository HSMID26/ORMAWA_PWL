import type { Organization, Post, Activity } from '@/types/api'

/**
 * Public Service Layer
 * 
 * NOTE: The Laravel backend currently does not expose public endpoints for 
 * organizations, posts, or activities (they are all behind auth:sanctum).
 * 
 * This service acts as a clean abstraction layer ready for future backend integration.
 * It intentionally rejects promises with a specific error so the UI can display
 * a professional "Content Unavailable" state rather than breaking or using fake data.
 */

class EndpointUnavailableError extends Error {
  constructor(message: string = 'Layanan backend publik belum tersedia.') {
    super(message)
    this.name = 'EndpointUnavailableError'
  }
}

export const publicService = {
  // Expected: GET /api/public/organizations
  async getOrganizations(): Promise<Organization[]> {
    throw new EndpointUnavailableError('Data organisasi publik belum dapat dimuat.')
  },

  // Expected: GET /api/public/organizations/{slug}
  async getOrganizationBySlug(slug: string): Promise<Organization> {
    throw new EndpointUnavailableError('Profil organisasi publik belum dapat dimuat.')
  },

  // Expected: GET /api/public/organizations/{slug}/posts
  async getPostsByTenant(slug: string): Promise<Post[]> {
    throw new EndpointUnavailableError('Berita organisasi belum dapat dimuat.')
  },

  // Expected: GET /api/public/organizations/{slug}/activities
  async getActivitiesByTenant(slug: string): Promise<Activity[]> {
    throw new EndpointUnavailableError('Kegiatan organisasi belum dapat dimuat.')
  },

  // Expected: GET /api/public/organizations/{slug}/posts/{postSlug}
  async getPostBySlug(slug: string, postSlug: string): Promise<Post> {
    throw new EndpointUnavailableError('Detail berita publik belum dapat dimuat.')
  },

  // Expected: GET /api/public/organizations/{slug}/activities/{id}
  async getActivityById(slug: string, id: string | number): Promise<Activity> {
    throw new EndpointUnavailableError('Detail kegiatan publik belum dapat dimuat.')
  },

  // Expected: GET /api/public/organizations/{slug}/structure
  async getStructureByTenant(slug: string): Promise<any[]> {
    throw new EndpointUnavailableError('Struktur kepengurusan organisasi belum dapat dimuat.')
  }
}
