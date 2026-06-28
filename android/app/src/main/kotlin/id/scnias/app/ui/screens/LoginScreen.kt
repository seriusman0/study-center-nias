package id.scnias.app.ui.screens

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Error
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import id.scnias.app.R
import id.scnias.app.core.AppGraph
import id.scnias.app.data.repo.ApiResult
import id.scnias.app.ui.Route
import id.scnias.app.ui.navTopLevel
import id.scnias.app.ui.theme.*
import kotlinx.coroutines.launch

@Composable
fun LoginScreen(nav: NavHostController) {
    var login by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    var showPass by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    val canSubmit = !loading && login.trim().isNotEmpty() && password.isNotBlank()

    fun submit() {
        val u = login.trim()
        val p = password.trim()
        if (u.isEmpty() || p.isEmpty()) {
            error = "Isi username/email dan kata sandi terlebih dahulu."
            return
        }
        error = null
        loading = true
        scope.launch {
            when (val r = AppGraph.auth.login(u, p)) {
                is ApiResult.Success -> { loading = false; nav.navTopLevel(Route.Home) }
                is ApiResult.Error   -> { error = r.message; loading = false }
            }
        }
    }

    val textColors = OutlinedTextFieldDefaults.colors(
        focusedBorderColor = ScTeal600,
        unfocusedBorderColor = ScLine,
        focusedLabelColor = ScTeal700,
        cursorColor = ScTeal600,
        focusedTextColor = ScInk900,
        unfocusedTextColor = ScInk900,
    )

    Box(Modifier.fillMaxSize().background(ScBg).statusBarsPadding()) {
        Column(
            Modifier.fillMaxSize().padding(horizontal = 24.dp).verticalScroll(rememberScrollState()),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Spacer(Modifier.height(48.dp))

            // Logo
            Image(
                painter = painterResource(R.drawable.logo),
                contentDescription = "Study Center Logo",
                modifier = Modifier.size(96.dp),
            )
            Spacer(Modifier.height(16.dp))

            // Display title (Grandeur)
            Text(
                "Selamat datang",
                style = MaterialTheme.typography.displaySmall,
                color = ScTeal800,
                fontWeight = FontWeight.SemiBold,
            )
            Spacer(Modifier.height(4.dp))
            Text("Masuk ke Study Center Nias", fontSize = 13.sp, color = ScInk500)
            Spacer(Modifier.height(22.dp))

            // Google button
            OutlinedButton(
                onClick = { /* TODO: Google Sign-In SDK integration */ },
                modifier = Modifier.fillMaxWidth().height(50.dp),
                shape = RoundedCornerShape(12.dp),
                border = BorderStroke(1.dp, ScLine),
                colors = ButtonDefaults.outlinedButtonColors(containerColor = Color.White, contentColor = ScInk900),
            ) {
                Text("G", color = Color(0xFF4285F4), fontSize = 18.sp, fontWeight = FontWeight.ExtraBold)
                Spacer(Modifier.width(10.dp))
                Text("Masuk dengan Google", fontWeight = FontWeight.SemiBold, fontSize = 14.sp)
            }

            // Divider with ATAU
            Row(Modifier.fillMaxWidth().padding(vertical = 18.dp), verticalAlignment = Alignment.CenterVertically) {
                HorizontalDivider(modifier = Modifier.weight(1f), color = ScLine)
                Text("  ATAU  ", fontSize = 11.sp, fontWeight = FontWeight.Bold, color = ScInk500, letterSpacing = 0.8.sp)
                HorizontalDivider(modifier = Modifier.weight(1f), color = ScLine)
            }

            // Email / Username
            Column(Modifier.fillMaxWidth()) {
                Text("Email atau Username", fontSize = 11.sp, fontWeight = FontWeight.SemiBold, color = ScInk900)
                Spacer(Modifier.height(6.dp))
                OutlinedTextField(
                    value = login,
                    onValueChange = { login = it.trimStart() },
                    placeholder = { Text("email@contoh.com", color = ScInk300) },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Text),
                    shape = RoundedCornerShape(12.dp),
                    modifier = Modifier.fillMaxWidth(),
                    isError = error != null,
                    colors = textColors,
                )
            }
            Spacer(Modifier.height(12.dp))

            // Password
            Column(Modifier.fillMaxWidth()) {
                Text("Kata sandi", fontSize = 11.sp, fontWeight = FontWeight.SemiBold, color = ScInk900)
                Spacer(Modifier.height(6.dp))
                OutlinedTextField(
                    value = password,
                    onValueChange = { password = it },
                    placeholder = { Text("••••••••", color = ScInk300) },
                    singleLine = true,
                    trailingIcon = {
                        IconButton(onClick = { showPass = !showPass }) {
                            Icon(
                                if (showPass) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                contentDescription = null, tint = ScInk500,
                            )
                        }
                    },
                    visualTransformation = if (showPass) VisualTransformation.None else PasswordVisualTransformation(),
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                    shape = RoundedCornerShape(12.dp),
                    modifier = Modifier.fillMaxWidth(),
                    isError = error != null,
                    colors = textColors,
                )
            }

            error?.let {
                Spacer(Modifier.height(10.dp))
                Surface(color = Color(0xFFFBE5E2), shape = RoundedCornerShape(10.dp), modifier = Modifier.fillMaxWidth()) {
                    Row(Modifier.padding(horizontal = 12.dp, vertical = 10.dp), verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Default.Error, null, tint = Color(0xFFC1352B), modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(8.dp))
                        Text(it, color = Color(0xFFC1352B), fontSize = 13.sp, fontWeight = FontWeight.Medium)
                    }
                }
            }

            Spacer(Modifier.height(18.dp))
            Button(
                onClick = { submit() },
                enabled = canSubmit,
                colors = ButtonDefaults.buttonColors(
                    containerColor = ScTeal600,
                    disabledContainerColor = ScTeal100,
                ),
                shape = RoundedCornerShape(12.dp),
                modifier = Modifier.fillMaxWidth().height(50.dp),
            ) {
                if (loading) {
                    CircularProgressIndicator(color = Color.White, strokeWidth = 2.dp, modifier = Modifier.size(20.dp))
                } else {
                    Text("Masuk", fontWeight = FontWeight.Bold, color = Color.White, fontSize = 15.sp)
                }
            }

            TextButton(onClick = { nav.navigate(Route.Register) }, modifier = Modifier.padding(top = 6.dp)) {
                Text("Belum punya akun? ", color = ScInk500, fontSize = 13.sp)
                Text("Daftar Akun Tamu", color = ScTeal700, fontSize = 13.sp, fontWeight = FontWeight.Bold)
            }

            Spacer(Modifier.height(32.dp))
        }
    }
}
