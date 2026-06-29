# SC NIAS — Android App

Kotlin + Jetpack Compose client for the SC NIAS Laravel backend (https://studycenter.overcomer.my.id/).

## Toolchain

- JDK 17 (Eclipse Adoptium installed)
- Android SDK at `C:\dev\android` (compileSdk 34, build-tools 34)
- Gradle 8.7 (downloaded via wrapper)
- AGP 8.5.2, Kotlin 1.9.24

## Bootstrap (first run)

Wrapper jar is not committed. Generate it from a system Gradle install **once**:

```bash
gradle wrapper --gradle-version 8.7 --distribution-type bin
```

Or download the wrapper jar manually to `gradle/wrapper/gradle-wrapper.jar`.

## Build APK

```bash
# Debug (emulator hits http://10.0.2.2:8000/api/)
./gradlew assembleDebug

# Release (signed, production https://studycenter.overcomer.my.id/api/)
./gradlew assembleRelease

# Output: app/build/outputs/apk/release/app-release.apk
```

## Keystore

Dev keystore expected at `keystore/scnias.keystore`. Generate one if missing:

```bash
keytool -genkeypair -v -keystore keystore/scnias.keystore \
    -alias scnias -keyalg RSA -keysize 2048 -validity 36500 \
    -storepass scnias-dev-2026 -keypass scnias-dev-2026 \
    -dname "CN=SC NIAS, OU=Dev, O=SC NIAS, L=Gunungsitoli, S=Sumut, C=ID"
```

Override passwords via env: `KEYSTORE_PASS`, `KEY_PASS`.

**Replace this keystore before publishing to Play Store.**

## Architecture

- Single-activity Compose
- Manual DI via `AppGraph` singleton (initialised in `ScNiasApp.onCreate`)
- Retrofit + Moshi networking, OkHttp `AuthInterceptor` injects Bearer token
- `EncryptedSharedPreferences` for token persistence
- Screens: Splash, Login, Register, Home (role-aware), Jurnal, Blog list, MentorPresensi list+form

## Roles and screens

| Role | Default home extras |
|---|---|
| student | Jurnal Harian, Blog |
| mentor | Presensi Saya, Blog |
| admin | Blog, Presensi Mentor |
| fulltimer/guest | Blog |

## Environments

Build config field `API_BASE_URL`:
- debug → `http://10.0.2.2:8000/api/` (Android emulator → host machine)
- release → `https://studycenter.overcomer.my.id/api/`

## Tests

```bash
./gradlew test
./gradlew connectedAndroidTest   # requires emulator/device
```

## Notes / out of scope (v1)

- Image upload (avatar/blog cover) not implemented
- TipTap rich-text editor → plain text only
- Google OAuth → email/password only
- Admin CRUD for KelasMaster/Jurnal stays on web
