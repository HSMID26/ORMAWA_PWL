# CMS ORMAWA ITI — Design System & Visual Guidelines (Design DNA)
## Stitch Institutional Design System Reference

This document serves as the project's **Design DNA** and single source of truth for all public frontend interfaces in CMS ORMAWA ITI, anchored in the Stitch Article Detail visual language.

---

## 1. Visual Personality & Tone
- **Archetype**: Institutional Academic + University Editorial + Student Organization Portal.
- **Character**: Structured, restrained, authoritative, minimal, high-readability, and academic.
- **Target Aesthetic**: Official university student affairs publication platform.

---

## 2. Core Tokens & Palette (Stitch Color System)

| Token | Hex / Value | Usage |
| :--- | :--- | :--- |
| **Primary** | `#00346F` | Primary CTAs, active highlights, header accents |
| **Primary Container** | `#004A99` | Hover states, secondary action accents |
| **Background Canvas** | `#F8F9FA` | Page background, alternate canvas |
| **Surface** | `#FFFFFF` | Cards, navigation bars, modal backgrounds |
| **Surface Low** | `#F3F4F5` | Secondary containers, blockquote background |
| **Surface High** | `#E7E8E9` | Category badges, filter chips |
| **Text Primary** | `#191C1D` | Headlines, titles, primary article body |
| **Text Secondary** | `#424751` | Subheads, descriptions, metadata |
| **Text Muted** | `#737783` | Captions, dates, secondary labels |
| **Surface Border** | `#C2C6D3` | 1px clean hairline borders |
| **Subtle Divider** | `#E1E3E4` | Internal card separators, section rules |
| **Error** | `#BA1A1A` | Urgent announcements, error states |
| **Organization Accent** | `organization.warna_tema` | Secondary badges, indicator dots, active tabs |

---

## 3. Typography Scale

- **Display Hero (H1)**: `64px` (Desktop) / `40px` (Mobile) (Line height: `1.1–1.2`, Weight: `800`) — *Article Detail & Platform Hero*
- **Page Title (H1)**: `32px–40px` (Weight: `800`) — *Internal Directory, News List, Agenda, Profile*
- **Section Heading (H2)**: `24px–32px` (Weight: `700`)
- **Card Subhead (H3)**: `18px–24px` (Weight: `600`)
- **Article Body**: `19px` (Line height: `1.75`, max reading column width: `760px`)
- **Body Standard**: `14px–16px` (Line height: `1.6`, Weight: `400`)
- **Button**: `14px` (Weight: `600`)
- **Label / Tag**: `12px` (Weight: `600`, letter-spacing: `0.05em`)

---

## 4. Radius & Border System

- **Default**: `2px` (`rounded-xs`)
- **Small**: `4px` (`rounded`)
- **Medium**: `8px` (`rounded-lg`)
- **Large / Major Surfaces**: `12px` (`rounded-xl` max)
- **Border**: `1px solid #C2C6D3` is the primary visual boundary; avoid heavy box-shadows.

---

## 5. Motion Guidelines
- **Fast**: 150ms (micro-interactions, button hover)
- **Normal**: 200ms (card elevation, dropdowns)
- **Content Reveal**: 350–420ms (smooth subtle fade)
- **Reduced Motion**: All animations disabled when `prefers-reduced-motion: reduce` is detected.

