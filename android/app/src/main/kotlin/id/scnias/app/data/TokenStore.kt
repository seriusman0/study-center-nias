package id.scnias.app.data

import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey

class TokenStore(ctx: Context) {
    private val prefs = EncryptedSharedPreferences.create(
        ctx,
        "sc_nias_secure",
        MasterKey.Builder(ctx).setKeyScheme(MasterKey.KeyScheme.AES256_GCM).build(),
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM,
    )

    var token: String?
        get() = prefs.getString(KEY_TOKEN, null)
        set(value) = prefs.edit().apply {
            if (value == null) remove(KEY_TOKEN) else putString(KEY_TOKEN, value)
        }.apply()

    var primaryRole: String?
        get() = prefs.getString(KEY_ROLE, null)
        set(value) = prefs.edit().apply {
            if (value == null) remove(KEY_ROLE) else putString(KEY_ROLE, value)
        }.apply()

    var userName: String?
        get() = prefs.getString(KEY_NAME, null)
        set(value) = prefs.edit().apply {
            if (value == null) remove(KEY_NAME) else putString(KEY_NAME, value)
        }.apply()

    var userUsername: String?
        get() = prefs.getString(KEY_USERNAME, null)
        set(value) = prefs.edit().apply {
            if (value == null) remove(KEY_USERNAME) else putString(KEY_USERNAME, value)
        }.apply()

    var userId: Long?
        get() = prefs.getLong(KEY_ID, -1L).takeIf { it != -1L }
        set(value) = prefs.edit().apply {
            if (value == null) remove(KEY_ID) else putLong(KEY_ID, value)
        }.apply()

    fun clear() {
        prefs.edit().clear().apply()
    }

    companion object {
        private const val KEY_TOKEN = "auth_token"
        private const val KEY_ROLE = "primary_role"
        private const val KEY_NAME = "user_name"
        private const val KEY_USERNAME = "user_username"
        private const val KEY_ID = "user_id"
    }
}
