---
name: study-center-nias-design
description: Use this skill to generate well-branded interfaces and assets for Study Center Nias — a non-profit teen-education community in the Nias islands of North Sumatra, Indonesia — either for production or throwaway prototypes / mocks. Contains essential design guidelines, colors (logo-aligned teal + orange + yellow), type (Plus Jakarta Sans + Fraunces), fonts, logos, iconography mapping, and ready-to-copy UI kit components for the web app and Android app.
user-invocable: true
---

# Study Center Nias — design skill

Study Center is a Christian, non-profit "rumah kedua" (second home) for teenagers in four Nias regencies — Gunungsitoli, Kab. Nias, Kab. Nias Selatan, Kab. Nias Utara. The product family is:

- A **Laravel web app** — public marketing + member blog + jurnal harian (daily Bible-reading journal) + presensi + admin panel.
- A **Compose Android app** mirroring the same member surfaces.
- **Printed artifacts** — kartu nama (business cards) and name tags for students.

Audience: teenagers 12–18 and their parents/guardians. Language: Bahasa Indonesia. Tone: warm, encouraging, reverent, never corporate.

## How to use this skill

1. **Read `README.md`** in this directory — it documents Content Fundamentals (tone of voice, copy examples), Visual Foundations (color, type, motion, layout rules), and Iconography. There is also a clear list of asks/caveats at the bottom.
2. **Read `colors_and_type.css`** — drop-in CSS variables and semantic classes. Either link it (`<link rel="stylesheet" href="colors_and_type.css">`) or copy the `:root` block into your file. The token names are the single source of truth — never hard-code colors when a token exists.
3. **Copy from `ui_kits/`** — `ui_kits/web/` has React JSX components for the web app (Nav, Footer, BlogCard, JurnalDay, SignIn, etc); `ui_kits/android/` has Compose-equivalent React mockups inside Android device frames. Both are styled with the design system; copy and adapt.
4. **Use `assets/logo.png`** — the master brand mark. 4500×4500 transparent PNG. Minimum 32 px in chrome.
5. **Browse `preview/`** for self-contained reference cards of every primitive — colors, type, spacing, radii, shadows, motion, buttons, form controls, chips, role badges, cards, avatars, logo lockups, iconography.

If creating visual artifacts (slides, mocks, throwaway prototypes), copy assets out and create static HTML files for the user to view.

If working on production code, you can copy assets and read the rules here to become an expert in designing with the Study Center Nias brand — but **the live Laravel + Android code still uses the legacy navy + gold scheme**, so propose color migration explicitly before propagating teal+orange+yellow into shipping product.

If the user invokes this skill without any other guidance, ask them what they want to build or design, ask some questions, and act as an expert designer who outputs HTML artifacts _or_ production code, depending on the need.

## Key things to remember (gotchas)

- **Color migration in progress.** Logo says teal/orange/yellow; shipped code says navy/gold (`#1E3A5F` / `#C9A84C`). This design system encodes the logo-aligned palette as the future direction. When editing existing Laravel views or Compose theme, **ask the user first** whether to migrate or match the current screen.
- **Iconography substitution.** Active codebase uses Font Awesome 5 in admin views; we recommend Lucide. The full FA→Lucide name map is in README → Iconography.
- **Font substitution.** Code uses `system-ui`. This system specifies **Grandeur** (brand display, supplied as `fonts/Grandeur.ttf`) + Plus Jakarta Sans (UI body) via Google Fonts. Confirm before swapping in production.
- **Language.** All user-facing copy is in Bahasa Indonesia. Do not invent English copy unless it's the fixed brand tagline ("Second home for the better future") or a loanword (Login, Blog).
- **Christian context.** The journal includes Perjanjian Lama (OT) and Perjanjian Baru (NT) passages and a memorized weekly verse. Treat scriptural copy with reverence — Fraunces italic, never casual.
- **Indonesian dates.** Spell out month names in Indonesian (`14 Mei 2026`). The codebase uses `translatedFormat('j F Y')` with locale `id`.

## Files at a glance

```
README.md                 Full system docs
SKILL.md                  ← you are here
colors_and_type.css       Tokens + semantic classes
assets/logo.png           Brand mark
preview/                  20+ self-contained reference cards
ui_kits/web/              Laravel-equivalent React kit (Home, Blog, Jurnal, Sign-in)
ui_kits/android/          Compose-equivalent device mockups
```
