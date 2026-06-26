package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.PassengerModel
import com.roxcode.leestravelcruises.data.repository.PassengerRepository
import kotlinx.coroutines.launch
import org.json.JSONObject

class PassengerViewModel : ViewModel() {

    private val repository = PassengerRepository()

    val passengers = MutableLiveData<List<PassengerModel>?>()
    val passengerDetail = MutableLiveData<PassengerModel?>()

    val isLoading = MutableLiveData<Boolean>()
    val error = MutableLiveData<String>()
    val boardingSuccess = MutableLiveData<String?>()

    fun getPassengers(idTrip: Int) {
        isLoading.value = true

        passengers.postValue(null)

        viewModelScope.launch {
            try {
                val response = repository.getPassengersByTrip(idTrip)
                if (response.isSuccessful) {
                    val data = response.body()?.data ?: emptyList()
                    val uniqueData = data.distinctBy { it.idTraveler }
                    passengers.postValue(uniqueData)
                } else {
                    error.postValue("Error: ${response.code()}")
                }
            } catch (e: Exception) {
                error.postValue(e.message)
            } finally {
                isLoading.value = false
            }
        }
    }

    fun loadPassengerDetail(idPassengerToView: Int, idTravelerRequesting: Int, roleRequesting: String) {
        isLoading.value = true
        viewModelScope.launch {
            try {
                val response = repository.getPassengerDetail(
                    idPassengerToView,
                    idTravelerRequesting,
                    roleRequesting
                )

                if (response.isSuccessful && response.body()?.status == 200) {
                    passengerDetail.postValue(response.body()?.data)
                } else {
                    error.postValue(response.body()?.message ?: "Error al cargar detalle")
                }
            } catch (e: Exception) {
                error.postValue(e.message)
            } finally {
                isLoading.value = false
            }
        }
    }
    fun updateBoarding(idTrip: Int, idPassenger: Int, status: String) {
        isLoading.value = true
        viewModelScope.launch {
            try {
                val response = repository.updatePassengerBoarding(idTrip, idPassenger, status)

                if (response.isSuccessful && response.body() != null) {
                    val rawJson = response.body()!!.string()
                    val jsonObject = JSONObject(rawJson)
                    val statusCode = jsonObject.optInt("status", 200)
                    val message = jsonObject.optString("message", "Asistencia actualizada con éxito")

                    if (statusCode == 200 || jsonObject.optBoolean("success", false)) {
                        boardingSuccess.postValue(message)
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