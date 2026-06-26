package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class PublicationPost(
    @SerializedName("Id_Post") val idPost: String,
    @SerializedName("Id_Trip") val idTrip: Int,
    @SerializedName("Id_User") val idUser: String,
    @SerializedName("Title") val title: String,
    @SerializedName("Description") val description: String,
    @SerializedName("Latitude") val latitude: String?,
    @SerializedName("Longitude") val longitude: String?,
    @SerializedName("Moderation_Status") val moderationStatus: String,
    @SerializedName("Is_Public") val isPublic: Int,
    @SerializedName("Published_Date") val publishedDate: String?,
    @SerializedName("gallery") val gallery: List<String> = emptyList()
)
data class RegisterResponse(
    @SerializedName("idPost") val idPost: String
)

data class UpdateResponse(
    @SerializedName("deleteFiles") val deleteFiles: List<String>?
)