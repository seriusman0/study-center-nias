package id.scnias.app.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.navTopLevel
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun RegisterScreen(nav: NavHostController) {
    var name by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    Scaffold(topBar = { TopAppBar(title = { Text("Daftar") }) }) { pad ->
        Column(
            Modifier.padding(pad).padding(24.dp).fillMaxSize(),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            OutlinedTextField(value = name, onValueChange = { name = it }, label = { Text("Nama lengkap") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = email, onValueChange = { email = it }, label = { Text("Email") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = password, onValueChange = { password = it }, label = { Text("Kata sandi (min 8, huruf+angka)") },
                visualTransformation = PasswordVisualTransformation(), modifier = Modifier.fillMaxWidth())
            error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
            Button(
                onClick = {
                    error = null; loading = true
                    scope.launch {
                        when (val r = AppGraph.auth.register(name.trim(), email.trim(), password)) {
                            is ApiResult.Success -> nav.navTopLevel(Route.Home)
                            is ApiResult.Error -> { error = r.message; loading = false }
                        }
                    }
                },
                enabled = !loading && name.isNotBlank() && email.isNotBlank() && password.length >= 8,
                modifier = Modifier.fillMaxWidth().height(48.dp),
            ) { Text(if (loading) "Memproses…" else "Daftar", fontWeight = FontWeight.SemiBold) }
            TextButton(onClick = { nav.popBackStack() }) { Text("Sudah punya akun? Masuk") }
        }
    }
}
