package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.PublicationApi
import okhttp3.MultipartBody
import okhttp3.RequestBody

class PublicationRepository {
    private val api = RetrofitClient.createService(PublicationApi::class.java)

    suspend fun getPublications(idTrip: Int, idUser: Int) =
        api.getPublications(idTrip, idUser)

    suspend fun registerPublication(
        idTrip: RequestBody,
        idUser: RequestBody,
        title: RequestBody,
        description: RequestBody,
        latitude: RequestBody,
        longitude: RequestBody,
        images: List<MultipartBody.Part>
    ) = api.registerPublication(idTrip, idUser, title, description, latitude, longitude, images)

    suspend fun updatePublication(
        idPost: RequestBody,
        idUser: RequestBody,
        title: RequestBody,
        description: RequestBody,
        latitude: RequestBody,
        longitude: RequestBody,
        retainedImages: RequestBody,
        images: List<MultipartBody.Part>?
    ) = api.updatePublication(idPost, idUser, title, description, latitude, longitude, retainedImages, images)

    suspend fun deletePublication(idPost: String, idUser: Int) =
        api.deletePublication(idPost, idUser)
}