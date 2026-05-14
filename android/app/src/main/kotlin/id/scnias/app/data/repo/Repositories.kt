package id.scnias.app.data.repo

import id.scnias.app.data.TokenStore
import id.scnias.app.data.dto.*
import id.scnias.app.network.ApiService
import retrofit2.HttpException

// ──────────────────────────────────────────────
// Shared result wrapper
// ──────────────────────────────────────────────

sealed class ApiResult<out T> {
    data class Success<T>(val value: T) : ApiResult<T>()
    data class Error(val message: String, val code: Int? = null) : ApiResult<Nothing>()
}

internal suspend fun <T> apiCall(block: suspend () -> T): ApiResult<T> = try {
    ApiResult.Success(block())
} catch (e: HttpException) {
    val raw = try { e.response()?.errorBody()?.string() } catch (_: Throwable) { null }
    val parsed = parseErrorMessage(raw)
    val fallback = when (e.code()) {
        401 -> "Username/email atau kata sandi salah."
        403 -> "Akses ditolak."
        404 -> "Data tidak ditemukan."
        422 -> "Data tidak valid, periksa kembali."
        429 -> "Terlalu banyak percobaan. Coba lagi nanti."
        in 500..599 -> "Server bermasalah. Coba lagi nanti."
        else -> "Terjadi kesalahan (HTTP ${e.code()})."
    }
    ApiResult.Error(parsed ?: fallback, e.code())
} catch (e: java.net.UnknownHostException) {
    ApiResult.Error("Tidak ada koneksi internet.")
} catch (e: java.net.SocketTimeoutException) {
    ApiResult.Error("Koneksi timeout. Coba lagi.")
} catch (e: Throwable) {
    ApiResult.Error(e.message?.takeIf { it.isNotBlank() } ?: "Terjadi kesalahan jaringan.")
}

private fun parseErrorMessage(body: String?): String? {
    if (body.isNullOrBlank()) return null
    // Try JSON: {"message":"..."} or {"errors":{"field":["msg",...]}}
    return runCatching {
        val json = org.json.JSONObject(body)
        val errors = json.optJSONObject("errors")
        if (errors != null) {
            val keys = errors.keys()
            if (keys.hasNext()) {
                val first = errors.optJSONArray(keys.next())
                if (first != null && first.length() > 0) return@runCatching first.optString(0)
            }
        }
        json.optString("message").takeIf { it.isNotBlank() }
    }.getOrNull()
}

// ──────────────────────────────────────────────
// Auth
// ──────────────────────────────────────────────

class AuthRepository(
    private val api: ApiService,
    private val tokenStore: TokenStore,
) {
    suspend fun login(login: String, password: String): ApiResult<UserDto> = apiCall {
        val r = api.login(LoginRequest(login, password))
        tokenStore.token = r.token
        tokenStore.userName = r.user.name
        tokenStore.userUsername = r.user.username
        tokenStore.userId = r.user.id
        tokenStore.primaryRole = r.user.roleNames?.firstOrNull() ?: r.user.roles?.firstOrNull()?.name
        r.user
    }

    suspend fun register(name: String, email: String, password: String): ApiResult<UserDto> = apiCall {
        val r = api.register(RegisterRequest(name, email, password))
        tokenStore.token = r.token
        tokenStore.userName = r.user.name
        tokenStore.userUsername = r.user.username
        tokenStore.userId = r.user.id
        tokenStore.primaryRole = r.user.roleNames?.firstOrNull() ?: r.user.roles?.firstOrNull()?.name ?: "guest"
        r.user
    }

    suspend fun me(): ApiResult<UserDto> = apiCall {
        val u = api.me()
        tokenStore.userName = u.name
        tokenStore.userUsername = u.username
        tokenStore.userId = u.id
        tokenStore.primaryRole = u.roleNames?.firstOrNull() ?: u.roles?.firstOrNull()?.name
        u
    }

    suspend fun logout(): ApiResult<Unit> = apiCall {
        runCatching { api.logout() }
        tokenStore.clear()
    }

    fun hasToken(): Boolean = tokenStore.token != null
    fun cachedRole(): String? = tokenStore.primaryRole
    fun cachedName(): String? = tokenStore.userName
    fun cachedUsername(): String? = tokenStore.userUsername
    fun cachedUserId(): Long? = tokenStore.userId
}

// ──────────────────────────────────────────────
// Blog
// ──────────────────────────────────────────────

class BlogRepository(private val api: ApiService) {
    suspend fun list(params: Map<String, String> = emptyMap()): ApiResult<PaginatedDto<BlogDto>> =
        apiCall { api.blogs(params) }

    suspend fun detail(slug: String): ApiResult<BlogDto> = apiCall { api.blog(slug) }

    suspend fun create(title: String, content: String, cabangId: Long): ApiResult<BlogDto> =
        apiCall {
            api.createBlog(mapOf("title" to title, "content" to content, "cabang_id" to cabangId))
        }

    suspend fun update(id: Long, title: String, content: String): ApiResult<BlogDto> =
        apiCall {
            api.updateBlog(id, mapOf("title" to title, "content" to content))
        }

    suspend fun delete(id: Long): ApiResult<MessageResponse> = apiCall { api.deleteBlog(id) }
}

// ──────────────────────────────────────────────
// Comment
// ──────────────────────────────────────────────

class CommentRepository(private val api: ApiService) {
    suspend fun list(blogId: Long): ApiResult<List<CommentDto>> = apiCall { api.comments(blogId) }

    suspend fun create(blogId: Long, content: String, parentId: Long? = null): ApiResult<CommentDto> =
        apiCall { api.createComment(blogId, CommentCreateRequest(content, parentId)) }

    suspend fun delete(id: Long): ApiResult<MessageResponse> = apiCall { api.deleteComment(id) }
}

// ──────────────────────────────────────────────
// Profile
// ──────────────────────────────────────────────

class ProfileRepository(private val api: ApiService) {
    suspend fun show(username: String): ApiResult<ProfileResponse> =
        apiCall { api.profile(username) }

    suspend fun update(fields: Map<String, Any?>): ApiResult<UserDto> =
        apiCall { api.updateProfile(fields) }
}

// ──────────────────────────────────────────────
// Jurnal
// ──────────────────────────────────────────────

class JurnalRepository(private val api: ApiService) {
    suspend fun today(date: String? = null): ApiResult<JurnalSnapshotDto> =
        apiCall { api.jurnalToday(date) }

    suspend fun check(req: JurnalCheckRequest): ApiResult<Unit> =
        apiCall { api.jurnalCheck(req); Unit }

    suspend fun history(from: String, to: String): ApiResult<List<JurnalHistoryDay>> =
        apiCall { api.jurnalHistory(from, to).data }
}

// ──────────────────────────────────────────────
// Mentor Presensi
// ──────────────────────────────────────────────

class MentorPresensiRepository(private val api: ApiService) {
    suspend fun list(): ApiResult<PaginatedDto<MentorPresensiDto>> =
        apiCall { api.mentorPresensi() }

    suspend fun kelas(cabangId: Long?): ApiResult<List<KelasMasterDto>> = apiCall {
        val params = buildMap {
            if (cabangId != null) put("cabang_id", cabangId.toString())
            put("active", "1")
        }
        api.kelasMaster(params).data
    }

    suspend fun create(req: MentorPresensiRequest): ApiResult<MentorPresensiDto> =
        apiCall { api.createMentorPresensi(req).data }

    suspend fun update(id: Long, req: MentorPresensiRequest): ApiResult<MentorPresensiDto> =
        apiCall { api.updateMentorPresensi(id, req).data }

    suspend fun delete(id: Long): ApiResult<Unit> =
        apiCall { api.deleteMentorPresensi(id); Unit }
}

// ──────────────────────────────────────────────
// Student Presensi
// ──────────────────────────────────────────────

class PresensiRepository(private val api: ApiService) {
    suspend fun list(params: Map<String, String> = emptyMap()): ApiResult<PaginatedDto<PresensiDto>> =
        apiCall { api.presensiList(params) }

    suspend fun show(id: Long): ApiResult<PresensiDto> =
        apiCall { api.presensiShow(id).data }

    suspend fun create(req: PresensiRequest): ApiResult<PresensiDto> =
        apiCall { api.createPresensi(req).data }

    suspend fun update(id: Long, req: PresensiRequest): ApiResult<PresensiDto> =
        apiCall { api.updatePresensi(id, req).data }

    suspend fun delete(id: Long): ApiResult<MessageResponse> =
        apiCall { api.deletePresensi(id) }

    suspend fun searchStudents(q: String): ApiResult<List<StudentSearchDto>> =
        apiCall { api.searchStudents(q).data }
}

// ──────────────────────────────────────────────
// Admin
// ──────────────────────────────────────────────

class AdminRepository(private val api: ApiService) {
    suspend fun dashboard(): ApiResult<DashboardStatsDto> =
        apiCall { api.adminDashboard() }

    suspend fun users(params: Map<String, String> = emptyMap()): ApiResult<PaginatedDto<UserDto>> =
        apiCall { api.adminUsers(params) }

    suspend fun toggleUserActive(id: Long): ApiResult<UserDto> =
        apiCall { api.toggleUserActive(id) }

    suspend fun deleteUser(id: Long): ApiResult<MessageResponse> =
        apiCall { api.deleteUser(id) }

    suspend fun cabangs(): ApiResult<List<CabangDto>> =
        apiCall { api.cabangs() }

    suspend fun createCabang(req: CabangRequest): ApiResult<CabangDto> =
        apiCall { api.createCabang(req) }

    suspend fun updateCabang(id: Long, req: CabangRequest): ApiResult<CabangDto> =
        apiCall { api.updateCabang(id, req) }

    suspend fun deleteCabang(id: Long): ApiResult<MessageResponse> =
        apiCall { api.deleteCabang(id) }
}
