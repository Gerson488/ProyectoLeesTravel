package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.PromoPost
import retrofit2.Response
import retrofit2.http.GET

interface PromotionApi {
    @GET("Api/Promotions/GetPromotionsApp.php")
    suspend fun getPromotionsApp(): Response<ApiResponse<List<PromoPost>>>
}