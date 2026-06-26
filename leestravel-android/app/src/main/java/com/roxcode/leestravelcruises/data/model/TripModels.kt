package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class TripRequest(
    @SerializedName("idTraveler") val idTraveler: Int
)
data class Trip(
    @SerializedName("Id_Trip") val idTrip: Int,
    @SerializedName("Departure_Port") val origin: String,
    @SerializedName("Destination_Name") val destination: String,
    @SerializedName("Start_Date") val departureDate: String,
    @SerializedName("Ship_Name") val shipName: String,
    @SerializedName("Boarding_Status") val boardingStatus: String,
    @SerializedName("Status") val tripStatus: String,
    @SerializedName("Trip_Photo") val tripPhoto: String?
)