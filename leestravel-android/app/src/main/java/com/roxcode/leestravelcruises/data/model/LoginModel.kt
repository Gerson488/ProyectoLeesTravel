package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class LoginRequest(
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String
)

data class LoginData(
    @SerializedName("Id_User") val idUser: Int,
    @SerializedName("Id_Traveler") val idTraveler: Int,
    @SerializedName("Access_Role") val role: String,
    @SerializedName("Full_Name") val fullName: String,
    @SerializedName("Email") val email: String,
    @SerializedName("Photo") val photo: String?

)