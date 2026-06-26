package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName
import java.io.Serializable

data class PassengerModel(
    @SerializedName("Id_Passenger") val idPassenger: Int,
    @SerializedName("Id_Traveler") val idTraveler: Int,
    @SerializedName("First_Name") val firstName: String,
    @SerializedName("Last_Name") val lastName: String,
    @SerializedName("Age") val age: Int = 0,
    @SerializedName("Birth_Date") val birthDate: String? = null,
    @SerializedName("Gender") val gender: String? = null,
    @SerializedName("Nationality") val nationality: String? = null,
    @SerializedName("Document_Type") val documentType: String? = null,
    @SerializedName("Id_Card_Passport") val idCardPassport: String,
    @SerializedName("Cabin_Number") val cabinNumber: String? = null,
    @SerializedName("Blood_Type") val bloodType: String? = null,
    @SerializedName("Allergies") val allergies: String? = null,
    @SerializedName("Chronic_Diseases") val chronicDiseases: String? = null,
    @SerializedName("Observations") val observations: String? = null,
    @SerializedName("Special_Assistance") val specialAssistance: String? = null,
    @SerializedName("Boarding_Status") val boardingStatus: String? = null,
    @SerializedName("Access_Role") val accessRole: String? = null
) : Serializable