package id.scnias.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import id.scnias.app.ui.NavGraph
import id.scnias.app.ui.theme.ScNiasTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            ScNiasTheme {
                NavGraph()
            }
        }
    }
}
