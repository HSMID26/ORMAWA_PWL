---
name: minimalist-ui
description: Principles for high-craft minimalist, editorial, and Swiss-inspired user interfaces. Focuses on content-first hierarchy, crisp typographic structure, restrained monochromatic palettes, purposeful whitespace, and eliminating UI ornamentation.
---

# Minimalist UI & Swiss Editorial Design Framework

The Minimalist UI framework prioritizes extreme intentionality, typographic clarity, and functional elegance over decorative elements.

---

## 1. Core Tenets of Minimalist Design

1. **Content IS the Interface**: Treat headlines, body copy, and photography as the primary visual anchors. Remove unnecessary icons, background flourishes, and card wrappers.
2. **Hairline Precision**: Use 1px crisp borders (`border-slate-200` / `#E2E8F0`) with 0px or 8–12px radius. Avoid heavy drop shadows.
3. **Restrained Color Palette**: The UI should function in pure monochrome (black, white, grays). Color is applied exclusively as an active state, status badge, or high-priority CTA.
4. **Air & Rhythm**: Generous yet proportional padding. Whitespace is a structural element, not dead space.

---

## 2. Component Guidelines

- **Cards**: Flat white surface, 1px border, minimal inner padding (16–24px), zero heavy shadow.
- **Headers & Navigation**: Minimalist height (64–72px), single border bottom, clean text links with active underlines.
- **Tables & Lists**: Clean horizontal row dividers (`divide-y divide-slate-100`), clear typographic column alignments.
- **Modals**: Centered, compact max-width (560–700px), subtle backdrop opacity (50–70%), rapid dismiss via ESC.
- **Empty States**: Neutral slate text, clear concise sentence, single secondary action button.

---

## 3. The 404 / 500 Minimalist Pattern
- Fullscreen pure background.
- Centered layout: `[Code] | [Message]` (e.g. `404 | Not Found`).
- Neutral typography (`text-gray-500 font-light`).
