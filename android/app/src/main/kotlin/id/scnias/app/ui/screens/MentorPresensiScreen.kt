package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.MentorPresensiDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.theme.ScNavy

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MentorPresensiScreen(nav: NavHostController) {
    var items by remember { mutableStateOf<List<MentorPresensiDto>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    suspend fun reload() {
        loading = true
        when (val r = AppGraph.mentorPresensi.list()) {
            is ApiResult.Success -> { items = r.value.data; error = null }
            is ApiResult.Error   -> error = r.message
        }
        loading = false
    }

    LaunchedEffect(Unit) { reload() }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Presensi Mentor") },
                navigationIcon = { IconButton(onClick = { nav.popBackStack() }) { Icon(Icons.Default.ArrowBack, contentDescription = null) } },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ScNavy, titleContentColor = Color.White, navigationIconContentColor = Color.White),
            )
        },
        floatingActionButton = {
            FloatingActionButton(onClick = { nav.navigate(Route.MentorPresensiForm) }) {
                Icon(Icons.Default.Add, contentDescription = "Tambah")
            }
        }
    ) { pad ->
        when {
            loading -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
            error != null -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { Text("Error: $error") }
            items.isEmpty() -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { Text("Belum ada presensi") }
            else -> LazyColumn(Modifier.padding(pad).fillMaxSize().padding(12.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(items) { p ->
                    ElevatedCard(Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)) {
                        Column(Modifier.padding(14.dp)) {
                            Text(p.kelas?.nama ?: "—", fontWeight = FontWeight.Bold, color = ScNavy)
                            Text("${p.tanggal} · ${p.jamDatang.take(5)} → ${p.jamPulang.take(5)}", style = MaterialTheme.typography.bodySmall)
                            Text("Murid: ${p.jumlahMurid}", style = MaterialTheme.typography.bodySmall)
                            p.catatan?.let { Text(it, style = MaterialTheme.typography.bodySmall) }
                        }
                    }
                }
            }
        }
    }
}
