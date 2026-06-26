package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class TravelerRequest(
    @SerializedName("idTraveler") val idTraveler: Int
)

data class TravelerData(
    @SerializedName("Id_Traveler") val idTraveler: Int,
    @SerializedName("First_Name") val firstName: String,
    @SerializedName("Last_Name") val lastName: String,
    @SerializedName("Birth_Date") val birthDate: String,
    @SerializedName("Gender") val gender: String,
    @SerializedName("Nationality") val nationality: String,
    @SerializedName("Document_Type") val documentType: String,
    @SerializedName("Id_Card_Passport") val idCardPassport: String,
    @SerializedName("Emergency_Contact") val emergencyContact: String?,
    @SerializedName("Emergency_Phone") val emergencyPhone: String?
)