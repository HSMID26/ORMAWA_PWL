# Refactoring Log

## 2026-11-17 — PRD Digital Ormawa PKA

- Added tenant-scoped annual work programs with PIC, participant target, estimated budget, status, pagination, and summary counts.
- Added API authorization, organization-scoped member/period validation, activity-log entries, and default role permissions for work programs.
- Added the organization work-program page, form, API service, route, and sidebar entry.
- Verified tenant isolation and validation with focused feature tests.
- Added organization-scoped incoming/outgoing correspondence records with private attachments, reference-number validation, review states, reviewer notes, and audit events.
- Added role permissions, the correspondence API and workflow service, and an organization UI for drafting, submitting, reviewing, and downloading letters.
- Verified attachment access, tenant isolation, and approval transitions with focused feature tests.
- Extended agenda approval with revision requests, reviewer notes, review metadata, notifications, and auditable status changes while keeping the existing status/API fields.
- Confirmed member/period history and import/export flows remain covered by the existing focused feature tests.
- Fixed correspondence multipart submission, exposed organization selection and records to Super Admin, and retained attachment URLs long enough to complete downloads.
- Added an authenticated calendar for all roles with tenant-scoped event data and published-only visibility for roles without agenda access.
