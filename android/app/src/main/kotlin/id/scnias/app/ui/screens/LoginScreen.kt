package id.scnias.app.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.core.AppGraph
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.navTopLevel
import id.scnias.app.ui.theme.ScGold
import id.scnias.app.ui.theme.ScNavy
import kotlinx.coroutines.launch

@Composable
fun LoginScreen(nav: NavHostController) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    Box(Modifier.fillMaxSize().background(ScNavy)) {
        Column(
            Modifier.fillMaxSize().padding(24.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Text("SC NIAS", color = ScGold, fontSize = 38.sp, fontWeight = FontWeight.Black)
            Text("Study Center Nias", color = Color.White.copy(0.7f))
            Spacer(Modifier.height(32.dp))

            Surface(
                color = Color.White,
                shape = RoundedCornerShape(16.dp),
                modifier = Modifier.fillMaxWidth()
            ) {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Text("Masuk", fontSize = 22.sp, fontWeight = FontWeight.Bold, color = ScNavy)

                    OutlinedTextField(
                        value = email, onValueChange = { email = it },
                        label = { Text("Email") }, singleLine = true,
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                        modifier = Modifier.fillMaxWidth()
                    )
                    OutlinedTextField(
                        value = password, onValueChange = { password = it },
                        label = { Text("Kata sandi") }, singleLine = true,
                        visualTransformation = PasswordVisualTransformation(),
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                        modifier = Modifier.fillMaxWidth()
                    )

                    error?.let { Text(it, color = MaterialTheme.colorScheme.error) }

                    Button(
                        onClick = {
                            error = null
                            loading = true
                            scope.launch {
                                when (val r = AppGraph.auth.login(email.trim(), password)) {
                                    is ApiResult.Success -> nav.navTopLevel(Route.Home)
                                    is ApiResult.Error -> { error = r.message; loading = false }
                                }
                            }
                        },
                        enabled = !loading && email.isNotBlank() && password.isNotBlank(),
                        colors = ButtonDefaults.buttonColors(containerColor = ScNavy),
                        modifier = Modifier.fillMaxWidth().height(48.dp),
                    ) {
                        if (loading) CircularProgressIndicator(color = Color.White, modifier = Modifier.size(20.dp))
                        else Text("Masuk", fontWeight = FontWeight.SemiBold)
                    }

                    TextButton(
                        onClick = { nav.navigate(Route.Register) },
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Text("Belum punya akun? Daftar", color = ScNavy)
                    }
                }
            }
        }
    }
}
