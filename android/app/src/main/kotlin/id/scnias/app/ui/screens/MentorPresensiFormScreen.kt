package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Save
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.KelasMasterDto
import id.scnias.app.data.dto.MentorPresensiRequest
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.components.*
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*

/**
 * Supports create (editId = null) and edit (editId != null) modes.
 */
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MentorPresensiFormScreen(nav: NavHostController, editId: Long? = null) {
    val isEdit = editId != null
    val today = remember { SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date()) }

    var kelasList by remember { mutableStateOf<List<KelasMasterDto>>(emptyList()) }
    var kelasExpanded by remember { mutableStateOf(false) }
    var selectedKelas by remember { mutableStateOf<KelasMasterDto?>(null) }
    var tanggal by remember { mutableStateOf(today) }
    var jamDatang by remember { mutableStateOf("08:00") }
    var jamPulang by remember { mutableStateOf("10:00") }
    var jumlah by remember { mutableStateOf("0") }
    var catatan by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(isEdit) }
    var saving by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        when (val r = AppGraph.mentorPresensi.kelas(null)) {
            is ApiResult.Success -> kelasList = r.value
            is ApiResult.Error   -> error = r.message
        }
        if (isEdit) {
            // Prefill from the API (show endpoint returns MentorPresensiEnvelope)
            when (val r = AppGraph.mentorPresensi.list()) {
                is ApiResult.Success -> {
                    val existing = r.value.data.find { it.id == editId }
                    if (existing != null) {
                        tanggal = existing.tanggal
                        jamDatang = existing.jamDatang.take(5)
                        jamPulang = existing.jamPulang.take(5)
                        jumlah = existing.jumlahMurid.toString()
                        catatan = existing.catatan ?: ""
                        selectedKelas = kelasList.find { it.id == existing.kelasId }
                    }
                }
                is ApiResult.Error -> error = r.message
            }
            loading = false
        }
    }

    Scaffold(
        topBar = { ScTopBar(if (isEdit) "Edit Presensi" else "Buat Presensi", onBack = { nav.popBackStack() }) }
    ) { pad ->
        if (loading) {
            LoadingBox(Modifier.padding(pad))
        } else {
            Column(
                Modifier.padding(pad).padding(16.dp).fillMaxSize().verticalScroll(rememberScrollState()),
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
                    OutlinedTextField(value = jamDatang, onValueChange = { jamDatang = it }, label = { Text("Jam Datang") }, modifier = Modifier.weight(1f))
                    OutlinedTextField(value = jamPulang, onValueChange = { jamPulang = it }, label = { Text("Jam Pulang") }, modifier = Modifier.weight(1f))
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
                        error = null; saving = true
                        scope.launch {
                            val req = MentorPresensiRequest(
                                kelasId = k.id,
                                tanggal = tanggal,
                                jamDatang = jamDatang,
                                jamPulang = jamPulang,
                                jumlahMurid = jumlah.toIntOrNull() ?: 0,
                                catatan = catatan.ifBlank { null },
                            )
                            val result = if (isEdit) {
                                AppGraph.mentorPresensi.update(editId!!, req)
                            } else {
                                AppGraph.mentorPresensi.create(req)
                            }
                            when (result) {
                                is ApiResult.Success -> nav.popBackStack()
                                is ApiResult.Error   -> { error = result.message; saving = false }
                            }
                        }
                    },
                    enabled = !saving,
                    modifier = Modifier.fillMaxWidth().height(48.dp),
                ) {
                    Icon(Icons.Default.Save, contentDescription = null); Spacer(Modifier.width(8.dp))
                    Text(if (saving) "Menyimpan…" else "Simpan")
                }
            }
        }
    }
}
