import { watchEffect, onUnmounted } from 'vue'

export interface SeoOptions {
  title?: string
  description?: string
  canonicalUrl?: string
  ogImage?: string
  ogType?: 'website' | 'article' | 'event' | string
  publishedTime?: string
  author?: string
  jsonLd?: Record<string, any>
}

export function useSeoMeta(getOptions: () => SeoOptions) {
  const setMetaTag = (selector: string, attr: string, value: string) => {
    let el = document.querySelector(selector)
    if (!el) {
      el = document.createElement('meta')
      const [nameOrProperty, key] = selector.replace('meta[', '').replace(']', '').split('=')
      el.setAttribute(nameOrProperty, key.replace(/["']/g, ''))
      document.head.appendChild(el)
    }
    el.setAttribute(attr, value)
  }

  const setCanonical = (url?: string) => {
    let link = document.querySelector("link[rel='canonical']") as HTMLLinkElement | null
    if (!url) {
      link?.remove()
      return
    }
    if (!link) {
      link = document.createElement('link')
      link.setAttribute('rel', 'canonical')
      document.head.appendChild(link)
    }
    link.setAttribute('href', url)
  }

  const setJsonLd = (data?: Record<string, any>) => {
    let script = document.querySelector('#seo-json-ld') as HTMLScriptElement | null
    if (!data) {
      script?.remove()
      return
    }
    if (!script) {
      script = document.createElement('script')
      script.id = 'seo-json-ld'
      script.type = 'application/ld+json'
      document.head.appendChild(script)
    }
    script.textContent = JSON.stringify(data)
  }

  watchEffect(() => {
    const opts = getOptions()
    const baseSiteName = 'ORMAWA ITI'

    // Document Title
    document.title = opts.title ? `${opts.title} | ${baseSiteName}` : baseSiteName

    // Standard Meta Description
    if (opts.description) {
      setMetaTag('meta[name="description"]', 'content', opts.description)
    }

    // Canonical Link
    setCanonical(opts.canonicalUrl || window.location.href)

    // Open Graph
    setMetaTag('meta[property="og:title"]', 'content', opts.title || baseSiteName)
    if (opts.description) setMetaTag('meta[property="og:description"]', 'content', opts.description)
    setMetaTag('meta[property="og:type"]', 'content', opts.ogType || 'website')
    setMetaTag('meta[property="og:url"]', 'content', opts.canonicalUrl || window.location.href)
    setMetaTag('meta[property="og:site_name"]', 'content', baseSiteName)
    if (opts.ogImage) {
      setMetaTag('meta[property="og:image"]', 'content', opts.ogImage)
    }

    // Twitter Card
    setMetaTag('meta[name="twitter:card"]', 'content', opts.ogImage ? 'summary_large_image' : 'summary')
    setMetaTag('meta[name="twitter:title"]', 'content', opts.title || baseSiteName)
    if (opts.description) setMetaTag('meta[name="twitter:description"]', 'content', opts.description)
    if (opts.ogImage) setMetaTag('meta[name="twitter:image"]', 'content', opts.ogImage)

    // Article Specifics
    if (opts.ogType === 'article') {
      if (opts.publishedTime) setMetaTag('meta[property="article:published_time"]', 'content', opts.publishedTime)
      if (opts.author) setMetaTag('meta[property="article:author"]', 'content', opts.author)
    }

    // JSON-LD Structured Data
    if (opts.jsonLd) {
      setJsonLd(opts.jsonLd)
    }
  })

  onUnmounted(() => {
    document.querySelector('#seo-json-ld')?.remove()
  })
}
