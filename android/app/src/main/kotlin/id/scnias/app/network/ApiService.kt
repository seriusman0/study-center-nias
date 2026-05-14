package id.scnias.app.network

import id.scnias.app.data.dto.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // ── Auth ──────────────────────────────────────
    @POST("auth/login")
    suspend fun login(@Body req: LoginRequest): AuthResponse

    @POST("auth/register")
    suspend fun register(@Body req: RegisterRequest): AuthResponse

    @POST("auth/logout")
    suspend fun logout(): Response<Unit>

    @POST("auth/refresh")
    suspend fun refresh(): AuthResponse

    @GET("me")
    suspend fun me(): UserDto

    // ── Blog ─────────────────────────────────────
    @GET("blogs")
    suspend fun blogs(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<BlogDto>

    @GET("blogs/{slug}")
    suspend fun blog(@Path("slug") slug: String): BlogDto

    @POST("blogs")
    suspend fun createBlog(@Body req: Map<String, @JvmSuppressWildcards Any?>): BlogDto

    @PUT("blogs/{id}")
    suspend fun updateBlog(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): BlogDto

    @DELETE("blogs/{id}")
    suspend fun deleteBlog(@Path("id") id: Long): MessageResponse

    // ── Comments ─────────────────────────────────
    @GET("blogs/{blogId}/comments")
    suspend fun comments(@Path("blogId") blogId: Long): List<CommentDto>

    @POST("blogs/{blogId}/comments")
    suspend fun createComment(@Path("blogId") blogId: Long, @Body req: CommentCreateRequest): CommentDto

    @DELETE("comments/{id}")
    suspend fun deleteComment(@Path("id") id: Long): MessageResponse

    // ── Profile ──────────────────────────────────
    @GET("profil/{username}")
    suspend fun profile(@Path("username") username: String): ProfileResponse

    @PUT("profile")
    suspend fun updateProfile(@Body req: Map<String, @JvmSuppressWildcards Any?>): UserDto

    // ── Cabang (public) ──────────────────────────
    @GET("cabangs")
    suspend fun cabangs(): List<CabangDto>

    // ── Jurnal (student) ─────────────────────────
    @GET("jurnal/today")
    suspend fun jurnalToday(@Query("date") date: String? = null): JurnalSnapshotDto

    @POST("jurnal/check")
    suspend fun jurnalCheck(@Body req: JurnalCheckRequest): Response<Unit>

    @GET("jurnal/history")
    suspend fun jurnalHistory(@Query("from") from: String, @Query("to") to: String): JurnalHistoryEnvelope

    // ── Kelas master ─────────────────────────────
    @GET("kelas-master")
    suspend fun kelasMaster(@QueryMap params: Map<String, String> = emptyMap()): KelasMasterEnvelope

    // ── Mentor presensi ──────────────────────────
    @GET("mentor-presensi")
    suspend fun mentorPresensi(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<MentorPresensiDto>

    @POST("mentor-presensi")
    suspend fun createMentorPresensi(@Body req: MentorPresensiRequest): MentorPresensiEnvelope

    @PUT("mentor-presensi/{id}")
    suspend fun updateMentorPresensi(@Path("id") id: Long, @Body req: MentorPresensiRequest): MentorPresensiEnvelope

    @DELETE("mentor-presensi/{id}")
    suspend fun deleteMentorPresensi(@Path("id") id: Long): Response<Unit>

    // ── Student presensi ─────────────────────────
    @GET("presensi")
    suspend fun presensiList(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<PresensiDto>

    @GET("presensi/{id}")
    suspend fun presensiShow(@Path("id") id: Long): PresensiEnvelope

    @POST("presensi")
    suspend fun createPresensi(@Body req: PresensiRequest): PresensiEnvelope

    @PUT("presensi/{id}")
    suspend fun updatePresensi(@Path("id") id: Long, @Body req: PresensiRequest): PresensiEnvelope

    @DELETE("presensi/{id}")
    suspend fun deletePresensi(@Path("id") id: Long): MessageResponse

    @GET("presensi/students/search")
    suspend fun searchStudents(@Query("q") query: String): StudentSearchEnvelope

    // ── Admin ────────────────────────────────────
    @GET("admin/dashboard")
    suspend fun adminDashboard(): DashboardStatsDto

    @GET("admin/users")
    suspend fun adminUsers(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<UserDto>

    @PATCH("admin/users/{id}/toggle-active")
    suspend fun toggleUserActive(@Path("id") id: Long): UserDto

    @DELETE("admin/users/{id}")
    suspend fun deleteUser(@Path("id") id: Long): MessageResponse

    // ── Admin cabang ─────────────────────────────
    @POST("admin/cabangs")
    suspend fun createCabang(@Body req: CabangRequest): CabangDto

    @PUT("admin/cabangs/{id}")
    suspend fun updateCabang(@Path("id") id: Long, @Body req: CabangRequest): CabangDto

    @DELETE("admin/cabangs/{id}")
    suspend fun deleteCabang(@Path("id") id: Long): MessageResponse
}
