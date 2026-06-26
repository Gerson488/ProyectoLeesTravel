package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.HistoryModel
import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.HistoryApi
import okhttp3.ResponseBody
import retrofit2.Response

class HistoryRepository {

    private val historyApi = RetrofitClient.createService(HistoryApi::class.java)

    // 1. Obtener historial por viaje
    suspend fun getHistoryByTrip(idTrip: Int): Response<ApiResponse<List<HistoryModel>>> {
        return historyApi.getHistoryByTrip(mapOf("idTrip" to idTrip))
    }

    // 2. Obtener historial por pasajero
    suspend fun getHistoryByPassenger(idPassenger: Int): Response<ApiResponse<List<HistoryModel>>> {
        return historyApi.getHistoryByPassenger(mapOf("idPassenger" to idPassenger))
    }

    // 3. Registrar nueva incidencia
    suspend fun registerHistory(history: HistoryModel): Response<ResponseBody> {
        return historyApi.registerHistory(history)
    }

    // 4. Actualizar incidencia
    suspend fun updateHistory(history: HistoryModel): Response<ResponseBody> {
        return historyApi.updateHistory(history)
    }

    // 5. Eliminar incidencia
    suspend fun deleteHistory(idHistory: Int): Response<ResponseBody> {
        return historyApi.deleteHistory(mapOf("idHistory" to idHistory))
    }
}