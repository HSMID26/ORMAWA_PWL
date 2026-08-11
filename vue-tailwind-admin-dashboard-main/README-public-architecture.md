# Public Frontend Architecture for Multi-Tenant CMS Ormawa

## 1. Information Architecture
- Home
- Profile
- Structure
- News List
- News Detail
- Agenda
- Gallery
- Documents
- Contact
- Search
- 404

## 2. Sitemap
- / → Home
- /profile → Organization profile
- /structure → Organizational structure
- /news → News list
- /news/:slug → News detail
- /agenda → Events and agenda
- /gallery → Gallery
- /documents → Documents
- /contact → Contact page
- /search → Search results
- /404 → Not found

## 3. User Flow
1. Visitor lands on the organization website.
2. Navigation directs them to profile, news, agenda, gallery, documents, and contact.
3. Search enables cross-content discovery across news, events, and documents.
4. Contact page provides direct access to communication channels.

## 4. Design System
- Reusable primitives: Button, Card, Section, Container, Badge, Tag, Avatar, Modal, Drawer, Dropdown, Pagination, Breadcrumb, Navbar, Footer, SearchBox, ArticleCard, EventCard, GalleryCard, DocumentCard.
- All page content is sourced from configuration files and shared data modules.
- Theme values use the organization config instead of hardcoded branding.

## 5. Frontend Folder Structure
- src/components/public → reusable public UI primitives
- src/views/public → page-level public views
- src/config/organization.ts → tenant configuration and sample content
- src/composables/usePublicSite.ts → shared public-site state
- src/types/public.ts → shared public interfaces

## 6. Maintainability Strategy
- Feature-based structure keeps public pages isolated from admin features.
- Shared layouts and components prevent duplication.
- Data-driven content enables one app to serve many orgs with config changes only.
- Modular components make future expansion straightforward.

## 7. Extension Path
Future modules such as voting, recruitment, admissions, or member portals can be added as new feature folders without changing the public shell architecture.
