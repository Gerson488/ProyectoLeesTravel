package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class PublicPublication(
    @SerializedName("Id_Post") val idPost: String,
    @SerializedName("Id_Trip") val idTrip: Int?,
    @SerializedName("Id_User") val idUser: Int?,
    @SerializedName("Title") val title: String,
    @SerializedName("Description") val description: String,
    @SerializedName("Published_Date") val publishedDate: String?,
    @SerializedName("First_Name") val firstName: String,
    @SerializedName("Last_Name") val lastName: String,
    @SerializedName("gallery") val gallery: List<String> = emptyList()
)