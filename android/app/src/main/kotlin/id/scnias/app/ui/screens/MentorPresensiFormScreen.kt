package id.scnias.app.ui.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CalendarToday
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
import id.scnias.app.ui.formatDateDisplay
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
    var showDatePicker by remember { mutableStateOf(false) }
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
            when (val r = AppGraph.mentorPresensi.show(editId!!)) {
                is ApiResult.Success -> {
                    val p = r.value
                    tanggal = p.tanggal
                    jamDatang = p.jamDatang.take(5)
                    jamPulang = p.jamPulang.take(5)
                    jumlah = p.jumlahMurid.toString()
                    catatan = p.catatan ?: ""
                    selectedKelas = kelasList.find { it.id == p.kelasId }
                        ?: p.kelas?.let { km -> kelasList.firstOrNull { it.id == km.id } }
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

                if (showDatePicker) {
                    val cal = remember(tanggal) {
                        Calendar.getInstance().apply {
                            runCatching { time = SimpleDateFormat("yyyy-MM-dd", Locale.US).parse(tanggal)!! }
                        }
                    }
                    val dpState = rememberDatePickerState(initialSelectedDateMillis = cal.timeInMillis)
                    DatePickerDialog(
                        onDismissRequest = { showDatePicker = false },
                        confirmButton = {
                            TextButton(onClick = {
                                dpState.selectedDateMillis?.let { ms ->
                                    tanggal = SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date(ms))
                                }
                                showDatePicker = false
                            }) { Text("OK") }
                        },
                        dismissButton = { TextButton(onClick = { showDatePicker = false }) { Text("Batal") } },
                    ) { DatePicker(state = dpState) }
                }

                Box(Modifier.fillMaxWidth().clickable { showDatePicker = true }) {
                    OutlinedTextField(
                        value = formatDateDisplay(tanggal),
                        onValueChange = {},
                        enabled = false,
                        label = { Text("Tanggal") },
                        trailingIcon = { Icon(Icons.Default.CalendarToday, contentDescription = null) },
                        modifier = Modifier.fillMaxWidth(),
                        colors = OutlinedTextFieldDefaults.colors(
                            disabledTextColor = MaterialTheme.colorScheme.onSurface,
                            disabledBorderColor = MaterialTheme.colorScheme.outline,
                            disabledLabelColor = MaterialTheme.colorScheme.onSurfaceVariant,
                            disabledTrailingIconColor = MaterialTheme.colorScheme.onSurfaceVariant,
                        ),
                    )
                }
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
