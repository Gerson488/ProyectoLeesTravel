package com.roxcode.leestravelcruises.data.repository

import com.roxcode.leestravelcruises.data.network.RetrofitClient
import com.roxcode.leestravelcruises.data.network.api.PublicPublicationApi

class PublicPublicationRepository {

    private val api = RetrofitClient.createService(PublicPublicationApi::class.java)

    suspend fun getPublicFeed() = api.getPublicFeed()

    suspend fun getPublicationDetail(idPost: String) = api.getPublicationDetail(idPost)
}