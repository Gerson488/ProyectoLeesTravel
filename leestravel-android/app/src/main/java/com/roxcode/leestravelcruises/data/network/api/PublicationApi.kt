package com.roxcode.leestravelcruises.data.network.api

import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.PublicationPost
import com.roxcode.leestravelcruises.data.model.RegisterResponse
import com.roxcode.leestravelcruises.data.model.UpdateResponse
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface PublicationApi {

    @GET("Api/Publication/GetPublicationsByTrip.php")
    suspend fun getPublications(
        @Query("idTrip") idTrip: Int,
        @Query("idUser") idUser: Int
    ): Response<ApiResponse<List<PublicationPost>>>

    @Multipart
    @POST("Api/Publication/RegisterPublication.php")
    suspend fun registerPublication(
        @Part("idTrip") idTrip: RequestBody,
        @Part("idUser") idUser: RequestBody,
        @Part("title") title: RequestBody,
        @Part("description") description: RequestBody,
        @Part("latitude") latitude: RequestBody,
        @Part("longitude") longitude: RequestBody,
        @Part images: List<MultipartBody.Part>
    ): Response<ApiResponse<RegisterResponse>>

    @Multipart
    @POST("Api/Publication/UpdatePublication.php")
    suspend fun updatePublication(
        @Part("idPost") idPost: RequestBody,
        @Part("idUser") idUser: RequestBody,
        @Part("title") title: RequestBody,
        @Part("description") description: RequestBody,
        @Part("latitude") latitude: RequestBody,
        @Part("longitude") longitude: RequestBody,
        @Part("retainedImages") retainedImages: RequestBody,
        @Part images: List<MultipartBody.Part>?
    ): Response<ApiResponse<UpdateResponse>>

    @DELETE("Api/Publication/DeletePublication.php")
    suspend fun deletePublication(
        @Query("idPost") idPost: String,
        @Query("idUser") idUser: Int
    ): Response<ApiResponse<UpdateResponse>>
}