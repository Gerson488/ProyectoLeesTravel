package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.PromotionApi

class PromotionRepository {
    private val api = RetrofitClient.createService(PromotionApi::class.java)

    suspend fun getPromotionsApp() = api.getPromotionsApp()
}