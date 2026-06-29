package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
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
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.MentorPresensiDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.components.*
import id.scnias.app.ui.formatDateDisplay
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MentorPresensiScreen(nav: NavHostController) {
    var items by remember { mutableStateOf<List<MentorPresensiDto>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var deleteTarget by remember { mutableStateOf<MentorPresensiDto?>(null) }
    val snackbar = remember { SnackbarHostState() }
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
            message = "Presensi ${formatDateDisplay(target.tanggal)} — ${target.kelas?.nama ?: ""} akan dihapus.",
            onConfirm = {
                deleteTarget = null
                val prior = items
                items = items.filterNot { it.id == target.id }
                scope.launch {
                    val r = AppGraph.mentorPresensi.delete(target.id)
                    if (r is ApiResult.Error) {
                        items = prior
                        snackbar.showSnackbar("Gagal hapus: ${r.message}")
                    }
                }
            },
            onDismiss = { deleteTarget = null }
        )
    }

    Scaffold(
        topBar = { ScTopBar("Presensi Mentor", onBack = { nav.popBackStack() }) },
        snackbarHost = { SnackbarHost(snackbar) },
        floatingActionButton = {
            FloatingActionButton(
                onClick = { nav.navigate(Route.MentorPresensiForm) },
                containerColor = MaterialTheme.colorScheme.primary,
                contentColor = MaterialTheme.colorScheme.onPrimary,
            ) {
                Icon(Icons.Default.Add, contentDescription = "Tambah")
            }
        }
    ) { pad ->
        when {
            loading -> LoadingBox(Modifier.padding(pad))
            error != null -> ErrorBox(error!!, Modifier.padding(pad)) { scope.launch { reload() } }
            items.isEmpty() -> EmptyBox("Belum ada presensi", Modifier.padding(pad))
            else -> LazyColumn(
                Modifier.padding(pad).fillMaxSize().padding(12.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp),
            ) {
                items(items) { p ->
                    MentorPresensiCard(
                        item = p,
                        onEdit = { nav.navigate(Route.mentorPresensiEditPath(p.id)) },
                        onDelete = { deleteTarget = p },
                    )
                }
            }
        }
    }
}

@Composable
private fun MentorPresensiCard(
    item: MentorPresensiDto,
    onEdit: () -> Unit,
    onDelete: () -> Unit,
) {
    ElevatedCard(
        onClick = onEdit,
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.elevatedCardColors(containerColor = Color.White),
        elevation = CardDefaults.elevatedCardElevation(defaultElevation = 2.dp),
    ) {
        Row(
            Modifier.padding(horizontal = 16.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Box(
                Modifier.size(44.dp),
                contentAlignment = Alignment.Center,
            ) {
                Icon(
                    Icons.Default.EventAvailable,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.size(28.dp),
                )
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(2.dp)) {
                Text(
                    item.kelas?.nama ?: "—",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                Text(
                    formatDateDisplay(item.tanggal),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                val jam = "${item.jamDatang.take(5)} – ${item.jamPulang.take(5)}"
                Text(
                    jam,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                Text(
                    "Murid: ${item.jumlahMurid}",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                item.catatan?.takeIf { it.isNotBlank() }?.let {
                    Text(
                        it,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        maxLines = 2,
                    )
                }
            }
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                IconButton(onClick = onEdit, modifier = Modifier.size(40.dp)) {
                    Icon(
                        Icons.Default.Edit,
                        contentDescription = "Edit",
                        tint = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.size(20.dp),
                    )
                }
                IconButton(onClick = onDelete, modifier = Modifier.size(40.dp)) {
                    Icon(
                        Icons.Default.Delete,
                        contentDescription = "Hapus",
                        tint = MaterialTheme.colorScheme.error,
                        modifier = Modifier.size(20.dp),
                    )
                }
            }
        }
    }
}
