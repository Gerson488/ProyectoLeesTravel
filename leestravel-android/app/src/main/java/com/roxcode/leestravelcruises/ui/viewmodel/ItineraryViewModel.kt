package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.ItineraryModel
import com.roxcode.leestravelcruises.data.repository.ItineraryRepository
import kotlinx.coroutines.launch

class ItineraryViewModel : ViewModel() {
    private val repository = ItineraryRepository()
    val itinerary = MutableLiveData<List<ItineraryModel>?>()
    val isLoading = MutableLiveData<Boolean>()
    val error = MutableLiveData<String>()

    fun getItinerary(idTrip: Int) {
        isLoading.value = true
        viewModelScope.launch {
            try {
                val response = repository.getItineraryByTrip(idTrip)

                // Confiamos en isSuccessful y validamos que el cuerpo no sea nulo
                if (response.isSuccessful) {
                    val body = response.body()
                    // Si tu API envuelve los datos en un campo 'data', usa eso
                    // Si la API devuelve la lista directamente, usa 'body' directamente
                    itinerary.postValue(body?.data)
                } else {
                    // Si falla, mostramos el código real (ej. 404, 500)
                    error.postValue("Error del servidor: ${response.code()}")
                }
            } catch (e: Exception) {
                error.postValue("Fallo de red: ${e.message}")
            } finally {
                isLoading.value = false
            }
        }
    }
}