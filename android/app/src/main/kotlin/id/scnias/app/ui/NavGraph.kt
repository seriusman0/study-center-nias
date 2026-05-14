package id.scnias.app.ui

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import id.scnias.app.ui.screens.*

object Route {
    const val Splash = "splash"
    const val Login = "login"
    const val Register = "register"
    const val Home = "home"

    // Blog
    const val BlogList = "blog"
    const val BlogNew = "blog/new"
    const val BlogDetail = "blog/{slug}"
    const val BlogEdit = "blog/{slug}/edit"
    fun blogDetailPath(slug: String) = "blog/$slug"
    fun blogEditPath(slug: String) = "blog/$slug/edit"

    // Profile
    const val Profile = "profile/{username}"
    const val ProfileEdit = "profile/edit"
    fun profilePath(username: String) = "profile/$username"

    // Jurnal
    const val Jurnal = "jurnal"

    // Mentor Presensi
    const val MentorPresensi = "mentor-presensi"
    const val MentorPresensiForm = "mentor-presensi/new"
    const val MentorPresensiEdit = "mentor-presensi/{id}/edit"
    fun mentorPresensiEditPath(id: Long) = "mentor-presensi/$id/edit"

    // Student Presensi
    const val PresensiList = "presensi"
    const val PresensiNew = "presensi/new"

    // Admin
    const val AdminDashboard = "admin/dashboard"
    const val AdminUsers = "admin/users"
    const val AdminCabang = "admin/cabang"

    // Public Cabang
    const val CabangList = "cabang"
}

@Composable
fun NavGraph() {
    val nav = rememberNavController()
    NavHost(navController = nav, startDestination = Route.Splash) {
        composable(Route.Splash)    { SplashScreen(nav) }
        composable(Route.Login)     { LoginScreen(nav) }
        composable(Route.Register)  { RegisterScreen(nav) }
        composable(Route.Home)      { HomeScreen(nav) }

        // Blog
        composable(Route.BlogList)  { BlogListScreen(nav) }
        composable(Route.BlogNew)   { BlogFormScreen(nav) }
        composable(
            Route.BlogDetail,
            arguments = listOf(navArgument("slug") { type = NavType.StringType })
        ) { entry ->
            BlogDetailScreen(nav, entry.arguments!!.getString("slug")!!)
        }
        composable(
            Route.BlogEdit,
            arguments = listOf(navArgument("slug") { type = NavType.StringType })
        ) { entry ->
            BlogFormScreen(nav, editSlug = entry.arguments!!.getString("slug")!!)
        }

        // Profile
        composable(Route.ProfileEdit) { ProfileEditScreen(nav) }
        composable(
            Route.Profile,
            arguments = listOf(navArgument("username") { type = NavType.StringType })
        ) { entry ->
            ProfileScreen(nav, entry.arguments!!.getString("username")!!)
        }

        // Jurnal
        composable(Route.Jurnal)    { JurnalScreen(nav) }

        // Mentor presensi
        composable(Route.MentorPresensi)     { MentorPresensiScreen(nav) }
        composable(Route.MentorPresensiForm) { MentorPresensiFormScreen(nav) }
        composable(
            Route.MentorPresensiEdit,
            arguments = listOf(navArgument("id") { type = NavType.LongType })
        ) { entry ->
            MentorPresensiFormScreen(nav, editId = entry.arguments!!.getLong("id"))
        }

        // Student presensi
        composable(Route.PresensiList) { PresensiScreen(nav) }
        composable(Route.PresensiNew)  { PresensiFormScreen(nav) }

        // Admin
        composable(Route.AdminDashboard) { AdminDashboardScreen(nav) }
        composable(Route.AdminUsers)     { AdminUsersScreen(nav) }
        composable(Route.AdminCabang)    { AdminCabangScreen(nav) }

        // Public Cabang
        composable(Route.CabangList)     { CabangListScreen(nav) }
    }
}

fun NavHostController.navTopLevel(route: String) {
    this.navigate(route) {
        popUpTo(graph.startDestinationId) { inclusive = true }
        launchSingleTop = true
    }
}
