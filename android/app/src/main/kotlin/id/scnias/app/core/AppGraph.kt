package id.scnias.app.core

import android.content.Context
import id.scnias.app.BuildConfig
import id.scnias.app.data.TokenStore
import id.scnias.app.data.repo.*
import id.scnias.app.network.ApiClient
import id.scnias.app.network.ApiService

/**
 * Manual lightweight DI graph. Initialised in Application.onCreate.
 * Avoids Hilt/KSP build complexity for this slim app.
 */
object AppGraph {
    lateinit var tokenStore: TokenStore
        private set
    lateinit var api: ApiService
        private set
    lateinit var auth: AuthRepository
        private set
    lateinit var blog: BlogRepository
        private set
    lateinit var comment: CommentRepository
        private set
    lateinit var profile: ProfileRepository
        private set
    lateinit var jurnal: JurnalRepository
        private set
    lateinit var mentorPresensi: MentorPresensiRepository
        private set
    lateinit var presensi: PresensiRepository
        private set
    lateinit var admin: AdminRepository
        private set

    fun init(ctx: Context) {
        tokenStore = TokenStore(ctx)
        api = ApiClient.build(BuildConfig.API_BASE_URL, tokenStore)
        auth = AuthRepository(api, tokenStore)
        blog = BlogRepository(api)
        comment = CommentRepository(api)
        profile = ProfileRepository(api)
        jurnal = JurnalRepository(api)
        mentorPresensi = MentorPresensiRepository(api)
        presensi = PresensiRepository(api)
        admin = AdminRepository(api)
    }
}
