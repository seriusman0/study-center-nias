package id.scnias.app.ui.screens

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.Error
import androidx.compose.material.icons.filled.Lock
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
import id.scnias.app.ui.theme.ScBg
import id.scnias.app.ui.theme.ScInk500
import id.scnias.app.ui.theme.ScInk900
import id.scnias.app.ui.theme.ScLine
import id.scnias.app.ui.theme.ScTeal100
import id.scnias.app.ui.theme.ScTeal600
import id.scnias.app.ui.theme.ScTeal700
import id.scnias.app.ui.theme.ScTeal800
import id.scnias.app.ui.theme.ScYellow300
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

    Box(Modifier.fillMaxSize().background(ScBg)) {
        Column(
            Modifier.fillMaxSize().padding(24.dp).verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            // Hero brand block (teal panel above card)
            Surface(
                color = ScTeal700,
                shape = RoundedCornerShape(20.dp),
                modifier = Modifier.fillMaxWidth(),
            ) {
                Column(
                    Modifier.fillMaxWidth().padding(vertical = 24.dp),
                    horizontalAlignment = Alignment.CenterHorizontally,
                ) {
                    Image(
                        painter = painterResource(id = R.drawable.logo),
                        contentDescription = "Study Center Logo",
                        modifier = Modifier.size(88.dp),
                    )
                    Spacer(Modifier.height(8.dp))
                    Text("Study Center Nias", color = Color.White, fontSize = 18.sp, fontWeight = FontWeight.Bold)
                    Text(
                        "RUMAH KEDUA REMAJA",
                        color = ScYellow300,
                        fontSize = 10.sp,
                        fontWeight = FontWeight.Bold,
                        letterSpacing = 1.5.sp,
                    )
                }
            }
            Spacer(Modifier.height(20.dp))

            Surface(
                color = Color.White,
                shape = RoundedCornerShape(16.dp),
                shadowElevation = 2.dp,
                modifier = Modifier.fillMaxWidth(),
            ) {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Text("Masuk", fontSize = 22.sp, fontWeight = FontWeight.ExtraBold, color = ScInk900)
                    Text(
                        "Gunakan username atau email akun Study Center Nias.",
                        fontSize = 13.sp, color = ScInk500,
                    )

                    OutlinedTextField(
                        value = login,
                        onValueChange = { login = it.trimStart() },
                        label = { Text("Username atau Email") },
                        placeholder = { Text("email@contoh.com / username") },
                        singleLine = true,
                        leadingIcon = { Icon(Icons.Default.Email, contentDescription = null, tint = ScTeal700) },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Text),
                        modifier = Modifier.fillMaxWidth(),
                        isError = error != null,
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = ScTeal600,
                            unfocusedBorderColor = ScLine,
                            focusedLabelColor = ScTeal700,
                            cursorColor = ScTeal600,
                            focusedTextColor = ScInk900,
                            unfocusedTextColor = ScInk900,
                            errorTextColor = ScInk900,
                        ),
                    )

                    OutlinedTextField(
                        value = password,
                        onValueChange = { password = it },
                        label = { Text("Kata sandi") },
                        singleLine = true,
                        leadingIcon = { Icon(Icons.Default.Lock, contentDescription = null, tint = ScTeal700) },
                        trailingIcon = {
                            IconButton(onClick = { showPass = !showPass }) {
                                Icon(
                                    if (showPass) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                    contentDescription = if (showPass) "Sembunyikan" else "Tampilkan",
                                    tint = ScInk500,
                                )
                            }
                        },
                        visualTransformation = if (showPass) VisualTransformation.None else PasswordVisualTransformation(),
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                        modifier = Modifier.fillMaxWidth(),
                        isError = error != null,
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = ScTeal600,
                            unfocusedBorderColor = ScLine,
                            focusedLabelColor = ScTeal700,
                            cursorColor = ScTeal600,
                            focusedTextColor = ScInk900,
                            unfocusedTextColor = ScInk900,
                            errorTextColor = ScInk900,
                        ),
                    )

                    error?.let { ErrorCard(it) }

                    Button(
                        onClick = { submit() },
                        enabled = canSubmit,
                        colors = ButtonDefaults.buttonColors(
                            containerColor = ScTeal600,
                            disabledContainerColor = ScTeal100,
                        ),
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth().height(48.dp),
                    ) {
                        if (loading) {
                            CircularProgressIndicator(color = Color.White, strokeWidth = 2.dp, modifier = Modifier.size(20.dp))
                        } else {
                            Text("Masuk", fontWeight = FontWeight.Bold, color = Color.White, fontSize = 15.sp)
                        }
                    }

                    TextButton(
                        onClick = { nav.navigate(Route.Register) },
                        modifier = Modifier.fillMaxWidth(),
                    ) {
                        Text("Belum punya akun? ", color = ScInk500, fontSize = 13.sp)
                        Text("Daftar", color = ScTeal700, fontSize = 13.sp, fontWeight = FontWeight.Bold)
                    }
                }
            }
        }
    }
}

@Composable
private fun ErrorCard(message: String) {
    Surface(
        color = Color(0xFFFBE5E2),
        shape = RoundedCornerShape(10.dp),
        modifier = Modifier.fillMaxWidth(),
    ) {
        Row(
            Modifier.padding(horizontal = 12.dp, vertical = 10.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            Icon(Icons.Default.Error, contentDescription = null, tint = Color(0xFFC1352B), modifier = Modifier.size(20.dp))
            Text(
                message,
                color = Color(0xFFC1352B),
                fontSize = 13.sp,
                fontWeight = FontWeight.Medium,
                lineHeight = 18.sp,
            )
        }
    }
}
