# Study Center Nias — Design System

> _Second home for the better future._ — Logo tagline

Study Center is a non-profit education community under the **Bejana Mulia Foundation** in the Nias islands of North Sumatra, Indonesia. It serves as a "rumah kedua" (second home) for teenagers — helping them grow spiritually, academically, and characterologically through after-school study, mentorship, daily journaling, and a Christian devotional rhythm.

This design system encodes the brand so that any new screen, slide, poster, or app surface feels unmistakably _Study Center_ — warm, structured, faith-rooted, and friendly to both the teens it serves and the parents who entrust them.

---

## At a glance

| | |
| --- | --- |
| **Audience** | Teenagers (SMP–SMA, ~12–18 yrs) and their parents/guardians |
| **Tone** | Warm, encouraging, reverent, _gotong-royong_ (community-first) |
| **Language** | Bahasa Indonesia (primary) with sparing English where natural |
| **Vibe** | Modern minimalist with cream paper warmth, not corporate-cold |
| **Brand colors** | Teal-green · warm yellow · sunny orange · cream paper |
| **Channels** | Marketing site, member web app (blog + jurnal + presensi), Android app, printed name tags & kartu nama |

---

## Sources used to build this system

This design system was synthesized from materials the user provided:

- **GitHub repo** — [`seriusman0/study-center-nias`](https://github.com/seriusman0/study-center-nias) — full Laravel monorepo (web + Android client). The other related repo, [`seriusman0/study_center`](https://github.com/seriusman0/study_center), is the public-facing description.
- **Local codebase mount** — `studycenter-app/` — same Laravel + Android codebase, examined directly via the File-System Access bridge. Most exact tokens (colors, copy strings, view structures) were lifted from here.
- **Brand mark** — `uploads/logo.png` (now copied to `assets/logo.png`). All brand colors in this system are **sampled from the logo's actual pixels**, not estimated.
- **Brand description** — `studycenter-app/deskripsi.txt`, contributed by the team, describes the desired feel: clean, warm, structured, professional, welcoming.

Anyone iterating on this system should pull the repo locally and read it alongside — the views in `resources/views/` are the canonical source of truth for current product UI.

---

## ⚠️ Important — color migration

The **current product** (web + Android) uses a **navy blue (`#1E3A5F`) + gold (`#C9A84C`)** palette across most screens — see `tailwind.config.js`, `resources/css/app.css`, and `android/.../colors.xml`. The **logo itself**, however, is teal-green + yellow + orange.

The user explicitly asked us to **make this design system match the logo**, so this system is built around the **teal-green + orange + yellow** palette. Where the codebase still uses navy/gold, treat that as legacy — new screens and asset refreshes should adopt the logo-aligned tokens defined in `colors_and_type.css`. The printed _kartu nama_ template in `resources/views/cv/kartu-nama.blade.php` already uses the logo-aligned greens and oranges, so the migration is already partially underway.

---

## Content Fundamentals

**Language.** Bahasa Indonesia. Slightly formal — uses _Anda_ in admin panels and forms (`"Akun Anda belum terkait cabang. Hubungi admin."`), but warm and personal in member-facing surfaces (`"Halo, {nama} 👋"` in the jurnal). English appears only in fixed brand assets (`"SECOND HOME FOR THE BETTER FUTURE"` tagline) and in loanword tech terms (Blog, Login, Dashboard).

**Tone.** Three registers, used contextually:

1. **Warm/personal** — onboarding, jurnal, profile, error toasts. _"Bergabung untuk membaca dan berkomentar di blog Study Center Nias"_, _"Jadilah yang pertama menulis!"_, _"🔥 Streak: 12 hari berturut-turut"_.
2. **Functional/clear** — forms, admin tables, navigation. _"Pilih atau cari kelas..."_, _"Simpan Presensi"_, _"Filter"_.
3. **Reverent/scriptural** — verse cards, daily Bible reading. _"Pembacaan Alkitab"_, _"Perjanjian Lama"_, _"Hafal Ayat Mingguan"_. These are never casual.

**Capitalization.** Sentence case for everything except buttons and section titles, which use Title Case. Indonesian month names spelled out (`5 Mei 2026`, not `5/5/26`). The brand name is always written **Study Center Nias** (never abbreviated to SCN in user-facing copy, only in code).

**Person.** Member surfaces use second-person familiar — _"Tulis komentar..."_, _"Sudah punya akun?"_, _"Nama kamu"_. Admin and form copy switches to _Anda_ for respect.

**Emoji.** Used sparingly as decorative anchors, never as primary iconography. Current uses in the codebase: 🏫 (cabang), 📝 (blog placeholder), 📭 (empty state), 📍 (address), 📞 (phone), 🔥 (streak), 👋 (greeting). Limit to one per surface; never stack. New designs should prefer real iconography (see `ICONOGRAPHY`).

**Example copy (real, lifted from the codebase):**

- Empty blog state: _"Tidak ada blog ditemukan."_
- Empty journal: _"Porsi Alkitab belum tersedia untuk tanggal ini."_
- CTA primary: _"Daftar Sekarang"_, _"Simpan Presensi"_, _"Cetak / Simpan PDF"_
- Microcopy hint: _"Pilih dari master kelas cabang Anda."_, _"(opsional)"_
- Confirmation toast: _"Tersimpan"_
- Error toast: _"Gagal menyimpan, coba lagi."_
- Brand tagline (English, fixed): _"SECOND HOME FOR THE BETTER FUTURE"_
- Brand mission (Indonesian, fixed): _"Komunitas Belajar dan Rumah Kedua Remaja Sehat, Positif dan Berprestasi"_

---

## Visual Foundations

**Colors.** The palette is sampled directly from the logo. Teal-green is the workhorse — used for primary actions, navigation chrome, and the brand chip. Orange and yellow are accent colors used sparingly for warmth, streaks, accents, and the kartu nama corner illustrations. The page background is a warm cream (`#f7f6f1`) — never pure cold white. Full token tables live in `colors_and_type.css`.

- Use **teal-700/600** for filled primary buttons, header bars, links, focus rings.
- Use **orange-500** for streak/celebration accents, kartu nama corner shapes, mentor role color, and important highlights (verse-of-the-week border-left).
- Use **yellow-500** for the verse-card surface (with yellow-100 bg) and the fulltimer role.
- **Never use pure black on pure white** — body text is `--sc-ink-900` on `--sc-bg` (warm cream). Card surfaces are `#fff` but pages are cream.

**Typography.** Two-family system:

- **Plus Jakarta Sans** (UI sans) — humanist, Jakarta-designed, friendly + open apertures. Used for all UI text, headings, body, buttons, labels. Loaded from Google Fonts. _Substitution flag — confirm or supply a preferred webfont if needed._
- **Grandeur** (display) — the brand-supplied elegant semi-condensed display sans. Used for hero displays, big quotes, the verse-of-the-week treatment, and the welcome greeting on the journal. **Display use only** — never set below ~22 px; pairs with Plus Jakarta Sans for everything else. File lives at `fonts/Grandeur.ttf`.
- **JetBrains Mono** — for the rare code/badge use (CV section, debugging).

Sizes follow a Major-Third scale anchored on 16px. Display is 48px, H1 36, H2 28, H3 22, body 16, small 14, meta 12. Line-heights: 1.15 for display, 1.3 for headings, 1.55 for body, 1.72 for long-form articles.

**Spacing.** 4-pt grid with a t-shirt scale (`--sp-1` → `--sp-20`). Cards typically use `--sp-5` (20px) padding; section gutters are `--sp-12` (48px). The whole grid is exposed as CSS vars so it can be tightened or relaxed wholesale.

**Backgrounds.** No gradients on UI chrome (the navy hero gradient in the legacy app is being phased out). The cream paper background carries the warmth. Two intentional gradient uses are allowed:

1. The journal hero / dashboard hero, when used, fades from `--sc-teal-700` → `--sc-teal-600` (was navy in legacy).
2. The kartu nama corner illustrations — flat triangles of teal/orange/yellow stacked on cream paper (`#f5efe2`). These are an **intentional brand motif** unique to the print template, do not use as web chrome.

No background images on chrome. Real photos are used in blog cards and cabang pages — see "Imagery" below.

**Hand-drawn / illustration.** None in the current codebase. The brand mark is geometric (rooftop + book stack) and we follow that — straight strokes, flat fills, no sketch or doodle. Iconography is Lucide / Phosphor (see ICONOGRAPHY).

**Patterns / textures.** None. The cream page is the texture; everything else is flat.

**Animation & easing.** Sparing. Currently the app uses two motions:

- A toast slide-in (`x-transition` from AlpineJS) at top-right or bottom-right, auto-dismissing after 2.2–6s depending on severity.
- Hover lifts on cards: `hover:shadow-md` + a soft border color change to `--sc-teal-600`.

For new motion, use `--ease-out` for entries (200ms), `--ease-in-out` for state changes (200ms), and reserve `--ease-bounce` (320ms) for confirmation moments like a streak increment or a "Tersimpan" toast. No infinite-loop spinners on text — use a slim progress bar or a single rotating loader icon (Lucide `loader-2`).

**Hover states.**

- **Buttons** — darken the fill by one step (teal-600 → teal-700; orange-500 → orange-600). No translateY lift.
- **Cards** — `box-shadow: var(--sh-3)` (was `--sh-1`) + 1px border tints to `--sc-teal-300`.
- **Text links** — solid underline appears, color shifts from `--sc-teal-700` to `--sc-teal-800`.
- **Nav items** — soft `--sc-teal-50` background fill on hover.

**Press / active states.**

- Buttons: filled buttons darken one more step and apply `transform: scale(0.98)` for tactile feedback.
- Cards in tap context (mobile): scale to 0.99 + `--sh-1`.
- Checkboxes (journal items): tick mark animates in with `--ease-bounce` over 200ms.

**Focus rings.** All interactive elements get `--sh-focus` (3px teal halo at 28% alpha). Outline never removed without replacement.

**Borders.** Hairline borders only — `1px solid var(--sc-line)` (`#d9e2dc`, very subtle warm gray-green). No 2px+ borders except on the avatar ring (4px solid `--sc-orange-500` for member, `--sc-teal-700` for admin). No dashed or dotted borders in production UI.

**Shadow system.** Four soft tiers (`--sh-1` to `--sh-4`) all using warm-ink (`rgba(21,32,28,…)`), never pure black or blue-shifted. Use `--sh-2` for default cards, `--sh-3` for hover, `--sh-4` only for modals/popovers. Inset shadow `--sh-inset` provides a sub-1px tonal border on filled chips.

**Corner radii.** Cards: `--r-md` (12px). Pills/chips: `--r-pill`. Inputs: `--r-md`. Heroes and feature cards: `--r-lg` (16px) or `--r-xl` (24px). Avatars: full circle. Print artifacts (name tag, kartu nama) keep `--r-xs` (4px) — they look like physical cards.

**Transparency & blur.** Used very intentionally:

- Navbar/header has `--sc-teal-700` solid (not translucent) — readability over taste.
- Modal scrims use `rgba(21,32,28,0.55)`.
- Verse card uses 12% tint of `--sc-yellow-500` on cream.
- No backdrop-blur in production — it conflicted with low-end Android devices.

**Imagery & photography.** Warm, candid, daylight. Group shots of teens studying, mentors teaching, branch (cabang) buildings. Always shot in natural light, never desaturated to mood-board grayscale. Blog hero images are 16:9 cropped to 192px tall on cards. Tropical Indonesian context — green vegetation, blue sky, traditional architecture — should be embraced, not flattened.

**Protection gradients.** Avoided. When text must sit over a photo (rare — primarily hero on blog show page), use a solid 24% black bottom-up gradient only, no soft top fade.

**Layout rules.**

- Max content width: 1152px (`max-w-6xl` in Tailwind terms) for marketing/blog pages; 768px (`max-w-3xl`) for reading surfaces like blog detail and the journal.
- Page padding: 16px mobile, 24px tablet, 32px desktop.
- Sticky nav at top (z-40). Toasts at top-right (z-50).
- Footer is always centered, always 8 lines tall or fewer, on a deep teal background.

**Cards (definitive recipe).** White surface, `--r-md` radius, `--sh-2` shadow, optional `1px solid --sc-line` border. Padding `--sp-5` (20px). Hover: shadow steps up to `--sh-3`, border tints to `--sc-teal-300`. Content: title (sc-h4), 1-line meta (sc-meta), 2-line description (sc-small with `line-clamp-2`), avatar+name footer if attributed.

---

## Iconography

**Primary system — Lucide.** The current product uses **Font Awesome 5** loaded from CDN inside the admin panel (`<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">`). For new work we **recommend migrating to [Lucide](https://lucide.dev)** — open source, MIT, matching stroke weight, much smaller, and offering parity for every icon currently in use. This is a substitution; please confirm before we propagate it across the codebase.

Stroke weight: 1.75px. Default size: 20px in nav/buttons, 16px in chips/meta, 24px in section headers. Color: inherit from text (`currentColor`); never tint icons separately from their label.

**Icon usage map** — names below are Lucide names. The Font Awesome equivalent (used today) is in parentheses for migration.

| Surface | Icon | FA name |
| --- | --- | --- |
| Dashboard | `layout-dashboard` | `tachometer-alt` |
| Pengguna / Users | `users` | `users` |
| Roles | `user-cog` | `user-tag` |
| Permissions | `key-round` | `key` |
| Name tag | `id-card` | `id-card` |
| Presensi (attendance) | `clipboard-check` | `clipboard-check` |
| Jurnal / Journal | `book-open` | `book-open` |
| Porsi Alkitab (Bible passage) | `book-marked` | `book` |
| Hafal Ayat (verse memorize) | `sparkles` | (none — emoji ✨) |
| Jadwal Kehidupan (life schedule) | `heart-handshake` | (none) |
| Cabang (branches) | `map-pin` | `map-marker-alt` |
| Blog | `newspaper` | `newspaper` |
| Mentor Presensi | `user-clock` | `user-clock` |
| Master Kelas | `school` | `school` |
| Sign out | `log-out` | `sign-out-alt` |
| Beranda / Home | `home` | `home` |
| Profile / Akun | `user-circle` | `user` |
| Streak | `flame` | (emoji 🔥) |

**Emoji.** Currently used as cheap pictograms — 🏫 for cabang, 📝 for blog placeholders, 📭 for empty state, 🔥 for streak, 👋 for greeting. **For new screens, replace with the Lucide equivalents above.** Emoji is fine in informal user-generated content (blog post bodies, comments) and in toasts, but should not be load-bearing iconography.

**Unicode glyphs.** Only `←` and `→` are used as nav arrows (e.g. journal date stepper, "Kembali" links). Keep that — it's lighter than rendering an icon and reads instantly.

**SVG.** The kartu nama corners (`resources/views/cv/kartu-nama.blade.php`) use inline polygon SVGs — these are intentional brand illustrations and should be lifted into reusable assets, not redrawn. See `assets/kartu-nama-corner-*.svg` (placeholders — TBD; not yet copied because they're hand-tuned inside the Blade template).

**Logos.** `assets/logo.png` (the master logo, 4500×4500 PNG, transparent background). Use at minimum 32px (favicon), preferred 64px+ in marketing chrome. Pair with the wordmark "Study Center Nias" set in Plus Jakarta Sans 700 with `--tracking-tight`. There is currently no horizontal lockup; one should be designed.

---

## Index — what's in this folder

```
.
├── README.md                ← you are here
├── SKILL.md                 ← Agent-Skill manifest (Claude Code-compatible)
├── colors_and_type.css      ← canonical CSS tokens + semantic classes
├── assets/
│   └── logo.png             ← master brand mark
├── fonts/                   ← (loaded via Google Fonts CDN; no local files)
├── preview/                 ← Design System tab cards
│   ├── colors-primary.html
│   ├── colors-accent.html
│   ├── colors-neutrals.html
│   ├── colors-semantic.html
│   ├── colors-roles.html
│   ├── type-display.html
│   ├── type-scale.html
│   ├── type-roles.html
│   ├── spacing-scale.html
│   ├── radii.html
│   ├── shadows.html
│   ├── motion.html
│   ├── buttons.html
│   ├── form-controls.html
│   ├── chips-and-badges.html
│   ├── cards.html
│   ├── avatar-and-role.html
│   ├── logo-card.html
│   └── iconography.html
└── ui_kits/
    ├── web/                 ← Laravel marketing + blog + jurnal recreation
    │   ├── README.md
    │   ├── index.html       ← interactive click-through prototype
    │   ├── Nav.jsx, Footer.jsx, BlogCard.jsx, JurnalDay.jsx, ...
    └── android/             ← Compose mobile app screens (mocked)
        ├── README.md
        ├── index.html
        └── ...screens
```

---

## Asks for the user (please confirm to iterate)

1. **Fonts.** Brand display font **Grandeur** (user-supplied) is in `fonts/Grandeur.ttf` and wired up as `--font-display`. Body / UI uses **Plus Jakarta Sans** from Google Fonts. ➜ Confirm OK, or supply a custom body font if you have one.
2. **Icon set.** We substituted **Lucide** for **Font Awesome 5**. Migration is mechanical (1:1 mapping above). ➜ Approve or veto.
3. **Color migration.** This system uses logo-aligned **teal + orange + yellow**, replacing the navy + gold currently shipping. ➜ Confirm we should propagate this to web + Android, or limit to new artifacts.
4. **Imagery.** We have no real photos of branches, teens, or mentors. Cards in this system use placeholder boxes. ➜ Share a Drive folder of approved imagery when ready.

---

## Going further

To do an even better job designing with Study Center Nias's brand, the GitHub repositories below are the canonical source. Open them locally alongside this design system:

- **[`seriusman0/study-center-nias`](https://github.com/seriusman0/study-center-nias)** — the full Laravel + Android monorepo. Read `resources/views/` for the live Blade templates and `android/app/src/main/kotlin/id/scnias/app/ui/` for the Compose screens.
- **[`seriusman0/study_center`](https://github.com/seriusman0/study_center)** — the organization's public-facing description (mission, audience, governance under the Bejana Mulia Foundation).

The local mount used while building this system was `studycenter-app/` — same content as the GitHub repo, accessed directly to read view files and confirm exact CSS/Tailwind token usage. If anything in this design system feels under-specified, pulling those repos locally will give you 10× more context than this folder alone.


