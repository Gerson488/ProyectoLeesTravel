package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class UpdateBoardingRequest(
    @SerializedName("idTrip")
    val idTrip: Int,
    @SerializedName("idPassenger")
    val idPassenger: Int,
    @SerializedName("boardingStatus")
    val boardingStatus: String
)