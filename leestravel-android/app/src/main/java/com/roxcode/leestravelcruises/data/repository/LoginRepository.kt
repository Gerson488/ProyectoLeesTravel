package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.model.LoginRequest
import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.LoginApi

class LoginRepository {
    private val api = RetrofitClient.createService(LoginApi::class.java)

    suspend fun login(email: String, pass: String) =
        api.login(LoginRequest(email, pass))
}