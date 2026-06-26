package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.PublicationPost
import com.roxcode.leestravelcruises.data.repository.PublicationRepository
import kotlinx.coroutines.launch

class PublicationViewModel : ViewModel() {
    private val repository = PublicationRepository()

    private val _publications = MutableLiveData<List<PublicationPost>>()
    val publications: LiveData<List<PublicationPost>> = _publications

    fun fetchPublications(idTrip: Int, idUser: Int) {
        viewModelScope.launch {
            try {
                val response = repository.getPublications(idTrip, idUser)
                if (response.isSuccessful) {
                    _publications.value = response.body()?.data ?: emptyList()
                } else {
                    _publications.value = emptyList()
                }
            } catch (e: Exception) {
                _publications.value = emptyList()
            }
        }
    }
}