package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.ItineraryApi

class ItineraryRepository {
    private val api = RetrofitClient.createService(ItineraryApi::class.java)

    suspend fun getItineraryByTrip(idTrip: Int) = api.getItinerary(mapOf("idTrip" to idTrip))
}