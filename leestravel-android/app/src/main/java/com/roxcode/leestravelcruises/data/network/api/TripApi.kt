package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.Trip
import com.roxcode.leestravelcruises.data.model.TripRequest
import com.roxcode.leestravelcruises.data.model.UpdateTripStatusRequest // 🚀 NUEVO IMPORT
import okhttp3.ResponseBody // 🚀 NUEVO IMPORT
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface TripApi {
    @POST("Api/Trips/GetMyTrips.php")
    suspend fun getTrips(
        @Body request: TripRequest
    ): Response<ApiResponse<List<Trip>>>
    @POST("Api/Trips/UpdateTripStatusApp.php")
    suspend fun updateTripStatus(
        @Body request: UpdateTripStatusRequest
    ): Response<ResponseBody>
}