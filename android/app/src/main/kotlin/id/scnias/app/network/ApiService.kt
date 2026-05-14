package id.scnias.app.network

import id.scnias.app.data.dto.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    // Auth
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

    // Blog
    @GET("blogs")
    suspend fun blogs(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<BlogDto>

    @GET("blogs/{slug}")
    suspend fun blog(@Path("slug") slug: String): BlogDto

    @GET("cabangs")
    suspend fun cabangs(): List<CabangDto>

    // Jurnal (student)
    @GET("jurnal/today")
    suspend fun jurnalToday(@Query("date") date: String? = null): JurnalSnapshotDto

    @POST("jurnal/check")
    suspend fun jurnalCheck(@Body req: JurnalCheckRequest): Response<Unit>

    // Kelas master (mentor uses for dropdown)
    @GET("kelas-master")
    suspend fun kelasMaster(@QueryMap params: Map<String, String> = emptyMap()): KelasMasterEnvelope

    // Mentor presensi
    @GET("mentor-presensi")
    suspend fun mentorPresensi(@QueryMap params: Map<String, String> = emptyMap()): PaginatedDto<MentorPresensiDto>

    @POST("mentor-presensi")
    suspend fun createMentorPresensi(@Body req: MentorPresensiRequest): MentorPresensiEnvelope

    @PUT("mentor-presensi/{id}")
    suspend fun updateMentorPresensi(@Path("id") id: Long, @Body req: MentorPresensiRequest): MentorPresensiEnvelope

    @DELETE("mentor-presensi/{id}")
    suspend fun deleteMentorPresensi(@Path("id") id: Long): Response<Unit>
}
