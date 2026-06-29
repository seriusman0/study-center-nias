package id.scnias.app.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Typography
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.Font
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp
import id.scnias.app.R

/* -------- Logo-aligned palette (teal + orange + yellow) -------- */
val ScTeal900   = Color(0xFF003A2C)
val ScTeal800   = Color(0xFF005A44)
val ScTeal700   = Color(0xFF007A5C)
val ScTeal600   = Color(0xFF0E8E6D)
val ScTeal500   = Color(0xFF2BA888)
val ScTeal300   = Color(0xFF9DD9C5)
val ScTeal100   = Color(0xFFE1F3EC)
val ScTeal50    = Color(0xFFF1F9F5)

val ScOrange700 = Color(0xFFB85A10)
val ScOrange600 = Color(0xFFD8731A)
val ScOrange500 = Color(0xFFF19121)
val ScOrange300 = Color(0xFFF8C187)
val ScOrange100 = Color(0xFFFDE6CF)

val ScYellow700 = Color(0xFF97810F)
val ScYellow600 = Color(0xFFC8A91A)
val ScYellow500 = Color(0xFFE0C020)
val ScYellow300 = Color(0xFFF0DB77)
val ScYellow100 = Color(0xFFFBF3C5)

val ScInk900    = Color(0xFF15201C)
val ScInk700    = Color(0xFF324840)
val ScInk500    = Color(0xFF61756C)
val ScInk300    = Color(0xFFA3B3AC)
val ScLine      = Color(0xFFD9E2DC)
val ScLineSoft  = Color(0xFFEEF2EE)
val ScBg        = Color(0xFFF7F6F1)
val ScBgAlt     = Color(0xFFFBFAF6)
val ScPaper     = Color(0xFFF5EFE2)

/* Legacy aliases — keep so old screens still compile during migration */
val ScNavy     = ScTeal700
val ScNavyDark = ScTeal900
val ScGold     = ScOrange500

/* -------- Typography -------- */
val GrandeurFamily = FontFamily(Font(R.font.grandeur, FontWeight.Normal))

private val sans = FontFamily.SansSerif

private val ScTypography = Typography(
    displayLarge   = TextStyle(fontFamily = GrandeurFamily, fontWeight = FontWeight.SemiBold, fontSize = 48.sp, lineHeight = 56.sp),
    displayMedium  = TextStyle(fontFamily = GrandeurFamily, fontWeight = FontWeight.SemiBold, fontSize = 36.sp, lineHeight = 44.sp),
    displaySmall   = TextStyle(fontFamily = GrandeurFamily, fontWeight = FontWeight.SemiBold, fontSize = 28.sp, lineHeight = 36.sp),
    headlineLarge  = TextStyle(fontFamily = sans, fontWeight = FontWeight.ExtraBold, fontSize = 28.sp, lineHeight = 36.sp),
    headlineMedium = TextStyle(fontFamily = sans, fontWeight = FontWeight.Bold,      fontSize = 22.sp, lineHeight = 28.sp),
    headlineSmall  = TextStyle(fontFamily = sans, fontWeight = FontWeight.Bold,      fontSize = 18.sp, lineHeight = 24.sp),
    titleLarge     = TextStyle(fontFamily = sans, fontWeight = FontWeight.Bold,      fontSize = 16.sp, lineHeight = 22.sp),
    titleMedium    = TextStyle(fontFamily = sans, fontWeight = FontWeight.SemiBold,  fontSize = 14.sp, lineHeight = 20.sp),
    titleSmall     = TextStyle(fontFamily = sans, fontWeight = FontWeight.SemiBold,  fontSize = 13.sp, lineHeight = 18.sp),
    bodyLarge      = TextStyle(fontFamily = sans, fontWeight = FontWeight.Normal,    fontSize = 16.sp, lineHeight = 24.sp),
    bodyMedium     = TextStyle(fontFamily = sans, fontWeight = FontWeight.Normal,    fontSize = 14.sp, lineHeight = 20.sp),
    bodySmall      = TextStyle(fontFamily = sans, fontWeight = FontWeight.Normal,    fontSize = 12.sp, lineHeight = 16.sp),
    labelLarge     = TextStyle(fontFamily = sans, fontWeight = FontWeight.SemiBold,  fontSize = 14.sp, lineHeight = 20.sp),
    labelMedium    = TextStyle(fontFamily = sans, fontWeight = FontWeight.Medium,    fontSize = 12.sp, lineHeight = 16.sp),
    labelSmall     = TextStyle(fontFamily = sans, fontWeight = FontWeight.Medium,    fontSize = 11.sp, lineHeight = 14.sp),
)

private val LightScheme = lightColorScheme(
    primary              = ScTeal600,
    onPrimary            = Color.White,
    primaryContainer     = ScTeal100,
    onPrimaryContainer   = ScTeal900,
    secondary            = ScOrange500,
    onSecondary          = Color.White,
    secondaryContainer   = ScOrange100,
    onSecondaryContainer = ScOrange700,
    tertiary             = ScYellow600,
    onTertiary           = ScInk900,
    background           = ScBg,
    onBackground         = ScInk900,
    surface              = Color.White,
    onSurface            = ScInk900,
    surfaceVariant       = ScTeal50,
    onSurfaceVariant     = ScInk700,
    outline              = ScLine,
    outlineVariant       = ScLineSoft,
    error                = Color(0xFFC1352B),
    onError              = Color.White,
)

private val DarkScheme = darkColorScheme(
    primary      = ScTeal500,
    onPrimary    = ScTeal900,
    secondary    = ScOrange500,
    onSecondary  = ScInk900,
    background   = ScTeal900,
    onBackground = Color.White,
    surface      = ScTeal800,
    onSurface    = Color.White,
    outline      = ScTeal700,
)

@Composable
fun ScNiasTheme(content: @Composable () -> Unit) {
    // Force light scheme: brand designed for cream paper background. Dark mode
    // produced poor contrast with hard-coded grays in screen-local code.
    MaterialTheme(colorScheme = LightScheme, typography = ScTypography, content = content)
}
