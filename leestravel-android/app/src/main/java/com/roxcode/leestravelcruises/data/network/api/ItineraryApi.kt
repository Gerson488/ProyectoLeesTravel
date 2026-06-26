package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ItineraryResponse
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface ItineraryApi {
    @POST("Api/Itineraries/GetItineraryByTrip.php")
    suspend fun getItinerary(
        @Body request: Map<String, Int>
    ): Response<ItineraryResponse>
}