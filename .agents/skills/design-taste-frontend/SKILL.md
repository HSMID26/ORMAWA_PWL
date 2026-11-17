---
name: design-taste-frontend
description: High-agency frontend design skill and taste framework. Enforces clean institutional typography, modular grids, visual hierarchy, restrained color palettes, subtle motion design, and eliminates generic AI slop. Activate whenever creating, redesigning, or refining UI components, pages, or styling in web applications.
---

# Design Taste & Frontend Aesthetic Framework (Taste Skill)

This skill equips agents with a high-end design sensibility to produce human-crafted, distinctive, and institutional-grade user interfaces while strictly preventing generic "AI slop" or template-heavy designs.

---

## 1. The Anti-Slop Manifesto (What NOT to do)

Never generate or accept the following AI boilerplate patterns:
- ❌ **No Glowing Blobs & Neon**: No floating purple/cyan radial blur blobs behind text.
- ❌ **No Glassmorphism Abuse**: No ubiquitous blurry transparent cards (`backdrop-blur-md bg-white/10`) unless specifically requested.
- ❌ **No Bubbly / Weight-900 Typography**: No cartoonish round display fonts or overly heavy black weights (weight 900) on small text.
- ❌ **No Giant Pill Buttons**: No `rounded-full` buttons with oversized padding and heavy drop shadows.
- ❌ **No Fake / Mock Data**: Never invent fake review stars, fake view counts, fake like numbers, or placeholder testimonials on production interfaces.
- ❌ **No Generic Marketing Buzzwords**: Avoid AI cliches like "Elevate your experience", "Seamlessly integrate", "Next-gen platform", "Unleash power".
- ❌ **No Giant Blank Spaces**: Never force artificial heights (e.g. `min-h-[600px]`) on content sections with few items.

---

## 2. Typography & Hierarchy (Editorial Excellence)

Typography is 80% of interface design. Adhere to these typographic principles:

- **Heading Hierarchy**:
  - **H1 / Hero**: `clamp(2.25rem, 5vw, 4rem)` | Weight: `700–800` | Line-height: `1.12–1.2`
  - **H2 (Section Header)**: `1.5rem–2.25rem` | Weight: `700` | Line-height: `1.25–1.3`
  - **H3 (Card / Block Header)**: `1.125rem–1.35rem` | Weight: `600–700` | Line-height: `1.35`
  - **Body Text**: `0.9375rem–1.0625rem (15–17px)` | Line-height: `1.6–1.7`
  - **Article Reading Body**: `1.125rem (18px)` | Line-height: `1.8–1.85` | Reading Column Width: `680–760px` max
  - **Muted / Metadata / Badges**: `0.6875rem–0.8125rem (11–13px)` | Weight: `500–600` | Letter-spacing: `0.025em–0.05em`
- **Text Color Hierarchy**:
  - High Contrast Primary: `#0F172A` / `#111827` (headings, titles)
  - Secondary Readable: `#334155` / `#475569` (body copy)
  - Muted Subtle: `#64748B` / `#94A3B8` (dates, tags, meta)

---

## 3. Color Architecture & Restraint

Follow the **60-30-10 Rule**:
- **60% Base Foundation**: Crisp white (`#FFFFFF`) and clean subtle background (`#F8FAFC` or `#F5F7FA`).
- **30% Structural Neutral**: Clean card surfaces (`#FFFFFF`), subtle hairline borders (`#E2E8F0` / `#E5E7EB`), dark typography.
- **10% Intentional Accent**: A single clear brand/institutional accent (e.g. `#2563EB` Royal Blue) used purposefully for CTAs, active indicators, focus rings, and section anchors.
- **Organization Custom Accent**: Applied as badges, dots, subtle underlines, and highlights—never washing over the entire page.

---

## 4. Grid, Layout & Dynamic Content Density

- **Underlying Grid**: 12-column flex/grid system with max container width of `1280px` (`max-w-7xl`).
- **Responsive Rhythms**:
  - Desktop: 3–4 columns (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3` or `lg:grid-cols-4`).
  - Tablet: 2 columns (`sm:grid-cols-2`).
  - Mobile: 1 column (`grid-cols-1`) with clean vertical rhythm.
- **Symmetrical Balance**: Always adapt layouts for sparse data (e.g., 1 card centered, 2 cards centered symmetrically).
- **Card Styling Tokens**:
  - Corner Radius: `8px–12px` (`rounded-lg` or `rounded-xl`). Never `rounded-3xl`.
  - Borders: `1px solid #E2E8F0` (clean, crisp hairline).
  - Shadows: Subtle ambient elevation (`shadow-2xs` or `shadow-xs`).
  - Hover States: `hover:-translate-y-0.5 hover:border-accent hover:shadow-xs transition duration-150`.

---

## 5. Motion Design Tokens & Accessibility

Motion exists to provide feedback, rhythm, and spatial hierarchy—never for decoration.

- **Tokens**:
  - `Fast`: `120–150ms` (buttons, tabs, links)
  - `Normal`: `180–220ms` (cards, modal backdrops, dropdowns)
  - `Medium`: `300–400ms` (hero entrance, drawers)
- **Safe Properties**: Only animate `transform` and `opacity` (GPU accelerated). Avoid animating `height`, `width`, `margin`, or `padding`.
- **Accessibility (`prefers-reduced-motion`)**:
  Always respect user motion preferences by wrapping decorative transitions in reduced motion media queries or providing instant fallbacks.

---

## 6. Accessibility & Semantic Foundation

- **Semantic HTML**: `<header>`, `<nav>`, `<main>`, `<article>`, `<aside>`, `<section>`, `<footer>`.
- **Interactive Elements**: `<button>` for actions, `<a>` / `<router-link>` for navigation. No unstyled clickable `<div>`s.
- **Focus Indicators**: Always maintain visible focus rings (`focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none`).
- **Screen Reader Support**: Meaningful `aria-label`, `aria-expanded`, `aria-modal`, and accessible skip links.
