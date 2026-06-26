package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class HistoryModel(
    @SerializedName("Id_History") val idHistory: Int? = null,
    @SerializedName("Id_Trip") val idTrip: Int,
    @SerializedName("Id_Passenger") val idPassenger: Int,
    @SerializedName("Event_Description") val eventDescription: String,
    @SerializedName("Event_Date") val eventDate: String? = null,
    @SerializedName("Id_Guia_User") val idGuiaUser: Int,
    @SerializedName("Cabin_Number") val cabinNumber: String? = null,
    @SerializedName("Id_Card_Passport") val idCardPassport: String? = null,
    @SerializedName("Passenger_Name") val passengerName: String? = null,
    @SerializedName("Guia_Name") val guiaName: String? = null
)