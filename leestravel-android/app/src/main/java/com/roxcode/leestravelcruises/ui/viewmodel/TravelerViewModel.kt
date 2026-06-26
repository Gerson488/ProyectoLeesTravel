package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.TravelerData
import com.roxcode.leestravelcruises.data.repository.TravelerRepository
import kotlinx.coroutines.launch

class TravelerViewModel : ViewModel() {
    private val repository = TravelerRepository()

    private val _traveler = MutableLiveData<TravelerData?>()
    val traveler: LiveData<TravelerData?> = _traveler

    fun fetchProfile(idTraveler: Int) {
        viewModelScope.launch {
            try {
                val response = repository.getTravelerById(idTraveler)
                if (response.isSuccessful) {
                    _traveler.value = response.body()?.data
                }
            } catch (e: Exception) {
                _traveler.value = null
            }
        }
    }
}