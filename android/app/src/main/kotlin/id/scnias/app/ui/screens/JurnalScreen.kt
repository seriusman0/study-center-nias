package id.scnias.app.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.Today
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.JurnalCheckRequest
import id.scnias.app.data.dto.JurnalSnapshotDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.components.*
import id.scnias.app.ui.theme.ScGold
import id.scnias.app.ui.theme.ScNavy
import id.scnias.app.ui.theme.ScOrange500
import id.scnias.app.ui.theme.ScTeal600
import id.scnias.app.ui.theme.ScTeal700
import id.scnias.app.ui.theme.ScYellow100
import id.scnias.app.ui.theme.ScYellow300
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun JurnalScreen(nav: NavHostController) {
    val todayStr = remember { SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date()) }
    var currentDate by remember { mutableStateOf(todayStr) }
    var snapshot by remember { mutableStateOf<JurnalSnapshotDto?>(null) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()
    val userName = remember { AppGraph.auth.cachedName() ?: "User" }

    suspend fun load(date: String) {
        loading = true
        when (val r = AppGraph.jurnal.today(date)) {
            is ApiResult.Success -> { snapshot = r.value; error = null }
            is ApiResult.Error -> error = r.message
        }
        loading = false
    }

    LaunchedEffect(currentDate) { load(currentDate) }

    fun shiftDate(days: Int) {
        val sdf = SimpleDateFormat("yyyy-MM-dd", Locale.US)
        val cal = Calendar.getInstance()
        cal.time = sdf.parse(currentDate)!!
        cal.add(Calendar.DAY_OF_YEAR, days)
        val newDate = sdf.format(cal.time)
        if (newDate <= todayStr) currentDate = newDate
    }

    // Format display date
    val displayDate = remember(currentDate) {
        try {
            val sdfIn = SimpleDateFormat("yyyy-MM-dd", Locale.US)
            val sdfOut = SimpleDateFormat("EEEE, d MMMM yyyy", Locale("id"))
            sdfOut.format(sdfIn.parse(currentDate)!!)
        } catch (_: Exception) { currentDate }
    }
    val isToday = currentDate == todayStr

    Scaffold(
        topBar = { ScTopBar("Jurnal Harian", onBack = { nav.popBackStack() }) }
    ) { pad ->
        when {
            loading -> LoadingBox(Modifier.padding(pad))
            error != null -> ErrorBox(error!!, Modifier.padding(pad)) { scope.launch { load(currentDate) } }
            snapshot != null -> {
                val s = snapshot!!
                LazyColumn(
                    Modifier.padding(pad).fillMaxSize(),
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp),
                ) {
                    // ── Header card with date nav ──
                    item {
                        Surface(
                            modifier = Modifier.fillMaxWidth(),
                            shape = RoundedCornerShape(14.dp),
                            color = Color.Transparent,
                        ) {
                            Box(
                                Modifier
                                    .background(
                                        Brush.linearGradient(listOf(ScTeal700, ScTeal600)),
                                        shape = RoundedCornerShape(14.dp)
                                    )
                                    .padding(16.dp)
                            ) {
                                Column {
                                    Text("Halo, $userName 👋", fontWeight = FontWeight.Bold, fontSize = 18.sp, color = Color.White)
                                    Text(displayDate, fontSize = 13.sp, color = Color.White.copy(alpha = 0.8f))
                                    Spacer(Modifier.height(10.dp))
                                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                                        OutlinedButton(
                                            onClick = { shiftDate(-1) },
                                            colors = ButtonDefaults.outlinedButtonColors(contentColor = Color.White),
                                        ) {
                                            Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kemarin", modifier = Modifier.size(16.dp))
                                        }
                                        if (!isToday) {
                                            OutlinedButton(
                                                onClick = { shiftDate(1) },
                                                colors = ButtonDefaults.outlinedButtonColors(contentColor = Color.White),
                                            ) {
                                                Icon(Icons.AutoMirrored.Filled.ArrowForward, contentDescription = "Besok", modifier = Modifier.size(16.dp))
                                            }
                                        }
                                        if (!isToday) {
                                            Button(
                                                onClick = { currentDate = todayStr },
                                                colors = ButtonDefaults.buttonColors(containerColor = ScOrange500, contentColor = Color.White),
                                            ) {
                                                Icon(Icons.Default.Today, contentDescription = null, modifier = Modifier.size(16.dp))
                                                Spacer(Modifier.width(4.dp))
                                                Text("Hari ini", fontWeight = FontWeight.SemiBold, fontSize = 13.sp)
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // ── 1. Pembacaan Alkitab ──
                    item {
                        SectionCard(title = "1. Pembacaan Alkitab") {
                            if (s.bible?.plPorsi != null || s.bible?.pbPorsi != null) {
                                CheckRow(
                                    label = "Perjanjian Lama",
                                    detail = s.bible?.plPorsi,
                                    checked = s.bible?.plChecked == true,
                                ) {
                                    scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("pl", date = currentDate, checked = it)); load(currentDate) }
                                }
                                CheckRow(
                                    label = "Perjanjian Baru",
                                    detail = s.bible?.pbPorsi,
                                    checked = s.bible?.pbChecked == true,
                                ) {
                                    scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("pb", date = currentDate, checked = it)); load(currentDate) }
                                }
                            } else {
                                Text("Porsi Alkitab belum tersedia untuk tanggal ini.", color = Color(0xFF6B7280), fontSize = 13.sp)
                                Spacer(Modifier.height(8.dp))
                                CheckRow(label = "Perjanjian Lama", checked = s.bible?.plChecked == true) {
                                    scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("pl", date = currentDate, checked = it)); load(currentDate) }
                                }
                                CheckRow(label = "Perjanjian Baru", checked = s.bible?.pbChecked == true) {
                                    scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("pb", date = currentDate, checked = it)); load(currentDate) }
                                }
                            }
                        }
                    }

                    // ── 2. Hafal Ayat Mingguan ──
                    item {
                        SectionCard(title = "2. Hafal Ayat Mingguan") {
                            s.week?.let { w ->
                                Text(
                                    "Minggu ke-${w.minggu}",
                                    fontSize = 12.sp,
                                    color = Color(0xFF6B7280),
                                )
                                Spacer(Modifier.height(4.dp))
                            }
                            if (s.verse != null) {
                                Surface(
                                    color = ScYellow100,
                                    shape = RoundedCornerShape(10.dp),
                                    border = androidx.compose.foundation.BorderStroke(1.dp, ScYellow300),
                                    modifier = Modifier.fillMaxWidth(),
                                ) {
                                    Column(Modifier.padding(12.dp)) {
                                        Text(s.verse!!.referensi ?: "—", fontWeight = FontWeight.Bold, color = ScTeal700)
                                        Spacer(Modifier.height(4.dp))
                                        Text(s.verse!!.isi ?: "", fontSize = 14.sp, color = Color(0xFF15201C), lineHeight = 20.sp)
                                    }
                                }
                                Spacer(Modifier.height(8.dp))
                                CheckRow(label = "Sudah hafal ayat minggu ini", checked = s.verse!!.checked) {
                                    scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("verse", date = currentDate, checked = it)); load(currentDate) }
                                }
                            } else {
                                Text("Ayat hafalan belum ditetapkan untuk minggu ini.", color = Color(0xFF6B7280), fontSize = 13.sp)
                            }
                        }
                    }

                    // ── 3. Jadwal Kehidupan (grouped by kategori) ──
                    val kategoriList = listOf("kerohanian" to "Kerohanian", "pendidikan" to "Pendidikan", "karakter" to "Karakter")
                    val grouped = s.lifeItems?.groupBy { it.kategori.lowercase() } ?: emptyMap()

                    item {
                        SectionCard(title = "3. Jadwal Kehidupan") {
                            kategoriList.forEach { (key, label) ->
                                val items = grouped[key]
                                Text(label, fontWeight = FontWeight.Bold, fontSize = 13.sp, color = Color(0xFF6B7280))
                                Spacer(Modifier.height(4.dp))
                                if (items.isNullOrEmpty()) {
                                    Text("Belum ada item.", color = Color(0xFF9CA3AF), fontSize = 13.sp, modifier = Modifier.padding(start = 8.dp))
                                } else {
                                    items.forEach { item ->
                                        CheckRow(label = item.label, checked = item.checked) {
                                            scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("life", itemId = item.id, date = currentDate, checked = it)); load(currentDate) }
                                        }
                                    }
                                }
                                Spacer(Modifier.height(8.dp))
                            }
                        }
                    }

                    // Bottom spacer
                    item { Spacer(Modifier.height(16.dp)) }
                }
            }
        }
    }
}

@Composable
private fun CheckRow(label: String, detail: String? = null, checked: Boolean, onChange: (Boolean) -> Unit) {
    Surface(
        modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp),
        shape = RoundedCornerShape(8.dp),
        color = if (checked) Color(0xFFF0FDF4) else Color(0xFFF8FAFC),
        border = ButtonDefaults.outlinedButtonBorder,
    ) {
        Row(
            Modifier.padding(horizontal = 8.dp, vertical = 6.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Checkbox(
                checked = checked,
                onCheckedChange = onChange,
                colors = CheckboxDefaults.colors(checkedColor = ScTeal600, checkmarkColor = Color.White)
            )
            Column(Modifier.weight(1f)) {
                Text(label, fontWeight = FontWeight.SemiBold, fontSize = 14.sp, color = Color(0xFF1F2937))
                detail?.let { Text(it, fontSize = 13.sp, color = Color(0xFF6B7280)) }
            }
        }
    }
}
