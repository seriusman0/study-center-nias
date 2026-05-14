package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.ui.Route
import id.scnias.app.ui.navTopLevel
import id.scnias.app.ui.theme.ScGold
import id.scnias.app.ui.theme.ScNavy
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(nav: NavHostController) {
    val role = remember { AppGraph.auth.cachedRole() ?: "guest" }
    val name = remember { AppGraph.auth.cachedName() ?: "User" }
    val scope = rememberCoroutineScope()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Column { Text("Halo, $name", fontWeight = FontWeight.Bold); Text(role.uppercase(), fontSize = 11.sp, color = ScGold) } },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ScNavy, titleContentColor = Color.White),
                actions = {
                    IconButton(onClick = {
                        scope.launch { AppGraph.auth.logout(); nav.navTopLevel(Route.Login) }
                    }) { Icon(Icons.Default.Logout, contentDescription = "Logout", tint = Color.White) }
                }
            )
        }
    ) { pad ->
        Column(
            Modifier.padding(pad).padding(20.dp).fillMaxSize(),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            Text("Menu Anda", fontWeight = FontWeight.SemiBold, color = ScNavy)
            when (role.lowercase()) {
                "student" -> {
                    MenuCard("Jurnal Harian", "Centang aktivitas hari ini", Icons.Default.MenuBook) { nav.navigate(Route.Jurnal) }
                    MenuCard("Blog", "Baca artikel terbaru", Icons.Default.Article) { nav.navigate(Route.BlogList) }
                }
                "mentor" -> {
                    MenuCard("Presensi Saya", "Catat kehadiran mengajar", Icons.Default.EventAvailable) { nav.navigate(Route.MentorPresensi) }
                    MenuCard("Blog", "Baca / tulis artikel", Icons.Default.Article) { nav.navigate(Route.BlogList) }
                }
                "admin" -> {
                    MenuCard("Blog", "Kelola artikel", Icons.Default.Article) { nav.navigate(Route.BlogList) }
                    MenuCard("Presensi Mentor", "Lihat semua catatan mentor", Icons.Default.EventAvailable) { nav.navigate(Route.MentorPresensi) }
                }
                else -> {
                    MenuCard("Blog", "Baca artikel terbaru", Icons.Default.Article) { nav.navigate(Route.BlogList) }
                }
            }
        }
    }
}

@Composable
private fun MenuCard(title: String, subtitle: String, icon: androidx.compose.ui.graphics.vector.ImageVector, onClick: () -> Unit) {
    ElevatedCard(
        onClick = onClick,
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.elevatedCardColors(containerColor = Color.White),
    ) {
        Row(Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
            Box(
                Modifier.size(44.dp).padding(8.dp),
                contentAlignment = Alignment.Center
            ) {
                Icon(icon, contentDescription = null, tint = ScNavy)
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(title, fontWeight = FontWeight.SemiBold, color = ScNavy)
                Text(subtitle, fontSize = 12.sp, color = Color.Gray)
            }
            Icon(Icons.Default.ChevronRight, contentDescription = null, tint = Color.Gray)
        }
    }
}
