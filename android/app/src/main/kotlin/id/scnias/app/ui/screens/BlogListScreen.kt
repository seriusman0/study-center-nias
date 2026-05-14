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
import id.scnias.app.data.dto.BlogDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.theme.ScNavy

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun BlogListScreen(nav: NavHostController) {
    var items by remember { mutableStateOf<List<BlogDto>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        loading = true
        when (val r = AppGraph.blog.list()) {
            is ApiResult.Success -> items = r.value.data
            is ApiResult.Error   -> error = r.message
        }
        loading = false
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Blog") },
                navigationIcon = { IconButton(onClick = { nav.popBackStack() }) { Icon(Icons.Default.ArrowBack, contentDescription = null) } },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ScNavy, titleContentColor = Color.White, navigationIconContentColor = Color.White),
            )
        }
    ) { pad ->
        when {
            loading -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
            error != null -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { Text("Error: $error") }
            items.isEmpty() -> Box(Modifier.padding(pad).fillMaxSize(), contentAlignment = Alignment.Center) { Text("Belum ada blog") }
            else -> LazyColumn(Modifier.padding(pad).fillMaxSize().padding(12.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(items) { b ->
                    ElevatedCard(Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)) {
                        Column(Modifier.padding(14.dp)) {
                            Text(b.title, fontWeight = FontWeight.Bold, color = ScNavy)
                            b.user?.let { Text("oleh ${it.name}", style = MaterialTheme.typography.bodySmall) }
                            Text(b.content?.take(160)?.plus("…") ?: "", modifier = Modifier.padding(top = 6.dp))
                        }
                    }
                }
            }
        }
    }
}
