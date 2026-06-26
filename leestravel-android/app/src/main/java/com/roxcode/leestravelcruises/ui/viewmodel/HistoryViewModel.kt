package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.HistoryModel
import com.roxcode.leestravelcruises.data.repository.HistoryRepository
import kotlinx.coroutines.launch
import org.json.JSONObject

class HistoryViewModel : ViewModel() {

    private val repository = HistoryRepository()

    val historyList = MutableLiveData<List<HistoryModel>?>()
    val isLoading = MutableLiveData<Boolean>()
    val error = MutableLiveData<String>()
    val operationSuccess = MutableLiveData<String?>()

    // 1. Obtener el historial para la tabla principal (Mostrador)
    fun getHistoryByTrip(idTrip: Int) {
        isLoading.value = true
        viewModelScope.launch {
            try {
                val response = repository.getHistoryByTrip(idTrip)
                if (response.isSuccessful) {
                    historyList.postValue(response.body()?.data)
                } else {
                    error.postValue("Error al cargar historial: ${response.code()}")
                }
            } catch (e: Exception) {
                error.postValue("Error: ${e.message}")
            } finally {
                isLoading.value = false
            }
        }
    }

    // 2. Registrar nueva incidencia médica
    // Versión más limpia y segura
    fun registerLog(history: HistoryModel) {
        isLoading.value = true
        viewModelScope.launch {
            try {
                val response = repository.registerHistory(history)
                if (response.isSuccessful) {
                    operationSuccess.postValue("Registro exitoso")
                } else {
                    error.postValue("Error al guardar: ${response.code()}")
                }
            } catch (e: Exception) {
                error.postValue("Error de conexión: ${e.message}")
            } finally {
                isLoading.value = false
            }
        }
    }
    fun clearSuccessState() {
        operationSuccess.value = null
    }
}