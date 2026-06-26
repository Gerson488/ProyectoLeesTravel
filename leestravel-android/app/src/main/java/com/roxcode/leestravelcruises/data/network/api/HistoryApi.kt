package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.HistoryModel // El modelo que creamos en el paso 1
import okhttp3.ResponseBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface HistoryApi {

    // 1. Listar historial por crucero (Para el mostrador principal)
    @POST("Api/History/GetHistoryByTrip.php")
    suspend fun getHistoryByTrip(
        @Body request: Map<String, Int>
    ): Response<ApiResponse<List<HistoryModel>>>

    // 2. Listar historial por pasajero
    @POST("Api/History/GetHistory.php")
    suspend fun getHistoryByPassenger(
        @Body request: Map<String, Int>
    ): Response<ApiResponse<List<HistoryModel>>>

    // 3. Registrar nueva incidencia (La médica)
    @POST("Api/History/RegisterHistory.php")
    suspend fun registerHistory(
        @Body request: HistoryModel
    ): Response<ResponseBody>

    // 4. Actualizar incidencia
    @POST("Api/History/UpdateHistory.php")
    suspend fun updateHistory(
        @Body request: HistoryModel
    ): Response<ResponseBody>

    // 5. Eliminar incidencia
    @POST("Api/History/DeleteHistory.php")
    suspend fun deleteHistory(
        @Body request: Map<String, Int>
    ): Response<ResponseBody>
}