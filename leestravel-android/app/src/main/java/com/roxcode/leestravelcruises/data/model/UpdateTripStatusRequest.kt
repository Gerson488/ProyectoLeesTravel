package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class UpdateTripStatusRequest(
    @SerializedName("idTrip")
    val idTrip: Int,

    @SerializedName("status")
    val status: String
)