package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.TravelerData
import com.roxcode.leestravelcruises.data.model.TravelerRequest
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface TravelerApi {
    @POST("Api/Travelers/GetTravelerById.php")
    suspend fun getTravelerById(
        @Body request: TravelerRequest
    ): Response<ApiResponse<TravelerData>>
}