package id.scnias.app.data.repo

import id.scnias.app.data.TokenStore
import id.scnias.app.data.dto.*
import id.scnias.app.network.ApiService
import retrofit2.HttpException

sealed class ApiResult<out T> {
    data class Success<T>(val value: T) : ApiResult<T>()
    data class Error(val message: String, val code: Int? = null) : ApiResult<Nothing>()
}

internal suspend fun <T> apiCall(block: suspend () -> T): ApiResult<T> = try {
    ApiResult.Success(block())
} catch (e: HttpException) {
    val msg = try { e.response()?.errorBody()?.string()?.take(180) ?: e.message() } catch (_: Throwable) { e.message() }
    ApiResult.Error(msg ?: "HTTP ${e.code()}", e.code())
} catch (e: Throwable) {
    ApiResult.Error(e.message ?: "Network error")
}

class AuthRepository(
    private val api: ApiService,
    private val tokenStore: TokenStore,
) {
    suspend fun login(email: String, password: String): ApiResult<UserDto> = apiCall {
        val r = api.login(LoginRequest(email, password))
        tokenStore.token = r.token
        tokenStore.userName = r.user.name
        tokenStore.primaryRole = r.user.roleNames?.firstOrNull() ?: r.user.roles?.firstOrNull()?.name
        r.user
    }

    suspend fun register(name: String, email: String, password: String): ApiResult<UserDto> = apiCall {
        val r = api.register(RegisterRequest(name, email, password))
        tokenStore.token = r.token
        tokenStore.userName = r.user.name
        tokenStore.primaryRole = r.user.roleNames?.firstOrNull() ?: r.user.roles?.firstOrNull()?.name ?: "guest"
        r.user
    }

    suspend fun me(): ApiResult<UserDto> = apiCall {
        val u = api.me()
        tokenStore.userName = u.name
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
}

class JurnalRepository(private val api: ApiService) {
    suspend fun today(date: String? = null): ApiResult<JurnalSnapshotDto> = apiCall { api.jurnalToday(date) }
    suspend fun check(req: JurnalCheckRequest): ApiResult<Unit> = apiCall { api.jurnalCheck(req); Unit }
}

class BlogRepository(private val api: ApiService) {
    suspend fun list(): ApiResult<PaginatedDto<BlogDto>> = apiCall { api.blogs() }
}

class MentorPresensiRepository(private val api: ApiService) {
    suspend fun list(): ApiResult<PaginatedDto<MentorPresensiDto>> = apiCall { api.mentorPresensi() }
    suspend fun kelas(cabangId: Long?): ApiResult<List<KelasMasterDto>> = apiCall {
        val params = buildMap {
            if (cabangId != null) put("cabang_id", cabangId.toString())
            put("active", "1")
        }
        api.kelasMaster(params).data
    }
    suspend fun create(req: MentorPresensiRequest): ApiResult<MentorPresensiDto> = apiCall { api.createMentorPresensi(req).data }
    suspend fun delete(id: Long): ApiResult<Unit> = apiCall { api.deleteMentorPresensi(id); Unit }
}
