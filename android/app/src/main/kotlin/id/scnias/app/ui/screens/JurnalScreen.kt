package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
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
import id.scnias.app.data.dto.JurnalCheckRequest
import id.scnias.app.data.dto.JurnalSnapshotDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.theme.ScGold
import id.scnias.app.ui.theme.ScNavy
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun JurnalScreen(nav: NavHostController) {
    var snapshot by remember { mutableStateOf<JurnalSnapshotDto?>(null) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    suspend fun reload() {
        loading = true
        when (val r = AppGraph.jurnal.today()) {
            is ApiResult.Success -> { snapshot = r.value; error = null }
            is ApiResult.Error -> error = r.message
        }
        loading = false
    }

    LaunchedEffect(Unit) { reload() }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Jurnal Harian") },
                navigationIcon = { IconButton(onClick = { nav.popBackStack() }) { Icon(Icons.Default.ArrowBack, contentDescription = null) } },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ScNavy, titleContentColor = Color.White, navigationIconContentColor = Color.White),
            )
        }
    ) { pad ->
        when {
            loading -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
            error != null -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { Text("Error: $error") }
            snapshot != null -> {
                val s = snapshot!!
                LazyColumn(
                    Modifier.padding(pad).fillMaxSize().padding(16.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp),
                ) {
                    item {
                        SectionCard(title = "Pembacaan Alkitab") {
                            val pl = s.bible?.plChecked == true
                            val pb = s.bible?.pbChecked == true
                            CheckRow("Perjanjian Lama" + (s.bible?.plPorsi?.let { " — $it" } ?: ""), pl) {
                                scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("pl", checked = it)); reload() }
                            }
                            CheckRow("Perjanjian Baru" + (s.bible?.pbPorsi?.let { " — $it" } ?: ""), pb) {
                                scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("pb", checked = it)); reload() }
                            }
                        }
                    }
                    s.verse?.let { v ->
                        item {
                            SectionCard(title = "Hafal Ayat Mingguan") {
                                Text(v.referensi ?: "—", fontWeight = FontWeight.Bold, color = ScNavy)
                                Text(v.isi ?: "", modifier = Modifier.padding(vertical = 4.dp))
                                CheckRow("Sudah hafal minggu ini", v.checked) {
                                    scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("verse", checked = it)); reload() }
                                }
                            }
                        }
                    }
                    s.lifeItems?.groupBy { it.kategori }?.forEach { (kategori, items) ->
                        item {
                            SectionCard(title = kategori.replaceFirstChar { it.uppercase() }) {
                                items.forEach { item ->
                                    CheckRow(item.label, item.checked) {
                                        scope.launch { AppGraph.jurnal.check(JurnalCheckRequest("life", itemId = item.id, checked = it)); reload() }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun SectionCard(title: String, content: @Composable ColumnScope.() -> Unit) {
    ElevatedCard(
        Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.elevatedCardColors(containerColor = Color.White),
    ) {
        Column(Modifier.padding(14.dp)) {
            Text(title, fontWeight = FontWeight.Bold, color = ScNavy, modifier = Modifier.padding(bottom = 8.dp))
            content()
        }
    }
}

@Composable
private fun CheckRow(label: String, checked: Boolean, onChange: (Boolean) -> Unit) {
    Row(
        Modifier.fillMaxWidth().padding(vertical = 4.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Checkbox(
            checked = checked,
            onCheckedChange = onChange,
            colors = CheckboxDefaults.colors(checkedColor = ScGold)
        )
        Text(label, modifier = Modifier.weight(1f))
    }
}
