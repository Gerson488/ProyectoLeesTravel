package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName
import java.io.Serializable

data class ItineraryModel(
    @SerializedName("Id_Itinerary") val idItinerary: Int,
    @SerializedName("Id_Trip") val idTrip: Int,
    @SerializedName("Day_Number") val dayNumber: Int,
    @SerializedName("Port_of_Call") val port: String,
    @SerializedName("Activity_Description") val description: String,
    @SerializedName("Arrival_Time") val arrivalTime: String?,
    @SerializedName("Departure_Time") val departureTime: String?,
    @SerializedName("requires_attendance") val requiresAttendance: Int
) : Serializable

data class ItineraryResponse(
    @SerializedName("status") val status: Int,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<ItineraryModel>
)