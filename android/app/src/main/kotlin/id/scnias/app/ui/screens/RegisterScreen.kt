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
import androidx.compose.material.icons.filled.Person
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
import id.scnias.app.ui.theme.ScYellow300
import kotlinx.coroutines.launch

@Composable
fun RegisterScreen(nav: NavHostController) {
    var name by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    var showPass by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    val canSubmit = !loading && name.isNotBlank() && email.isNotBlank() && password.length >= 8

    val textFieldColors = OutlinedTextFieldDefaults.colors(
        focusedBorderColor = ScTeal600,
        unfocusedBorderColor = ScLine,
        focusedLabelColor = ScTeal700,
        cursorColor = ScTeal600,
        focusedTextColor = ScInk900,
        unfocusedTextColor = ScInk900,
    )

    Box(Modifier.fillMaxSize().background(ScBg)) {
        Column(
            Modifier.fillMaxSize().padding(24.dp).verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Surface(
                color = ScTeal700,
                shape = RoundedCornerShape(20.dp),
                modifier = Modifier.fillMaxWidth(),
            ) {
                Column(
                    Modifier.fillMaxWidth().padding(vertical = 20.dp),
                    horizontalAlignment = Alignment.CenterHorizontally,
                ) {
                    Image(
                        painter = painterResource(id = R.drawable.logo),
                        contentDescription = "Logo",
                        modifier = Modifier.size(72.dp),
                    )
                    Spacer(Modifier.height(6.dp))
                    Text("Study Center Nias", color = Color.White, fontSize = 16.sp, fontWeight = FontWeight.Bold)
                    Text(
                        "DAFTAR AKUN TAMU",
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
                    Text("Daftar", fontSize = 22.sp, fontWeight = FontWeight.ExtraBold, color = ScInk900)
                    Text(
                        "Bergabung untuk membaca dan berkomentar di blog.",
                        fontSize = 13.sp, color = ScInk500,
                    )

                    OutlinedTextField(
                        value = name, onValueChange = { name = it },
                        label = { Text("Nama lengkap") }, singleLine = true,
                        leadingIcon = { Icon(Icons.Default.Person, contentDescription = null, tint = ScTeal700) },
                        modifier = Modifier.fillMaxWidth(),
                        isError = error != null,
                        colors = textFieldColors,
                    )
                    OutlinedTextField(
                        value = email, onValueChange = { email = it.trim() },
                        label = { Text("Email") },
                        placeholder = { Text("email@contoh.com") },
                        singleLine = true,
                        leadingIcon = { Icon(Icons.Default.Email, contentDescription = null, tint = ScTeal700) },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                        modifier = Modifier.fillMaxWidth(),
                        isError = error != null,
                        colors = textFieldColors,
                    )
                    OutlinedTextField(
                        value = password, onValueChange = { password = it },
                        label = { Text("Kata sandi (min 8 karakter)") },
                        singleLine = true,
                        leadingIcon = { Icon(Icons.Default.Lock, contentDescription = null, tint = ScTeal700) },
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
                        modifier = Modifier.fillMaxWidth(),
                        isError = error != null,
                        colors = textFieldColors,
                    )

                    error?.let {
                        Surface(color = Color(0xFFFBE5E2), shape = RoundedCornerShape(10.dp), modifier = Modifier.fillMaxWidth()) {
                            Row(
                                Modifier.padding(horizontal = 12.dp, vertical = 10.dp),
                                verticalAlignment = Alignment.CenterVertically,
                                horizontalArrangement = Arrangement.spacedBy(8.dp),
                            ) {
                                Icon(Icons.Default.Error, contentDescription = null, tint = Color(0xFFC1352B), modifier = Modifier.size(20.dp))
                                Text(it, color = Color(0xFFC1352B), fontSize = 13.sp, fontWeight = FontWeight.Medium, lineHeight = 18.sp)
                            }
                        }
                    }

                    Button(
                        onClick = {
                            error = null; loading = true
                            scope.launch {
                                when (val r = AppGraph.auth.register(name.trim(), email.trim(), password)) {
                                    is ApiResult.Success -> { loading = false; nav.navTopLevel(Route.Home) }
                                    is ApiResult.Error -> { error = r.message; loading = false }
                                }
                            }
                        },
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
                            Text("Daftar Sekarang", fontWeight = FontWeight.Bold, color = Color.White, fontSize = 15.sp)
                        }
                    }

                    TextButton(onClick = { nav.popBackStack() }, modifier = Modifier.fillMaxWidth()) {
                        Text("Sudah punya akun? ", color = ScInk500, fontSize = 13.sp)
                        Text("Masuk", color = ScTeal700, fontSize = 13.sp, fontWeight = FontWeight.Bold)
                    }
                }
            }
        }
    }
}
