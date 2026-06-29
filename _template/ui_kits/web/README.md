# Web UI Kit — Study Center Nias

A high-fidelity recreation of the Laravel web app at `studycenter-app/`. These are click-through prototypes built with React and inline CSS so a designer can drop them straight into mocks; they are NOT production code.

The UI kit faithfully reproduces what's currently shipping, **migrated to the logo-aligned teal palette** as the design system recommends. The colors are the most visible change vs. the live product.

## Files

- `index.html` — the prototype shell. Routes between Home, Blog index, Blog detail, Jurnal (daily journal), and Sign-in.
- `Tokens.jsx` — small re-export of the design tokens as JS objects for inline-style consumption.
- `Nav.jsx` — sticky top nav (member + guest variants).
- `Footer.jsx` — branded footer.
- `Hero.jsx` — landing hero with brand mark.
- `CabangGrid.jsx` — branch grid card.
- `BlogCard.jsx` — blog list card (image + meta + author).
- `BlogDetail.jsx` — blog detail with prose, comments, author.
- `JurnalDay.jsx` — full daily journal: Bible passage + verse memorization + life schedule, with optimistic checkbox toggles.
- `SignIn.jsx` — login card with Google button.
- `Toast.jsx` — toast notification (Tersimpan / Gagal).
- `Chip.jsx`, `Button.jsx`, `Avatar.jsx`, `Icon.jsx` — small reusables.

## Faithful to / cut from real product

| Section of real app | UI-kit treatment |
| --- | --- |
| Marketing hero + cabang grid + blog feed | ✓ Recreated |
| Blog detail + comments | ✓ Recreated |
| Jurnal harian (Bible reading + verse + life schedule) | ✓ Recreated, with working checkbox state |
| Sign in / Register | ✓ Login recreated, register cut to save space |
| Admin panel (AdminLTE-based) | Cut — admin panel is a separate aesthetic (Bootstrap 4 + AdminLTE); see `/ui_kits/admin/` if/when added |
| Mentor presensi form | Cut from this kit |
| CV / Kartu Nama print template | Cut — these are print artifacts, not screen UI |

Iconography uses Lucide inline SVGs at 1.75 px stroke.
