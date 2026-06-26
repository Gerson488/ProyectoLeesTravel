package com.roxcode.leestravelcruises.data.model

import com.google.gson.annotations.SerializedName

data class PromoPost(
    @SerializedName("Id_Promo") val idPromo: Int,
    @SerializedName("Id_Trip") val idTrip: Int,
    @SerializedName("Title_Offer") val titleOffer: String,
    @SerializedName("Description") val description: String,
    @SerializedName("Image_Banner") val imageBanner: String?,
    @SerializedName("Action_Link") val actionLink: String?,
    @SerializedName("Special_Price_USD") val specialPriceUSD: Double?,
    @SerializedName("Start_Date") val startDate: String?,
    @SerializedName("Expiration_Date") val expirationDate: String?,
    @SerializedName("Destination_Name") val destinationName: String,
    @SerializedName("Ship_Name") val shipName: String
)