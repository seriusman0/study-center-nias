package id.scnias.app.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

val ScNavy = Color(0xFF1E3A5F)
val ScNavyDark = Color(0xFF152744)
val ScGold = Color(0xFFC9A84C)
val ScBg = Color(0xFFF8FAFC)

private val LightScheme = lightColorScheme(
    primary = ScNavy,
    onPrimary = Color.White,
    secondary = ScGold,
    onSecondary = ScNavy,
    background = ScBg,
    onBackground = Color(0xFF1F2937),
    surface = Color.White,
    onSurface = Color(0xFF1F2937),
)

private val DarkScheme = darkColorScheme(
    primary = ScGold,
    onPrimary = ScNavy,
    secondary = ScGold,
    background = ScNavyDark,
    surface = ScNavy,
    onSurface = Color.White,
)

@Composable
fun ScNiasTheme(content: @Composable () -> Unit) {
    val scheme = if (isSystemInDarkTheme()) DarkScheme else LightScheme
    MaterialTheme(colorScheme = scheme, content = content)
}
