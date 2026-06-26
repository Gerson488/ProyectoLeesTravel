package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.model.TripRequest
import com.roxcode.leestravelcruises.data.model.UpdateTripStatusRequest // 🚀 NUEVO IMPORT
import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.TripApi
import okhttp3.ResponseBody // 🚀 NUEVO IMPORT
import retrofit2.Response // 🚀 NUEVO IMPORT

class TripRepository {
    private val api = RetrofitClient.createService(TripApi::class.java)

    suspend fun getTripsByTraveler(id: Int) =
        api.getTrips(TripRequest(id))
    suspend fun updateTripStatus(idTrip: Int, status: String): Response<ResponseBody> {
        val request = UpdateTripStatusRequest(idTrip = idTrip, status = status)
        return api.updateTripStatus(request)
    }
}