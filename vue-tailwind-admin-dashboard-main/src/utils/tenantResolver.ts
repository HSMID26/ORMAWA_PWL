/**
 * Utility for determining the current organization tenant from the environment.
 * 
 * Logic priority:
 * 1. Production subdomain (e.g. hmif.iti.ac.id -> hmif)
 * 2. Development path (if applicable, e.g. /hmif handled by router)
 * 3. Main portal fallback
 */

export const resolveTenantFromHostname = (): string | null => {
  const hostname = window.location.hostname;

  // Local development / direct IP
  if (
    hostname === 'localhost' ||
    hostname === '127.0.0.1' ||
    hostname.startsWith('192.168.')
  ) {
    return null; // Let the Vue router resolve the tenant from the path in dev (e.g., /:tenantSlug)
  }

  // Production portal
  if (hostname === 'ormawa.iti.ac.id') {
    return null; // Main portal, no specific tenant
  }

  // Extract subdomain (e.g. hmif.iti.ac.id -> hmif)
  const parts = hostname.split('.');
  if (parts.length >= 3 && hostname.endsWith('.iti.ac.id')) {
    return parts[0];
  }

  return null;
};
