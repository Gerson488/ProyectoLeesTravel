package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class BoardingRequest(
    @SerializedName("Id_Trip") val idTrip: Int,
    @SerializedName("Id_Passenger") val idPassenger: Int,
    @SerializedName("Boarding_Status") val boardingStatus: String
)

data class BoardingResponse(
    @SerializedName("status") val status: Int,
    @SerializedName("message") val message: String
)