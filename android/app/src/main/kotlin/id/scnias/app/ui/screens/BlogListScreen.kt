package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.navigation.NavHostController
import coil.compose.AsyncImage
import id.scnias.app.core.AppGraph
import id.scnias.app.data.dto.BlogDto
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.components.*
import id.scnias.app.ui.theme.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun BlogListScreen(nav: NavHostController) {
    var items by remember { mutableStateOf<List<BlogDto>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val canWrite = remember {
        val r = AppGraph.auth.cachedRole()?.lowercase()
        r in listOf("admin", "fulltimer", "mentor", "student")
    }

    LaunchedEffect(Unit) {
        loading = true
        when (val r = AppGraph.blog.list()) {
            is ApiResult.Success -> items = r.value.data
            is ApiResult.Error   -> error = r.message
        }
        loading = false
    }

    Scaffold(
        topBar = { ScTopBar("Blog", onBack = { nav.popBackStack() }) },
        floatingActionButton = {
            if (canWrite) {
                FloatingActionButton(onClick = { nav.navigate(Route.BlogNew) }) {
                    Icon(Icons.Default.Add, contentDescription = "Tulis Blog")
                }
            }
        }
    ) { pad ->
        when {
            loading -> LoadingBox(Modifier.padding(pad))
            error != null -> ErrorBox(error!!, Modifier.padding(pad))
            items.isEmpty() -> EmptyBox("Belum ada blog", Modifier.padding(pad))
            else -> LazyColumn(
                Modifier.padding(pad).fillMaxSize().padding(12.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp),
            ) {
                items(items) { b ->
                    ElevatedCard(
                        onClick = { nav.navigate(Route.blogDetailPath(b.slug)) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                    ) {
                        Column {
                            b.image?.let { img ->
                                val url = if (img.startsWith("http")) img
                                          else "https://studycenter.overcomer.my.id/storage/$img"
                                AsyncImage(
                                    model = url,
                                    contentDescription = b.title,
                                    modifier = Modifier.fillMaxWidth().height(160.dp)
                                        .clip(RoundedCornerShape(topStart = 12.dp, topEnd = 12.dp)),
                                    contentScale = ContentScale.Crop,
                                )
                            }
                            Column(Modifier.padding(14.dp)) {
                                Text(b.title, fontWeight = FontWeight.Bold, color = ScNavy, maxLines = 2, overflow = TextOverflow.Ellipsis)
                                Spacer(Modifier.height(4.dp))
                                b.user?.let { Text("oleh ${it.name}", style = MaterialTheme.typography.bodySmall, color = Color(0xFF4B5563)) }
                                b.cabang?.let { Text(it.nama, style = MaterialTheme.typography.labelSmall, color = ScInk500) }
                                Spacer(Modifier.height(6.dp))
                                Text(
                                    stripHtmlPreview(b.content ?: ""),
                                    style = MaterialTheme.typography.bodySmall,
                                    color = Color(0xFF374151),
                                    maxLines = 3,
                                    overflow = TextOverflow.Ellipsis,
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}

private fun stripHtmlPreview(html: String): String {
    return html
        .replace(Regex("<[^>]*>"), "")
        .replace(Regex("&nbsp;"), " ")
        .replace(Regex("&amp;"), "&")
        .replace(Regex("&lt;"), "<")
        .replace(Regex("&gt;"), ">")
        .replace(Regex("&quot;"), "\"")
        .replace(Regex("\n{2,}"), " ")
        .trim()
        .take(150)
}
