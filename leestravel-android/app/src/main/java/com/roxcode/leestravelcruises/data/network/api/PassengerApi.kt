package com.roxcode.leestravelcruises.data.network.api // 🚀 CORREGIDO: Coincide exactamente con tu carpeta

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.PassengerModel
import com.roxcode.leestravelcruises.data.model.PassengerResponse
import com.roxcode.leestravelcruises.data.model.PassengerDetailRequest
import com.roxcode.leestravelcruises.data.model.UpdateBoardingRequest
import okhttp3.ResponseBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface PassengerApi {

    @POST("Api/Passengers/GetPassengerByTrip.php")
    suspend fun getPassengersByTrip(
        @Body request: Map<String, Int>
    ): Response<PassengerResponse>

    @POST("Api/Passengers/GetPassengerDetail.php")
    suspend fun getPassengerDetail(
        @Body request: PassengerDetailRequest
    ): Response<ApiResponse<PassengerModel>>

    @POST("Api/Passengers/UpdateBoardingApp.php")
    suspend fun updateBoardingStatus(
        @Body request: UpdateBoardingRequest
    ): Response<ResponseBody>
}