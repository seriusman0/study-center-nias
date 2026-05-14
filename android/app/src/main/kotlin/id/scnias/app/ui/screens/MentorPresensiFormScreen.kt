package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Save
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.compose.foundation.text.KeyboardOptions
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.KelasMasterDto
import id.scnias.app.data.dto.MentorPresensiRequest
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.theme.ScNavy
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MentorPresensiFormScreen(nav: NavHostController) {
    val today = remember { SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date()) }

    var kelasList by remember { mutableStateOf<List<KelasMasterDto>>(emptyList()) }
    var kelasExpanded by remember { mutableStateOf(false) }
    var selectedKelas by remember { mutableStateOf<KelasMasterDto?>(null) }
    var tanggal by remember { mutableStateOf(today) }
    var jamDatang by remember { mutableStateOf("08:00") }
    var jamPulang by remember { mutableStateOf("10:00") }
    var jumlah by remember { mutableStateOf("0") }
    var catatan by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        when (val r = AppGraph.mentorPresensi.kelas(null)) {
            is ApiResult.Success -> kelasList = r.value
            is ApiResult.Error   -> error = r.message
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Buat Presensi") },
                navigationIcon = { IconButton(onClick = { nav.popBackStack() }) { Icon(Icons.Default.ArrowBack, contentDescription = null) } },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ScNavy, titleContentColor = Color.White, navigationIconContentColor = Color.White),
            )
        }
    ) { pad ->
        Column(
            Modifier.padding(pad).padding(16.dp).fillMaxSize(),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            ExposedDropdownMenuBox(expanded = kelasExpanded, onExpandedChange = { kelasExpanded = !kelasExpanded }) {
                OutlinedTextField(
                    value = selectedKelas?.let { it.nama + (it.cabang?.let { c -> " — $c" } ?: "") } ?: "Pilih kelas…",
                    onValueChange = {}, readOnly = true,
                    label = { Text("Nama Kelas") },
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = kelasExpanded) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                )
                ExposedDropdownMenu(expanded = kelasExpanded, onDismissRequest = { kelasExpanded = false }) {
                    kelasList.forEach { k ->
                        DropdownMenuItem(
                            text = { Text(k.nama + (k.cabang?.let { " — $it" } ?: "")) },
                            onClick = { selectedKelas = k; kelasExpanded = false }
                        )
                    }
                }
            }

            OutlinedTextField(value = tanggal, onValueChange = { tanggal = it }, label = { Text("Tanggal (YYYY-MM-DD)") }, modifier = Modifier.fillMaxWidth())
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedTextField(value = jamDatang, onValueChange = { jamDatang = it }, label = { Text("Jam Datang (HH:mm)") }, modifier = Modifier.weight(1f))
                OutlinedTextField(value = jamPulang, onValueChange = { jamPulang = it }, label = { Text("Jam Pulang (HH:mm)") }, modifier = Modifier.weight(1f))
            }
            OutlinedTextField(
                value = jumlah, onValueChange = { jumlah = it.filter { ch -> ch.isDigit() } },
                label = { Text("Jumlah Murid") },
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                modifier = Modifier.fillMaxWidth()
            )
            OutlinedTextField(value = catatan, onValueChange = { catatan = it }, label = { Text("Catatan (opsional)") }, modifier = Modifier.fillMaxWidth())

            error?.let { Text(it, color = MaterialTheme.colorScheme.error) }

            Button(
                onClick = {
                    val k = selectedKelas ?: run { error = "Pilih kelas dulu"; return@Button }
                    error = null; loading = true
                    scope.launch {
                        val req = MentorPresensiRequest(
                            kelasId = k.id,
                            tanggal = tanggal,
                            jamDatang = jamDatang,
                            jamPulang = jamPulang,
                            jumlahMurid = jumlah.toIntOrNull() ?: 0,
                            catatan = catatan.ifBlank { null },
                        )
                        when (val r = AppGraph.mentorPresensi.create(req)) {
                            is ApiResult.Success -> nav.popBackStack()
                            is ApiResult.Error   -> { error = r.message; loading = false }
                        }
                    }
                },
                enabled = !loading,
                modifier = Modifier.fillMaxWidth().height(48.dp),
            ) {
                Icon(Icons.Default.Save, contentDescription = null); Spacer(Modifier.width(8.dp))
                Text(if (loading) "Menyimpan…" else "Simpan")
            }
        }
    }
}
