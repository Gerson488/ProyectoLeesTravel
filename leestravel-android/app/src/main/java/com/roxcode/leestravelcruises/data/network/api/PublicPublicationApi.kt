package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.PublicPublication
import retrofit2.Response
import retrofit2.http.GET
import retrofit2.http.Query

interface PublicPublicationApi {
    @GET("Api/Publication/GetPublicFeedApp.php")
    suspend fun getPublicFeed(): Response<ApiResponse<List<PublicPublication>>>

    @GET("Api/Publication/GetPublicationDetail.php")
    suspend fun getPublicationDetail(
        @Query("idPost") idPost: String
    ): Response<ApiResponse<PublicPublication>>
}