package id.scnias.app.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.navTopLevel
import id.scnias.app.ui.theme.ScGold
import id.scnias.app.ui.theme.ScNavy

@Composable
fun SplashScreen(nav: NavHostController) {
    LaunchedEffect(Unit) {
        val auth = AppGraph.auth
        if (!auth.hasToken()) {
            nav.navTopLevel(Route.Login)
            return@LaunchedEffect
        }
        when (auth.me()) {
            is ApiResult.Success -> nav.navTopLevel(Route.Home)
            is ApiResult.Error   -> nav.navTopLevel(Route.Login)
        }
    }

    Box(
        Modifier.fillMaxSize().background(ScNavy),
        contentAlignment = Alignment.Center
    ) {
        Column(horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.spacedBy(16.dp)) {
            Text("SC NIAS", color = ScGold, fontSize = 36.sp, fontWeight = FontWeight.Black)
            Text("Study Center Nias", color = Color.White.copy(alpha = 0.75f))
            Spacer(Modifier.height(24.dp))
            CircularProgressIndicator(color = ScGold)
        }
    }
}
