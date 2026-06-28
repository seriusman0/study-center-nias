package id.scnias.app.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Article
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.LocationOn
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.ui.Route
import id.scnias.app.ui.components.*
import id.scnias.app.ui.navTopLevel
import id.scnias.app.ui.theme.*
import kotlinx.coroutines.launch

@Composable
fun HomeScreen(nav: NavHostController) {
    val role = remember { (AppGraph.auth.cachedRole() ?: "guest").lowercase() }
    val name = remember { AppGraph.auth.cachedName() ?: "User" }
    val username = remember { AppGraph.auth.cachedUsername() }
    val scope = rememberCoroutineScope()

    val roleLabel = when (role) {
        "student"   -> "SISWA"
        "mentor"    -> "MENTOR"
        "admin"     -> "ADMINISTRATOR"
        "fulltimer" -> "FULL-TIMER"
        else        -> "TAMU"
    }

    val tabs = remember(role) {
        buildList {
            add(BottomTab("home", "Beranda", Icons.Default.Home))
            add(BottomTab("blog", "Blog", Icons.AutoMirrored.Filled.Article))
            when (role) {
                "student"   -> add(BottomTab("jurnal", "Jurnal", Icons.AutoMirrored.Filled.MenuBook))
                "mentor"    -> add(BottomTab("presensi", "Presensi", Icons.Default.EventAvailable))
                "admin"     -> add(BottomTab("admin", "Admin", Icons.Default.Dashboard))
                "fulltimer" -> add(BottomTab("tulis", "Tulis", Icons.Default.Edit))
            }
            add(BottomTab("profile", "Profil", Icons.Default.Person))
        }
    }

    Scaffold(
        containerColor = ScBg,
        topBar = {
            ScBrandTopBar(
                title = "Halo, $name",
                subtitle = roleLabel,
                actions = {
                    IconButton(onClick = { scope.launch { AppGraph.auth.logout(); nav.navTopLevel(Route.Login) } }) {
                        Icon(Icons.AutoMirrored.Filled.Logout, contentDescription = "Logout", tint = Color.White)
                    }
                    Box(
                        Modifier.size(34.dp).clip(CircleShape).background(ScTeal600)
                            .border(width = 2.dp, color = ScOrange500, shape = CircleShape),
                        contentAlignment = Alignment.Center,
                    ) {
                        Text(
                            name.split(" ").take(2).map { it.first() }.joinToString("").uppercase().ifBlank { "U" },
                            color = Color.White,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold,
                        )
                    }
                },
            )
        },
        bottomBar = {
            ScBottomBar(tabs = tabs, active = "home") { tab ->
                when (tab.key) {
                    "home" -> {}
                    "blog" -> nav.navigate(Route.BlogList)
                    "jurnal" -> nav.navigate(Route.Jurnal)
                    "presensi" -> nav.navigate(Route.MentorPresensi)
                    "admin" -> nav.navigate(Route.AdminDashboard)
                    "tulis" -> nav.navigate(Route.BlogNew)
                    "profile" -> username?.let { nav.navigate(Route.profilePath(it)) }
                }
            }
        },
    ) { pad ->
        Column(
            Modifier.padding(pad).fillMaxSize().verticalScroll(rememberScrollState()).padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            VerseCard(
                verse = "Setiap orang harus cepat untuk mendengar, tetapi lambat untuk berkata-kata.",
                attribution = "Yakobus 1:19",
            )

            // Tile grid: rows of two
            val tiles = buildTiles(role)
            tiles.chunked(2).forEach { row ->
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    row.forEach { t ->
                        ActionTile(
                            icon = t.icon,
                            label = t.label,
                            sub = t.sub,
                            tone = t.tone,
                            modifier = Modifier.weight(1f),
                            onClick = { nav.navigate(t.route) },
                        )
                    }
                    if (row.size == 1) Spacer(Modifier.weight(1f))
                }
            }
            Spacer(Modifier.height(8.dp))
        }
    }
}

private data class TileSpec(val icon: ImageVector, val label: String, val sub: String, val tone: TileTone, val route: String)

private fun buildTiles(role: String): List<TileSpec> = buildList {
    when (role) {
        "student" -> {
            add(TileSpec(Icons.AutoMirrored.Filled.MenuBook, "Jurnal Harian", "Centang hari ini", TileTone.Teal, Route.Jurnal))
            add(TileSpec(Icons.Default.LocalFireDepartment, "Streak", "Lihat progres", TileTone.Orange, Route.Jurnal))
            add(TileSpec(Icons.AutoMirrored.Filled.Article, "Blog", "Baca & tulis", TileTone.Teal, Route.BlogList))
            add(TileSpec(Icons.Default.Badge, "Kartu Nama", "Siap dicetak", TileTone.Yellow, Route.ProfileEdit))
            add(TileSpec(Icons.Outlined.LocationOn, "Cabang", "Empat cabang", TileTone.Teal, Route.CabangList))
            add(TileSpec(Icons.Default.Description, "CV", "Atur CV publik", TileTone.Orange, Route.Cv))
        }
        "mentor" -> {
            add(TileSpec(Icons.Default.EventAvailable, "Presensi Saya", "Catat datang & pulang", TileTone.Teal, Route.MentorPresensi))
            add(TileSpec(Icons.Default.Checklist, "Presensi Siswa", "Catat per sesi", TileTone.Orange, Route.PresensiList))
            add(TileSpec(Icons.Default.Class, "Kelas Master", "Kelola kelas", TileTone.Teal, Route.AdminKelasMaster))
            add(TileSpec(Icons.AutoMirrored.Filled.Article, "Blog", "Baca & tulis", TileTone.Yellow, Route.BlogList))
            add(TileSpec(Icons.Outlined.LocationOn, "Cabang", "Empat cabang", TileTone.Teal, Route.CabangList))
        }
        "admin" -> {
            add(TileSpec(Icons.Default.Dashboard, "Dashboard", "Statistik", TileTone.Teal, Route.AdminDashboard))
            add(TileSpec(Icons.Default.People, "Users", "Kelola pengguna", TileTone.Orange, Route.AdminUsers))
            add(TileSpec(Icons.Default.Business, "Cabang", "Kelola cabang", TileTone.Teal, Route.AdminCabang))
            add(TileSpec(Icons.Default.Class, "Kelas", "Master kelas", TileTone.Yellow, Route.AdminKelasMaster))
            add(TileSpec(Icons.Default.MenuBook, "Jurnal", "Master jadwal", TileTone.Teal, Route.AdminLifeItems))
            add(TileSpec(Icons.Default.AdminPanelSettings, "Roles", "Hak akses", TileTone.Orange, Route.AdminRoles))
            add(TileSpec(Icons.Default.Key, "Permissions", "Kelola izin", TileTone.Yellow, Route.AdminPermissions))
            add(TileSpec(Icons.Default.Book, "Porsi Alkitab", "Master PL/PB", TileTone.Teal, Route.AdminBibleSchedules))
            add(TileSpec(Icons.Default.FormatQuote, "Ayat Hafalan", "Master mingguan", TileTone.Orange, Route.AdminWeeklyVerses))
            add(TileSpec(Icons.Default.Assessment, "Laporan Jurnal", "Per siswa", TileTone.Teal, Route.AdminJurnalReports))
            add(TileSpec(Icons.Default.Badge, "Name Tags", "Generate", TileTone.Yellow, Route.AdminNameTags))
            add(TileSpec(Icons.Default.EventAvailable, "Laporan Mentor", "Rekap presensi", TileTone.Teal, Route.AdminMentorPresensiReports))
            add(TileSpec(Icons.AutoMirrored.Filled.Article, "Moderasi Blog", "Hapus blog", TileTone.Orange, Route.AdminBlogs))
            add(TileSpec(Icons.Default.Comment, "Komentar", "Moderasi", TileTone.Yellow, Route.AdminComments))
        }
        "fulltimer" -> {
            add(TileSpec(Icons.Default.Edit, "Tulis Blog", "Buat artikel", TileTone.Teal, Route.BlogNew))
            add(TileSpec(Icons.AutoMirrored.Filled.Article, "Semua Blog", "Kurasi & moderasi", TileTone.Orange, Route.BlogList))
            add(TileSpec(Icons.Outlined.LocationOn, "Cabang", "Empat cabang", TileTone.Teal, Route.CabangList))
        }
        else -> {
            add(TileSpec(Icons.AutoMirrored.Filled.Article, "Blog", "Baca artikel", TileTone.Teal, Route.BlogList))
            add(TileSpec(Icons.Outlined.LocationOn, "Cabang", "Empat cabang", TileTone.Orange, Route.CabangList))
        }
    }
}

