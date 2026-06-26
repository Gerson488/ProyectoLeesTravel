package com.roxcode.leestravelcruises.utils

import android.content.Context
import android.content.SharedPreferences

class SessionManager(context: Context) {
    private val prefs: SharedPreferences = context.getSharedPreferences("LeesTravelPrefs", Context.MODE_PRIVATE)

    fun saveSession(idUser: Int, idTraveler: Int, role: String, fullName: String) {
        val editor = prefs.edit()
        editor.putInt("id_user", idUser)
        editor.putInt("id_traveler", idTraveler)
        editor.putString("USER_ROLE", role)
        editor.putString("USER_NAME", fullName)
        editor.apply()
    }

    fun getUserId(): Int {
        return prefs.getInt("id_user", -1)
    }

    fun getIdTraveler(): Int {
        return prefs.getInt("id_traveler", -1)
    }

    fun getUserRole(): String {
        return prefs.getString("USER_ROLE", "Pasajero") ?: "Pasajero"
    }

    fun getUserName(): String {
        return prefs.getString("USER_NAME", "Guía") ?: "Guía"
    }

    fun isLoggedIn(): Boolean {
        return getUserId() != -1
    }

    fun clear() {
        prefs.edit().clear().apply()
    }
}