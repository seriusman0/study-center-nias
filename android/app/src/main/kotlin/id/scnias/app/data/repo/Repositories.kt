package id.scnias.app.data.repo

import id.scnias.app.data.TokenStore
import id.scnias.app.data.dto.*
import id.scnias.app.network.ApiService
import okhttp3.MediaType.Companion.toMediaType
import retrofit2.HttpException

private fun String.toMediaTypeCompat() = this.toMediaType()

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

    suspend fun createMultipart(
        title: String, content: String, cabangId: Long, tags: List<String>,
        imageBytes: ByteArray?, imageMime: String?, imageName: String?,
    ): ApiResult<BlogDto> = apiCall {
        val textType = "text/plain".toMediaTypeCompat()
        val fields = mutableMapOf<String, okhttp3.RequestBody>()
        fields["title"] = okhttp3.RequestBody.create(textType, title)
        fields["content"] = okhttp3.RequestBody.create(textType, content)
        fields["cabang_id"] = okhttp3.RequestBody.create(textType, cabangId.toString())
        tags.forEachIndexed { idx, t -> fields["tags[$idx]"] = okhttp3.RequestBody.create(textType, t) }
        val part = if (imageBytes != null) {
            val mt = (imageMime ?: "image/jpeg").toMediaTypeCompat()
            okhttp3.MultipartBody.Part.createFormData("image", imageName ?: "image.jpg", okhttp3.RequestBody.create(mt, imageBytes))
        } else null
        api.createBlogMultipart(fields, part)
    }

    suspend fun updateMultipart(
        id: Long, title: String, content: String, cabangId: Long?, tags: List<String>,
        imageBytes: ByteArray?, imageMime: String?, imageName: String?,
    ): ApiResult<BlogDto> = apiCall {
        val textType = "text/plain".toMediaTypeCompat()
        val fields = mutableMapOf<String, okhttp3.RequestBody>()
        fields["_method"] = okhttp3.RequestBody.create(textType, "PUT")
        fields["title"] = okhttp3.RequestBody.create(textType, title)
        fields["content"] = okhttp3.RequestBody.create(textType, content)
        cabangId?.let { fields["cabang_id"] = okhttp3.RequestBody.create(textType, it.toString()) }
        tags.forEachIndexed { idx, t -> fields["tags[$idx]"] = okhttp3.RequestBody.create(textType, t) }
        val part = if (imageBytes != null) {
            val mt = (imageMime ?: "image/jpeg").toMediaTypeCompat()
            okhttp3.MultipartBody.Part.createFormData("image", imageName ?: "image.jpg", okhttp3.RequestBody.create(mt, imageBytes))
        } else null
        api.updateBlogMultipart(id, fields, part)
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

    suspend fun show(id: Long): ApiResult<MentorPresensiDto> =
        apiCall { api.mentorPresensiShow(id).data }

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

    suspend fun createWithFoto(
        req: PresensiRequest,
        fotoBytes: ByteArray?,
        fotoMime: String?,
        fotoName: String?,
    ): ApiResult<PresensiDto> = apiCall {
        val fields = mutableMapOf<String, okhttp3.RequestBody>()
        val textType = "text/plain".toMediaTypeCompat()
        fun put(k: String, v: String) { fields[k] = okhttp3.RequestBody.create(textType, v) }
        put("mentor_id", req.mentorId.toString())
        req.cabangId?.let { put("cabang_id", it.toString()) }
        put("kelas_id", req.kelasId.toString())
        put("tanggal", req.tanggal)
        put("jam_mulai", req.jamMulai)
        put("jam_selesai", req.jamSelesai)
        put("materi", req.materi)
        req.studentIds.forEachIndexed { idx, sid -> fields["student_ids[$idx]"] = okhttp3.RequestBody.create(textType, sid.toString()) }
        req.studentStatus?.forEach { (sid, status) -> fields["student_status[$sid]"] = okhttp3.RequestBody.create(textType, status) }
        val fotoPart = if (fotoBytes != null) {
            val mt = (fotoMime ?: "image/jpeg")
            val body = okhttp3.RequestBody.create(mt.toMediaTypeCompat(), fotoBytes)
            okhttp3.MultipartBody.Part.createFormData("foto", fotoName ?: "foto.jpg", body)
        } else null
        api.createPresensiMultipart(fields, fotoPart).data
    }

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

    // ── Kelas master CRUD ──
    suspend fun kelasMaster(): ApiResult<List<KelasMasterDto>> = apiCall {
        api.kelasMaster(emptyMap()).data
    }

    suspend fun createKelasMaster(nama: String, cabangId: Long, keterangan: String?, isActive: Boolean): ApiResult<KelasMasterDto> =
        apiCall {
            api.createKelasMaster(
                mapOf("nama" to nama, "cabang_id" to cabangId, "keterangan" to keterangan, "is_active" to isActive)
            ).data
        }

    suspend fun updateKelasMaster(id: Long, nama: String, cabangId: Long, keterangan: String?, isActive: Boolean): ApiResult<KelasMasterDto> =
        apiCall {
            api.updateKelasMaster(id,
                mapOf("nama" to nama, "cabang_id" to cabangId, "keterangan" to keterangan, "is_active" to isActive)
            ).data
        }

    suspend fun deleteKelasMaster(id: Long): ApiResult<MessageResponse> =
        apiCall { api.deleteKelasMaster(id) }

    // ── Admin Jurnal Life Items ──
    suspend fun lifeItems(): ApiResult<List<LifeItemDto>> =
        apiCall { api.adminLifeItems().data }

    suspend fun createLifeItem(kategori: String, label: String, isActive: Boolean): ApiResult<LifeItemDto> =
        apiCall { api.createLifeItem(mapOf("kategori" to kategori, "label" to label, "is_active" to isActive)).data }

    suspend fun updateLifeItem(id: Long, kategori: String, label: String, isActive: Boolean): ApiResult<LifeItemDto> =
        apiCall { api.updateLifeItem(id, mapOf("kategori" to kategori, "label" to label, "is_active" to isActive)).data }

    suspend fun deleteLifeItem(id: Long): ApiResult<MessageResponse> =
        apiCall { api.deleteLifeItem(id) }

    // ── Admin Roles (read) ──
    suspend fun roles(): ApiResult<List<AdminRoleDto>> =
        apiCall { api.adminRoles().data }

    // ── Admin User CRUD ──
    suspend fun userShow(id: Long): ApiResult<UserDto> = apiCall { api.adminUserShow(id) }

    suspend fun createUser(
        name: String, email: String?, password: String?, cabangId: Long?, roleNames: List<String>,
    ): ApiResult<UserDto> = apiCall {
        api.adminCreateUser(buildMap {
            put("name", name)
            email?.takeIf { it.isNotBlank() }?.let { put("email", it) }
            password?.takeIf { it.isNotBlank() }?.let { put("password", it) }
            cabangId?.let { put("cabang_id", it) }
            put("role_names", roleNames)
        })
    }

    suspend fun updateUser(
        id: Long, name: String?, username: String?, email: String?, password: String?,
        cabangId: Long?, isActive: Boolean?, roleNames: List<String>?,
    ): ApiResult<UserDto> = apiCall {
        api.adminUpdateUser(id, buildMap {
            name?.let { put("name", it) }
            username?.let { put("username", it) }
            email?.let { put("email", it) }
            password?.takeIf { it.isNotBlank() }?.let { put("password", it) }
            cabangId?.let { put("cabang_id", it) }
            isActive?.let { put("is_active", it) }
            roleNames?.let { put("role_names", it) }
        })
    }

    suspend fun updateUserRole(id: Long, roleNames: List<String>): ApiResult<UserDto> =
        apiCall { api.adminUpdateUserRole(id, mapOf("roles" to roleNames)) }

    // ── Admin Roles CRUD + permission sync ──
    suspend fun createRole(name: String, description: String?): ApiResult<AdminRoleDto> =
        apiCall { api.adminCreateRole(mapOf("name" to name, "description" to description)).data }

    suspend fun updateRole(id: Long, name: String, description: String?): ApiResult<AdminRoleDto> =
        apiCall { api.adminUpdateRole(id, mapOf("name" to name, "description" to description)).data }

    suspend fun syncRolePermissions(id: Long, permissionIds: List<Long>): ApiResult<AdminRoleDto> =
        apiCall { api.adminSyncRolePermissions(id, mapOf("permissions" to permissionIds)).data }

    suspend fun deleteRole(id: Long): ApiResult<MessageResponse> =
        apiCall { api.adminDeleteRole(id) }

    // ── Admin Permissions CRUD ──
    suspend fun permissions(): ApiResult<List<PermissionDto>> =
        apiCall { api.adminPermissions().data }

    suspend fun createPermission(name: String, description: String?): ApiResult<PermissionDto> =
        apiCall { api.adminCreatePermission(mapOf("name" to name, "description" to description)).data }

    suspend fun updatePermission(id: Long, name: String, description: String?): ApiResult<PermissionDto> =
        apiCall { api.adminUpdatePermission(id, mapOf("name" to name, "description" to description)).data }

    suspend fun deletePermission(id: Long): ApiResult<MessageResponse> =
        apiCall { api.adminDeletePermission(id) }

    // ── Bible schedules ──
    suspend fun bibleSchedules(bulan: Int?, tahun: Int?): ApiResult<List<BibleScheduleDto>> =
        apiCall {
            val params = buildMap<String, String> {
                bulan?.let { put("bulan", it.toString()) }
                tahun?.let { put("tahun", it.toString()) }
            }
            api.adminBibleSchedules(params).data
        }

    suspend fun createBibleSchedule(tanggal: String, plPorsi: String?, pbPorsi: String?): ApiResult<BibleScheduleDto> =
        apiCall { api.adminCreateBibleSchedule(mapOf("tanggal" to tanggal, "pl_porsi" to plPorsi, "pb_porsi" to pbPorsi)).data }

    suspend fun updateBibleSchedule(id: Long, tanggal: String, plPorsi: String?, pbPorsi: String?): ApiResult<BibleScheduleDto> =
        apiCall { api.adminUpdateBibleSchedule(id, mapOf("tanggal" to tanggal, "pl_porsi" to plPorsi, "pb_porsi" to pbPorsi)).data }

    suspend fun deleteBibleSchedule(id: Long): ApiResult<MessageResponse> =
        apiCall { api.adminDeleteBibleSchedule(id) }

    suspend fun bulkBibleSchedule(from: String, to: String, plPorsi: String?, pbPorsi: String?, overwrite: Boolean): ApiResult<MessageResponse> =
        apiCall { api.adminBulkBibleSchedule(mapOf("from" to from, "to" to to, "pl_porsi" to plPorsi, "pb_porsi" to pbPorsi, "overwrite" to overwrite)) }

    // ── Weekly verses ──
    suspend fun weeklyVerses(tahun: Int?): ApiResult<List<WeeklyVerseDto>> =
        apiCall {
            val params = buildMap<String, String> { tahun?.let { put("tahun", it.toString()) } }
            api.adminWeeklyVerses(params).data
        }

    suspend fun createWeeklyVerse(tahun: Int, bulan: Int, minggu: Int, referensi: String, isi: String): ApiResult<WeeklyVerseDto> =
        apiCall { api.adminCreateWeeklyVerse(mapOf("tahun" to tahun, "bulan" to bulan, "minggu" to minggu, "referensi" to referensi, "isi" to isi)).data }

    suspend fun updateWeeklyVerse(id: Long, tahun: Int, bulan: Int, minggu: Int, referensi: String, isi: String): ApiResult<WeeklyVerseDto> =
        apiCall { api.adminUpdateWeeklyVerse(id, mapOf("tahun" to tahun, "bulan" to bulan, "minggu" to minggu, "referensi" to referensi, "isi" to isi)).data }

    suspend fun deleteWeeklyVerse(id: Long): ApiResult<MessageResponse> =
        apiCall { api.adminDeleteWeeklyVerse(id) }

    // ── Student life items sync ──
    suspend fun studentLifeItems(studentId: Long): ApiResult<StudentLifeItemsResponse> =
        apiCall { api.adminStudentLifeItems(studentId) }

    suspend fun syncStudentLifeItems(studentId: Long, templateIds: List<Long>): ApiResult<MessageResponse> =
        apiCall { api.adminSyncStudentLifeItems(studentId, mapOf("template_ids" to templateIds)) }

    // ── Jurnal reports ──
    suspend fun jurnalReports(params: Map<String, String> = emptyMap()): ApiResult<JurnalReportsIndexResponse> =
        apiCall { api.adminJurnalReports(params) }

    suspend fun jurnalReportShow(studentId: Long, from: String?, to: String?): ApiResult<JurnalReportShowResponse> =
        apiCall {
            val p = buildMap<String, String> {
                from?.let { put("from", it) }
                to?.let { put("to", it) }
            }
            api.adminJurnalReport(studentId, p)
        }

    // ── Name tags ──
    suspend fun nametags(params: Map<String, String> = emptyMap()): ApiResult<PaginatedDto<UserDto>> =
        apiCall { api.adminNameTags(params) }

    suspend fun generateNametags(userIds: List<Long>, widthCm: Double?, heightCm: Double?): ApiResult<NameTagGenerateResponse> =
        apiCall {
            api.adminGenerateNameTags(buildMap {
                put("user_ids", userIds)
                widthCm?.let { put("width_cm", it) }
                heightCm?.let { put("height_cm", it) }
            })
        }

    // ── Mentor presensi admin ──
    suspend fun mentorPresensiList(params: Map<String, String> = emptyMap()): ApiResult<AdminMentorPresensiListResponse> =
        apiCall { api.adminMentorPresensiList(params) }

    suspend fun mentorPresensiReports(params: Map<String, String> = emptyMap()): ApiResult<MentorPresensiReportsResponse> =
        apiCall { api.adminMentorPresensiReports(params) }

    // ── Blog moderation ──
    suspend fun adminBlogs(params: Map<String, String> = emptyMap()): ApiResult<PaginatedDto<BlogDto>> =
        apiCall { api.adminBlogs(params) }

    suspend fun adminDeleteBlog(id: Long): ApiResult<MessageResponse> =
        apiCall { api.adminDeleteBlog(id) }

    // ── Comment moderation ──
    suspend fun adminComments(params: Map<String, String> = emptyMap()): ApiResult<PaginatedDto<CommentDto>> =
        apiCall { api.adminComments(params) }

    suspend fun adminDeleteComment(id: Long): ApiResult<MessageResponse> =
        apiCall { api.adminDeleteComment(id) }
}

// ──────────────────────────────────────────────
// CV
// ──────────────────────────────────────────────
class CvRepository(private val api: ApiService) {
    suspend fun show(): ApiResult<CvDto> = apiCall { api.cv() }

    suspend fun update(fields: Map<String, Any?>): ApiResult<CvDto> =
        apiCall { api.updateCv(fields) }

    suspend fun publicCv(username: String): ApiResult<PublicCvResponse> =
        apiCall { api.publicCv(username) }

    suspend fun kartuNama(username: String): ApiResult<KartuNamaResponse> =
        apiCall { api.kartuNama(username) }
}

// ──────────────────────────────────────────────
// Cabang detail
// ──────────────────────────────────────────────
class CabangRepository(private val api: ApiService) {
    suspend fun list(): ApiResult<List<CabangDto>> = apiCall { api.cabangs() }
    suspend fun detail(slug: String): ApiResult<CabangDetailResponse> = apiCall { api.cabangShow(slug) }
}

// ──────────────────────────────────────────────
// Google mobile login extension to AuthRepository
// ──────────────────────────────────────────────
suspend fun AuthRepository.googleLogin(idToken: String, tokenStore: TokenStore, api: ApiService): ApiResult<UserDto> = apiCall {
    val r = api.googleMobileLogin(mapOf("id_token" to idToken))
    tokenStore.token = r.token
    tokenStore.userName = r.user.name
    tokenStore.userUsername = r.user.username
    tokenStore.userId = r.user.id
    tokenStore.primaryRole = r.user.roleNames?.firstOrNull() ?: r.user.roles?.firstOrNull()?.name
    r.user
}
