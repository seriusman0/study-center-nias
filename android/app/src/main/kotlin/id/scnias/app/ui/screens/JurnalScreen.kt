package id.scnias.app.ui.screens

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
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
import id.scnias.app.data.dto.JurnalCheckRequest
import id.scnias.app.data.dto.JurnalSnapshotDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.components.*
import id.scnias.app.ui.theme.*
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
    val saving = remember { mutableStateMapOf<String, Boolean>() }
    val snackbar = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()

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
        val cal = Calendar.getInstance().apply { time = sdf.parse(currentDate)!!; add(Calendar.DAY_OF_YEAR, days) }
        val n = sdf.format(cal.time)
        if (n <= todayStr) currentDate = n
    }

    fun optimisticCheck(itemType: String, itemId: Long?, newChecked: Boolean) {
        val current = snapshot ?: return
        val key = if (itemId != null) "$itemType:$itemId" else itemType
        val prior = current
        snapshot = when (itemType) {
            "pl" -> current.copy(bible = current.bible?.copy(plChecked = newChecked))
            "pb" -> current.copy(bible = current.bible?.copy(pbChecked = newChecked))
            "verse" -> current.copy(verse = current.verse?.copy(checked = newChecked))
            "life" -> current.copy(lifeItems = current.lifeItems?.map { if (it.id == itemId) it.copy(checked = newChecked) else it })
            else -> current
        }
        saving[key] = true
        scope.launch {
            val r = AppGraph.jurnal.check(JurnalCheckRequest(itemType, itemId, currentDate, newChecked))
            saving[key] = false
            if (r is ApiResult.Error) {
                snapshot = prior
                snackbar.showSnackbar("Gagal menyimpan: ${r.message}")
            }
        }
    }

    val displayDate = remember(currentDate) {
        try {
            val sdfIn = SimpleDateFormat("yyyy-MM-dd", Locale.US)
            val sdfOut = SimpleDateFormat("EEEE · d MMMM yyyy", Locale("id"))
            sdfOut.format(sdfIn.parse(currentDate)!!).uppercase()
        } catch (_: Exception) { currentDate }
    }
    val isToday = currentDate == todayStr

    Scaffold(
        containerColor = ScBg,
        topBar = {
            ScBrandTopBar(
                title = "Jurnal Harian",
                subtitle = displayDate,
                onBack = { nav.popBackStack() },
                actions = {
                    SmallIconBtn(Icons.AutoMirrored.Filled.ArrowBack) { shiftDate(-1) }
                    Spacer(Modifier.width(4.dp))
                    SmallIconBtn(Icons.AutoMirrored.Filled.ArrowForward, enabled = !isToday) { shiftDate(1) }
                },
            )
        },
        snackbarHost = { SnackbarHost(snackbar) },
    ) { pad ->
        when {
            loading && snapshot == null -> LoadingBox(Modifier.padding(pad))
            error != null && snapshot == null -> ErrorBox(error!!, Modifier.padding(pad)) { scope.launch { load(currentDate) } }
            snapshot != null -> {
                val s = snapshot!!
                LazyColumn(
                    Modifier.padding(pad).fillMaxSize(),
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    item { StreakChip("Streak hari ini berlanjut") }

                    item {
                        PhoneSection(number = "1", title = "Pembacaan Alkitab") {
                            PhoneCheckRow(
                                title = "Perjanjian Lama",
                                sub = s.bible?.plPorsi,
                                checked = s.bible?.plChecked == true,
                                saving = saving["pl"] == true,
                            ) { optimisticCheck("pl", null, it) }
                            PhoneCheckRow(
                                title = "Perjanjian Baru",
                                sub = s.bible?.pbPorsi,
                                checked = s.bible?.pbChecked == true,
                                saving = saving["pb"] == true,
                            ) { optimisticCheck("pb", null, it) }
                        }
                    }

                    item {
                        PhoneSection(
                            number = "2",
                            title = "Hafal Ayat Mingguan",
                            eyebrow = s.week?.let { "Minggu ke-${it.minggu} · Bulan ${it.bulan}" },
                        ) {
                            if (s.verse != null) {
                                Surface(
                                    color = ScYellow100,
                                    shape = RoundedCornerShape(10.dp),
                                    border = BorderStroke(1.dp, ScYellow300),
                                    modifier = Modifier.fillMaxWidth(),
                                ) {
                                    Column(Modifier.padding(12.dp)) {
                                        Text(s.verse!!.referensi ?: "—", fontWeight = FontWeight.ExtraBold, color = ScTeal800, fontSize = 13.sp)
                                        Spacer(Modifier.height(4.dp))
                                        Text(
                                            "“${s.verse!!.isi ?: ""}”",
                                            style = MaterialTheme.typography.displaySmall.copy(fontSize = 15.sp, lineHeight = 21.sp),
                                            color = ScInk900,
                                        )
                                    }
                                }
                                PhoneCheckRow(
                                    title = "Sudah hafal ayat minggu ini",
                                    checked = s.verse!!.checked,
                                    saving = saving["verse"] == true,
                                ) { optimisticCheck("verse", null, it) }
                            } else {
                                Text("Ayat hafalan belum ditetapkan.", color = ScInk500, fontSize = 13.sp)
                            }
                        }
                    }

                    item {
                        val kategoriList = listOf("kerohanian" to "Kerohanian", "pendidikan" to "Pendidikan", "karakter" to "Karakter")
                        val grouped = s.lifeItems?.groupBy { it.kategori.lowercase() } ?: emptyMap()
                        PhoneSection(number = "3", title = "Jadwal Kehidupan") {
                            kategoriList.forEach { (key, label) ->
                                SubHead(label)
                                val items = grouped[key]
                                if (items.isNullOrEmpty()) {
                                    Text("Belum ada item.", color = ScInk300, fontSize = 12.sp)
                                } else {
                                    items.forEach { item ->
                                        PhoneCheckRow(
                                            title = item.label,
                                            checked = item.checked,
                                            saving = saving["life:${item.id}"] == true,
                                            compact = true,
                                        ) { optimisticCheck("life", item.id, it) }
                                    }
                                }
                            }
                        }
                    }

                    item { Spacer(Modifier.height(16.dp)) }
                }
            }
        }
    }
}

@Composable
private fun SmallIconBtn(icon: androidx.compose.ui.graphics.vector.ImageVector, enabled: Boolean = true, onClick: () -> Unit) {
    Surface(
        color = if (enabled) Color.White.copy(alpha = 0.15f) else Color.Transparent,
        shape = RoundedCornerShape(8.dp),
        modifier = Modifier.size(32.dp),
        onClick = onClick,
        enabled = enabled,
    ) {
        Box(contentAlignment = Alignment.Center) {
            Icon(icon, null, tint = Color.White.copy(alpha = if (enabled) 1f else 0.4f), modifier = Modifier.size(16.dp))
        }
    }
}
