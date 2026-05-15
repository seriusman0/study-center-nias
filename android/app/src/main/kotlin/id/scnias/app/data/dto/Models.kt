package id.scnias.app.data.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

// ──────────────────────────────────────────────
// Auth
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class LoginRequest(val login: String, val password: String)

@JsonClass(generateAdapter = true)
data class RegisterRequest(val name: String, val email: String, val password: String)

@JsonClass(generateAdapter = true)
data class AuthResponse(val user: UserDto, val token: String)

@JsonClass(generateAdapter = true)
data class UserDto(
    val id: Long,
    val name: String,
    val username: String?,
    val email: String?,
    val avatar: String?,
    val bio: String?,
    @Json(name = "cabang_id") val cabangId: Long?,
    val cabang: CabangDto?,
    val roles: List<RoleDto>?,
    @Json(name = "role_names") val roleNames: List<String>?,
    @Json(name = "is_active") val isActive: Boolean?,
    @Json(name = "profile_public") val profilePublic: Boolean?,
    @Json(name = "cv_enabled") val cvEnabled: Boolean?,
    @Json(name = "social_links") val socialLinks: List<SocialLinkDto>?,
)

@JsonClass(generateAdapter = true)
data class RoleDto(val id: Long, val name: String)

@JsonClass(generateAdapter = true)
data class CabangDto(
    val id: Long,
    val nama: String,
    val slug: String?,
    val alamat: String?,
    val kontak: String?,
)

@JsonClass(generateAdapter = true)
data class SocialLinkDto(
    val id: Long? = null,
    val platform: String,
    val value: String,
)

// ──────────────────────────────────────────────
// Pagination (generic)
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class PaginatedDto<T>(
    val data: List<T>,
    val meta: PageMeta?,
)

@JsonClass(generateAdapter = true)
data class PageMeta(
    @Json(name = "current_page") val currentPage: Int,
    @Json(name = "last_page")    val lastPage: Int,
    @Json(name = "per_page")     val perPage: Int,
    val total: Int,
)

// ──────────────────────────────────────────────
// Blog
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class BlogDto(
    val id: Long,
    val title: String,
    val slug: String,
    val content: String?,
    val image: String?,
    @Json(name = "published_at") val publishedAt: String?,
    val user: UserDto?,
    val cabang: CabangDto?,
    val tags: List<TagDto>?,
)

@JsonClass(generateAdapter = true)
data class TagDto(val id: Long, val name: String, val slug: String?)

// ──────────────────────────────────────────────
// Comment
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class CommentDto(
    val id: Long,
    val content: String,
    @Json(name = "blog_id") val blogId: Long?,
    @Json(name = "user_id") val userId: Long?,
    @Json(name = "parent_id") val parentId: Long?,
    val user: UserDto?,
    val replies: List<CommentDto>?,
    @Json(name = "created_at") val createdAt: String?,
)

@JsonClass(generateAdapter = true)
data class CommentCreateRequest(
    val content: String,
    @Json(name = "parent_id") val parentId: Long? = null,
)

// ──────────────────────────────────────────────
// Profile
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class ProfileResponse(
    val user: UserDto,
    val blogs: List<BlogDto>?,
)

// ──────────────────────────────────────────────
// Jurnal
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class JurnalSnapshotDto(
    val date: String,
    val week: JurnalWeekDto?,
    val bible: JurnalBibleDto?,
    val verse: JurnalVerseDto?,
    @Json(name = "life_items") val lifeItems: List<JurnalLifeItemDto>?,
)

@JsonClass(generateAdapter = true)
data class JurnalWeekDto(val tahun: Int, val bulan: Int, val minggu: Int, val key: String)

@JsonClass(generateAdapter = true)
data class JurnalBibleDto(
    @Json(name = "pl_porsi")   val plPorsi: String?,
    @Json(name = "pb_porsi")   val pbPorsi: String?,
    @Json(name = "pl_checked") val plChecked: Boolean,
    @Json(name = "pb_checked") val pbChecked: Boolean,
)

@JsonClass(generateAdapter = true)
data class JurnalVerseDto(val referensi: String?, val isi: String?, val checked: Boolean)

@JsonClass(generateAdapter = true)
data class JurnalLifeItemDto(
    val id: Long,
    val kategori: String,
    val label: String,
    val checked: Boolean,
)

@JsonClass(generateAdapter = true)
data class JurnalCheckRequest(
    @Json(name = "item_type") val itemType: String,
    @Json(name = "item_id")   val itemId: Long? = null,
    val date: String? = null,
    val checked: Boolean,
)

@JsonClass(generateAdapter = true)
data class JurnalHistoryDay(
    val date: String,
    @Json(name = "pl_checked") val plChecked: Boolean,
    @Json(name = "pb_checked") val pbChecked: Boolean,
    @Json(name = "verse_checked") val verseChecked: Boolean,
    @Json(name = "life_checked_ids") val lifeCheckedIds: List<Long>,
)

@JsonClass(generateAdapter = true)
data class JurnalHistoryEnvelope(val data: List<JurnalHistoryDay>)

// ──────────────────────────────────────────────
// Kelas master
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class KelasMasterEnvelope(val data: List<KelasMasterDto>)

@JsonClass(generateAdapter = true)
data class KelasMasterSingleEnvelope(val data: KelasMasterDto)

// ── Admin Jurnal Life Items ──
@JsonClass(generateAdapter = true)
data class LifeItemDto(
    val id: Long,
    val kategori: String,
    val label: String,
    @Json(name = "is_default") val isDefault: Boolean?,
    @Json(name = "is_active")  val isActive: Boolean?,
)

@JsonClass(generateAdapter = true)
data class LifeItemEnvelope(val data: List<LifeItemDto>)

@JsonClass(generateAdapter = true)
data class LifeItemSingleEnvelope(val data: LifeItemDto)

// ── Admin Roles ──
@JsonClass(generateAdapter = true)
data class AdminRoleDto(
    val id: Long,
    val name: String,
    val permissions: List<PermissionDto>?,
)

@JsonClass(generateAdapter = true)
data class PermissionDto(val id: Long, val name: String)

@JsonClass(generateAdapter = true)
data class AdminRoleEnvelope(val data: List<AdminRoleDto>)

@JsonClass(generateAdapter = true)
data class KelasMasterDto(
    val id: Long,
    val nama: String,
    @Json(name = "cabang_id") val cabangId: Long?,
    val cabang: String?,
    val keterangan: String?,
    @Json(name = "is_active") val isActive: Boolean?,
)

// ──────────────────────────────────────────────
// Mentor presensi
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class MentorPresensiEnvelope(val data: MentorPresensiDto)

@JsonClass(generateAdapter = true)
data class MentorPresensiDto(
    val id: Long,
    @Json(name = "mentor_id") val mentorId: Long?,
    @Json(name = "kelas_id")  val kelasId: Long?,
    val kelas: KelasMasterDto?,
    val cabang: CabangDto?,
    val tanggal: String,
    @Json(name = "jam_datang") val jamDatang: String,
    @Json(name = "jam_pulang") val jamPulang: String,
    @Json(name = "jumlah_murid") val jumlahMurid: Int,
    val catatan: String?,
)

@JsonClass(generateAdapter = true)
data class MentorPresensiRequest(
    @Json(name = "kelas_id")     val kelasId: Long,
    val tanggal: String,
    @Json(name = "jam_datang")   val jamDatang: String,
    @Json(name = "jam_pulang")   val jamPulang: String,
    @Json(name = "jumlah_murid") val jumlahMurid: Int,
    val catatan: String? = null,
)

// ──────────────────────────────────────────────
// Student presensi
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class PresensiDto(
    val id: Long,
    @Json(name = "mentor_id") val mentorId: Long?,
    @Json(name = "cabang_id") val cabangId: Long?,
    @Json(name = "kelas_id")  val kelasId: Long?,
    val mentor: UserDto?,
    val cabang: CabangDto?,
    @Json(name = "kelas_master") val kelasMaster: KelasMasterDto?,
    val kelas: String?,
    val tanggal: String,
    @Json(name = "jam_mulai")   val jamMulai: String,
    @Json(name = "jam_selesai") val jamSelesai: String,
    val materi: String?,
    val foto: String?,
    val students: List<PresensiStudentDto>?,
    @Json(name = "students_count") val studentsCount: Int?,
)

@JsonClass(generateAdapter = true)
data class PresensiStudentPivot(val status: String?)

@JsonClass(generateAdapter = true)
data class PresensiStudentDto(
    val id: Long,
    val name: String,
    val username: String?,
    val pivot: PresensiStudentPivot?,
)

@JsonClass(generateAdapter = true)
data class PresensiEnvelope(val data: PresensiDto)

@JsonClass(generateAdapter = true)
data class PresensiRequest(
    @Json(name = "mentor_id")      val mentorId: Long,
    @Json(name = "cabang_id")      val cabangId: Long? = null,
    @Json(name = "kelas_id")       val kelasId: Long,
    val tanggal: String,
    @Json(name = "jam_mulai")      val jamMulai: String,
    @Json(name = "jam_selesai")    val jamSelesai: String,
    val materi: String,
    @Json(name = "student_ids")    val studentIds: List<Long>,
    @Json(name = "student_status") val studentStatus: Map<String, String>? = null,
)

@JsonClass(generateAdapter = true)
data class StudentSearchDto(
    val id: Long,
    val name: String,
    val kelas: String?,
    val cabang: String?,
)

@JsonClass(generateAdapter = true)
data class StudentSearchEnvelope(val data: List<StudentSearchDto>)

// ──────────────────────────────────────────────
// Admin dashboard
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class DashboardStatsDto(
    @Json(name = "users_by_role")      val usersByRole: List<RoleCountDto>,
    @Json(name = "blogs_by_cabang")    val blogsByCabang: List<CabangCountDto>,
    @Json(name = "comments_per_month") val commentsPerMonth: List<MonthCountDto>,
    @Json(name = "total_users")        val totalUsers: Int,
    @Json(name = "total_blogs")        val totalBlogs: Int,
    @Json(name = "total_comments")     val totalComments: Int,
)

@JsonClass(generateAdapter = true)
data class RoleCountDto(val role: String, val total: Int)

@JsonClass(generateAdapter = true)
data class CabangCountDto(val cabang: String, val total: Int)

@JsonClass(generateAdapter = true)
data class MonthCountDto(val month: String, val total: Int)

// ──────────────────────────────────────────────
// Admin cabang request
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class CabangRequest(
    val nama: String,
    val alamat: String? = null,
    val kontak: String? = null,
)

// ──────────────────────────────────────────────
// Generic message envelope
// ──────────────────────────────────────────────

@JsonClass(generateAdapter = true)
data class MessageResponse(val message: String)

// ──────────────────────────────────────────────
// CV
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class CvDto(
    val id: Long? = null,
    @Json(name = "user_id") val userId: Long? = null,
    val pendidikan: List<CvPendidikan>? = null,
    val pengalaman: List<CvPengalaman>? = null,
    val keterampilan: List<String>? = null,
    val portofolio: String? = null,
    val template: String? = null,
)

@JsonClass(generateAdapter = true)
data class CvPendidikan(
    val jenjang: String,
    val institusi: String,
    @Json(name = "tahun_lulus") val tahunLulus: String? = null,
)

@JsonClass(generateAdapter = true)
data class CvPengalaman(
    val posisi: String,
    val deskripsi: String? = null,
    val tahun: String? = null,
)

@JsonClass(generateAdapter = true)
data class PublicCvResponse(val user: UserDto, val cv: CvDto?)

// ──────────────────────────────────────────────
// Kartu Nama
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class KartuNamaResponse(
    val user: KartuNamaUser,
    @Json(name = "social_links") val socialLinks: List<SocialLinkDto>?,
)

@JsonClass(generateAdapter = true)
data class KartuNamaUser(
    val id: Long,
    val name: String,
    val username: String?,
    val avatar: String?,
    val bio: String?,
    val role: String?,
    val cabang: String?,
)

// ──────────────────────────────────────────────
// Cabang detail
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class CabangDetailResponse(val cabang: CabangDto, val blogs: PaginatedDto<BlogDto>?)

// ──────────────────────────────────────────────
// Admin role + permission (mutations)
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class AdminRoleSingleEnvelope(val data: AdminRoleDto)

@JsonClass(generateAdapter = true)
data class PermissionEnvelope(val data: List<PermissionDto>)

@JsonClass(generateAdapter = true)
data class PermissionSingleEnvelope(val data: PermissionDto)

// ──────────────────────────────────────────────
// Bible schedule
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class BibleScheduleDto(
    val id: Long,
    val tanggal: String,
    @Json(name = "pl_porsi") val plPorsi: String?,
    @Json(name = "pb_porsi") val pbPorsi: String?,
)

@JsonClass(generateAdapter = true)
data class BibleScheduleEnvelope(
    val data: List<BibleScheduleDto>,
    val bulan: Int? = null,
    val tahun: Int? = null,
)

@JsonClass(generateAdapter = true)
data class BibleScheduleSingleEnvelope(val data: BibleScheduleDto)

// ──────────────────────────────────────────────
// Weekly verse
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class WeeklyVerseDto(
    val id: Long,
    val tahun: Int,
    val bulan: Int,
    val minggu: Int,
    val referensi: String,
    val isi: String,
)

@JsonClass(generateAdapter = true)
data class WeeklyVerseEnvelope(val data: List<WeeklyVerseDto>, val tahun: Int? = null)

@JsonClass(generateAdapter = true)
data class WeeklyVerseSingleEnvelope(val data: WeeklyVerseDto)

// ──────────────────────────────────────────────
// Student life items sync
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class StudentLifeItemsResponse(
    val student: KartuNamaUser? = null,
    val templates: List<LifeItemDto>?,
    val custom: List<LifeItemDto>?,
    @Json(name = "assigned_ids") val assignedIds: List<Long>?,
)

// ──────────────────────────────────────────────
// Jurnal reports
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class JurnalReportsIndexResponse(
    val students: PaginatedDto<UserDto>,
    @Json(name = "total_today") val totalToday: Int,
    @Json(name = "total_week")  val totalWeek: Int,
)

@JsonClass(generateAdapter = true)
data class JurnalReportShowResponse(
    val student: UserDto,
    val from: String,
    val to: String,
    val matrix: JurnalReportMatrix,
)

@JsonClass(generateAdapter = true)
data class JurnalReportMatrix(
    val headers: List<String>,
    val rows: List<List<String>>,
    val pct: Double,
    val checked: Int,
    val total: Int,
)

// ──────────────────────────────────────────────
// Name tag generate
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class NameTagGenerateResponse(
    @Json(name = "width_cm")  val widthCm: Double,
    @Json(name = "height_cm") val heightCm: Double,
    val students: List<UserDto>,
)

// ──────────────────────────────────────────────
// Mentor presensi admin reports
// ──────────────────────────────────────────────
@JsonClass(generateAdapter = true)
data class AdminMentorPresensiListResponse(
    val data: PaginatedDto<MentorPresensiDto>? = null,
    val from: String?,
    val to: String?,
)

@JsonClass(generateAdapter = true)
data class MentorPresensiReportsResponse(
    val totals: MentorPresensiTotals?,
    val perMentor: List<Map<String, @JvmSuppressWildcards Any?>>? = null,
    val perCabang: List<Map<String, @JvmSuppressWildcards Any?>>? = null,
    val trend: List<Map<String, @JvmSuppressWildcards Any?>>? = null,
    val from: String?,
    val to: String?,
)

@JsonClass(generateAdapter = true)
data class MentorPresensiTotals(
    val sesi: Int,
    val jam: Double,
    val murid: Int,
    @Json(name = "mentor_aktif") val mentorAktif: Int,
)
