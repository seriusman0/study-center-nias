package id.scnias.app.network

import id.scnias.app.data.dto.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
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

    @Multipart
    @POST("blogs")
    suspend fun createBlogMultipart(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part image: MultipartBody.Part? = null,
    ): BlogDto

    @Multipart
    @POST("blogs/{id}")
    suspend fun updateBlogMultipart(
        @Path("id") id: Long,
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part image: MultipartBody.Part? = null,
    ): BlogDto

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

    @POST("kelas-master")
    suspend fun createKelasMaster(@Body req: Map<String, @JvmSuppressWildcards Any?>): KelasMasterSingleEnvelope

    @PUT("kelas-master/{id}")
    suspend fun updateKelasMaster(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): KelasMasterSingleEnvelope

    @DELETE("kelas-master/{id}")
    suspend fun deleteKelasMaster(@Path("id") id: Long): MessageResponse

    // ── Mentor presensi ──────────────────────────
    @GET("mentor-presensi")
    suspend fun mentorPresensi(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<MentorPresensiDto>

    @GET("mentor-presensi/{id}")
    suspend fun mentorPresensiShow(@Path("id") id: Long): MentorPresensiEnvelope

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

    @Multipart
    @POST("presensi")
    suspend fun createPresensiMultipart(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part foto: MultipartBody.Part? = null,
    ): PresensiEnvelope

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

    // ── Admin Jurnal Life Items ──────────────────
    @GET("admin/jurnal/life-items")
    suspend fun adminLifeItems(): LifeItemEnvelope

    @POST("admin/jurnal/life-items")
    suspend fun createLifeItem(@Body req: Map<String, @JvmSuppressWildcards Any?>): LifeItemSingleEnvelope

    @PUT("admin/jurnal/life-items/{id}")
    suspend fun updateLifeItem(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): LifeItemSingleEnvelope

    @DELETE("admin/jurnal/life-items/{id}")
    suspend fun deleteLifeItem(@Path("id") id: Long): MessageResponse

    // ── Admin Roles (read) ───────────────────────
    @GET("admin/roles")
    suspend fun adminRoles(): AdminRoleEnvelope

    // ── Admin cabang ─────────────────────────────
    @POST("admin/cabangs")
    suspend fun createCabang(@Body req: CabangRequest): CabangDto

    @PUT("admin/cabangs/{id}")
    suspend fun updateCabang(@Path("id") id: Long, @Body req: CabangRequest): CabangDto

    @DELETE("admin/cabangs/{id}")
    suspend fun deleteCabang(@Path("id") id: Long): MessageResponse

    // ── Auth: Google mobile (id_token) ──
    @POST("auth/google")
    suspend fun googleMobileLogin(@Body req: Map<String, @JvmSuppressWildcards String>): AuthResponse

    // ── CV ──
    @GET("cv")
    suspend fun cv(): CvDto

    @PUT("cv")
    suspend fun updateCv(@Body req: Map<String, @JvmSuppressWildcards Any?>): CvDto

    @GET("profil/{username}/cv")
    suspend fun publicCv(@Path("username") username: String): PublicCvResponse

    @GET("profil/{username}/kartu-nama")
    suspend fun kartuNama(@Path("username") username: String): KartuNamaResponse

    // ── Cabang detail ──
    @GET("cabangs/{slug}")
    suspend fun cabangShow(@Path("slug") slug: String): CabangDetailResponse

    // ── Admin: User CRUD ──
    @GET("admin/users/{id}")
    suspend fun adminUserShow(@Path("id") id: Long): UserDto

    @POST("admin/users")
    suspend fun adminCreateUser(@Body req: Map<String, @JvmSuppressWildcards Any?>): UserDto

    @PATCH("admin/users/{id}")
    suspend fun adminUpdateUser(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): UserDto

    @PATCH("admin/users/{id}/role")
    suspend fun adminUpdateUserRole(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): UserDto

    // ── Admin: Roles full CRUD + permissions sync ──
    @POST("admin/roles")
    suspend fun adminCreateRole(@Body req: Map<String, @JvmSuppressWildcards Any?>): AdminRoleSingleEnvelope

    @PUT("admin/roles/{id}")
    suspend fun adminUpdateRole(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): AdminRoleSingleEnvelope

    @POST("admin/roles/{id}/permissions")
    suspend fun adminSyncRolePermissions(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): AdminRoleSingleEnvelope

    @DELETE("admin/roles/{id}")
    suspend fun adminDeleteRole(@Path("id") id: Long): MessageResponse

    // ── Admin: Permissions CRUD ──
    @GET("admin/permissions")
    suspend fun adminPermissions(): PermissionEnvelope

    @POST("admin/permissions")
    suspend fun adminCreatePermission(@Body req: Map<String, @JvmSuppressWildcards Any?>): PermissionSingleEnvelope

    @PUT("admin/permissions/{id}")
    suspend fun adminUpdatePermission(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): PermissionSingleEnvelope

    @DELETE("admin/permissions/{id}")
    suspend fun adminDeletePermission(@Path("id") id: Long): MessageResponse

    // ── Admin: Bible Schedules ──
    @GET("admin/jurnal/bible-schedules")
    suspend fun adminBibleSchedules(@QueryMap params: Map<String, String> = emptyMap()): BibleScheduleEnvelope

    @POST("admin/jurnal/bible-schedules")
    suspend fun adminCreateBibleSchedule(@Body req: Map<String, @JvmSuppressWildcards Any?>): BibleScheduleSingleEnvelope

    @PUT("admin/jurnal/bible-schedules/{id}")
    suspend fun adminUpdateBibleSchedule(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): BibleScheduleSingleEnvelope

    @DELETE("admin/jurnal/bible-schedules/{id}")
    suspend fun adminDeleteBibleSchedule(@Path("id") id: Long): MessageResponse

    @POST("admin/jurnal/bible-schedules/bulk")
    suspend fun adminBulkBibleSchedule(@Body req: Map<String, @JvmSuppressWildcards Any?>): MessageResponse

    // ── Admin: Weekly Verses ──
    @GET("admin/jurnal/weekly-verses")
    suspend fun adminWeeklyVerses(@QueryMap params: Map<String, String> = emptyMap()): WeeklyVerseEnvelope

    @POST("admin/jurnal/weekly-verses")
    suspend fun adminCreateWeeklyVerse(@Body req: Map<String, @JvmSuppressWildcards Any?>): WeeklyVerseSingleEnvelope

    @PUT("admin/jurnal/weekly-verses/{id}")
    suspend fun adminUpdateWeeklyVerse(@Path("id") id: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): WeeklyVerseSingleEnvelope

    @DELETE("admin/jurnal/weekly-verses/{id}")
    suspend fun adminDeleteWeeklyVerse(@Path("id") id: Long): MessageResponse

    // ── Admin: Student Life Items sync ──
    @GET("admin/jurnal/students/{student}/life-items")
    suspend fun adminStudentLifeItems(@Path("student") studentId: Long): StudentLifeItemsResponse

    @POST("admin/jurnal/students/{student}/life-items")
    suspend fun adminSyncStudentLifeItems(@Path("student") studentId: Long, @Body req: Map<String, @JvmSuppressWildcards Any?>): MessageResponse

    // ── Admin: Jurnal Reports ──
    @GET("admin/jurnal/reports")
    suspend fun adminJurnalReports(@QueryMap params: Map<String, String> = emptyMap()): JurnalReportsIndexResponse

    @GET("admin/jurnal/reports/{student}")
    suspend fun adminJurnalReport(@Path("student") studentId: Long, @QueryMap params: Map<String, String> = emptyMap()): JurnalReportShowResponse

    // ── Admin: Name Tags ──
    @GET("admin/nametags")
    suspend fun adminNameTags(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<UserDto>

    @POST("admin/nametags/generate")
    suspend fun adminGenerateNameTags(@Body req: Map<String, @JvmSuppressWildcards Any?>): NameTagGenerateResponse

    // ── Admin: Mentor Presensi Reports ──
    @GET("admin/mentor-presensi")
    suspend fun adminMentorPresensiList(@QueryMap params: Map<String, String> = emptyMap()): AdminMentorPresensiListResponse

    @GET("admin/mentor-presensi/reports")
    suspend fun adminMentorPresensiReports(@QueryMap params: Map<String, String> = emptyMap()): MentorPresensiReportsResponse

    // ── Admin: Blog moderation ──
    @GET("admin/blogs")
    suspend fun adminBlogs(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<BlogDto>

    @DELETE("admin/blogs/{id}")
    suspend fun adminDeleteBlog(@Path("id") id: Long): MessageResponse

    // ── Admin: Comments moderation ──
    @GET("admin/comments")
    suspend fun adminComments(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<CommentDto>

    @DELETE("admin/comments/{id}")
    suspend fun adminDeleteComment(@Path("id") id: Long): MessageResponse
}
