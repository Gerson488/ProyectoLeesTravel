package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.Trip
import com.roxcode.leestravelcruises.data.repository.TripRepository
import kotlinx.coroutines.launch
import org.json.JSONObject

class TripsViewModel : ViewModel() {
    private val repository = TripRepository()

    private var fullList: List<Trip> = emptyList()

    private val _trips = MutableLiveData<List<Trip>?>()
    val trips: LiveData<List<Trip>?> = _trips
    val isLoading = MutableLiveData<Boolean>()
    val error = MutableLiveData<String>()
    val tripStatusUpdateSuccess = MutableLiveData<String>()

    fun fetchTrips(idTraveler: Int) {
        viewModelScope.launch {
            try {
                val response = repository.getTripsByTraveler(idTraveler)
                if (response.isSuccessful) {
                    val data = response.body()?.data ?: emptyList()
                    fullList = data
                    _trips.value = fullList
                }
            } catch (e: Exception) {
                _trips.value = emptyList()
            }
        }
    }
    fun filterTrips(statusName: String) {
        if (statusName == "Todos") {
            _trips.value = fullList
        } else {
            _trips.value = fullList.filter { trip ->
                when (statusName) {
                    "En curso"    -> trip.tripStatus.equals("En Curso", ignoreCase = true)
                    "Programado"  -> trip.tripStatus.equals("Programado", ignoreCase = true)
                    "Finalizado"  -> trip.tripStatus.equals("Finalizado", ignoreCase = true)
                    else          -> true
                }
            }
        }
    }
    fun updateTripStatus(idTrip: Int, newStatus: String) {
        isLoading.value = true
        viewModelScope.launch {
            try {
                val response = repository.updateTripStatus(idTrip, newStatus)

                if (response.isSuccessful && response.body() != null) {
                    val rawJson = response.body()!!.string()
                    val jsonObject = JSONObject(rawJson)

                    val statusCode = jsonObject.optInt("status", 200)
                    val message = jsonObject.optString("message", "Estado del viaje actualizado correctamente.")

                    if (statusCode == 200 || jsonObject.optBoolean("success", false)) {
                        fullList = fullList.map { trip ->
                            if (trip.idTrip == idTrip) trip.copy(tripStatus = newStatus) else trip
                        }
                        _trips.value = fullList

                        tripStatusUpdateSuccess.postValue(newStatus)
                    } else {
                        error.postValue(message)
                    }
                } else {
                    error.postValue("Error en el servidor: Código ${response.code()}")
                }
            } catch (e: Exception) {
                error.postValue("Error de conexión: ${e.message}")
            } finally {
                isLoading.value = false
            }
        }
    }
}