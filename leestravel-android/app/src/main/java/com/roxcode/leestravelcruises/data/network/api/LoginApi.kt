package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.LoginData
import com.roxcode.leestravelcruises.data.model.LoginRequest
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface LoginApi {
    @POST("Api/Users/Login.php")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<ApiResponse<LoginData>>
}