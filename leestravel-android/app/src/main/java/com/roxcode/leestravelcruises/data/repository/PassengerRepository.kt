package com.roxcode.leestravelcruises.data.repository

import android.util.Log
import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.PassengerModel
import com.roxcode.leestravelcruises.data.model.PassengerResponse
import com.roxcode.leestravelcruises.data.model.PassengerDetailRequest
import com.roxcode.leestravelcruises.data.model.UpdateBoardingRequest
import com.roxcode.leestravelcruises.data.network.api.PassengerApi
import com.roxcode.leestravelcruises.data.network.RetrofitClient
import okhttp3.ResponseBody
import retrofit2.Response

class PassengerRepository {

    private val passengerApi = RetrofitClient.createService(PassengerApi::class.java)

    suspend fun getPassengersByTrip(idTrip: Int): Response<PassengerResponse> {
        val response = passengerApi.getPassengersByTrip(mapOf("Id_Trip" to idTrip))
        if (response.isSuccessful) {
            val lista = response.body()?.data
            Log.d("DEBUG_REPO", "ID Trip: $idTrip | Pasajeros recibidos: ${lista?.size ?: 0}")
            lista?.forEachIndexed { index, p ->
                Log.d("DEBUG_REPO", "[$index] Pasajero: ${p.firstName} (ID: ${p.idPassenger})")
            }
        } else {
            Log.e("DEBUG_REPO", "Error en API: ${response.code()} - ${response.message()}")
        }

        return response
    }

    suspend fun getPassengerDetail(idPass: Int, idTrav: Int, role: String): Response<ApiResponse<PassengerModel>> {
        val request = PassengerDetailRequest(idPass, idTrav, role)
        return passengerApi.getPassengerDetail(request)
    }

    suspend fun updatePassengerBoarding(idTrip: Int, idPassenger: Int, boardingStatus: String): Response<ResponseBody> {
        val request = UpdateBoardingRequest(
            idTrip = idTrip,
            idPassenger = idPassenger,
            boardingStatus = boardingStatus
        )
        return passengerApi.updateBoardingStatus(request)
    }
}