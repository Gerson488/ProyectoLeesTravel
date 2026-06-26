package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class PassengerDetailRequest(
    @SerializedName("idPassengerToView") val idPassenger: Int,
    @SerializedName("idTravelerRequesting") val idTraveler: Int,
    @SerializedName("roleRequesting") val role: String
)