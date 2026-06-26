package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class PassengerResponse(
    @SerializedName("status") val status: Int,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<PassengerModel>
)