package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.MentorPresensiDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.components.*
import id.scnias.app.ui.theme.ScNavy
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MentorPresensiScreen(nav: NavHostController) {
    var items by remember { mutableStateOf<List<MentorPresensiDto>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var deleteTarget by remember { mutableStateOf<MentorPresensiDto?>(null) }
    val scope = rememberCoroutineScope()

    suspend fun reload() {
        loading = true
        when (val r = AppGraph.mentorPresensi.list()) {
            is ApiResult.Success -> { items = r.value.data; error = null }
            is ApiResult.Error   -> error = r.message
        }
        loading = false
    }

    LaunchedEffect(Unit) { reload() }

    deleteTarget?.let { target ->
        ConfirmDeleteDialog(
            title = "Hapus Presensi?",
            message = "Presensi ${target.tanggal} — ${target.kelas?.nama ?: ""} akan dihapus.",
            onConfirm = {
                deleteTarget = null
                scope.launch { AppGraph.mentorPresensi.delete(target.id); reload() }
            },
            onDismiss = { deleteTarget = null }
        )
    }

    Scaffold(
        topBar = { ScTopBar("Presensi Mentor", onBack = { nav.popBackStack() }) },
        floatingActionButton = {
            FloatingActionButton(onClick = { nav.navigate(Route.MentorPresensiForm) }) {
                Icon(Icons.Default.Add, contentDescription = "Tambah")
            }
        }
    ) { pad ->
        when {
            loading -> LoadingBox(Modifier.padding(pad))
            error != null -> ErrorBox(error!!, Modifier.padding(pad)) { scope.launch { reload() } }
            items.isEmpty() -> EmptyBox("Belum ada presensi", Modifier.padding(pad))
            else -> LazyColumn(Modifier.padding(pad).fillMaxSize().padding(12.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(items) { p ->
                    ElevatedCard(
                        onClick = { nav.navigate(Route.mentorPresensiEditPath(p.id)) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                    ) {
                        Row(Modifier.padding(14.dp)) {
                            Column(Modifier.weight(1f)) {
                                Text(p.kelas?.nama ?: "—", fontWeight = FontWeight.Bold, color = ScNavy)
                                Text("${p.tanggal} · ${p.jamDatang.take(5)} → ${p.jamPulang.take(5)}", style = MaterialTheme.typography.bodySmall)
                                Text("Murid: ${p.jumlahMurid}", style = MaterialTheme.typography.bodySmall)
                                p.catatan?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = Color.Gray) }
                            }
                            IconButton(onClick = { deleteTarget = p }) {
                                Icon(Icons.Default.Delete, contentDescription = "Hapus", tint = Color.Red.copy(alpha = 0.7f))
                            }
                        }
                    }
                }
            }
        }
    }
}
