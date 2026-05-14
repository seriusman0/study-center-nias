package id.scnias.app.ui

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import id.scnias.app.ui.screens.*

object Route {
    const val Splash = "splash"
    const val Login = "login"
    const val Register = "register"
    const val Home = "home"
    const val Jurnal = "jurnal"
    const val BlogList = "blog"
    const val MentorPresensi = "mentor-presensi"
    const val MentorPresensiForm = "mentor-presensi/new"
}

@Composable
fun NavGraph() {
    val nav = rememberNavController()
    NavHost(navController = nav, startDestination = Route.Splash) {
        composable(Route.Splash)              { SplashScreen(nav) }
        composable(Route.Login)               { LoginScreen(nav) }
        composable(Route.Register)            { RegisterScreen(nav) }
        composable(Route.Home)                { HomeScreen(nav) }
        composable(Route.Jurnal)              { JurnalScreen(nav) }
        composable(Route.BlogList)            { BlogListScreen(nav) }
        composable(Route.MentorPresensi)      { MentorPresensiScreen(nav) }
        composable(Route.MentorPresensiForm)  { MentorPresensiFormScreen(nav) }
    }
}

fun NavHostController.navTopLevel(route: String) {
    this.navigate(route) {
        popUpTo(graph.startDestinationId) { inclusive = true }
        launchSingleTop = true
    }
}
