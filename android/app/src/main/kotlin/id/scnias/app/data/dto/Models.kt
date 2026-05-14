package id.scnias.app.data.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

// ---- Auth ----

@JsonClass(generateAdapter = true)
data class LoginRequest(val email: String, val password: String)

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

// ---- Pagination ----

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

// ---- Blog ----

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
)

// ---- Jurnal ----

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
    @Json(name = "item_type") val itemType: String,   // pl|pb|verse|life
    @Json(name = "item_id")   val itemId: Long? = null,
    val date: String? = null,
    val checked: Boolean,
)

// ---- Kelas master ----

@JsonClass(generateAdapter = true)
data class KelasMasterEnvelope(val data: List<KelasMasterDto>)

@JsonClass(generateAdapter = true)
data class KelasMasterDto(
    val id: Long,
    val nama: String,
    @Json(name = "cabang_id") val cabangId: Long?,
    val cabang: String?,
    val keterangan: String?,
    @Json(name = "is_active") val isActive: Boolean?,
)

// ---- Mentor presensi ----

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
