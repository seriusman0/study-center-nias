# Android UI Kit — Study Center Nias

Recreates the official Compose-based Android app (under `studycenter-app/android/`). The real app is built with Jetpack Compose + Material 3 and currently uses the legacy navy + gold theme; this kit shows what it looks like with the **logo-aligned teal + orange + yellow** palette applied to the same screen structures.

These mockups live inside an Android device frame and use the design system's CSS tokens — they are not production code, just pixel-faithful prototypes for design review.

## Files

- `index.html` — phone canvas with three screens side-by-side (Login → Home → Jurnal).
- `Screens.jsx` — the three screens as small composable React components.
- `android-frame.jsx` — Android device shell (status bar, gesture nav, keyboard) from the starter kit.

## Screens included

| Screen | Source of truth (Compose) | Notes |
| --- | --- | --- |
| Login | `ui/screens/LoginScreen.kt` | Email + password, Google button, "Daftar Akun Tamu" link |
| Home | `ui/screens/HomeScreen.kt` | Top bar with name + role chip, action grid, bottom nav 4-tab |
| Jurnal Harian | `ui/screens/JurnalScreen.kt` | Date strip, 3 sections (Alkitab, Hafal Ayat, Jadwal Kehidupan) |

Cut from this kit (visible in the real app, but lower-priority for a brand review): Blog list, Blog detail, Presensi form, Mentor Presensi, Admin dashboard, Profile. The styling for any of these is straightforward to derive from the patterns shown.
