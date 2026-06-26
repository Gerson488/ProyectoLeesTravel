package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.model.TravelerRequest
import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.TravelerApi

class TravelerRepository {
    private val api = RetrofitClient.createService(TravelerApi::class.java)

    suspend fun getTravelerById(id: Int) =
        api.getTravelerById(TravelerRequest(id))
}