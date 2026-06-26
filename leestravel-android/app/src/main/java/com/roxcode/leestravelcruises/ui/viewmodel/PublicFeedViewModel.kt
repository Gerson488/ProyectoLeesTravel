package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.PromoPost
import com.roxcode.leestravelcruises.data.model.PublicPublication
import com.roxcode.leestravelcruises.data.repository.PromotionRepository
import com.roxcode.leestravelcruises.data.repository.PublicPublicationRepository
import kotlinx.coroutines.launch

class PublicFeedViewModel : ViewModel() {

    private val pubRepository = PublicPublicationRepository()
    private val promoRepository = PromotionRepository()

    private val _feed = MutableLiveData<List<PublicPublication>>()
    val feed: LiveData<List<PublicPublication>> = _feed

    private val _promotions = MutableLiveData<List<PromoPost>>()
    val promotions: LiveData<List<PromoPost>> = _promotions

    private val _postDetail = MutableLiveData<PublicPublication?>()
    val postDetail: LiveData<PublicPublication?> = _postDetail

    fun fetchPublicFeed() {
        viewModelScope.launch {
            try {
                val response = pubRepository.getPublicFeed()
                if (response.isSuccessful) {
                    _feed.value = response.body()?.data ?: emptyList()
                }
            } catch (e: Exception) {
                _feed.value = emptyList()
            }
        }
    }

    fun fetchPromotions() {
        viewModelScope.launch {
            try {
                val response = promoRepository.getPromotionsApp()
                if (response.isSuccessful) {
                    _promotions.value = response.body()?.data ?: emptyList()
                }
            } catch (e: Exception) {
                _promotions.value = emptyList()
            }
        }
    }

    fun fetchPublicationDetail(idPost: String) {
        viewModelScope.launch {
            try {
                val response = pubRepository.getPublicationDetail(idPost)
                if (response.isSuccessful) {
                    _postDetail.value = response.body()?.data
                }
            } catch (e: Exception) {
                _postDetail.value = null
            }
        }
    }
}