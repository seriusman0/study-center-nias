package id.scnias.app.ui.screens

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Article
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.LocationOn
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.R
import id.scnias.app.core.AppGraph
import id.scnias.app.ui.Route
import id.scnias.app.ui.navTopLevel
import id.scnias.app.ui.theme.ScInk500
import id.scnias.app.ui.theme.ScNavy
import id.scnias.app.ui.theme.ScOrange500
import id.scnias.app.ui.theme.ScTeal700
import id.scnias.app.ui.theme.ScYellow300
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(nav: NavHostController) {
    val role = remember { AppGraph.auth.cachedRole() ?: "guest" }
    val name = remember { AppGraph.auth.cachedName() ?: "User" }
    val username = remember { AppGraph.auth.cachedUsername() }
    val scope = rememberCoroutineScope()
    var selectedTab by remember { mutableIntStateOf(0) }

    // Build bottom nav items based on role
    val bottomItems = remember {
        buildList {
            add(BottomItem("Beranda", Icons.Default.Home, "home"))
            add(BottomItem("Blog", Icons.AutoMirrored.Filled.Article, "blog"))
            when (role.lowercase()) {
                "student" -> add(BottomItem("Jurnal", Icons.Default.CheckCircle, "jurnal"))
                "mentor" -> add(BottomItem("Presensi", Icons.Default.EventAvailable, "presensi"))
                "admin" -> add(BottomItem("Admin", Icons.Default.Dashboard, "admin"))
            }
            add(BottomItem("Profil", Icons.Default.Person, "profile"))
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Image(
                            painter = painterResource(id = R.drawable.logo),
                            contentDescription = "Logo",
                            modifier = Modifier.size(32.dp),
                        )
                        Spacer(Modifier.width(10.dp))
                        Column {
                            Text("Halo, $name", fontWeight = FontWeight.Bold, color = Color.White)
                            Text(role.uppercase(), fontSize = 11.sp, color = ScYellow300, fontWeight = FontWeight.Bold)
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ScNavy, titleContentColor = Color.White),
                actions = {
                    IconButton(onClick = {
                        scope.launch { AppGraph.auth.logout(); nav.navTopLevel(Route.Login) }
                    }) { Icon(Icons.AutoMirrored.Filled.Logout, contentDescription = "Logout", tint = Color.White) }
                }
            )
        },
        bottomBar = {
            NavigationBar(
                containerColor = Color.White,
                tonalElevation = 8.dp,
            ) {
                bottomItems.forEachIndexed { index, item ->
                    NavigationBarItem(
                        icon = { Icon(item.icon, contentDescription = item.label) },
                        label = { Text(item.label, fontSize = 11.sp) },
                        selected = selectedTab == index,
                        onClick = {
                            selectedTab = index
                            when (item.key) {
                                "home" -> { /* already here */ }
                                "blog" -> nav.navigate(Route.BlogList)
                                "jurnal" -> nav.navigate(Route.Jurnal)
                                "presensi" -> nav.navigate(Route.MentorPresensi)
                                "admin" -> nav.navigate(Route.AdminDashboard)
                                "profile" -> username?.let { nav.navigate(Route.profilePath(it)) }
                            }
                        },
                        colors = NavigationBarItemDefaults.colors(
                            selectedIconColor = ScTeal700,
                            selectedTextColor = ScTeal700,
                            indicatorColor = ScOrange500.copy(alpha = 0.15f),
                            unselectedIconColor = ScInk500,
                            unselectedTextColor = ScInk500,
                        )
                    )
                }
            }
        }
    ) { pad ->
        Column(
            Modifier.padding(pad).padding(20.dp).fillMaxSize().verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            Text("Menu Anda", fontWeight = FontWeight.SemiBold, color = ScNavy, fontSize = 16.sp)

            // ── Common menus (all authenticated) ──
            MenuCard("Profil Saya", "Lihat dan edit profil Anda", Icons.Default.Person) {
                username?.let { nav.navigate(Route.profilePath(it)) }
            }
            MenuCard("Blog", "Baca artikel terbaru", Icons.AutoMirrored.Filled.Article) {
                nav.navigate(Route.BlogList)
            }
            MenuCard("Cabang", "Empat cabang Study Center Nias", Icons.Outlined.LocationOn) {
                nav.navigate(Route.CabangList)
            }
            MenuCard("Tulis Blog", "Bagikan ceritamu", Icons.Default.Edit) {
                nav.navigate(Route.BlogNew)
            }

            // ── Role-specific menus ──
            when (role.lowercase()) {
                "student" -> {
                    MenuCard("Jurnal Harian", "Centang aktivitas hari ini", Icons.Default.CheckCircle) {
                        nav.navigate(Route.Jurnal)
                    }
                }
                "mentor" -> {
                    MenuCard("Presensi Saya", "Catat kehadiran mengajar", Icons.Default.EventAvailable) {
                        nav.navigate(Route.MentorPresensi)
                    }
                    MenuCard("Presensi Siswa", "Catat kehadiran siswa", Icons.Default.Checklist) {
                        nav.navigate(Route.PresensiList)
                    }
                    MenuCard("Panel Mentor", "Statistik dan ringkasan", Icons.Default.Dashboard) {
                        nav.navigate(Route.AdminDashboard)
                    }
                }
                "fulltimer" -> {
                    MenuCard("Moderasi Blog", "Kurasi & moderasi tulisan", Icons.Default.RateReview) {
                        nav.navigate(Route.BlogList)
                    }
                }
                "admin" -> {
                    MenuCard("Dashboard Admin", "Statistik dan ringkasan", Icons.Default.Dashboard) {
                        nav.navigate(Route.AdminDashboard)
                    }
                    MenuCard("Presensi Mentor", "Lihat semua catatan mentor", Icons.Default.EventAvailable) {
                        nav.navigate(Route.MentorPresensi)
                    }
                    MenuCard("Presensi Siswa", "Catat kehadiran siswa", Icons.Default.Checklist) {
                        nav.navigate(Route.PresensiList)
                    }
                    MenuCard("Kelola User", "Daftar dan status pengguna", Icons.Default.People) {
                        nav.navigate(Route.AdminUsers)
                    }
                    MenuCard("Kelola Cabang", "Tambah/edit cabang", Icons.Default.Business) {
                        nav.navigate(Route.AdminCabang)
                    }
                }
            }

            Spacer(Modifier.height(8.dp))
        }
    }
}

private data class BottomItem(val label: String, val icon: ImageVector, val key: String)

@Composable
private fun MenuCard(title: String, subtitle: String, icon: ImageVector, onClick: () -> Unit) {
    ElevatedCard(
        onClick = onClick,
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.elevatedCardColors(containerColor = Color.White),
    ) {
        Row(Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
            Box(
                Modifier.size(44.dp),
                contentAlignment = Alignment.Center
            ) {
                Icon(icon, contentDescription = null, tint = ScNavy, modifier = Modifier.size(28.dp))
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(title, fontWeight = FontWeight.SemiBold, color = ScNavy)
                Text(subtitle, fontSize = 12.sp, color = Color(0xFF6B7280))
            }
            Icon(Icons.Default.ChevronRight, contentDescription = null, tint = Color(0xFF9CA3AF))
        }
    }
}
